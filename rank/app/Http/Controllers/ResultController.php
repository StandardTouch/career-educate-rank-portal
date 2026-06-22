<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\ImportSheet;
use App\Models\RankRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ResultController extends Controller
{
    public function show(Request $request, Dataset $dataset)
    {
        abort_unless($dataset->is_active, 404);

        $latestImport = $dataset->imports()
            ->where('status', 'completed')
            ->latest('id')
            ->first() ?? $dataset->imports()->latest('id')->first();

        $sheetOptions = $latestImport
            ? ImportSheet::with('round')
                ->where('import_id', $latestImport->id)
                ->orderBy('id')
                ->get()
            : collect();

        $rounds = $dataset->rounds()
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $query = RankRecord::query()
            ->where('dataset_id', $dataset->id)
            ->with('round');

        $selectedRounds = $this->selectedValues($request, 'round_id', ['overall']);
        if (in_array('overall', $selectedRounds, true) && count($selectedRounds) > 1) {
            $selectedRounds = array_values(array_filter($selectedRounds, fn ($round) => $round !== 'overall'));
        }

        if (in_array('overall', $selectedRounds, true)) {
            $roundIds = array_values(array_filter($selectedRounds, fn ($round) => $round !== 'overall'));
            $query->where(function ($roundQuery) use ($roundIds) {
                $roundQuery->whereNull('round_id');

                if ($roundIds !== []) {
                    $roundQuery->orWhereIn('round_id', array_map('intval', $roundIds));
                }
            });
        } elseif ($selectedRounds !== []) {
            $query->whereIn('round_id', array_map('intval', $selectedRounds));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('college_name', 'like', "%{$search}%")
                    ->orWhere('quota', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('local_area', 'like', "%{$search}%");
            });
        }

        // Handle College Name and Local Area filters normally (if passed in request)
        foreach (['college_name', 'local_area'] as $field) {
            $selected = $this->selectedValues($request, $field);
            $selected = array_values(array_filter($selected, fn ($val) => $val !== 'any'));

            if ($selected !== []) {
                $query->whereIn($field, $selected);
            }
        }

        // Handle merged Quota and Category selection
        $quotaInput = $this->selectedValues($request, 'quota');
        $quotaInput = array_values(array_filter($quotaInput, fn ($val) => $val !== 'any'));
        
        if ($quotaInput !== []) {
            $allQuotas = $this->distinctValues($dataset, 'quota')->toArray();
            $allCategories = $this->distinctValues($dataset, 'category')->toArray();

            $selectedQuotas = array_intersect($quotaInput, $allQuotas);
            $selectedCategories = array_intersect($quotaInput, $allCategories);

            if ($selectedQuotas !== []) {
                $query->whereIn('quota', $selectedQuotas);
            }
            if ($selectedCategories !== []) {
                $query->whereIn('category', $selectedCategories);
            }
        }

        if ($request->filled('rank')) {
            $rank = (int) $request->input('rank');

            $query->where(function ($q) use ($rank) {
                $q->where('closing_rank', '>=', $rank)
                    ->orWhere('fem_closing_rank', '>=', $rank);
            });
        }

        // if ($request->filled('rank')) {
        //     $query->where('closing_rank', '>=', (int) $request->input('rank'));
        // }

        // if ($request->filled('fem_rank')) {
        //     $query->where('fem_closing_rank', '>=', (int) $request->input('fem_rank'));
        // }

        if ($request->filled('fem_mark')) {
            $query->where('fem_closing_mark', '>=', (float) $request->input('fem_mark'));
        }

        if ($request->filled('fee_max')) {
            $query->where(function ($feeQuery) use ($request) {
                $feeQuery
                    ->whereNull('fees')
                    ->orWhere('fees', '<=', (int) $request->input('fee_max'));
            });
        }

        $totalSeats = (int) (clone $query)->sum('seats');
        $roundComparisonMode = ! in_array('overall', $selectedRounds, true) && $selectedRounds !== [];
        $roundComparisonSheets = $roundComparisonMode
            ? $this->selectedRoundSheets($sheetOptions, $rounds, $selectedRounds)
            : collect();
        $roundComparisonColumns = $roundComparisonMode
            ? $this->roundComparisonColumns($roundComparisonSheets)
            : [];
        $roundComparisonRows = $roundComparisonMode
            ? $this->roundComparisonRows((clone $query)->get(), $roundComparisonColumns)
            : collect();

        $records = $query
            ->orderByRaw('closing_rank is null')
            ->orderByDesc('closing_rank')
            ->paginate(25)
            ->withQueryString();

        $filterValues = [
            'college_name' => $this->distinctValues($dataset, 'college_name'),
            'quota' => $this->distinctValues($dataset, 'quota'),
            'category' => $this->distinctValues($dataset, 'category'),
            'local_area' => $this->distinctValues($dataset, 'local_area'),
        ];

        $maxFee = (int) RankRecord::where('dataset_id', $dataset->id)->max('fees');

        $selectedRound = $selectedRounds[0] ?? 'overall';
        $selectedFilters = [
            'round_id' => $selectedRounds,
            'college_name' => $this->selectedValues($request, 'college_name'),
            'quota' => $quotaInput,
            'category' => [], // Category dropdown is removed
            'local_area' => $this->selectedValues($request, 'local_area'),
        ];

        $resultCount = $roundComparisonMode ? $roundComparisonRows->count() : $records->total();

        return view('results.show', compact(
            'dataset',
            'rounds',
            'sheetOptions',
            'records',
            'filterValues',
            'selectedRound',
            'selectedRounds',
            'selectedFilters',
            'maxFee',
            'totalSeats',
            'roundComparisonMode',
            'roundComparisonColumns',
            'roundComparisonRows',
            'resultCount'
        ));
    }

    protected function selectedValues(Request $request, string $field, array $default = []): array
    {
        $value = $request->input($field, $default);
        $values = is_array($value) ? $value : [$value];

        return array_values(array_filter($values, fn ($item) => $item !== null && $item !== ''));
    }

    protected function distinctValues(Dataset $dataset, string $column)
    {
        return RankRecord::where('dataset_id', $dataset->id)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column);
    }

    protected function selectedRoundSheets($sheetOptions, $rounds, array $selectedRounds)
    {
        $selected = array_map('strval', $selectedRounds);
        $sheets = $sheetOptions
            ->filter(fn ($sheet) => $sheet->sheet_type !== 'overall' && in_array((string) $sheet->round_id, $selected, true))
            ->values();

        if ($sheets->isNotEmpty()) {
            return $sheets;
        }

        return $rounds
            ->filter(fn ($round) => in_array((string) $round->id, $selected, true))
            ->map(fn ($round) => (object) [
                'round_id' => $round->id,
                'sheet_name' => $round->name,
            ])
            ->values();
    }

    protected function roundComparisonColumns($roundSheets): array
    {
        return $roundSheets
            ->map(function ($sheet) {
                $roundId = (int) $sheet->round_id;
                $label = trim((string) $sheet->sheet_name) !== '' ? trim((string) $sheet->sheet_name) : 'Round ' . $roundId;
                $key = 'round_' . $roundId . '_' . Str::slug($label, '_');

                return [
                    'round_id' => $roundId,
                    'label' => $label,
                    'gen_rank_key' => $key . '_gen_rank',
                    'gen_mark_key' => $key . '_gen_mark',
                    'fem_rank_key' => $key . '_fem_rank',
                    'fem_mark_key' => $key . '_fem_mark',
                ];
            })
            ->values()
            ->all();
    }

    protected function roundComparisonRows($records, array $roundColumns)
    {
        return $records
            ->groupBy(function (RankRecord $record) {
                $payload = $this->recordPayload($record);

                return implode('|', [
                    $payload['state_name'] ?? '',
                    $record->college_name ?? '',
                    $record->category ?? '',
                    $record->local_area ?? '',
                    $record->course ?? '',
                    (string) ($record->fees ?? ''),
                ]);
            })
            ->map(function ($group) use ($roundColumns) {
                /** @var RankRecord $first */
                $first = $group->first();
                $payload = $this->recordPayload($first);
                $roundValues = [];

                foreach ($roundColumns as $column) {
                    $roundRecord = $group->firstWhere('round_id', $column['round_id']);
                    $roundValues[$column['round_id']] = [
                        'gen_rank' => $roundRecord?->closing_rank,
                        'gen_mark' => $roundRecord?->marks,
                        'fem_rank' => $roundRecord?->fem_closing_rank,
                        'fem_mark' => $roundRecord?->fem_closing_mark,
                    ];
                }

                return [
                    'state_name' => $payload['state_name'] ?? '-',
                    'college_name' => $first->college_name ?? '-',
                    'category' => $first->category ?? '-',
                    'local_area' => $first->local_area ?? '-',
                    'course' => $first->course ?? '-',
                    'seats' => $group->pluck('seats')->filter(fn ($seats) => $seats !== null)->first(),
                    'fees' => $first->fees,
                    'rounds' => $roundValues,
                ];
            })
            ->sortByDesc(function (array $row) use ($roundColumns) {
                $firstRound = $roundColumns[0]['round_id'] ?? null;

                return $firstRound ? (int) ($row['rounds'][$firstRound]['gen_rank'] ?? 0) : 0;
            })
            ->values();
    }

    protected function recordPayload(RankRecord $record): array
    {
        if (is_array($record->raw_payload)) {
            return $record->raw_payload;
        }

        return json_decode((string) $record->raw_payload, true) ?: [];
    }
}
