<?php

namespace App\Http\Controllers;

use App\Models\Criteria;
use App\Models\Faculty;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransparencyController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->role;
        $stage = $request->query('stage', 'fakultas');
        $faculties = Faculty::all();

        // $query = Registration::with(['student.user', 'student.faculty'])->where('status', '!=', 'draft');

        $query = Registration::with(['student.user', 'student.faculty'])
            ->whereIn('status', ['submitted', 'verified', 'approved']) 
            ->whereNotNull('file_gk')
            ->whereNotNull('file_transkrip');

        // LOGIKA FILTER BERDASARKAN ROLE
        if ($role === 'mahasiswa') {
            $student = $user->student;
            if (!$student) return redirect()->route('profile.edit')->with('error', 'Lengkapi biodata akademik Anda.');
            
            if ($stage === 'fakultas') {
                $query->whereHas('student', function($q) use ($student) {
                    $q->where('faculty_id', $student->faculty_id); 
                });
            }
            $myRegistration = Registration::where('student_id', $student->id)->first();
        }
        elseif (in_array($role, ['admin_fakultas', 'dosen'])) {
            $isUnivJudge = ($role === 'dosen' && $user->lecturer && $user->lecturer->is_univ_judge);
            $userFacultyId = $role === 'dosen' ? $user->lecturer->faculty_id : $user->faculty_id;

            if (!$isUnivJudge) {
                $query->whereHas('student', function($q) use ($userFacultyId) {
                    $q->where('faculty_id', $userFacultyId);
                });
            } else {
                if ($request->filled('faculty_id')) {
                    $query->whereHas('student', function($q) use ($request) {
                        $q->where('faculty_id', $request->faculty_id);
                    });
                }
            }
            $myRegistration = null;
        } 
        else {
            if ($request->filled('faculty_id')) {
                $query->whereHas('student', function($q) use ($request) {
                    $q->where('faculty_id', $request->faculty_id);
                });
            }
            $myRegistration = null;
        }

        // PENGURUTAN (RANKING)
        if ($stage === 'fakultas') {
            $rankings = $query->orderBy('total_score_fakultas', 'desc')->get();
        } else {
            $rankings = $query->where('stage', 'universitas')->orderBy('total_score_univ', 'desc')->get();
        }

        return view('transparency.index', compact('rankings', 'stage', 'myRegistration', 'role', 'user', 'faculties'));
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $role = $user->role;
        
        // Mahasiswa dilarang cetak PDF
        if ($role === 'mahasiswa') {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang mencetak PDF.');
        }

        $stage = $request->query('stage', 'fakultas');
        $isUnivJudge = ($role === 'dosen' && $user->lecturer && $user->lecturer->is_univ_judge);
        $userFacultyId = $role === 'dosen' ? $user->lecturer->faculty_id : $user->faculty_id;

        if ($isUnivJudge) {
            $stage = 'universitas'; 
        } elseif (in_array($role, ['admin_fakultas']) || ($role === 'dosen' && !$isUnivJudge)) {
            $stage = 'fakultas'; 
        }

        // Ambil data yang hanya sudah disubmit dan ada file-nya 
        $query = Registration::with(['student.user', 'student.faculty'])
            ->whereIn('status', ['submitted', 'verified', 'approved']) 
            ->whereNotNull('file_gk')
            ->whereNotNull('file_transkrip')
            ->where('stage', $stage);

        $facultyNameTitle = '';
        $fileNameSlug = '';

        if (in_array($role, ['admin_fakultas', 'dosen']) && !$isUnivJudge) {
            $query->whereHas('student', function($q) use ($userFacultyId) {
                $q->where('faculty_id', $userFacultyId);
            });
            $faculty = Faculty::find($userFacultyId);
            if ($faculty) {
                $facultyNameTitle = ' - ' . strtoupper($faculty->name);
                $fileNameSlug = '_' . \Illuminate\Support\Str::slug($faculty->name);
            }
        } 
        else {
            if ($request->filled('faculty_id')) {
                $query->whereHas('student', function($q) use ($request) {
                    $q->where('faculty_id', $request->faculty_id);
                });
                $faculty = Faculty::find($request->faculty_id);
                if ($faculty) {
                    $facultyNameTitle = ' - ' . strtoupper($faculty->name);
                    $fileNameSlug = '_' . \Illuminate\Support\Str::slug($faculty->name);
                }
            }
        }

        $scoreColumn = $stage === 'fakultas' ? 'total_score_fakultas' : 'total_score_univ';
        $rankings = $query->orderBy($scoreColumn, 'desc')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('transparency.pdf', compact('rankings', 'stage', 'scoreColumn', 'facultyNameTitle'));
        
        $pdf->setPaper('a4', 'portrait');
        $fileName = 'SK_Pemenang_Pilmapres_Tahap_' . ucfirst($stage) . $fileNameSlug . '_2026.pdf';

        return $pdf->download($fileName);
    }

    public function show(Request $request, $id)
    {
        $user = Auth::user();
        $role = $user->role;
        $stage = $request->query('stage', 'fakultas');

        $registration = Registration::with([
            'achievements',
            'assessments.criteria',
            'assessments.lecturer.user',
            'student.user',
            'student.faculty',
        ])->findOrFail($id);

        if ($role === 'mahasiswa') {
            if ($registration->student->user_id !== $user->id) {
                abort(403, 'Akses Ditolak: Anda hanya dapat melihat rincian transparansi nilai Anda sendiri.');
            }
        } elseif (in_array($role, ['admin_fakultas', 'dosen'])) {
            $isUnivJudge = ($role === 'dosen' && $user->lecturer && $user->lecturer->is_univ_judge);
            $userFacultyId = $role === 'dosen' ? $user->lecturer->faculty_id : $user->faculty_id;

            if (!$isUnivJudge && $registration->student->faculty_id !== $userFacultyId) {
                abort(403, 'Akses Ditolak: Anda hanya dapat melihat peserta dari Fakultas Anda.');
            }
        }

        $criterias = Criteria::whereNull('parent_id')
            ->with(['children.children.children'])
            ->get();

        // Data per-juri hanya disiapkan untuk role non-mahasiswa
        $assessmentsByLecturer = collect();
        $allJuriDone = false;

        if ($role !== 'mahasiswa') {
            // Kelompokkan: [ lecturer_id => [ criteria_id => assessment ] ]
            $assessmentsByLecturer = $registration->assessments
                ->groupBy('lecturer_id')
                ->map(fn($items) => $items->keyBy('criteria_id'));

            // Cek apakah semua juri yang diharapkan sudah menilai
            $expectedJuriQuery = \App\Models\Lecturer::query();
            if ($registration->stage === 'universitas') {
                $expectedJuriQuery->where('is_univ_judge', true);
            } else {
                $expectedJuriQuery->where('faculty_id', $registration->student->faculty_id)
                                  ->where('is_univ_judge', false);
            }
            $expectedJuriCount = $expectedJuriQuery->count();
            $juriYangSudahNilai = $assessmentsByLecturer->count();

            $allJuriDone = $expectedJuriCount > 0 && $juriYangSudahNilai >= $expectedJuriCount;
        }

        return view('transparency.show', compact(
            'registration', 'criterias', 'stage', 'role',
            'assessmentsByLecturer', 'allJuriDone'
        ));
    }
}