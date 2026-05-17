<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CommitteeController extends Controller
{
    private const MANAGEABLE_ROLES = ['admin_fakultas', 'admin_univ'];

    public function index(Request $request)
    {
        $request->validate([
            'stage' => ['nullable', 'in:fakultas,universitas'],
            'faculty_id' => ['nullable', 'integer', 'exists:faculties,id'],
        ]);

        $stage = $request->query('stage', 'fakultas');
        $faculties = Faculty::all();
        $query = User::query();

        if ($stage === 'universitas') {
            $query->where('role', '=', 'admin_univ')
                ->where('faculty_id', '=', null);
        } else {
            $query->where('role', '=', 'admin_fakultas');

            if ($request->filled('faculty_id')) {
                $query->where('faculty_id', '=', $request->faculty_id);
            }
        }

        $committees = $query
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('superadmin.committees.index', compact('committees', 'faculties', 'stage'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin_univ,admin_fakultas',
            'faculty_id' => 'nullable|required_if:role,admin_fakultas|exists:faculties,id',
        ]);

        $facultyId = $request->role === 'admin_fakultas'
            ? (int) $request->faculty_id
            : null;

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'faculty_id' => $facultyId,
        ]);

        return back()->with('success', 'Akun Panitia berhasil ditambahkan!');
    }

    public function destroy(User $user)
    {
        $this->ensureManageableCommittee($user);

        if (Auth::id() === $user->id) {
            return back()->withErrors(['committee' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        User::query()->whereKey($user->id)->delete();

        return back()->with('success', 'Akun Panitia berhasil dihapus!');
    }

    public function edit(User $user)
    {
        $this->ensureManageableCommittee($user);

        $faculties = Faculty::all();

        return view('superadmin.committees.edit', compact('user', 'faculties'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureManageableCommittee($user);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'role' => 'required|in:admin_univ,admin_fakultas',
            'password' => 'nullable|string|min:8',
            'faculty_id' => 'nullable|required_if:role,admin_fakultas|exists:faculties,id',
        ]);

        $facultyId = $request->role === 'admin_fakultas'
            ? (int) $request->faculty_id
            : null;

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->faculty_id = $facultyId;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('superadmin.committees.index')->with('success', 'Data Panitia berhasil diperbarui!');
    }

    private function ensureManageableCommittee(User $user): void
    {
        abort_unless(in_array($user->role, self::MANAGEABLE_ROLES, true), 404);
    }
}
