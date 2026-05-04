<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\PilmapresPeriod;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PeriodController extends Controller
{
    /**
     * Hanya admin_fakultas yang boleh akses controller ini.
     * Dipanggil dari routes yang sudah di-middleware.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = in_array($user->role, ['super_admin', 'admin_univ']);

        if ($isSuperAdmin) {
            // 'universitas' sentinel means NULL faculty_id; otherwise use the selected faculty id
            $selectedFacultyId = $request->query('faculty_id');
            if ($selectedFacultyId === 'universitas') {
                $facultyId = null;
            } elseif ($selectedFacultyId !== null) {
                $facultyId = (int) $selectedFacultyId;
            } else {
                // Default: university-level (null)
                $facultyId = null;
            }
        } else {
            $facultyId = $user->faculty_id;
        }

        $query = PilmapresPeriod::withCount('registrations')
            ->orderByDesc('start_date');

        if ($facultyId === null) {
            $query->whereNull('faculty_id');
        } else {
            $query->where('faculty_id', $facultyId);
        }

        $periods = $query->get();
        $faculties = $isSuperAdmin ? Faculty::orderBy('name')->get() : collect();
        $selectedFacultyId = $isSuperAdmin ? ($request->query('faculty_id') ?? 'universitas') : null;

        return view('admin.periods.index', compact('periods', 'faculties', 'selectedFacultyId'));
    }

    public function show(PilmapresPeriod $period)
    {
        $this->authorizePeriod($period);

        $period->load([
            'registrations.student.user',
            'registrations.achievements',
        ]);

        $rankedRegistrations = $this->rankRegistrations($period->registrations);

        return view('admin.periods.show', [
            'period' => $period,
            'rankedRegistrations' => $rankedRegistrations,
        ]);
    }

    public function exportExcel(PilmapresPeriod $period): StreamedResponse
    {
        $this->authorizePeriod($period);

        $period->load([
            'registrations.student.user',
            'registrations.achievements',
        ]);

        $rankedRegistrations = $this->rankRegistrations($period->registrations);
        $fileName = sprintf('histori-periode-%s-%s.csv', $period->year, now()->format('Ymd_His'));

        return response()->streamDownload(function () use ($rankedRegistrations): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'Urutan Pemenang',
                'Nama Mahasiswa',
                'NIM',
                'Prodi',
                'Tahap',
                'Status',
                'Skor Akhir',
                'Submit Fakultas',
                'Submit Universitas',
                'File GK',
                'File Transkrip',
                'File Poster GK',
                'File Poster Diri',
                'Video Link',
                'Jumlah Capaian Unggulan',
                'Berkas Bukti CU',
            ]);

            foreach ($rankedRegistrations as $index => $registration) {
                $proofFiles = $registration->achievements
                    ->pluck('file_proof')
                    ->filter()
                    ->map(fn ($path) => '/storage/'.ltrim($path, '/'))
                    ->values()
                    ->implode(' | ');

                fputcsv($output, [
                    '#'.($index + 1),
                    $registration->student->user->name,
                    $registration->student->nim,
                    $registration->student->prodi,
                    $registration->stage,
                    $registration->status,
                    number_format($this->resolveFinalScore($registration), 2, '.', ''),
                    optional($registration->submitted_fakultas_at)->format('Y-m-d H:i:s'),
                    optional($registration->submitted_universitas_at)->format('Y-m-d H:i:s'),
                    $registration->file_gk ? '/storage/'.ltrim($registration->file_gk, '/') : '-',
                    $registration->file_transkrip ? '/storage/'.ltrim($registration->file_transkrip, '/') : '-',
                    $registration->file_poster_gk ? '/storage/'.ltrim($registration->file_poster_gk, '/') : '-',
                    $registration->file_poster_diri ? '/storage/'.ltrim($registration->file_poster_diri, '/') : '-',
                    $registration->video_link ?? '-',
                    $registration->achievements->count(),
                    $proofFiles ?: '-',
                ]);
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|string|max:10',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'faculty_id' => 'nullable|exists:faculties,id',
        ]);

        $user = Auth::user();
        $isSuperAdmin = in_array($user->role, ['super_admin', 'admin_univ']);

        if ($isSuperAdmin) {
            $facultyId = $request->input('faculty_id') ? (int) $request->input('faculty_id') : null;
        } else {
            $facultyId = $user->faculty_id;
        }

        // Nonaktifkan periode aktif lama milik fakultas/universitas ini
        PilmapresPeriod::where('is_active', true)
            ->when($facultyId === null, fn ($q) => $q->whereNull('faculty_id'), fn ($q) => $q->where('faculty_id', $facultyId))
            ->update(['is_active' => false]);

        PilmapresPeriod::create([
            'faculty_id' => $facultyId,
            'year' => $request->year,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => true,
        ]);

        return back()->with('success', 'Jadwal seleksi berhasil disimpan.');
    }

    public function update(Request $request, PilmapresPeriod $period)
    {
        $this->authorizePeriod($period);

        $request->validate([
            'year' => 'required|string|max:10',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        // Jika diaktifkan, nonaktifkan yang lain dulu
        if ($request->boolean('is_active')) {
            PilmapresPeriod::where('faculty_id', $period->faculty_id)
                ->where('id', '!=', $period->id)
                ->update(['is_active' => false]);
        }

        $period->update([
            'year' => $request->year,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Jadwal seleksi berhasil diperbarui.');
    }

    public function destroy(PilmapresPeriod $period)
    {
        $this->authorizePeriod($period);

        if ($period->registrations()->exists()) {
            return back()->with('error', 'Jadwal ini tidak bisa dihapus karena sudah memiliki data pendaftar. Gunakan histori periode.');
        }

        if (now()->greaterThanOrEqualTo($period->start_date)) {
            return back()->with('error', 'Jadwal yang sudah berjalan atau sudah lewat tidak bisa dihapus agar histori tetap tersimpan.');
        }

        $period->delete();

        return back()->with('success', 'Jadwal seleksi berhasil dihapus.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Registration>
     */
    private function rankRegistrations(Collection $registrations): Collection
    {
        return $registrations
            ->sortByDesc(fn (Registration $registration) => $this->resolveFinalScore($registration))
            ->values();
    }

    private function resolveFinalScore(Registration $registration): float
    {
        $universityScore = (float) ($registration->total_score_univ ?? 0);
        if ($universityScore > 0) {
            return $universityScore;
        }

        return (float) ($registration->total_score_fakultas ?? 0);
    }

    private function authorizePeriod(PilmapresPeriod $period): void
    {
        $user = Auth::user();

        if (in_array($user->role, ['super_admin', 'admin_univ'])) {
            return;
        }

        if ((int) $period->faculty_id !== (int) $user->faculty_id) {
            abort(403, 'Anda tidak memiliki akses ke jadwal ini.');
        }
    }
}
