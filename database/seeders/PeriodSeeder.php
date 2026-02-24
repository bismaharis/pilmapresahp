<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeriodSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pilmapres_periods')->insert([
            'year' => '2026',
            'is_active' => true,
            'start_date' => now(),
            'end_date' => now()->addMonths(3), 
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}