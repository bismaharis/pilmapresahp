<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Criteria extends Model
{
    protected $fillable = [
        'name',
        'type',
        'weight',
        'max_score',
        'parent_id'
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Criteria::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Criteria::class, 'parent_id');
    }

    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }
}