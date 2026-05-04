<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class JuriController extends Controller
{
    /**
     * Ambil faculty_id yang berlaku untuk user yang sedang login.
     * - admin_fakultas : hanya fakultasnya sendiri
     * - super_admin / admin_univ : bisa semua (null = tidak dibatasi)
     */
    private function getAdminFacultyId(): ?int
    {
        $user = Auth::user();
        if ($user->role === 'admin_fakultas') {
            if ($user->faculty_id === null) {
                abort(403, 'Akun admin fakultas Anda belum terhubung ke fakultas. Hubungi Super Admin untuk memperbarui data fakultas akun Anda.');
            }

            return (int) $user->faculty_id;
        }

        return null;
    }

    public function index(Request $request)
    {
        $adminFacultyId = $this->getAdminFacultyId();

        // admin_fakultas: faculties hanya miliknya sendiri, tidak perlu dropdown
        $faculties = $adminFacultyId
            ? Faculty::where('id', $adminFacultyId)->get()
            : Faculty::all();

        $query = User::where('role', 'dosen')->with('lecturer.faculty');

        if ($adminFacultyId) {
            // paksa filter ke fakultas admin yang login
            $query->whereHas('lecturer', fn ($q) => $q->where('faculty_id', $adminFacultyId));
        } elseif ($request->filled('faculty_id')) {
            // super_admin / admin_univ: filter opsional dari dropdown
            $query->whereHas('lecturer', fn ($q) => $q->where('faculty_id', $request->faculty_id));
        }

        $juries = $query->get();

        return view('admin.juries.index', compact('juries', 'faculties', 'adminFacultyId'));
    }

    public function store(Request $request)
    {
        $adminFacultyId = $this->getAdminFacultyId();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'nip' => 'nullable|string|max:50',
            'faculty_id' => 'required|exists:faculties,id',
        ]);

        // Pastikan admin_fakultas tidak bisa membuat juri di fakultas lain
        if ($adminFacultyId && (int) $request->faculty_id !== $adminFacultyId) {
            abort(403, 'Anda hanya dapat menambah juri di fakultas Anda sendiri.');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'dosen',
            'faculty_id' => (int) $request->faculty_id,
        ]);

        Lecturer::create([
            'user_id' => $user->id,
            'nip' => $request->nip,
            'faculty_id' => $request->faculty_id,
        ]);

        return back()->with('success', 'Akun Juri berhasil ditambahkan!');
    }

    public function edit(User $user)
    {
        $this->authorizeJuri($user);

        $user->load('lecturer');
        $adminFacultyId = $this->getAdminFacultyId();

        $faculties = $adminFacultyId
            ? Faculty::where('id', $adminFacultyId)->get()
            : Faculty::all();

        return view('admin.juries.edit', compact('user', 'faculties', 'adminFacultyId'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeJuri($user);

        $adminFacultyId = $this->getAdminFacultyId();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'nip' => 'nullable|string|max:50',
            'faculty_id' => 'required|exists:faculties,id',
            'password' => 'nullable|string|min:8',
        ]);

        if ($adminFacultyId && (int) $request->faculty_id !== $adminFacultyId) {
            abort(403, 'Anda hanya dapat memindahkan juri dalam fakultas Anda sendiri.');
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->faculty_id = (int) $request->faculty_id;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        $user->lecturer()->updateOrCreate(
            ['user_id' => $user->id],
            ['nip' => $request->nip, 'faculty_id' => $request->faculty_id]
        );

        return redirect()->route('admin.juries.index')->with('success', 'Data Juri berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        $this->authorizeJuri($user);
        $user->delete();

        return back()->with('success', 'Akun Juri berhasil dihapus!');
    }

    /**
     * Pastikan admin_fakultas hanya bisa mengubah/hapus juri di fakultasnya.
     */
    private function authorizeJuri(User $user): void
    {
        $adminFacultyId = $this->getAdminFacultyId();
        if (! $adminFacultyId) {
            return;
        }

        $jurisFacultyId = $user->lecturer?->faculty_id;
        if ((int) $jurisFacultyId !== $adminFacultyId) {
            abort(403, 'Anda tidak memiliki akses ke juri ini.');
        }
    }
}
