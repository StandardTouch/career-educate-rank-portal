<?php

namespace App\Services;

use App\Helpers\ScaffoldHelper;
use App\Models\AnalysisDataset;
use App\Models\AnalysisImport;
use App\Models\AnalysisImportSheet;
use App\Models\AnalysisRecord;
use App\Models\AnalysisRound;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DynamicAnalysisImportService
{
    public function importUploadedFile(UploadedFile $file, ?int $userId = null): AnalysisDataset
    {
        $originalName = $file->getClientOriginalName();
        $safeName = $this->safeFilename($originalName);
        $storedPath = $file->storeAs('analysis-imports', now()->format('YmdHis') . '-' . $safeName);
        $absolutePath = Storage::disk('local')->path($storedPath);

        return $this->importFile($absolutePath, $originalName, $storedPath, $userId);
    }

    public function importFile(string $absolutePath, string $originalName, string $storedPath, ?int $userId = null): AnalysisDataset
    {
        $meta = $this->metadataFromFilename($originalName);

        $dataset = AnalysisDataset::updateOrCreate(
            ['slug' => $meta['slug']],
            [
                'title' => $meta['title'],
                'year' => $meta['year'],
                'course' => $meta['course'],
                'state' => $meta['state'],
                'quota' => $meta['quota'],
                'descriptor' => $meta['descriptor'],
                'is_active' => true,
            ]
        );

        $import = AnalysisImport::create([
            'analysis_dataset_id' => $dataset->id,
            'original_filename' => $originalName,
            'stored_path' => $storedPath,
            'status' => 'pending',
            'imported_by' => $userId,
        ]);

        try {
            $totalRows = DB::transaction(function () use ($absolutePath, $dataset, $import) {
                return $this->replaceDatasetRecords($absolutePath, $dataset, $import);
            });

            $import->update([
                'status' => 'completed',
                'total_rows' => $totalRows,
            ]);

            return $dataset->fresh();
        } catch (\Throwable $exception) {
            $import->update([
                'status' => 'failed',
                'error_message' => Str::limit($exception->getMessage(), 4000),
            ]);

            Log::error('Dynamic analysis import failed', [
                'analysis_dataset_id' => $dataset->id,
                'import_id' => $import->id,
                'file' => $originalName,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    protected function replaceDatasetRecords(string $absolutePath, AnalysisDataset $dataset, AnalysisImport $import): int
    {
        $spreadsheet = IOFactory::load($absolutePath);
        $sheetNames = $spreadsheet->getSheetNames();

        $roundSheets = [];
        $overallSheet = null;

        foreach ($sheetNames as $sheetName) {
            if (preg_match('/round/i', $sheetName)) {
                $roundSheets[] = $sheetName;
                continue;
            }

            if ($overallSheet === null) {
                $overallSheet = $sheetName;
            }
        }

        if ($overallSheet === null && count($sheetNames) > 0) {
            $overallSheet = $sheetNames[0];
        }

        AnalysisRecord::where('analysis_dataset_id', $dataset->id)->delete();
        AnalysisRound::where('analysis_dataset_id', $dataset->id)->delete();

        $totalRows = 0;

        if ($overallSheet !== null) {
            $importSheet = AnalysisImportSheet::create([
                'analysis_import_id' => $import->id,
                'sheet_name' => $overallSheet,
                'sheet_type' => 'overall',
            ]);

            $rowCount = $this->importSheet(
                $spreadsheet->getSheetByName($overallSheet),
                $dataset,
                $import,
                $importSheet,
                null
            );

            $importSheet->update(['row_count' => $rowCount]);
            $totalRows += $rowCount;
        }

        foreach ($roundSheets as $sheetName) {
            $round = $this->createDatasetRound($dataset, $sheetName);
            $importSheet = AnalysisImportSheet::create([
                'analysis_import_id' => $import->id,
                'sheet_name' => $sheetName,
                'sheet_type' => 'round',
                'analysis_round_id' => $round->id,
            ]);

            $rowCount = $this->importSheet(
                $spreadsheet->getSheetByName($sheetName),
                $dataset,
                $import,
                $importSheet,
                $round
            );

            $importSheet->update(['row_count' => $rowCount]);
            $totalRows += $rowCount;
        }

        return $totalRows;
    }

    protected function importSheet($worksheet, AnalysisDataset $dataset, AnalysisImport $import, AnalysisImportSheet $importSheet, ?AnalysisRound $round): int
    {
        if ($worksheet === null) {
            return 0;
        }

        $rows = $worksheet->toArray(null, true, true, true);
        $header = array_shift($rows);
        if ($header === null) {
            return 0;
        }

        $columns = [];
        foreach ($header as $column => $title) {
            $columns[$column] = $this->normalizeHeader((string) $title);
        }

        $now = now();
        $batch = [];
        $rowCount = 0;

        foreach ($rows as $row) {
            $payload = [];

            foreach ($columns as $column => $name) {
                if ($name === '') {
                    continue;
                }

                $payload[$name] = $this->normalizeCellValue($row[$column] ?? null);
            }

            if ($this->isEmptyPayload($payload)) {
                continue;
            }

            $record = [
                'analysis_dataset_id' => $dataset->id,
                'analysis_import_id' => $import->id,
                'analysis_import_sheet_id' => $importSheet->id,
                'analysis_round_id' => $round?->id,
                'college_name' => $payload['college_name'] ?? null,
                'course' => $payload['course'] ?? $dataset->course,
                'quota' => $payload['quota'] ?? null,
                'category' => $payload['category'] ?? null,
                'local_area' => $payload['local_area'] ?? null,
                'seats' => $this->cleanInteger($payload['seats'] ?? $payload['total_seats'] ?? null),
                'opening_rank' => $this->cleanInteger($payload['opening_rank'] ?? null),
                'closing_rank' => $this->cleanInteger($payload['closing_rank'] ?? $payload['gen_closing_rank'] ?? $payload['rank'] ?? null),
                'marks' => $this->cleanMarks($payload['marks'] ?? $payload['gen_closing_mark'] ?? null),
                'fem_closing_rank' => $this->cleanInteger($payload['female_closing_rank'] ?? null),
                'fem_closing_mark' => $this->cleanMarks($payload['female_marks'] ?? null),
                'fees' => $this->cleanDecimal($payload['fees'] ?? $payload['tuition_fee'] ?? $payload['total_fee'] ?? null),
                'raw_payload' => json_encode($payload),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $batch[] = $record;
            $rowCount++;

            if (count($batch) >= 500) {
                AnalysisRecord::insert($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            AnalysisRecord::insert($batch);
        }

        return $rowCount;
    }

    protected function createDatasetRound(AnalysisDataset $dataset, string $sheetName): AnalysisRound
    {
        $roundNumber = $this->roundNumberFromSheetName($sheetName);
        $baseSlug = $this->deriveRoundSlug($sheetName);
        $slug = $this->uniqueRoundSlug($dataset->slug . '-' . $baseSlug);

        return AnalysisRound::create([
            'analysis_dataset_id' => $dataset->id,
            'name' => trim($sheetName) !== '' ? trim($sheetName) : ucwords(str_replace('_', ' ', $baseSlug)),
            'slug' => $slug,
            'round_number' => $roundNumber,
            'sort_order' => $roundNumber ?? 0,
        ]);
    }

    protected function metadataFromFilename(string $filename): array
    {
        try {
            $parts = ScaffoldHelper::parseFileName($filename);
        } catch (\Throwable) {
            $parts = ['state' => null, 'year' => null, 'descriptor' => null];
        }

        $title = $this->titleFromFilename($filename);
        preg_match('/\b(20\d{2})\b/', $filename, $yearMatch);
        $year = isset($yearMatch[1]) ? (int) $yearMatch[1] : ($parts['year'] ? (int) $parts['year'] : null);
        $course = $this->courseFromFilename($filename);
        $state = $this->stateFromParts($parts['state'] ?? null, $course);
        $descriptor = $parts['descriptor'] ?? null;
        $quota = $this->quotaFromText($filename);
        $slug = Str::slug(implode(' ', array_filter([$state, $course, $quota, $year, $descriptor, 'analysis'])));

        if ($slug === '') {
            $slug = Str::slug(pathinfo($filename, PATHINFO_FILENAME) . '-analysis');
        }

        return [
            'slug' => $slug,
            'title' => $title,
            'year' => $year,
            'course' => $course,
            'state' => $state,
            'quota' => $quota,
            'descriptor' => $descriptor,
        ];
    }

    protected function titleFromFilename(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/\s+/', ' ', str_replace(['_', '-'], ' ', $name));

        return trim(ucwords(strtolower($name)));
    }

    protected function stateFromParts(?string $state, ?string $course): ?string
    {
        if ($state === null || $state === '') {
            return null;
        }

        $state = preg_replace('/\b(' . strtolower((string) $course) . '|mbbs|bds|dental)\b/i', '', $state);
        $state = preg_replace('/_+/', '_', trim((string) $state, '_'));

        return $state !== '' ? $state : null;
    }

    protected function courseFromFilename(string $filename): ?string
    {
        if (preg_match('/\bmbbs\b/i', $filename)) {
            return 'MBBS';
        }

        if (preg_match('/\bbds\b|\bdental\b/i', $filename)) {
            return 'BDS';
        }

        return null;
    }

    protected function quotaFromText(string $text): ?string
    {
        return match (true) {
            preg_match('/management/i', $text) === 1 => 'Management',
            preg_match('/govt|government/i', $text) === 1 => 'Government',
            preg_match('/nri/i', $text) === 1 => 'NRI',
            preg_match('/all\s+india|all\s+over\s+india/i', $text) === 1 => 'All India',
            default => null,
        };
    }

    protected function normalizeHeader(string $title): string
    {
        $normalized = strtolower(trim($title));
        $normalized = preg_replace('/\s*\([^)]*\)/', '', $normalized);
        $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized);
        $normalized = trim($normalized, '_');

        return match ($normalized) {
            'state', 'state_name' => 'state_name',
            'college', 'college_name', 'institute', 'institute_name' => 'college_name',
            'course', 'stream' => 'course',
            'seat', 'seats', 'total_seat', 'total_seats' => 'seats',
            'opening_rank', 'open_rank' => 'opening_rank',
            'closing_rank', 'rank', 'gen_closing_rank', 'general_closing_rank' => 'closing_rank',
            'closing_mark', 'marks', 'mark', 'gen_closing_mark', 'general_closing_mark' => 'marks',
            'fee', 'fees', 'tuition_fee', 'total_fee' => 'fees',
            default => $this->normalizeRoundHeader($normalized),
        };
    }

    protected function normalizeRoundHeader(string $normalized): string
    {
        if (preg_match('/^(gen|general|fem|female)_.+_rank$/', $normalized)) {
            return str_starts_with($normalized, 'fem') || str_starts_with($normalized, 'female')
                ? 'female_closing_rank'
                : 'closing_rank';
        }

        if (preg_match('/^(gen|general|fem|female)_.+_mark$/', $normalized)) {
            return str_starts_with($normalized, 'fem') || str_starts_with($normalized, 'female')
                ? 'female_marks'
                : 'marks';
        }

        return $normalized;
    }

    protected function normalizeCellValue($value)
    {
        if ($value === null) {
            return null;
        }

        $value = is_string($value) ? trim($value) : $value;

        if (is_string($value) && preg_match('/^(?:-|--|\?|n\/a|na|null)$/i', $value)) {
            return null;
        }

        return $value;
    }

    protected function isEmptyPayload(array $payload): bool
    {
        foreach ($payload as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }

    protected function cleanDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $clean = preg_replace('/[^0-9.]/', '', (string) $value);

        return $clean === '' ? null : (float) $clean;
    }

    protected function cleanMarks($value): ?float
    {
        $marks = $this->cleanDecimal($value);

        if ($marks === null) {
            return null;
        }

        return $marks >= 0 && $marks <= 720 ? $marks : null;
    }

    protected function cleanInteger($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $clean = preg_replace('/[^0-9]/', '', (string) $value);

        return $clean === '' ? null : (int) $clean;
    }

    protected function deriveRoundSlug(string $sheetName): string
    {
        return Str::slug($sheetName) ?: 'round';
    }

    protected function uniqueRoundSlug(string $slug): string
    {
        $candidate = $slug;
        $suffix = 2;

        while (AnalysisRound::where('slug', $candidate)->exists()) {
            $candidate = $slug . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    protected function roundNumberFromSheetName(string $sheetName): ?int
    {
        if (preg_match('/(\d+)/', $sheetName, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    protected function safeFilename(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/[^A-Za-z0-9 _-]+/', '', $name);
        $name = preg_replace('/\s+/', ' ', trim($name));

        return ($name !== '' ? $name : 'analysis-import') . '.' . strtolower($extension);
    }
}
