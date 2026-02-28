<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $periodExists = DB::table('pilmapres_periods')->where('id', 1)->exists();
        if (!$periodExists) {
            DB::table('pilmapres_periods')->insert([
                'id' => 1,
                'year' => 2026,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);
        User::create([
            'name' => 'Admin Universitas',
            'email' => 'adminuniv@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin_univ',
            'email_verified_at' => now(),
        ]);

        $fakultasProdi = [
            1 => ['Teknik Informatika', 'Teknik Sipil', 'Teknik Elektro'], // Teknik
            2 => ['Manajemen', 'Akuntansi', 'Ilmu Ekonomi'], // FEB
            3 => ['Agroekoteknologi', 'Agribisnis'], // Pertanian
            4 => ['Peternakan'], // Peternakan
            5 => ['Ilmu Komunikasi', 'Sosiologi', 'Ilmu Hukum'], // ISIP
            6 => ['Teknologi Pangan', 'Teknik Pertanian'], // Fatepa
            7 => ['Pendidikan Biologi', 'Pendidikan Matematika', 'PGSD'], // FKIP
            8 => ['Matematika', 'Fisika', 'Biologi', 'Kimia'], // MIPA
            9 => ['Pendidikan Dokter', 'Farmasi'], // Kedokteran
        ];

        $studentCount = 0;

        foreach ($fakultasProdi as $facId => $prodis) {
            User::create([
                'name' => 'Admin Fakultas ' . $facId,
                'email' => 'adminfakultas'.$facId.'@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'admin_fakultas',
                'faculty_id' => $facId,
                'email_verified_at' => now(),
            ]);

            for ($j = 1; $j <= 2; $j++) {
                $juri = User::create([
                    'name' => 'Dr. Juri Fakultas ' . $facId . ' - ' . $j,
                    'email' => 'juri'.$facId.'_'.$j.'@gmail.com',
                    'password' => Hash::make('password'),
                    'role' => 'dosen',
                    'faculty_id' => $facId,
                    'email_verified_at' => now(),
                ]);
                DB::table('lecturers')->insert([
                    'user_id' => $juri->id,
                    'faculty_id' => $facId,
                    'nip' => $faker->unique()->numerify('198#######2025011001'),
                    'unit_kerja' => 'Fakultas ' . $facId,
                    'created_at' => now(), 
                    'updated_at' => now()
                ]);
            }

            foreach ($prodis as $prodi) {
                $studentCount++;
                $mhs = User::create([
                    'name' => 'Mhs ' . $prodi . ' ' . $studentCount,
                    'email' => 'mhs'.$studentCount.'@gmail.com',
                    'password' => Hash::make('password'),
                    'role' => 'mahasiswa',
                    'faculty_id' => $facId,
                    'email_verified_at' => now(),
                ]);

                $studentId = DB::table('students')->insertGetId([
                    'user_id' => $mhs->id,
                    'nim' => $faker->randomElement(['F1D0', 'A1B0', 'C1G0']) . $faker->numberBetween(21, 23) . str_pad($studentCount, 3, '0', STR_PAD_LEFT),
                    'prodi' => $prodi,
                    'faculty_id' => $facId,
                    'semester' => $faker->randomElement([4, 6]),
                    'ipk' => $faker->randomFloat(2, 3.20, 4.00),
                    'created_at' => now(), 'updated_at' => now()
                ]);

                DB::table('registrations')->insert([
                    'period_id' => 1,
                    'student_id' => $studentId,
                    'stage' => 'fakultas',
                    'status' => 'submitted',
                    'total_score_fakultas' => $faker->randomFloat(2, 50, 95),
                    'created_at' => now(), 'updated_at' => now()
                ]);
            }
        }

        while ($studentCount < 30) {
            $studentCount++;
            $facId = array_rand($fakultasProdi);
            $prodi = $fakultasProdi[$facId][array_rand($fakultasProdi[$facId])];

            $mhs = User::create([
                'name' => 'Mhs Tambahan ' . $studentCount,
                'email' => 'mhs'.$studentCount.'@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'faculty_id' => $facId,
                'email_verified_at' => now(),
            ]);

            $studentId = DB::table('students')->insertGetId([
                'user_id' => $mhs->id,
                'nim' => $faker->randomElement(['F1D0', 'A1B0']) . $faker->numberBetween(21, 23) . str_pad($studentCount, 3, '0', STR_PAD_LEFT),
                'prodi' => $prodi,
                'faculty_id' => $facId,
                'semester' => $faker->randomElement([4, 6]),
                'ipk' => $faker->randomFloat(2, 3.20, 4.00),
                'created_at' => now(), 'updated_at' => now()
            ]);

            DB::table('registrations')->insert([
                'period_id' => 1,
                'student_id' => $studentId,
                'stage' => 'fakultas',
                'status' => 'submitted',
                'total_score_fakultas' => $faker->randomFloat(2, 50, 95),
                'created_at' => now(), 'updated_at' => now()
            ]);
        }
    }
}