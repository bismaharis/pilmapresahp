<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Registration;
use App\Services\UniversityDelegationNotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RankingController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'stage' => ['nullable', 'in:fakultas,universitas'],
            'faculty_id' => ['nullable', 'integer', 'exists:faculties,id'],
        ]);

        $faculties = Faculty::all();
        $user = Auth::user();
        $role = $user->role;

        $isUnivJudge = ($role === 'dosen' && $user->lecturer && $user->lecturer->is_univ_judge);

        // Jika Admin Fakultas, PAKSA stage = fakultas
        $stage = $request->query('stage', 'fakultas');
        $admin = Auth::user();

        $stage = $request->query('stage', 'fakultas');
        if ($isUnivJudge) {
            $stage = 'universitas';
        } elseif (in_array($role, ['admin_fakultas']) || ($role === 'dosen' && ! $isUnivJudge)) {
            $stage = 'fakultas';
        }

        $scoreColumn = $stage === 'fakultas' ? 'total_score_fakultas' : 'total_score_univ';

        $query = Registration::query()->with(['student.user', 'student.faculty'])
            ->where('stage', $stage)
            ->whereNotNull($scoreColumn);

        if ($role === 'admin_fakultas' || ($role === 'dosen' && ! $isUnivJudge)) {
            // hanya lihat fakultasnya sendiri
            $query->whereHas('student', function ($q) use ($user) {
                $q->where('faculty_id', $user->faculty_id);
            });
        } else {
            // Super Admin, Admin Univ, & Juri Univ izinkan pakai Dropdown Filter
            if ($request->filled('faculty_id')) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('faculty_id', $request->faculty_id);
                });
            }
        }

        $scoreColumn = $stage === 'fakultas' ? 'total_score_fakultas' : 'total_score_univ';
        $rankings = $query->orderBy($scoreColumn, 'desc')->get();

        return view('admin.ranking.index', compact('rankings', 'stage', 'scoreColumn', 'faculties', 'role', 'isUnivJudge'));
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'stage' => ['nullable', 'in:fakultas,universitas'],
            'faculty_id' => ['nullable', 'integer', 'exists:faculties,id'],
        ]);

        $user = Auth::user();
        $role = $user->role;

        $stage = $request->query('stage', 'fakultas');
        $isUnivJudge = ($role === 'dosen' && $user->lecturer && $user->lecturer->is_univ_judge);

        if ($isUnivJudge) {
            $stage = 'universitas';
        } elseif (in_array($role, ['admin_fakultas']) || ($role === 'dosen' && ! $isUnivJudge)) {
            $stage = 'fakultas';
        }

        $scoreColumn = $stage === 'fakultas' ? 'total_score_fakultas' : 'total_score_univ';

        $query = Registration::query()->with(['student.user', 'student.faculty'])
            ->where('stage', $stage)
            ->whereNotNull($scoreColumn);

        if ($role === 'admin_fakultas' || ($role === 'dosen' && ! $isUnivJudge)) {
            $query->whereHas('student', function ($q) use ($user) {
                $q->where('faculty_id', $user->faculty_id);
            });

            $faculty = Faculty::query()->whereKey($user->faculty_id)->first();
        } else {
            if ($request->filled('faculty_id')) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('faculty_id', $request->faculty_id);
                });
                $faculty = Faculty::query()->whereKey($request->faculty_id)->first();
            } else {
                $faculty = null;
            }
        }

        $facultyNameTitle = $faculty ? ' - '.strtoupper($faculty->name) : '';
        $fileNameSlug = $faculty ? '_'.\Illuminate\Support\Str::slug($faculty->name) : '';

        $rankings = $query->orderBy($scoreColumn, 'desc')->get();

        Log::info('Ranking PDF Export Debug', [
            'stage' => $stage,
            'role' => $role,
            'user_faculty_id' => $user->faculty_id ?? null,
            'request_faculty_id' => $request->faculty_id,
            'rankings_count' => $rankings->count(),
            'first_ranking' => $rankings->first() ? [
                'id' => $rankings->first()->id,
                'stage' => $rankings->first()->stage,
                'status' => $rankings->first()->status,
                'score' => $rankings->first()->$scoreColumn ?? null,
                'student_nim' => $rankings->first()->student->nim ?? null,
                'student_prodi' => $rankings->first()->student->prodi ?? null,
                'user_name' => $rankings->first()->student->user->name ?? 'null',
            ] : null,
        ]);

        $pdf = Pdf::loadView('admin.ranking.pdf', compact('rankings', 'stage', 'scoreColumn', 'facultyNameTitle'));
        $pdf->setOptions(['defaultFont' => 'DejaVu Sans', 'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
        $pdf->setPaper('a4', 'portrait');

        $fileName = 'SK_Pemenang_Pilmapres_Tahap_'.ucfirst($stage).$fileNameSlug.'_2026.pdf';

        return $pdf->download($fileName);

        // $query = Registration::with(['student.user', 'student.faculty'])
        //     ->where('status', '!=', 'draft')
        //     ->where('stage', $stage);

        // if ($admin->role === 'admin_fakultas') {
        //     $query->whereHas('student', function($q) use ($admin) {
        //         // Asumsi: akun Admin memiliki field faculty_id di tabel users
        //         $q->where('faculty_id', $admin->faculty_id);
        //     });
        // }
        // else {
        //     if ($request->filled('faculty_id')) {
        //         $query->whereHas('student', function($q) use ($request) {
        //             $q->where('faculty_id', $request->faculty_id);
        //         });
        //     }
        // }

        // $scoreColumn = $stage === 'fakultas' ? 'total_score_fakultas' : 'total_score_univ';
        // $rankings = $query->orderBy($scoreColumn, 'desc')->get();

        // return view('admin.ranking.index', compact('rankings', 'stage', 'scoreColumn', 'faculties'));
    }

    public function delegate(Registration $registration, UniversityDelegationNotificationService $notificationService)
    {
        $registration->loadMissing('student.user');

        $user = Auth::user();
        if ($user->role === 'admin_fakultas' &&
            $registration->student->faculty_id != $user->faculty_id) {
            abort(403, 'Anda tidak berhak mendelegasikan peserta dari fakultas lain.');
        }

        if ($registration->stage !== 'fakultas') {
            return back()->with('error', 'Peserta hanya dapat didelegasikan jika berada di tahap fakultas.');
        }

        $registration->update(['stage' => 'universitas']);

        try {
            $notificationService->sendParticipantDelegatedToUniversity($registration);
        } catch (\Throwable $exception) {
            Log::error('Gagal mengirim email delegasi peserta universitas.', [
                'registration_id' => $registration->id,
                'student_id' => $registration->student_id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('warning', 'Peserta berhasil didelegasikan, tetapi email notifikasi gagal dikirim.');
        }

        return back()->with('success', 'Peserta berhasil didelegasikan ke tingkat Universitas!');
    }

    public function cancelDelegate(Registration $registration)
    {
        $registration->loadMissing('student');

        $user = Auth::user();
        if ($user->role === 'admin_fakultas' &&
            $registration->student->faculty_id != $user->faculty_id) {
            abort(403, 'Anda tidak berhak membatalkan delegasi peserta dari fakultas lain.');
        }

        if ($registration->stage !== 'universitas') {
            return back()->with('error', 'Delegasi hanya dapat dibatalkan untuk peserta di tahap universitas.');
        }

        $registration->update(['stage' => 'fakultas']);

        return back()->with('success', 'Delegasi dibatalkan!');
    }
}
