<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\PilmapresPeriod;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RegistrationController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;

        if (! $student) {
            return redirect()->route('profile.edit')
                ->with('error', 'Silakan lengkapi biodata akademik Anda (NIM, Prodi, dsb) terlebih dahulu sebelum mengakses halaman pendaftaran.');
        }

        $activePeriod = PilmapresPeriod::getActivePeriodForFaculty($student->faculty_id);

        // Hanya buat/ambil registration jika periode sedang berjalan
        if ($activePeriod) {
            $registration = Registration::firstOrCreate(
                ['student_id' => $student->id, 'period_id' => $activePeriod->id],
                ['status' => 'draft']
            );
        } else {
            // Coba ambil registration terakhir untuk ditampilkan (read-only)
            $registration = $student->registrations()->latest()->first();
        }

        return view('student.registration.index', compact('registration', 'student', 'activePeriod'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'file_gk' => 'nullable|file|mimes:pdf|max:10240',
            'file_transkrip' => 'nullable|file|mimes:pdf|max:10240',
            'file_poster_gk' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'file_poster_diri' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'video_link' => 'nullable|url|max:255',
        ], [
            'file_gk.max' => 'Ukuran file Naskah GK tidak boleh lebih dari 10MB.',
            'file_transkrip.max' => 'Ukuran file Transkrip tidak boleh lebih dari 10MB.',
            'file_gk.mimes' => 'Naskah GK wajib berformat PDF.',
            'file_transkrip.mimes' => 'Transkrip wajib berformat PDF.',
            'file_poster_gk.max' => 'Poster GK tidak boleh lebih dari 5MB.',
            'file_poster_diri.max' => 'Poster Diri tidak boleh lebih dari 5MB.',
            'file_poster_gk.mimes' => 'Poster GK wajib berformat PDF/JPEG/PNG.',
            'file_poster_diri.mimes' => 'Poster Diri wajib berformat PDF/JPEG/PNG.',
        ]);

        $student = Auth::user()->student;

        // Cek periode aktif — blokir jika tidak ada
        $activePeriod = PilmapresPeriod::getActivePeriodForFaculty($student->faculty_id);

        if (! $activePeriod) {
            return back()->with('error', 'Pendaftaran ditutup. Tidak ada periode seleksi yang sedang berjalan untuk fakultas Anda.');
        }

        // Ambil registration milik mahasiswa untuk periode yang sedang aktif saja
        $registration = Registration::where('student_id', $student->id)
            ->where('period_id', $activePeriod->id)
            ->firstOrFail();

        $dataToUpdate = [];

        // Upload berkas FAKULTAS
        if ($request->hasFile('file_gk')) {
            if ($registration->file_gk) {
                Storage::disk('public')->delete($registration->file_gk);
            }
            $dataToUpdate['file_gk'] = $request->file('file_gk')->store('files/gk', 'public');
        }

        if ($request->hasFile('file_transkrip')) {
            if ($registration->file_transkrip) {
                Storage::disk('public')->delete($registration->file_transkrip);
            }
            $dataToUpdate['file_transkrip'] = $request->file('file_transkrip')->store('files/transkrip', 'public');
        }

        // Upload berkas UNIVERSITAS
        if ($registration->stage === 'universitas') {
            if ($request->hasFile('file_poster_gk')) {
                if ($registration->file_poster_gk) {
                    Storage::disk('public')->delete($registration->file_poster_gk);
                }
                $dataToUpdate['file_poster_gk'] = $request->file('file_poster_gk')->store('files/posters', 'public');
            }

            if ($request->hasFile('file_poster_diri')) {
                if ($registration->file_poster_diri) {
                    Storage::disk('public')->delete($registration->file_poster_diri);
                }
                $dataToUpdate['file_poster_diri'] = $request->file('file_poster_diri')->store('files/posters', 'public');
            }

            if ($request->filled('video_link')) {
                $dataToUpdate['video_link'] = $request->video_link;
            }
        }

        $registration->update($dataToUpdate);

        return back()->with('success', 'Berkas pendaftaran berhasil diperbarui.');
    }
}
