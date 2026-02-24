<?php

namespace App\Repositories;

use App\Models\Criteria;
use App\Repositories\Contracts\CriteriaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentCriteriaRepository implements CriteriaRepositoryInterface
{
    public function getTree(): Collection
    {
        return Criteria::query()
            ->with(['children' => function ($query) {
                
                $query->with(['children' => function ($subQuery) {
                    
                    $subQuery->with('children'); 
                }]);
            }])
            ->whereNull('parent_id') 
            ->get();
    }

    public function getLeaves(): Collection
    {
        return Criteria::query()
            ->doesntHave('children') 
            ->get();
    }

    public function findById(int $id): ?Criteria
    {
        return Criteria::find($id);
    }

    public function update(int $id, array $data): bool
    {
        // Use a query update to avoid issues where the retrieved value
        // isn't an Eloquent model instance (which would cause "undefined
        // method update" errors). This returns the number of affected
        // rows; cast to bool for the interface contract.
        $affected = Criteria::query()->where('id', $id)->update($data);
        return (bool) $affected;
    }
}