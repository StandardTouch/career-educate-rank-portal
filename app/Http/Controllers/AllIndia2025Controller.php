<?php

namespace App\Http\Controllers;

use App\Models\AllIndia2025;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AllIndia2025Controller extends Controller
{
    /**
     * Display the NEET 2025 cutoff analysis page or return Yajra DataTables JSON response.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = AllIndia2025::query()
                ->select([
                    'all_india_2025.*',
                    'rounds.name as round_name'
                ])
                ->join('rounds', 'all_india_2025.round_id', '=', 'rounds.id');

            // 1. Rank Filter
            // Filters colleges where candidate qualifies. A candidate qualifies if their rank
            // is less than or equal to the closing rank. Therefore, closing rank must be >= candidate's rank.
            // if ($request->filled('rank')) {
            //     $rank = (int) $request->input('rank');
            //     $query->where(function ($q) use ($rank) {
            //         $q->where('gen_closing_rank', '>=', $rank)
            //           ->orWhere('fem_closing_rank', '>=', $rank);
            //     });
            // }
            if ($request->filled('marks')) {
                $marks = (float) $request->input('marks');
                $query->where('gen_closing_mark', '<=', $marks);
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

            // 4. Rounds Filter
            if ($request->has('rounds')) {
                $rounds = (array) $request->input('rounds');
                if (!in_array('any', $rounds) && !empty($rounds)) {
                    $query->whereIn('round_id', array_map('intval', $rounds));
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
                $query->orderBy('gen_closing_mark', 'desc');
            return DataTables::of($query)
                ->addColumn('category', function ($row) {
                    return $row->category; // Hardcoded context: MBBS Cutoff analysis
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

        // Fetch distinct values for dropdown filters
        $colleges = AllIndia2025::distinct()
            ->orderBy('college_name')
            ->pluck('college_name')
            ->toArray();

        $quotas = AllIndia2025::distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        $localAreas = AllIndia2025::distinct()
            ->orderBy('local_area')
            ->pluck('local_area')
            ->toArray();

        $rounds = DB::table('rounds')
            ->orderBy('sort_order')
            ->get()
            ->toArray();

        $maxFee = AllIndia2025::max('tuition_fee') ?? 10000000;

        return view('all_india_2025', compact('colleges', 'quotas', 'localAreas', 'rounds', 'maxFee'));
    }
}
