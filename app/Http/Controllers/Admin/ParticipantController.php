<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ParticipantController extends Controller
{
    public function index(Request $request)
    {
        $faculties = Faculty::all();
        $admin = Auth::user();

        $query = User::where('role', 'mahasiswa')->with('student.faculty');

        if ($admin->role === 'admin_fakultas') {
            $query->whereHas('student', function($q) use ($admin) {
                $q->where('faculty_id', $admin->faculty_id);
            });
        } 
        else {
            if ($request->filled('faculty_id')) {
                $query->whereHas('student', function($q) use ($request) {
                    $q->where('faculty_id', $request->faculty_id);
                });
            }
        }

        $participants = $query->get();
        return view('admin.participants.index', compact('participants', 'faculties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'nim' => 'required|string|max:50|unique:students,nim',
            'prodi' => 'required|string|max:100',
            'faculty_id' => 'required|exists:faculties,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'mahasiswa',
        ]);

        Student::create([
            'user_id' => $user->id,
            'faculty_id' => $request->faculty_id,
            'nim' => $request->nim,
            'prodi' => $request->prodi,
            'semester' => 1, 
            'ipk' => 0.00     
        ]);

        return back()->with('success', 'Akun Peserta berhasil ditambahkan!');
    }

    public function update(Request $request, User $user)
    {
        $studentId = $user->student ? $user->student->id : null;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8',
            'nim' => 'required|string|max:50|unique:students,nim,'.$studentId,
            'prodi' => 'required|string|max:100',
            'faculty_id' => 'required|exists:faculties,id',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        $user->student()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'faculty_id' => $request->faculty_id,
                'nim' => $request->nim,
                'prodi' => $request->prodi,
            ]
        );

        return back()->with('success', 'Data Peserta berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        // $user->delete();
        // return back()->with('success', 'Akun Peserta berhasil dihapus!');

        try {
        DB::transaction(function () use ($user) {
            if ($user->student) {
                DB::table('registrations')
                    ->where('student_id', $user->student->id)
                    ->delete();

                $user->student->delete();
            }

            $user->delete();
        });

        return back()->with('success', 'Akun Peserta dan semua data terkait berhasil dihapus!');
    } catch (\Exception $e) {
        return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
    }
    }
}