<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Criteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransparencyController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::user()->student;
        $stage = $request->query('stage', 'fakultas');

        if (!$student) {
            return redirect()->route('profile.edit')->with('error', 'Lengkapi biodata akademik Anda terlebih dahulu.');
        }

        $query = Registration::with('student.user')->where('status', '!=', 'draft');

        if ($stage === 'fakultas') {
            $query->whereHas('student', function($q) use ($student) {
                $q->where('prodi', $student->prodi); 
            });
            $rankings = $query->orderBy('total_score_fakultas', 'desc')->get();
        } else {
            $rankings = $query->where('stage', 'universitas')->orderBy('total_score_univ', 'desc')->get();
        }

        $myRegistration = Registration::where('student_id', $student->id)->first();

        return view('student.transparency.index', compact('rankings', 'stage', 'myRegistration'));
    }

    public function show(Request $request)
    {
        $student = Auth::user()->student;
        
        $stage = $request->query('stage', 'fakultas');
        
        $myRegistration = Registration::with(['achievements', 'assessments.criteria', 'assessments.lecturer'])
                            ->where('student_id', $student->id)
                            ->firstOrFail();

        $criterias = Criteria::whereNull('parent_id')
                        ->with(['children.children.children']) 
                        ->get();

        return view('student.transparency.show', compact('myRegistration', 'criterias', 'stage'));
    }
}