<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePairwiseComparisonRequest;
use App\Models\Criteria;
use App\Models\PairwiseComparison;
use App\Services\AhpMatrixService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PairwiseComparisonController extends Controller
{
    protected AhpMatrixService $ahpMatrixService;

    public function __construct(AhpMatrixService $ahpMatrixService)
    {
        $this->ahpMatrixService = $ahpMatrixService;
    }

    public function index()
    {
        // Ambil semua kriteria yang memiliki anak (bisa diinput pairwise)
        $criteriaWithChildren = Criteria::with('children')->whereHas('children')->get();

        return view('admin.pairwise-comparisons.index', compact('criteriaWithChildren'));
    }

    public function edit($parentId)
    {
        $parent = Criteria::with('children')->findOrFail($parentId);
        $children = $parent->children;

        // Ambil perbandingan existing, jika belum ada build berdasarkan criteria.weight
        $comparisons = PairwiseComparison::whereIn('criteria_id_1', $children->pluck('id'))
            ->whereIn('criteria_id_2', $children->pluck('id'))
            ->get()
            ->keyBy(function ($item) {
                return $item->criteria_id_1.'-'.$item->criteria_id_2;
            });

        // Raw values dari bobot role saat ini (dalam angka asli untuk tooltip/preview)
        $rawWeights = [];
        foreach ($children as $child) {
            $rawWeights[$child->id] = round($child->weight * 100, 6); // ambil nilai persepsi 0..1 jadi 0-100
        }

        // Sekarang buat data matriks dengan prioritas formula dari database/pairwise
        $criteriaIds = $children->pluck('id')->toArray();
        $preview = $this->ahpMatrixService->previewCalculation($criteriaIds);

        return view('admin.pairwise-comparisons.edit', compact('parent', 'children', 'comparisons', 'rawWeights', 'preview'));
    }

    public function update(StorePairwiseComparisonRequest $request, $parentId)
    {
        $parent = Criteria::findOrFail($parentId);
        $children = $parent->children;

        if ($children->isEmpty()) {
            return back()->with('error', 'Kriteria tidak memiliki sub-kriteria.');
        }

        $childIds = $children->pluck('id')->toArray();
        $rawValues = $request->validated()['raw_values'];

        // Filter raw values berdasarkan children yang ada
        $validRawValues = [];
        foreach ($children as $child) {
            if (isset($rawValues[$child->id])) {
                $validRawValues[$child->id] = (float) $rawValues[$child->id];
            }
        }

        if (empty($validRawValues)) {
            return back()->with('error', 'Tidak ada bobot yang valid.');
        }

        $sumRaw = array_sum($validRawValues);

        if ($sumRaw <= 0) {
            return back()->with('error', 'Total bobot harus lebih besar dari 0.');
        }

        try {
            DB::beginTransaction();

            // Simpan nilai ke criteria sebagai bobot (persentase: raw / sumRaw)
            foreach ($children as $child) {
                $raw = $validRawValues[$child->id] ?? 0;
                $weight = $sumRaw > 0 ? $raw / $sumRaw : 0.0;
                $weight = max(0, min(1, (float) $weight));

                $child->update(['weight' => $weight]);
            }

            // Bangun pairwise matrix dari raw values (a_ij = raw_i / raw_j)
            PairwiseComparison::whereIn('criteria_id_1', $childIds)
                ->whereIn('criteria_id_2', $childIds)
                ->delete();

            foreach ($children as $i => $c1) {
                foreach ($children as $j => $c2) {
                    if ($i < $j) {
                        $c2Value = $validRawValues[$c2->id] ?? 1;
                        if ($c2Value <= 0) {
                            $c2Value = 1;
                        }

                        $aij = $validRawValues[$c1->id] / $c2Value;
                        $aij = max(0.111, min(9, (float) $aij));

                        PairwiseComparison::create([
                            'criteria_id_1' => $c1->id,
                            'criteria_id_2' => $c2->id,
                            'value' => $aij,
                        ]);
                    }
                }
            }

            // Hitung ulang semua bobot AHP di seluruh hierarki
            $this->ahpMatrixService->recalculateAllWeights();

            DB::commit();

            return redirect()->route('admin.criteria.index')
                ->with('success', 'Bobot AHP berhasil diperbarui. Matrix pairwise consistency telah dihitung ulang.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("PairwiseComparison update failed: {$e->getMessage()}", ['parent_id' => $parentId]);

            return back()->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }
}
