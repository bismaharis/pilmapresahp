<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $faker = Faker::create('id_ID');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // faculty_id tidak diisi user, set random 1-9
        $facId = rand(1, 9);

        $fakultasProdi = [
            1 => ['Teknik Informatika', 'Teknik Sipil', 'Teknik Elektro'],
            2 => ['Manajemen', 'Akuntansi', 'Ilmu Ekonomi'],
            3 => ['Agroekoteknologi', 'Agribisnis'],
            4 => ['Peternakan'],
            5 => ['Ilmu Komunikasi', 'Sosiologi', 'Ilmu Hukum'],
            6 => ['Teknologi Pangan', 'Teknik Pertanian'],
            7 => ['Pendidikan Biologi', 'Pendidikan Matematika', 'PGSD'],
            8 => ['Matematika', 'Fisika', 'Biologi', 'Kimia'],
            9 => ['Pendidikan Dokter', 'Farmasi'],
        ];

        $prodi = $fakultasProdi[$facId][array_rand($fakultasProdi[$facId])] ?? 'Umum';

        return DB::transaction(function () use ($request, $faker, $facId, $prodi) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'mahasiswa',
                'faculty_id' => $facId,
                'email_verified_at' => now(),
            ]);

            $nimPrefix = $faker->randomElement(['F1D0', 'A1B0', 'C1G0']);
            $nim = $nimPrefix.$faker->numberBetween(21, 23).$faker->unique()->numerify('###');

            $studentId = DB::table('students')->insertGetId([
                'user_id' => $user->id,
                'faculty_id' => $facId,
                'nim' => $nim,
                'prodi' => $prodi,
                'semester' => $faker->randomElement([2, 6]),
                'ipk' => $faker->randomFloat(2, 3.20, 4.00),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Ambil periode aktif milik fakultas ini, atau buat baru jika belum ada
            $period = DB::table('pilmapres_periods')
                ->where('is_active', true)
                ->where('faculty_id', $facId)
                ->first();

            if (! $period) {
                $periodId = DB::table('pilmapres_periods')->insertGetId([
                    'faculty_id' => $facId,
                    'year' => now()->year,
                    'is_active' => true,
                    'start_date' => now(),
                    'end_date' => now()->addMonths(3),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $periodId = $period->id;
            }

            DB::table('registrations')->insert([
                'period_id' => $periodId,
                'student_id' => $studentId,
                'stage' => 'fakultas',
                'status' => 'submitted',
                'total_score_fakultas' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            event(new Registered($user));
            Auth::login($user);

            return redirect()->route('dashboard');
        });
    }
}
