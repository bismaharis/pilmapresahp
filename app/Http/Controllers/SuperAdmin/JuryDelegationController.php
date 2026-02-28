<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Lecturer;
use App\Models\Faculty;
use Illuminate\Http\Request;

class JuryDelegationController extends Controller
{
    public function index(Request $request)
    {
        $faculties = Faculty::all();
        $stage = $request->query('stage', 'fakultas'); // Default ke tab fakultas

        // Mulai Query: Ambil user dengan role dosen
        $query = User::where('role', 'dosen')->with(['lecturer.faculty']);

        // 1. Filter berdasarkan Tab (Tingkat Fakultas vs Universitas)
        if ($stage === 'universitas') {
            $query->whereHas('lecturer', function($q) {
                $q->where('is_univ_judge', true);
            });
        } else {
            $query->whereHas('lecturer', function($q) {
                $q->where('is_univ_judge', false);
            });
        }

        // 2. Filter berdasarkan Dropdown Asal Fakultas
        if ($request->filled('faculty_id')) {
            $query->whereHas('lecturer', function($q) use ($request) {
                $q->where('faculty_id', $request->faculty_id);
            });
        }

        $juries = $query->get();
        
        return view('superadmin.delegation.juries', compact('juries', 'faculties', 'stage'));
    }

    public function toggle(Lecturer $lecturer)
    {
        $lecturer->update([
            'is_univ_judge' => !$lecturer->is_univ_judge
        ]);

        $status = $lecturer->is_univ_judge ? 'dinaikkan menjadi Juri Universitas' : 'diturunkan menjadi Juri Fakultas';
        return back()->with('success', 'Status Juri berhasil ' . $status . '!');
    }
}