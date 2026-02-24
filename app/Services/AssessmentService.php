<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Achievement;
use App\Models\Criteria;
use Exception;
use Illuminate\Support\Facades\DB;

class AssessmentService
{
    public function saveScores(int $registrationId, int $lecturerId, array $scores, array $notes = [], array $achievementScores = []): void
    {
        DB::beginTransaction();
        try {
            $cuSums = []; // Array penampung jumlah nilai per kategori
            
            foreach ($achievementScores as $achievementId => $scoreValue) {
                // Beri tahu VS Code bahwa ini adalah Model Achievement
                /** @var \App\Models\Achievement $achievement */
                $achievement = Achievement::find($achievementId);
                
                if ($achievement) {
                    // Cara ini menghilangkan error Intelephense 'Undefined method update'
                    $achievement->score = $scoreValue ?? 0;
                    $achievement->save();
                    
                    // Akumulasi total nilai per kategori
                    if (!isset($cuSums[$achievement->category])) {
                        $cuSums[$achievement->category] = 0;
                    }
                    $cuSums[$achievement->category] += $scoreValue;
                }
            }

            // Simpan Total Akumulasi CU ke tabel Assessments (agar terbaca oleh AHP)
            $cuCriteria = Criteria::where('name', 'CAPAIAN UNGGULAN')->orWhere('type', 'cu')->with('children')->first();
            if ($cuCriteria) {
                foreach ($cuCriteria->children as $child) {
                    $totalCategoryScore = $cuSums[$child->name] ?? 0;
                    Assessment::updateOrCreate(
                        ['registration_id' => $registrationId, 'lecturer_id' => $lecturerId, 'criteria_id' => $child->id],
                        ['score' => $totalCategoryScore]
                    );
                }
            }

            // 2. PROSES SIMPAN SKOR KRITERIA LAIN (Gagasan Kreatif / Bahasa Inggris)
            foreach ($scores as $criteriaId => $scoreValue) {
                Assessment::updateOrCreate(
                    ['registration_id' => $registrationId, 'lecturer_id' => $lecturerId, 'criteria_id' => $criteriaId],
                    ['score' => $scoreValue ?? 0]
                );
            }

            // 3. PROSES SIMPAN KOMENTAR EVALUASI
            foreach ($notes as $criteriaId => $noteText) {
                if (!empty($noteText)) {
                    Assessment::updateOrCreate(
                        ['registration_id' => $registrationId, 'lecturer_id' => $lecturerId, 'criteria_id' => $criteriaId],
                        ['notes' => $noteText]
                    );
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}