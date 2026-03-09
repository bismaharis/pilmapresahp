<?php

namespace App\Services;

use App\Repositories\Contracts\CriteriaRepositoryInterface;
use Exception;

class AhpSettingsService
{
    public function __construct(
        protected CriteriaRepositoryInterface $repository
    ) {}

    public function getCriteriaTree()
    {
        return $this->repository->getTree();
    }

    public function updateWeight(int $id, float $weightPercentage): void
    {
        $decimalWeight = $weightPercentage / 100;

        if ($decimalWeight < 0 || $decimalWeight > 1) {
            throw new Exception("Bobot harus antara 0% sampai 100%");
        }

        // prevent editing weight of CU subcriteria (they are always 0)
        $criteria = $this->repository->findById($id);
        if ($criteria && $criteria->type === 'cu' && $criteria->parent_id !== null) {
            throw new Exception("Bobot tidak boleh diubah pada sub-kriteria CU");
        }

        $updated = $this->repository->update($id, [
            'weight' => $decimalWeight
        ]);

        if (!$updated) {
            throw new Exception("Gagal mengupdate kriteria ID: $id");
        }
        
        // TODO: (Nanti) Tambahkan logic untuk cek apakah total bobot siblings = 100%
        // Jika tidak, beri warning ke admin.
    }
}