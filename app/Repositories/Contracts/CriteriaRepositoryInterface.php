<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Criteria;

interface CriteriaRepositoryInterface
{
    public function getTree(): Collection;

    public function getLeaves(): Collection;

    public function findById(int $id): ?Criteria;

    public function update(int $id, array $data): bool;
}