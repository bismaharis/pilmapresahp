<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Registration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RankingController extends Controller
{
    public function index(Request $request)
    {
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
        } elseif (in_array($role, ['admin_fakultas']) || ($role === 'dosen' && !$isUnivJudge)) {
            $stage = 'fakultas'; 
        }

        $query = Registration::with(['student.user', 'student.faculty'])
            ->whereIn('status', ['submitted', 'verified', 'approved']) 
            ->where('stage', $stage);

        if ($role === 'admin_fakultas' || ($role === 'dosen' && !$isUnivJudge)) {
            // hanya lihat fakultasnya sendiri
            $query->whereHas('student', function($q) use ($user) {
                $q->where('faculty_id', $user->faculty_id);
            });
        } else {
            // Super Admin, Admin Univ, & Juri Univ izinkan pakai Dropdown Filter
            if ($request->filled('faculty_id')) {
                $query->whereHas('student', function($q) use ($request) {
                    $q->where('faculty_id', $request->faculty_id);
                });
            }
        }

        $scoreColumn = $stage === 'fakultas' ? 'total_score_fakultas' : 'total_score_univ';
        $rankings = $query->orderBy($scoreColumn, 'desc')->get();

        return view('admin.ranking.index', compact('rankings', 'stage', 'scoreColumn', 'faculties', 'role', 'isUnivJudge'));

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

    public function delegate(Registration $registration)
    {
        $user = Auth::user();
        if ($user->role === 'admin_fakultas' &&
            $registration->student->faculty_id != $user->faculty_id) {
            abort(403, 'Anda tidak berhak mendelegasikan peserta dari fakultas lain.');
        }
        $registration->update(['stage' => 'universitas']);
        return back()->with('success', 'Peserta berhasil didelegasikan ke tingkat Universitas!');
    }

    public function cancelDelegate(Registration $registration)
    {
        $user = Auth::user();
        if ($user->role === 'admin_fakultas' &&
            $registration->student->faculty_id != $user->faculty_id) {
            abort(403, 'Anda tidak berhak membatalkan delegasi peserta dari fakultas lain.');
        }
        $registration->update(['stage' => 'fakultas']);
        return back()->with('success', 'Delegasi dibatalkan!');
    }

}