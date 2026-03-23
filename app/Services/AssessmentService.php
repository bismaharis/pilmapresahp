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
            // ── CAPAIAN UNGGULAN ──────────────────────────────────────────
            // Nilai per sertifikat disimpan HANYA di tabel assessments milik
            // juri ini. Tabel achievements.score TIDAK disentuh agar nilai
            // antar juri tidak saling menimpa (fix bug #4).
            if (!empty($achievementScores)) {
                $cuSums = [];

                foreach ($achievementScores as $achievementId => $scoreValue) {
                    $achievement = Achievement::find($achievementId);
                    if (!$achievement) continue;

                    $cat = $achievement->category;
                    $cuSums[$cat] = ($cuSums[$cat] ?? 0) + ($scoreValue ?? 0);
                }

                $cuCriteriaRoot = Criteria::where('type', 'cu')->whereNull('parent_id')->first();

                if ($cuCriteriaRoot) {
                    $categories = Criteria::where('parent_id', $cuCriteriaRoot->id)->get();

                    foreach ($categories as $cat) {
                        $totalScore       = $cuSums[$cat->name] ?? 0;
                        $finalScore       = min($totalScore, $cat->max_score);

                        Assessment::updateOrCreate(
                            [
                                'registration_id' => $registrationId,
                                'lecturer_id'     => $lecturerId,
                                'criteria_id'     => $cat->id,
                            ],
                            ['score' => $finalScore, 'notes' => $notes[$cat->id] ?? null],
                        );
                    }
                }
            }

            // ── GK / BI — simpan scores, sisipkan notes sekaligus ─────────
            foreach ($scores as $criteriaId => $scoreValue) {
                Assessment::updateOrCreate(
                    [
                        'registration_id' => $registrationId,
                        'lecturer_id'     => $lecturerId,
                        'criteria_id'     => $criteriaId,
                    ],
                    [
                        'score' => $scoreValue ?? 0,
                        'notes' => $notes[$criteriaId] ?? null,
                    ]
                );
            }

            // ── Notes untuk kriteria root/parent (tidak punya input score) ─
            foreach ($notes as $criteriaId => $noteText) {
                if (empty($noteText)) continue;
                if (array_key_exists($criteriaId, $scores)) continue; // sudah ditangani atas

                Assessment::updateOrCreate(
                    [
                        'registration_id' => $registrationId,
                        'lecturer_id'     => $lecturerId,
                        'criteria_id'     => $criteriaId,
                    ],
                    ['score' => 0, 'notes' => $noteText]
                );
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}