<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class RankingController extends Controller
{
    public function index(Request $request)
    {
        
        $stage = $request->query('stage', 'fakultas');
        $admin = Auth::user();

        $query = Registration::with(['student.user'])
            ->where('stage', $stage);

        if ($admin->role === 'admin_fakultas') {
            $query->whereHas('student.user', function($q) use ($admin) {
                $q->where('faculty_id', $admin->faculty_id);
            });
        }

        $scoreColumn = $stage === 'fakultas' ? 'total_score_fakultas' : 'total_score_univ';
        $rankings = $query->orderBy($scoreColumn, 'desc')->get();

        return view('admin.ranking.index', compact('rankings', 'stage', 'scoreColumn'));
    }

    public function delegate(Registration $registration)
    {
        $registration->update(['stage' => 'universitas']);
        
        return back()->with('success', 'Peserta berhasil didelegasikan ke tingkat Universitas!');
    }

    public function exportPdf(Request $request)
    {
        $stage = $request->query('stage', 'fakultas');
        $admin = Auth::user();

        $query = Registration::with(['student.user'])
            ->where('stage', $stage);

        if ($admin->role === 'admin_fakultas') {
            $query->whereHas('student.user', function($q) use ($admin) {
                $q->where('faculty_id', $admin->faculty_id);
            });
        }

        $scoreColumn = $stage === 'fakultas' ? 'total_score_fakultas' : 'total_score_univ';
        $rankings = $query->orderBy($scoreColumn, 'desc')->get();

        $pdf = Pdf::loadView('admin.ranking.pdf', compact('rankings', 'stage', 'scoreColumn'));
        
        $pdf->setPaper('a4', 'portrait');

        $fileName = 'SK_Pemenang_Pilmapres_Tahap_' . ucfirst($stage) . '_2026.pdf';

        return $pdf->download($fileName);
    }
}