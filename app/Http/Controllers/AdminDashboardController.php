<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $menus = config('menus', []);
        $pageCount = collect($menus)->flatten(1)->count();
        $yearCount = count($menus);
        $userCount = Schema::hasTable('users') ? DB::table('users')->count() : 0;
        $latestPages = collect($menus)
            ->flatMap(fn ($items, $group) => collect($items)->map(fn ($item) => $item + ['group' => $group]))
            ->take(8)
            ->values();

        return view('admin.dashboard', compact('menus', 'pageCount', 'yearCount', 'userCount', 'latestPages'));
    }
}
