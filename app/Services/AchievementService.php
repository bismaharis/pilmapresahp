<?php

namespace App\Services;

use App\Models\Achievement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AchievementService
{
    public function create(int $registrationId, array $data): Achievement
    {
        // [cite_start]
        $totalCount = Achievement::query()
            ->where('registration_id', '=', $registrationId)
            ->count('id');
        if ($totalCount >= 10) {
            throw ValidationException::withMessages([
                'limit' => 'Anda hanya dapat menginput maksimal 10 Capaian Unggulan.',
            ]);
        }

        // [cite_start]
        $categoryCount = Achievement::query()
            ->where('registration_id', '=', $registrationId)
            ->where('category', '=', $data['category'])
            ->count('id');

        if ($categoryCount >= 4) {
            throw ValidationException::withMessages([
                'category' => "Kategori {$data['category']} sudah mencapai batas maksimal (4 item).",
            ]);
        }

        // 3. Simpan Data
        return Achievement::create([
            'registration_id' => $registrationId,
            'name' => $data['name'],
            'capaian' => $data['capaian'],
            'category' => $data['category'],
            'organizer' => $data['organizer'],
            'year' => $data['year'],
            'type' => $data['type'],
            'jumlah_peserta' => $data['jumlah_peserta'],
            'jumlah_penghargaan' => $data['jumlah_penghargaan'],
            'level' => $data['level'],
            'file_proof' => $data['file_proof'],
        ]);
    }

    public function delete(int $id, int $studentId): void
    {
        $achievement = Achievement::query()
            ->where('id', '=', $id)
            ->whereHas('registration', function ($query) use ($studentId) {
                $query->where('student_id', '=', $studentId);
            })
            ->firstOrFail();

        if ($achievement->file_proof) {
            Storage::disk(config('filesystems.default_public_disk'))->delete($achievement->file_proof);
        }

        Achievement::query()
            ->whereKey($achievement->id)
            ->delete();
    }
}
