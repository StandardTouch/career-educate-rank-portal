<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;

abstract class GenericPredictorController extends Controller
{
    protected string $mainTable = '';
    protected string $roundTable = '';
    protected string $viewName = '';
    protected string $stateLabel = '';
    protected array $columnCache = [];

    public function index(Request $request)
    {
        if (! Schema::hasTable($this->mainTable)) {
            return $this->renderEmptyPage();
        }

        if ($request->ajax() || $request->wantsJson() || $request->has('draw')) {
            return $this->renderDataTable($request);
        }

        return $this->renderPage();
    }

    protected function renderDataTable(Request $request)
    {
        $selectedRounds = [];
        $useRoundsTable = Schema::hasTable($this->roundTable);
        $sourceTable = $this->mainTable;
        $rankColumn = $this->getRankColumn($sourceTable);
        $secondaryRankColumn = $this->getSecondaryRankColumn($sourceTable);
        $selectColumns = $this->buildSelectColumns($sourceTable, false);

        if ($request->has('rounds')) {
            $selectedRounds = (array) $request->input('rounds');
            if (empty($selectedRounds) || in_array('any', $selectedRounds, true) || ! $useRoundsTable) {
                $useRoundsTable = false;
            }
        } else {
            $useRoundsTable = false;
        }

        if ($useRoundsTable) {
            $sourceTable = $this->roundTable;
            $rankColumn = $this->getRankColumn($sourceTable);
            $secondaryRankColumn = $this->getSecondaryRankColumn($sourceTable);
            $selectColumns = $this->buildSelectColumns($sourceTable, true);
            $roundIdColumn = DB::raw($this->qualifyColumn($this->roundTable, 'round_id'));

            $query = DB::table($this->roundTable)
                ->select($selectColumns)
                ->join('rounds', $roundIdColumn, '=', 'rounds.id')
                ->whereIn($roundIdColumn, array_map('intval', $selectedRounds));
        } else {
            $query = DB::table($this->mainTable)->select($selectColumns);
        }

        if ($request->filled('rank') && $rankColumn !== null) {
            $rank = (int) $request->input('rank');
            $query->where(function ($q) use ($rank, $sourceTable) {
                $primary = $this->getRankColumn($sourceTable);
                $secondary = $this->getSecondaryRankColumn($sourceTable);

                if ($primary !== null) {
                    $q->where($primary, '>=', $rank);
                }

                if ($secondary !== null) {
                    $q->orWhere($secondary, '>=', $rank);
                }
            });
        }

        if ($request->has('colleges')) {
            $colleges = (array) $request->input('colleges');
            if (! in_array('any', $colleges, true) && ! empty($colleges)) {
                $query->whereIn('college_name', $colleges);
            }
        }

        if ($request->has('quotas')) {
            $quotas = (array) $request->input('quotas');
            if (! in_array('any', $quotas, true) && ! empty($quotas)) {
                $query->whereIn('category', $quotas);
            }
        }

        if ($request->has('local_areas')) {
            $localAreas = (array) $request->input('local_areas');
            if (! in_array('any', $localAreas, true) && ! empty($localAreas)) {
                $query->whereIn('local_area', $localAreas);
            }
        }

        if ($request->filled('fee_min')) {
            $query->where('tuition_fee', '>=', (int) $request->input('fee_min'));
        }

        if ($request->filled('fee_max')) {
            $query->where('tuition_fee', '<=', (int) $request->input('fee_max'));
        }

        return DataTables::of($query)
            ->filterColumn('state_name', function ($query, $keyword) {
                // no-op for virtual column
            })
            ->orderColumn('state_name', function ($query, $order) {
                // no-op for virtual column
            })
            ->orderColumn('gen_closing_rank', function ($query, $order) use ($sourceTable) {
                $this->applyVirtualOrder($query, $sourceTable, 'gen_closing_rank', $this->getRankColumn($sourceTable), $order);
            })
            ->orderColumn($sourceTable . '.gen_closing_rank', function ($query, $order) use ($sourceTable) {
                $this->applyVirtualOrder($query, $sourceTable, 'gen_closing_rank', $this->getRankColumn($sourceTable), $order);
            })
            ->orderColumn('fem_closing_rank', function ($query, $order) use ($sourceTable) {
                $this->applyVirtualOrder($query, $sourceTable, 'fem_closing_rank', $this->getSecondaryRankColumn($sourceTable), $order);
            })
            ->orderColumn($sourceTable . '.fem_closing_rank', function ($query, $order) use ($sourceTable) {
                $this->applyVirtualOrder($query, $sourceTable, 'fem_closing_rank', $this->getSecondaryRankColumn($sourceTable), $order);
            })
            ->orderColumn('total_seats', function ($query, $order) use ($sourceTable) {
                $this->applyVirtualOrder($query, $sourceTable, 'total_seats', $this->hasColumn($sourceTable, 'total_seats') ? 'total_seats' : null, $order);
            })
            ->orderColumn($sourceTable . '.total_seats', function ($query, $order) use ($sourceTable) {
                $this->applyVirtualOrder($query, $sourceTable, 'total_seats', $this->hasColumn($sourceTable, 'total_seats') ? 'total_seats' : null, $order);
            })
            ->orderColumn('gen_closing_mark', function ($query, $order) use ($sourceTable) {
                $this->applyVirtualOrder($query, $sourceTable, 'gen_closing_mark', $this->hasColumn($sourceTable, 'gen_closing_mark') ? 'gen_closing_mark' : null, $order);
            })
            ->orderColumn($sourceTable . '.gen_closing_mark', function ($query, $order) use ($sourceTable) {
                $this->applyVirtualOrder($query, $sourceTable, 'gen_closing_mark', $this->hasColumn($sourceTable, 'gen_closing_mark') ? 'gen_closing_mark' : null, $order);
            })
            ->orderColumn('fem_closing_mark', function ($query, $order) use ($sourceTable) {
                $this->applyVirtualOrder($query, $sourceTable, 'fem_closing_mark', $this->hasColumn($sourceTable, 'fem_closing_mark') ? 'fem_closing_mark' : null, $order);
            })
            ->orderColumn($sourceTable . '.fem_closing_mark', function ($query, $order) use ($sourceTable) {
                $this->applyVirtualOrder($query, $sourceTable, 'fem_closing_mark', $this->hasColumn($sourceTable, 'fem_closing_mark') ? 'fem_closing_mark' : null, $order);
            })
            ->addColumn('category', function ($row) {
                return $row->category;
            })
            ->editColumn('local_area', function ($row) {
                return $row->local_area ?? '- ';
            })
            ->editColumn('round_id', function ($row) {
                return $row->round_name;
            })
            ->editColumn('tuition_fee', function ($row) {
                return $row->tuition_fee !== null ? (int) $row->tuition_fee : 0;
            })
            ->make(true);
    }

