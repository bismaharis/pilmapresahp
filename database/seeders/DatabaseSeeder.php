<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
            
        // ]);

        // 1. Jalankan Faculty Seeder dulu
        $this->call([
            FacultySeeder::class,
            CriteriaSeeder::class, 
            PeriodSeeder::class,
        ]);

        // 2. Ambil semua fakultas dari database
        // $faculties = \App\Models\Faculty::all();

        // // 3. Buat Admin Fakultas dan Juri berdasarkan nama fakultas asli
        // foreach ($faculties as $faculty) {
            
        //     // Buat Admin Fakultas
        //     \App\Models\User::create([
        //         'name' => 'Admin ' . $faculty->name,
        //         'email' => 'admin' . $faculty->slug . '@gmail.com', // adminteknik@pilmapres.com
        //         'password' => bcrypt('password'),
        //         'role' => 'admin_fakultas',
        //     ]);

        //     // Buat 2 Juri per Fakultas
        //     for ($i = 1; $i <= 2; $i++) {
        //         $juri = \App\Models\User::create([
        //             'name' => 'Dr. Juri ' . $faculty->name . ' ' . $i,
        //             'email' => 'juri' . $faculty->slug . $i . '@gmail.com', // juriteknik1@pilmapres.com
        //             'password' => bcrypt('password'),
        //             'role' => 'dosen',
        //         ]);

        //         \App\Models\Lecturer::create([
        //             'user_id' => $juri->id,
        //             'faculty_id' => $faculty->id,
        //             'nip' => '198' . rand(1000000000000, 9999999999999),
        //         ]);
        //     }
        // }

        $this->call([
            UserSeeder::class,
        ]);
    }
}
