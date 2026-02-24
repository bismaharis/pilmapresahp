<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        $faculties = [
            ['name' => 'Teknik', 'slug' => 'teknik'],
            ['name' => 'Ekonomi dan Bisnis', 'slug' => 'feb'],
            ['name' => 'Pertanian', 'slug' => 'pertanian'],
            ['name' => 'Peternakan', 'slug' => 'peternakan'],
            ['name' => 'Hukum, Ilmu Sosial dan Ilmu Politik', 'slug' => 'isip'],
            ['name' => 'teknologi pangan', 'slug' => 'fatepa'],
            ['name' => 'Keguruan dan Ilmu Pendidikan', 'slug' => 'fkip'],
            ['name' => 'Matematika dan Ilmu Pengetahuan Alam', 'slug' => 'mipa'],
            ['name' => 'Kedokteran', 'slug' => 'kedokteran'],
        ];

        DB::table('faculties')->insert($faculties);
    }
}