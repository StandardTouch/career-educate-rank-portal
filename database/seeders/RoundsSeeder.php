<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoundsSeeder extends Seeder
{
    public function run(): void
    {
        $rounds = [
            ['name' => 'Round 1',            'slug' => 'round_1',            'sort_order' => 1],
            ['name' => 'Round 2',            'slug' => 'round_2',            'sort_order' => 2],
            ['name' => 'Mopup Round',        'slug' => 'mopup_round',        'sort_order' => 3],
            ['name' => 'Stray Round',        'slug' => 'stray_round',        'sort_order' => 4],
            ['name' => 'Special Stray Round','slug' => 'special_stray_round','sort_order' => 5],
        ];

        DB::table('rounds')->insertOrIgnore(array_map(function ($r) {
            return array_merge($r, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, $rounds));
    }
}