    protected function renderPage()
    {
        $filterTable = Schema::hasTable($this->roundTable) ? $this->roundTable : $this->mainTable;
        $colleges = [];
        $quotas = [];
        $localAreas = [];
        $rounds = DB::table('rounds')->orderBy('sort_order')->get()->toArray();
        $seatsCount = 0;
        $maxFee = 10000000;

        if (Schema::hasTable($filterTable)) {
            $colleges = DB::table($filterTable)->distinct()->orderBy('college_name')->pluck('college_name')->toArray();
            $quotas = DB::table($filterTable)->distinct()->orderBy('category')->pluck('category')->toArray();
            $localAreas = DB::table($filterTable)->distinct()->orderBy('local_area')->pluck('local_area')->toArray();
            $maxFee = (int) (DB::table($filterTable)->max('tuition_fee') ?? 10000000);
        }

        if (Schema::hasTable($this->mainTable) && Schema::hasColumn($this->mainTable, 'total_seats')) {
            $seatsCount = (int) DB::table($this->mainTable)->sum('total_seats');
        }

        return view($this->viewName, compact('colleges', 'quotas', 'localAreas', 'rounds', 'maxFee', 'seatsCount'));
    }

    protected function renderEmptyPage()
    {
        $colleges = [];
        $quotas = [];
        $localAreas = [];
        $rounds = DB::table('rounds')->orderBy('sort_order')->get()->toArray();
        $seatsCount = 0;
        $maxFee = 10000000;

        return view($this->viewName, compact('colleges', 'quotas', 'localAreas', 'rounds', 'maxFee', 'seatsCount'));
    }

