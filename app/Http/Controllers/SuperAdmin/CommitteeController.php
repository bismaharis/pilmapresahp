<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CommitteeController extends Controller
{
    public function index(Request $request)
    {
        $faculties = Faculty::all();
        $query = User::whereIn('role', ['admin_fakultas', 'admin_univ']);

        // Filter berdasarkan Dropdown
        if ($request->filled('faculty_id')) {
            $query->where('faculty_id', $request->faculty_id); 
        }

        // Eksekusi query
        $committees = $query->get();
        return view('superadmin.committees.index', compact('committees', 'faculties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin_univ,admin_fakultas',
            'faculty_id' => 'nullable|exists:faculties,id' 
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'faculty_id' => $request->faculty_id 
        ]);

        return back()->with('success', 'Akun Panitia berhasil ditambahkan!');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'Akun Panitia berhasil dihapus!');
    }

    public function edit(User $user)
    {
        $faculties = Faculty::all(); 
        return view('superadmin.committees.edit', compact('user', 'faculties'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'role' => 'required|in:admin_univ,admin_fakultas',
            'password' => 'nullable|string|min:8', 
            'faculty_id' => 'nullable|exists:faculties,id' 
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->faculty_id = $request->faculty_id; 
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        $user->save();

        return redirect()->route('superadmin.committees.index')->with('success', 'Data Panitia berhasil diperbarui!');
    }
}