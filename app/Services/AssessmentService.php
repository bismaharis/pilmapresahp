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
            $cuSums = []; 
            
            foreach ($achievementScores as $achievementId => $scoreValue) {
                $achievement = Achievement::find($achievementId);
                if ($achievement) {
                    $achievement->score = $scoreValue ?? 0;
                    $achievement->save();
                    
                    // Kelompokkan skor berdasarkan kategori kriteria
                    if (!isset($cuSums[$achievement->category])) {
                        $cuSums[$achievement->category] = 0;
                    }
                    $cuSums[$achievement->category] += $scoreValue;
                }
            }

            $cuCriteriaRoot = Criteria::where('type', 'cu')->whereNull('parent_id')->first();

            if ($cuCriteriaRoot) {
                $categories = Criteria::where('parent_id', $cuCriteriaRoot->id)->get();
                
                foreach ($categories as $cat) {
                    // Gunakan pencarian case-insensitive atau pastikan data input sesuai
                    $totalScore = $cuSums[$cat->name] ?? 0;
                    
                    $finalCategoryScore = min($totalScore, $cat->max_score); // Gunakan max_score dari DB

                    Assessment::updateOrCreate(
                        ['registration_id' => $registrationId, 'lecturer_id' => $lecturerId, 'criteria_id' => $cat->id],
                        ['score' => $finalCategoryScore]
                    );
                }
            }

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