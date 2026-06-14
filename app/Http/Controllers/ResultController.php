<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\ImportSheet;
use App\Models\RankRecord;
use Illuminate\Http\Request;

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

        foreach (['college_name', 'quota', 'category', 'local_area'] as $field) {
            $selected = $this->selectedValues($request, $field);

            if ($selected !== []) {
                $query->whereIn($field, $selected);
            }
        }

        if ($request->filled('rank')) {
            $query->where('closing_rank', '>=', (int) $request->input('rank'));
        }

        if ($request->filled('fee_max')) {
            $query->where(function ($feeQuery) use ($request) {
                $feeQuery
                    ->whereNull('fees')
                    ->orWhere('fees', '<=', (int) $request->input('fee_max'));
            });
        }

        $totalSeats = (int) (clone $query)->sum('seats');

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
            'quota' => $this->selectedValues($request, 'quota'),
            'category' => $this->selectedValues($request, 'category'),
            'local_area' => $this->selectedValues($request, 'local_area'),
        ];

        return view('results.show', compact('dataset', 'rounds', 'sheetOptions', 'records', 'filterValues', 'selectedRound', 'selectedRounds', 'selectedFilters', 'maxFee', 'totalSeats'));
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
}
