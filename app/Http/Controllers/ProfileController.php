<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $faculties = \App\Models\Faculty::all();

        return view('profile.edit', [
            'user' => $request->user(),
            'faculties' => $faculties,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        if ($request->hasFile('photo')) {
            if ($request->user()->photo && \Illuminate\Support\Facades\Storage::disk(config('filesystems.default_public_disk'))->exists($request->user()->photo)) {
                \Illuminate\Support\Facades\Storage::disk(config('filesystems.default_public_disk'))->delete($request->user()->photo);
            }
            $path = $request->file('photo')->store('profile-photos', config('filesystems.default_public_disk'));
            $request->user()->photo = $path;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    // update mmhs
    public function updateAcademic(Request $request): RedirectResponse
    {
        $request->validate([
            'nim' => 'required|string|max:20',
            'prodi' => 'required|string|max:100',
            'semester' => 'required|integer|min:1|max:14',
            'ipk' => 'required|numeric|min:0|max:4',
        ]);

        $lockedFacultyId = (int) ($request->user()->student?->faculty_id ?? $request->user()->faculty_id ?? 0);
        if ($lockedFacultyId <= 0) {
            return back()->with('error', 'Data fakultas akun Anda belum valid. Hubungi admin untuk pembaruan fakultas.');
        }

        $request->user()->student()->updateOrCreate(
            ['user_id' => $request->user()->id],
            array_merge(
                $request->only(['nim', 'prodi', 'semester', 'ipk']),
                ['faculty_id' => $lockedFacultyId]
            )
        );

        return back()->with('status', 'academic-updated')->with('success', 'Data Akademik berhasil disimpan!');
    }

    // update juri
    public function updateLecturer(Request $request): RedirectResponse
    {
        $request->validate([
            'nip' => 'nullable|string|max:50',
            'unit_kerja' => 'required|string|max:100',
        ]);

        $lockedFacultyId = (int) ($request->user()->lecturer?->faculty_id ?? $request->user()->faculty_id ?? 0);
        if ($lockedFacultyId <= 0) {
            return back()->with('error', 'Data fakultas akun Anda belum valid. Hubungi admin untuk pembaruan fakultas.');
        }

        $request->user()->lecturer()->updateOrCreate(
            ['user_id' => $request->user()->id],
            array_merge(
                $request->only(['nip', 'unit_kerja']),
                ['faculty_id' => $lockedFacultyId]
            )
        );

        return back()->with('status', 'lecturer-updated')->with('success', 'Data Pegawai berhasil disimpan!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
