<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Criteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransparencyController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->role;
        $stage = $request->query('stage', 'fakultas');

        $query = Registration::with('student.user')->where('status', '!=', 'draft');

        // LOGIKA FILTER BERDASARKAN ROLE
        if ($role === 'mahasiswa') {
            $student = $user->student;
            if (!$student) return redirect()->route('profile.edit')->with('error', 'Lengkapi biodata akademik Anda.');
            
            if ($stage === 'fakultas') {
                $query->whereHas('student', function($q) use ($student) {
                    $q->where('faculty_id', $student->faculty_id); // Diubah ke Fakultas agar mhs bisa lihat saingan se-fakultas
                });
            }
            $myRegistration = Registration::where('student_id', $student->id)->first();
        } 
        elseif (in_array($role, ['admin_fakultas', 'dosen'])) {
            $isUnivJudge = ($role === 'dosen' && $user->lecturer && $user->lecturer->is_univ_judge);
            
            // Jika bukan Juri Univ, maka HANYA lihat fakultasnya saja
            if (!$isUnivJudge) {
                $query->whereHas('student', function($q) use ($user) {
                    $q->where('faculty_id', $user->faculty_id);
                });
            }
            $myRegistration = null;
        } 
        else {
            // Super Admin & Admin Univ melihat semua
            $myRegistration = null;
        }

        // PENGURUTAN (RANKING)
        if ($stage === 'fakultas') {
            $rankings = $query->orderBy('total_score_fakultas', 'desc')->get();
        } else {
            $rankings = $query->where('stage', 'universitas')->orderBy('total_score_univ', 'desc')->get();
        }

        return view('transparency.index', compact('rankings', 'stage', 'myRegistration', 'role', 'user'));
    }

    public function show(Request $request, $id)
    {
        $user = Auth::user();
        $role = $user->role;
        $stage = $request->query('stage', 'fakultas');
        
        $registration = Registration::with(['achievements', 'assessments.criteria', 'assessments.lecturer', 'student.user'])
                            ->findOrFail($id);

        // LOGIKA PROTEKSI (Hanya Boleh Lihat Jika Sesuai Hak Akses)
        if ($role === 'mahasiswa') {
            if ($registration->student->user_id !== $user->id) {
                abort(403, 'Akses Ditolak: Anda hanya dapat melihat rincian transparansi nilai Anda sendiri.');
            }
        } elseif (in_array($role, ['admin_fakultas', 'dosen'])) {
            $isUnivJudge = ($role === 'dosen' && $user->lecturer && $user->lecturer->is_univ_judge);
            if (!$isUnivJudge && $registration->student->faculty_id !== $user->faculty_id) {
                abort(403, 'Akses Ditolak: Anda hanya dapat melihat peserta dari Fakultas Anda.');
            }
        }

        $criterias = Criteria::whereNull('parent_id')
                        ->with(['children.children.children']) 
                        ->get();

        return view('transparency.show', compact('registration', 'criterias', 'stage', 'role'));
    }
}