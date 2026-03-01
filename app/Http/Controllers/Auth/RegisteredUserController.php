<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
// use Illuminate\Container\Attributes\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Faker\Factory as Faker;

class RegisteredUserController extends Controller
{

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $faker = Faker::create('id_ID');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
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

        $facId = array_rand($fakultasProdi);
        $prodi = $faker->randomElement($fakultasProdi[$facId]);

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
            $nim = $nimPrefix . $faker->numberBetween(21, 23) . $faker->unique()->numerify('###');

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

            DB::table('registrations')->insert([
                'period_id' => 1,
                'student_id' => $studentId,
                'stage' => 'fakultas',
                'status' => 'submitted',
                'total_score_fakultas' => $faker->randomFloat(2, 50, 95),
                'created_at' => now(), 'updated_at' => now()
            ]);

            event(new Registered($user));

            Auth::login($user); 

            return redirect()->route('dashboard');
        });    
    }
}
