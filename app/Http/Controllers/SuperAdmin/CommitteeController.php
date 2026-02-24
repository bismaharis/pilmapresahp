<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CommitteeController extends Controller
{
    public function index()
    {
        $committees = User::whereIn('role', ['admin_univ', 'admin_fakultas'])->get();
        return view('superadmin.committees.index', compact('committees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin_univ,admin_fakultas'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
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
        return view('superadmin.committees.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'role' => 'required|in:admin_univ,admin_fakultas',
            'password' => 'nullable|string|min:8', 
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        $user->save();

        return redirect()->route('superadmin.committees.index')->with('success', 'Data Panitia berhasil diperbarui!');
    }
}