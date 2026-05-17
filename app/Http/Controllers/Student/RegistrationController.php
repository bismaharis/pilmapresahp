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

        $activeFacultyPeriod = PilmapresPeriod::getActivePeriodForFaculty($student->faculty_id);
        $activeUniversityPeriod = PilmapresPeriod::getActiveUniversityPeriod();

        $registration = null;

        if ($activeFacultyPeriod) {
            $registration = $student->registrations()
                ->where('period_id', '=', $activeFacultyPeriod->id)
                ->latest('id')
                ->first();

            if (! $registration) {
                $registration = Registration::firstOrCreate(
                    ['student_id' => $student->id, 'period_id' => $activeFacultyPeriod->id],
                    ['status' => 'draft']
                );
            }
        }

        if (! $registration) {
            $registration = $student->registrations()
                ->latest('id')
                ->first();
        }

        // Pastikan data fresh dari database
        if ($registration) {
            $registration = $registration->fresh();
        }

        return view('student.registration.index', [
            'registration' => $registration,
            'student' => $student,
            'activePeriod' => $registration && $registration->stage === 'universitas'
                ? $activeUniversityPeriod
                : $activeFacultyPeriod,
        ]);
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

        if (! $student) {
            return redirect()->route('profile.edit')
                ->with('error', 'Silakan lengkapi biodata akademik Anda (NIM, Prodi, dsb) terlebih dahulu sebelum mengakses halaman pendaftaran.');
        }

        $activeFacultyPeriod = PilmapresPeriod::getActivePeriodForFaculty($student->faculty_id);
        $activeUniversityPeriod = PilmapresPeriod::getActiveUniversityPeriod();

        $registration = null;

        if ($activeFacultyPeriod) {
            $registration = $student->registrations()
                ->where('period_id', '=', $activeFacultyPeriod->id)
                ->latest('id')
                ->first();
        }

        if (! $registration) {
            $registration = $student->registrations()
                ->latest('id')
                ->first();
        }

        if (! $registration) {
            return back()->with('error', 'Tidak ada data pendaftaran yang ditemukan.');
        }

        $activePeriod = $registration->stage === 'universitas'
            ? $activeUniversityPeriod
            : $activeFacultyPeriod;

        if (! $activePeriod) {
            $message = $registration->stage === 'universitas'
                ? 'Pendaftaran ditutup. Tidak ada periode seleksi tingkat universitas yang sedang berjalan.'
                : 'Pendaftaran ditutup. Tidak ada periode seleksi yang sedang berjalan untuk fakultas Anda.';

            return back()->with('error', $message);
        }

        $dataToUpdate = [];

        // Upload berkas FAKULTAS
        if ($request->hasFile('file_gk')) {
            $newFileGkPath = $request->file('file_gk')->store('files/gk', config('filesystems.default_public_disk'));
            if ($registration->file_gk) {
                Storage::disk(config('filesystems.default_public_disk'))->delete($registration->file_gk);
            }
            $dataToUpdate['file_gk'] = $newFileGkPath;
        }

        if ($request->hasFile('file_transkrip')) {
            $newTranscriptPath = $request->file('file_transkrip')->store('files/transkrip', config('filesystems.default_public_disk'));
            if ($registration->file_transkrip) {
                Storage::disk(config('filesystems.default_public_disk'))->delete($registration->file_transkrip);
            }
            $dataToUpdate['file_transkrip'] = $newTranscriptPath;
        }

        // Upload berkas UNIVERSITAS
        if ($registration->stage === 'universitas') {
            if ($request->hasFile('file_poster_gk')) {
                $newPosterGkPath = $request->file('file_poster_gk')->store('files/posters', config('filesystems.default_public_disk'));
                if ($registration->file_poster_gk) {
                    Storage::disk(config('filesystems.default_public_disk'))->delete($registration->file_poster_gk);
                }
                $dataToUpdate['file_poster_gk'] = $newPosterGkPath;
            }

            if ($request->hasFile('file_poster_diri')) {
                $newPosterDiriPath = $request->file('file_poster_diri')->store('files/posters', config('filesystems.default_public_disk'));
                if ($registration->file_poster_diri) {
                    Storage::disk(config('filesystems.default_public_disk'))->delete($registration->file_poster_diri);
                }
                $dataToUpdate['file_poster_diri'] = $newPosterDiriPath;
            }

            if ($request->filled('video_link')) {
                $dataToUpdate['video_link'] = $request->video_link;
            }
        }

        if ($registration->stage === 'universitas') {
            $dataToUpdate['submitted_universitas_at'] = $registration->submitted_universitas_at ?? now();
        } else {
            $dataToUpdate['submitted_fakultas_at'] = $registration->submitted_fakultas_at ?? now();

            $resolvedFileGk = $dataToUpdate['file_gk'] ?? $registration->file_gk;
            $resolvedFileTranskrip = $dataToUpdate['file_transkrip'] ?? $registration->file_transkrip;

            if ($resolvedFileGk && $resolvedFileTranskrip) {
                $dataToUpdate['status'] = 'submitted';
            }
        }

        $registration->update($dataToUpdate);

        return back()->with('success', 'Berkas pendaftaran berhasil disimpan.');
    }
}
