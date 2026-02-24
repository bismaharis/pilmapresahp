<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Achievement;
use App\Services\AchievementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AchievementController extends Controller
{
    public function __construct(
        protected AchievementService $achievementService
    ) {}

    public function index()
    {
        $student = Auth::user()->student; 

        if (!$student) {
            return redirect()->route('profile.edit')
                ->with('error', 'Silakan lengkapi biodata akademik Anda (NIM, Prodi, dsb) terlebih dahulu sebelum mengakses halaman pendaftaran.');
        }
        
        $registration = Registration::firstOrCreate(
            [
                'student_id' => $student->id,
                'period_id' => 1 
            ],
            ['status' => 'draft']
        );

        $achievements = $registration->achievements()->latest()->get();

        return view('student.achievements.index', compact('achievements', 'registration'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capaian' => 'required|string|max:255',
            'category' => 'required|string', 
            'organizer' => 'required|string',
            'year' => 'required|integer|min:2020|max:'.date('Y'),
            'type' => 'required|in:Individu,Kelompok',
            'jumlah_peserta' => 'required|integer|min:1',
            'jumlah_penghargaan' => 'required|string|max:255',
            'level' => 'required|in:Perguruan Tinggi,Provinsi,Regional,Nasional,Internasional',
            'file_proof' => 'required|file|mimes:pdf,jpg,png|max:5000',
        ]);

        $student = Auth::user()->student;

        $registration = Registration::firstOrCreate(
            ['student_id' => $student->id, 'period_id' => 1], 
            ['stage' => 'fakultas', 'status' => 'draft']
        );

        $path = $request->file('file_proof')->store('proofs', 'public');

        
        try {
            $this->achievementService->create($registration->id, array_merge(
                $request->all(), 
                ['file_proof' => $path]
            ));

            return back()->with('success', 'Capaian Unggulan berhasil ditambahkan.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function destroy($id)
    {
        $studentId = Auth::user()->student->id;
        $this->achievementService->delete($id, $studentId);
        
        return back()->with('success', 'Item berhasil dihapus.');
    }
}