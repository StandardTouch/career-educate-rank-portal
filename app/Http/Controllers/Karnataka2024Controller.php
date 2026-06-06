<?php

namespace App\Http\Controllers;

use App\Models\Karnataka2024;
use App\Models\KarnatakaRounds2024;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class Karnataka2024Controller extends Controller
{
     public function index(Request $request)
    {
        if ($request->ajax()) {
            $useRoundsTable = false;
            $selectedRounds = [];

            if ($request->has('rounds')) {
                $selectedRounds = (array) $request->input('rounds');
                // If the selected rounds are not empty and do not contain 'any' (Over All), we query round-wise data.
                if (!empty($selectedRounds) && !in_array('any', $selectedRounds)) {
                    $useRoundsTable = true;
                }
            }

            if ($useRoundsTable) {
                // Fetch round-wise cutoff data from karnataka_2024_rounds
                $query = KarnatakaRounds2024::query()
                    ->select([
                        'karnataka_2024_rounds.*',
                        'rounds.name as round_name',
                        DB::raw("'Karnataka' as state_name")
                    ])
                    ->join('rounds', 'karnataka_2024_rounds.round_id', '=', 'rounds.id')
                    ->whereIn('karnataka_2024_rounds.round_id', array_map('intval', $selectedRounds));
            } else {
                // Fetch overall cutoff data from karnataka_2025 (no round check or joins needed)
                $query = Karnataka2024::query()
                    ->select([
                        'karnataka_2024.*',
                        DB::raw("'Over All' as round_name"),
                        DB::raw("'Karnataka' as state_name")
                    ]);
            }

            // 1. Rank Filter
            // Filters colleges where candidate qualifies. A candidate qualifies if their rank
            // is less than or equal to the closing rank. Therefore, closing rank must be >= candidate's rank.
            if ($request->filled('rank')) {
                $rank = (int) $request->input('rank');
                $query->where('gen_closing_rank', '>=', $rank);
            }
            
            // 2. Colleges Filter
            if ($request->has('colleges')) {
                $colleges = (array) $request->input('colleges');
                if (!in_array('any', $colleges) && !empty($colleges)) {
                    $query->whereIn('college_name', $colleges);
                }
            }

            // 3. Quotas (Category in DB) Filter
            if ($request->has('quotas')) {
                $quotas = (array) $request->input('quotas');
                if (!in_array('any', $quotas) && !empty($quotas)) {
                    $query->whereIn('category', $quotas);
                }
            }

            // 5. Local Areas Filter
            if ($request->has('local_areas')) {
                $localAreas = (array) $request->input('local_areas');
                if (!in_array('any', $localAreas) && !empty($localAreas)) {
                    $query->whereIn('local_area', $localAreas);
                }
            }

            // 6. Tuition Fee Filter
            if ($request->filled('fee_min')) {
                $query->where('tuition_fee', '>=', (int) $request->input('fee_min'));
            }
            if ($request->filled('fee_max')) {
                $query->where('tuition_fee', '<=', (int) $request->input('fee_max'));
            }

            return DataTables::of($query)
                ->filterColumn('state_name', function ($query, $keyword) {
                    // No-op to prevent searching on virtual/constant column
                })
                ->orderColumn('state_name', function ($query, $order) {
                    // No-op to prevent sorting on virtual/constant column
                })
                ->addColumn('category', function ($row) {
                    return $row->category;
                })
                ->editColumn('local_area', function ($row) {
                    return $row->local_area ?? "- ";
                })
                ->editColumn('round_id', function($row){
                    return $row->round_name;
                })
                ->editColumn('tuition_fee', function ($row) {
                    return $row->tuition_fee !== null ? (int) $row->tuition_fee : 0;
                })
                ->make(true);
        }

        // Fetch distinct values for dropdown filters from KarnatakaRounds2024
        $colleges = KarnatakaRounds2024::distinct()
            ->orderBy('college_name')
            ->pluck('college_name')
            ->toArray();

        $quotas = KarnatakaRounds2024::distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        $localAreas = KarnatakaRounds2024::distinct()
            ->orderBy('local_area')
            ->pluck('local_area')
            ->toArray();

        $rounds = DB::table('rounds')
            ->orderBy('sort_order')
            ->get()
            ->toArray();

        $seatsCount = Karnataka2024::count();

        $maxFee = KarnatakaRounds2024::max('tuition_fee') ?? 10000000;

        return view('karnataka_2024', compact('colleges', 'quotas', 'localAreas', 'rounds', 'maxFee', 'seatsCount'));
    }
}
