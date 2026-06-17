<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\Import;
use App\Models\Payment;
use App\Models\RankRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $menus = config('menus', []);
        $pageCount = collect($menus)->flatten(1)->count();
        $yearCount = count($menus);
        $datasets = collect();

        if (Schema::hasTable('datasets')) {
            $datasets = Dataset::where('is_active', true)
                ->orderByDesc('year')
                ->orderBy('title')
                ->get();
            $pageCount += $datasets->count();
            $yearCount = collect($menus)->keys()
                ->merge($datasets->pluck('year')->filter()->map(fn ($year) => 'Results ' . $year))
                ->unique()
                ->count();
        }

        $userCount = Schema::hasTable('users') ? DB::table('users')->count() : 0;
        $adminCount = Schema::hasTable('users') && Schema::hasColumn('users', 'is_admin')
            ? DB::table('users')->where('is_admin', true)->count()
            : 0;
        $verifiedMobileCount = Schema::hasTable('users') && Schema::hasColumn('users', 'mobile_verified_at')
            ? DB::table('users')->whereNotNull('mobile_verified_at')->count()
            : 0;
        $studentCount = Schema::hasTable('users') && Schema::hasColumn('users', 'is_admin')
            ? DB::table('users')->where('is_admin', false)->count()
            : max(0, $userCount - $adminCount);
        $paidUserCount = Schema::hasTable('users') && Schema::hasColumn('users', 'payment_status')
            ? DB::table('users')->where('payment_status', 'paid')->count()
            : 0;
        $unpaidUserCount = Schema::hasTable('users') && Schema::hasColumn('users', 'payment_status')
            ? DB::table('users')->where('payment_status', '!=', 'paid')->count()
            : 0;
        $basicPlanCount = Schema::hasTable('users') && Schema::hasColumn('users', 'plan')
            ? DB::table('users')->where('plan', 'basic')->count()
            : 0;
        $premiumPlanCount = Schema::hasTable('users') && Schema::hasColumn('users', 'plan')
            ? DB::table('users')->where('plan', 'premium')->count()
            : 0;

        $importCount = Schema::hasTable('imports') ? Import::count() : 0;
        $completedImportCount = Schema::hasTable('imports') ? Import::where('status', 'completed')->count() : 0;
        $failedImportCount = Schema::hasTable('imports') ? Import::where('status', 'failed')->count() : 0;
        $recordCount = Schema::hasTable('rank_records') ? RankRecord::count() : 0;
        $sheetCount = Schema::hasTable('import_sheets') ? DB::table('import_sheets')->count() : 0;
        $roundCount = Schema::hasTable('rounds') ? DB::table('rounds')->whereNotNull('dataset_id')->count() : 0;
        $paymentCount = Schema::hasTable('payments') ? Payment::count() : 0;
        $completedPaymentCount = Schema::hasTable('payments') ? Payment::whereIn('status', ['completed', 'captured'])->count() : 0;
        $pendingPaymentCount = Schema::hasTable('payments') ? Payment::where('status', 'pending')->count() : 0;
        $totalRevenue = Schema::hasTable('payments') ? (float) Payment::whereIn('status', ['completed', 'captured'])->sum('amount') : 0;

        $latestImport = Schema::hasTable('imports')
            ? Import::with('dataset')->latest('id')->first()
            : null;

        $recentImports = Schema::hasTable('imports')
            ? Import::with('dataset')->latest('id')->take(6)->get()
            : collect();
        $recentPayments = Schema::hasTable('payments')
            ? Payment::with('user')->latest('id')->take(6)->get()
            : collect();
        $recentUsers = Schema::hasTable('users')
            ? User::query()->latest('id')->take(6)->get()
            : collect();

        $datasetsByYear = $datasets
            ->groupBy(fn (Dataset $dataset) => $dataset->year ?: 'Dynamic')
            ->map(fn ($items, $year) => [
                'year' => $year,
                'count' => $items->count(),
            ])
            ->sortByDesc('year')
            ->values();

        $datasetsByCourse = $datasets
            ->groupBy(fn (Dataset $dataset) => $dataset->course ?: 'Unmapped')
            ->map(fn ($items, $course) => [
                'course' => $course,
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
            ->values();

        $topDatasets = Schema::hasTable('datasets') && Schema::hasTable('rank_records')
            ? Dataset::query()
                ->withCount('rankRecords')
                ->orderByDesc('rank_records_count')
                ->orderByDesc('year')
                ->take(6)
                ->get()
            : collect();

        $latestPages = collect($menus)
            ->flatMap(fn ($items, $group) => collect($items)->map(fn ($item) => $item + ['group' => $group]))
            ->merge($datasets->map(fn (Dataset $dataset) => [
                'label' => $dataset->title,
                'route' => 'results.show',
                'params' => ['dataset' => $dataset->slug],
                'group' => 'Results ' . ($dataset->year ?? 'Dynamic'),
            ]))
            ->take(8)
            ->values();

        return view('admin.dashboard', compact(
            'menus',
            'pageCount',
            'yearCount',
            'userCount',
            'adminCount',
            'verifiedMobileCount',
            'studentCount',
            'paidUserCount',
            'unpaidUserCount',
            'basicPlanCount',
            'premiumPlanCount',
            'importCount',
            'completedImportCount',
            'failedImportCount',
            'recordCount',
            'sheetCount',
            'roundCount',
            'paymentCount',
            'completedPaymentCount',
            'pendingPaymentCount',
            'totalRevenue',
            'latestImport',
            'recentImports',
            'recentPayments',
            'recentUsers',
            'datasetsByYear',
            'datasetsByCourse',
            'topDatasets',
            'latestPages'
        ));
    }

    public function users(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');
        $plan = $request->query('plan');

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['paid', 'unpaid'], true), fn ($query) => $query->where('payment_status', $status))
            ->when(in_array($plan, ['basic', 'premium', 'none'], true), fn ($query) => $query->where('plan', $plan))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users', compact('users', 'search', 'status', 'plan'));
    }

    public function payments(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $payments = Payment::query()
            ->with('user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('transaction_id', 'like', "%{$search}%")
                        ->orWhere('order_id', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $totalRevenue = Payment::whereIn('status', ['completed', 'captured'])->sum('amount');

        return view('admin.payments', compact('payments', 'search', 'status', 'totalRevenue'));
    }
}
