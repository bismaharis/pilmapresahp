<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\User;
use App\Services\UniversityDelegationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JuryDelegationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'stage' => ['nullable', 'in:fakultas,universitas'],
            'faculty_id' => ['nullable', 'integer', 'exists:faculties,id'],
        ]);

        $faculties = Faculty::all();
        $stage = $request->query('stage', 'fakultas'); // Default ke tab fakultas

        // Mulai Query: Ambil user dengan role dosen
        $query = User::query()->where('role', 'dosen')->with(['lecturer.faculty']);

        // 1. Filter berdasarkan Tab (Tingkat Fakultas vs Universitas)
        if ($stage === 'universitas') {
            $query->whereHas('lecturer', function ($q) {
                $q->where('is_univ_judge', true);
            });
        } else {
            $query->whereHas('lecturer', function ($q) {
                $q->where('is_univ_judge', false);
            });
        }

        // 2. Filter berdasarkan Dropdown Asal Fakultas
        if ($request->filled('faculty_id')) {
            $query->whereHas('lecturer', function ($q) use ($request) {
                $q->where('faculty_id', $request->faculty_id);
            });
        }

        $juries = $query
            ->latest('users.id')
            ->paginate(20)
            ->withQueryString();

        return view('superadmin.delegation.juries', compact('juries', 'faculties', 'stage'));
    }

    public function toggle(Lecturer $lecturer, UniversityDelegationNotificationService $notificationService)
    {
        $lecturer->loadMissing('user');

        if (! $lecturer->user || $lecturer->user->role !== 'dosen') {
            abort(404);
        }

        $lecturer->update([
            'is_univ_judge' => ! $lecturer->is_univ_judge,
        ]);

        if ($lecturer->is_univ_judge) {
            try {
                $notificationService->sendJuryDelegatedToUniversity($lecturer);
            } catch (\Throwable $exception) {
                Log::error('Gagal mengirim email delegasi juri universitas.', [
                    'lecturer_id' => $lecturer->id,
                    'user_id' => $lecturer->user_id,
                    'error' => $exception->getMessage(),
                ]);

                return back()->with('warning', 'Status juri berhasil diperbarui, tetapi email notifikasi gagal dikirim.');
            }
        }

        $status = $lecturer->is_univ_judge ? 'dinaikkan menjadi Juri Universitas' : 'diturunkan menjadi Juri Fakultas';

        return back()->with('success', 'Status Juri berhasil '.$status.'!');
    }
}
