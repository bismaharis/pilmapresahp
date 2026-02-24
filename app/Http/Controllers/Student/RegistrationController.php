<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RegistrationController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;

        if (!$student) {
            return redirect()->route('profile.edit')
                ->with('error', 'Silakan lengkapi biodata akademik Anda (NIM, Prodi, dsb) terlebih dahulu sebelum mengakses halaman pendaftaran.');
        }
        
        $registration = Registration::firstOrCreate(
            ['student_id' => $student->id, 'period_id' => 1], 
            ['status' => 'draft']
        );

        return view('student.registration.index', compact('registration', 'student'));
    }

    public function update(Request $request)
    {
        $request->validate([
            // Syarat Fakultas
            'file_gk' => 'nullable|file|mimes:pdf|max:10240',
            'file_transkrip' => 'nullable|file|mimes:pdf|max:10240',
            
            'file_poster_gk' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'file_poster_diri' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            
            'video_link' => 'nullable|url|max:255',
        ], [
            // Pesan error kustom (opsional tapi sangat disarankan)
            'file_gk.max' => 'Ukuran file Naskah Gagasan Kreatif tidak boleh lebih dari 10MB.',
            'file_transkrip.max' => 'Ukuran file Transkrip Nilai tidak boleh lebih dari 10MB.',
            'file_gk.mimes' => 'Naskah Gagasan Kreatif wajib berformat PDF.',
            'file_transkrip.mimes' => 'Transkrip Nilai wajib berformat PDF.',
            
            'file_poster_gk.max' => 'Ukuran file Poster Gagasan Kreatif tidak boleh lebih dari 5MB.',
            'file_poster_diri.max' => 'Ukuran file Poster Diri tidak boleh lebih dari 5MB.',
            'file_poster_gk.mimes' => 'Poster Gagasan Kreatif wajib berformat PDF/JPEG/PNG.',
            'file_poster_diri.mimes' => 'Poster Diri wajib berformat PDF/JPEG/PNG.',
        ]);

        $student = Auth::user()->student;
        $registration = Registration::where('student_id', $student->id)->firstOrFail();
        $dataToUpdate = [];

       // 2. Upload Berkas FAKULTAS
        if ($request->hasFile('file_gk')) {
            if ($registration->file_gk) Storage::disk('public')->delete($registration->file_gk);
            $dataToUpdate['file_gk'] = $request->file('file_gk')->store('files/gk', 'public');
        }

        if ($request->hasFile('file_transkrip')) {
            if ($registration->file_transkrip) Storage::disk('public')->delete($registration->file_transkrip);
            $dataToUpdate['file_transkrip'] = $request->file('file_transkrip')->store('files/transkrip', 'public');
        }

        // 3. Upload Berkas UNIVERSITAS
        if ($registration->stage == 'universitas') {
            
            if ($request->hasFile('file_poster_gk')) {
                if ($registration->file_poster_gk) Storage::disk('public')->delete($registration->file_poster_gk);
                $dataToUpdate['file_poster_gk'] = $request->file('file_poster_gk')->store('files/posters', 'public');
            }

            if ($request->hasFile('file_poster_diri')) {
                if ($registration->file_poster_diri) Storage::disk('public')->delete($registration->file_poster_diri);
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