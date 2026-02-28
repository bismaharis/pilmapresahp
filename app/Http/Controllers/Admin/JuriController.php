<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class JuriController extends Controller
{
    // public function index()
    // {
    //     // Ambil semua juri beserta data dosennya
    //     $juris = User::with('lecturer')->where('role', 'dosen')->get();
    //     return view('admin.juries.index', compact('juris'));
    // }

    public function index(Request $request)
    {
        $faculties = Faculty::all();
        $query = User::where('role', 'dosen')->with('lecturer.faculty');

        // Jika Dropdown Fakultas dipilih
        if ($request->filled('faculty_id')) {
            $query->whereHas('lecturer', function($q) use ($request) {
                $q->where('faculty_id', $request->faculty_id);
            });
        }

        $juries = $query->get();
        return view('admin.juries.index', compact('juries', 'faculties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'nip' => 'nullable|string|max:50',
            'faculty_id' => 'required|exists:faculties,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'dosen',
        ]);

        Lecturer::create([
            'user_id' => $user->id,
            'nip' => $request->nip,
            'faculty_id' => $request->faculty_id,
        ]);

        return back()->with('success', 'Akun Juri berhasil ditambahkan!');
    }

    public function destroy(User $user)
    {
        $user->delete(); 
        return back()->with('success', 'Akun Juri berhasil dihapus!');
    }

    public function edit(User $user)
    {
        $user->load('lecturer');
        $faculties = Faculty::all();
        return view('admin.juries.edit', compact('user', 'faculties'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'nip' => 'nullable|string|max:50',
            'faculty_id' => 'required|exists:faculties,id',
            'password' => 'nullable|string|min:8', 
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        $user->lecturer()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'nip' => $request->nip,
                'faculty_id' => $request->faculty_id,
            ]
        );

        return redirect()->route('admin.juries.index')->with('success', 'Data Juri berhasil diperbarui!');
    }
}