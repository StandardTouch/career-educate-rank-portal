<?php

namespace App\Services;

use App\Models\AnalysisDataset;
use App\Models\AnalysisImport;
use App\Models\Dataset;
use App\Models\Import;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ImportDuplicateService
{
    public function existingResultDuplicate(array $meta): ?array
    {
        $dataset = Dataset::with([
            'imports' => fn ($query) => $query->with('sheets')->latest('id'),
        ])->where('slug', $meta['slug'] ?? null)->first();

        return $dataset ? $this->datasetSummary('result', $dataset) : null;
    }

    public function existingAnalysisDuplicate(array $meta): ?array
    {
        $dataset = AnalysisDataset::with([
            'imports' => fn ($query) => $query->with('sheets')->latest('id'),
        ])->where('slug', $meta['slug'] ?? null)->first();

        return $dataset ? $this->datasetSummary('analysis', $dataset) : null;
    }

    public function duplicateGroups(): array
    {
        return [
            'result' => $this->resultGroups(),
            'analysis' => $this->analysisGroups(),
        ];
    }

    public function detail(string $type, int $firstId, int $secondId): ?array
    {
        if ($type === 'result') {
            $first = Import::with(['dataset', 'sheets'])->find($firstId);
            $second = Import::with(['dataset', 'sheets'])->find($secondId);
        } elseif ($type === 'analysis') {
            $first = AnalysisImport::with(['analysisDataset', 'sheets'])->find($firstId);
            $second = AnalysisImport::with(['analysisDataset', 'sheets'])->find($secondId);
        } else {
            return null;
        }

        if (! $first || ! $second) {
            return null;
        }

        return [
            'type' => $type,
            'label' => $type === 'result' ? 'Result Import' : 'Predicted Rank Import',
            'first' => $this->importSummary($type, $first),
            'second' => $this->importSummary($type, $second),
            'sheet_comparison' => $this->sheetComparison($first->sheets, $second->sheets),
            'row_samples' => $this->rowSamples($type, $first, $second),
        ];
    }

    protected function resultGroups(): Collection
    {
        $imports = Import::with(['dataset', 'sheets'])
            ->whereHas('dataset')
            ->latest('id')
            ->get();

        return $this->groupsFromImports('result', $imports);
    }

    protected function analysisGroups(): Collection
    {
        $imports = AnalysisImport::with(['analysisDataset', 'sheets'])
            ->whereHas('analysisDataset')
            ->latest('id')
            ->get();

        return $this->groupsFromImports('analysis', $imports);
    }

    protected function groupsFromImports(string $type, Collection $imports): Collection
    {
        return $imports
            ->groupBy(fn ($import) => $this->duplicateKey($type, $import))
            ->filter(fn (Collection $group) => $group->count() > 1)
            ->map(function (Collection $group) use ($type): array {
                $items = $group->values();
                $first = $items->get(0);
                $second = $items->get(1);

                return [
                    'type' => $type,
                    'label' => $type === 'result' ? 'Result imports' : 'Predicted rank imports',
                    'title' => $this->datasetForImport($type, $first)?->title ?? $first->original_filename,
                    'count' => $items->count(),
                    'first' => $this->importSummary($type, $first),
                    'second' => $this->importSummary($type, $second),
                    'items' => $items->map(fn ($import) => $this->importSummary($type, $import))->values(),
                    'detail_url' => route('admin.imports.duplicates.show', [
                        'type' => $type,
                        'first' => $first->id,
                        'second' => $second->id,
                    ]),
                ];
            })
            ->values();
    }

    protected function duplicateKey(string $type, Model $import): string
    {
        $dataset = $this->datasetForImport($type, $import);

        if ($dataset) {
            return implode('|', [
                'dataset',
                $this->normalizeKey($dataset->title),
                $this->normalizeKey((string) $dataset->course),
                (string) ($dataset->year ?? ''),
                $this->normalizeKey((string) $dataset->state),
                $this->normalizeKey((string) $dataset->quota),
                $this->normalizeKey((string) $dataset->descriptor),
            ]);
        }

        return 'file|' . $this->normalizeKey($import->original_filename);
    }

    protected function datasetSummary(string $type, Model $dataset): array
    {
        $latestImport = $dataset->imports->first();

        return [
            'type' => $type,
            'id' => $dataset->id,
            'title' => $dataset->title,
            'slug' => $dataset->slug,
            'course' => $dataset->course,
            'year' => $dataset->year,
            'state' => $dataset->state,
            'quota' => $dataset->quota,
            'descriptor' => $dataset->descriptor,
            'imports_count' => $dataset->imports->count(),
            'latest_import' => $latestImport ? $this->importSummary($type, $latestImport) : null,
            'url' => $type === 'result'
                ? route('results.show', $dataset)
                : route('analysis.show', $dataset),
        ];
    }

    protected function importSummary(string $type, Model $import): array
    {
        $dataset = $this->datasetForImport($type, $import);

        return [
            'id' => $import->id,
            'type' => $type,
            'dataset_title' => $dataset?->title ?? 'Deleted dataset',
            'dataset_slug' => $dataset?->slug,
            'original_filename' => $import->original_filename,
            'status' => $import->status,
            'total_rows' => $import->total_rows,
            'course' => $dataset?->course,
            'year' => $dataset?->year,
            'state' => $dataset?->state,
            'quota' => $dataset?->quota,
            'descriptor' => $dataset?->descriptor,
            'created_at' => $import->created_at,
            'page_url' => $dataset
                ? ($type === 'result' ? route('results.show', $dataset) : route('analysis.show', $dataset))
                : null,
            'sheets' => $import->sheets->map(fn ($sheet) => [
                'name' => $sheet->sheet_name,
                'type' => $sheet->sheet_type,
                'rows' => $sheet->row_count,
            ])->values(),
        ];
    }

    protected function sheetComparison(Collection $firstSheets, Collection $secondSheets): Collection
    {
        $names = $firstSheets->pluck('sheet_name')
            ->merge($secondSheets->pluck('sheet_name'))
            ->unique()
            ->values();

        return $names->map(function (string $name) use ($firstSheets, $secondSheets): array {
            $first = $firstSheets->firstWhere('sheet_name', $name);
            $second = $secondSheets->firstWhere('sheet_name', $name);

            return [
                'name' => $name,
                'first_rows' => $first?->row_count,
                'second_rows' => $second?->row_count,
                'first_type' => $first?->sheet_type,
                'second_type' => $second?->sheet_type,
                'matches' => $first && $second && (int) $first->row_count === (int) $second->row_count,
            ];
        });
    }

    protected function rowSamples(string $type, Model $first, Model $second): array
    {
        if ($type === 'result') {
            return [
                'first' => $first->dataset?->rankRecords()->where('import_id', $first->id)->limit(10)->get() ?? collect(),
                'second' => $second->dataset?->rankRecords()->where('import_id', $second->id)->limit(10)->get() ?? collect(),
            ];
        }

        return [
            'first' => $first->analysisDataset?->analysisRecords()->where('analysis_import_id', $first->id)->limit(10)->get() ?? collect(),
            'second' => $second->analysisDataset?->analysisRecords()->where('analysis_import_id', $second->id)->limit(10)->get() ?? collect(),
        ];
    }

    protected function datasetForImport(string $type, Model $import): ?Model
    {
        return $type === 'result'
            ? $import->dataset
            : $import->analysisDataset;
    }

    protected function normalizeKey(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->replaceMatches('/\.[a-z0-9]+$/', '')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }
}
