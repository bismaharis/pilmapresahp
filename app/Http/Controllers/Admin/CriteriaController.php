<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Criteria;
use App\Services\AhpSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CriteriaController extends Controller
{
    protected $ahpService;

    public function __construct(AhpSettingsService $ahpService)
    {
        $this->ahpService = $ahpService;
    }

    public function index(): View
    {
        // Langsung ambil dari Model beserta relasi anak-anaknya agar View bisa menampilkan tabel hierarki
        $criterias = Criteria::whereNull('parent_id')
                        ->with(['children.children.children'])
                        ->get();

        return view('admin.criteria.index', compact('criterias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:100', 
            'max_score' => 'required|numeric|min:0',
            'type' => 'nullable|string|in:cu,gk,bi',
            'parent_id' => 'nullable|exists:criterias,id'
        ]);

        Criteria::create([
            'name' => $request->name,
            'weight' => $request->weight / 100, // <--- RUMUS INI WAJIB ADA
            'max_score' => $request->max_score,
            'type' => $request->type ?? 'general',
            'parent_id' => $request->parent_id
        ]);

        return back()->with('success', 'Kriteria baru berhasil ditambahkan.');
    }

    public function update(Request $request, int $id)
    {
        $criteria = Criteria::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:100',
            'max_score' => 'required|numeric|min:0',
            'type' => 'nullable|string|in:cu,gk,bi'
        ]);

        try {
            $criteria->update([
                'name' => $request->name,
                'weight' => $request->weight / 100, // <--- RUMUS INI WAJIB ADA
                'max_score' => $request->max_score,
                'type' => $request->type ?? $criteria->type
            ]);

            return back()->with('success', 'Kriteria berhasil diperbarui.');
            
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $criteria = Criteria::findOrFail($id);
        
        if ($criteria->children()->count() > 0) {
            return back()->with('error', 'Gagal dihapus! Kriteria ini masih memiliki Sub-Kriteria di bawahnya. Hapus sub-kriteria terlebih dahulu.');
        }

        $criteria->delete();

        return back()->with('success', 'Kriteria berhasil dihapus.');
    }
}