    protected function buildSelectColumns(string $table, bool $isRoundTable): array
    {
        $columns = [DB::raw($this->wrapColumn($table . '.*'))];
        $hasStateName = $this->hasColumn($table, 'state_name');
        $hasTotalSeats = $this->hasColumn($table, 'total_seats');
        $hasGenRank = $this->hasColumn($table, 'gen_closing_rank');
        $hasFemRank = $this->hasColumn($table, 'fem_closing_rank');
        $hasGenMark = $this->hasColumn($table, 'gen_closing_mark');
        $hasFemMark = $this->hasColumn($table, 'fem_closing_mark');

        if (! $hasStateName) {
            $columns[] = DB::raw("'" . str_replace("'", "\\'", $this->stateLabel) . "' as state_name");
        }

        if ($isRoundTable) {
            $columns[] = 'rounds.name as round_name';
        } else {
            $columns[] = DB::raw("'Over All' as round_name");
        }

        if (! $hasTotalSeats) {
            $columns[] = DB::raw('null as total_seats');
        }

        if (! $hasGenRank && $this->hasColumn($table, 'rank')) {
            $columns[] = DB::raw($this->qualifyColumn($table, 'rank') . ' as gen_closing_rank');
        }

        if (! $hasFemRank) {
            $columns[] = DB::raw('null as fem_closing_rank');
        }

        if (! $hasGenMark) {
            $columns[] = DB::raw('null as gen_closing_mark');
        }

        if (! $hasFemMark) {
            $columns[] = DB::raw('null as fem_closing_mark');
        }

        return $columns;
    }

    protected function wrapColumn(string $column): string
    {
        return DB::connection()->getQueryGrammar()->wrap($column);
    }

    protected function qualifyColumn(string $table, string $column): string
    {
        $grammar = DB::connection()->getQueryGrammar();

        return $grammar->wrapTable($table) . '.' . $grammar->wrap($column);
    }

    protected function getRankColumn(string $table): ?string
    {
        if ($this->hasColumn($table, 'gen_closing_rank')) {
            return 'gen_closing_rank';
        }

        if ($this->hasColumn($table, 'rank')) {
            return 'rank';
        }

        return null;
    }

    protected function getSecondaryRankColumn(string $table): ?string
    {
        return $this->hasColumn($table, 'fem_closing_rank') ? 'fem_closing_rank' : null;
    }

    protected function hasColumn(string $table, string $column): bool
    {
        $cacheKey = $table;
        if (! array_key_exists($cacheKey, $this->columnCache)) {
            $this->columnCache[$cacheKey] = Schema::hasTable($table) ? Schema::getColumnListing($table) : [];
        }

        return in_array($column, $this->columnCache[$cacheKey], true);
    }

    protected function applyVirtualOrder($query, string $sourceTable, string $fallbackName, ?string $physicalColumn, string $order): void
    {
        if ($physicalColumn !== null) {
            $query->orderBy($physicalColumn, $order);
            return;
        }

        if ($this->hasColumn($sourceTable, $fallbackName)) {
            $query->orderBy($fallbackName, $order);
        }
    }
}
