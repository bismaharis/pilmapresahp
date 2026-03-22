<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PilmapresPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeriodController extends Controller
{
    /**
     * Hanya admin_fakultas yang boleh akses controller ini.
     * Dipanggil dari routes yang sudah di-middleware.
     */
    public function index()
    {
        $facultyId = Auth::user()->faculty_id;

        $periods = PilmapresPeriod::where('faculty_id', $facultyId)
            ->orderByDesc('start_date')
            ->get();

        return view('admin.periods.index', compact('periods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|string|max:10',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $facultyId = Auth::user()->faculty_id;

        // Nonaktifkan periode aktif lama milik fakultas ini
        PilmapresPeriod::where('faculty_id', $facultyId)
            ->where('is_active', true)
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
        $period->delete();

        return back()->with('success', 'Jadwal seleksi berhasil dihapus.');
    }

    private function authorizePeriod(PilmapresPeriod $period): void
    {
        if ((int) $period->faculty_id !== (int) Auth::user()->faculty_id) {
            abort(403, 'Anda tidak memiliki akses ke jadwal ini.');
        }
    }
}
