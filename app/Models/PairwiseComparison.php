<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PairwiseComparison extends Model
{
    protected $fillable = [
        'criteria_id_1',
        'criteria_id_2',
        'value',
    ];

    public function criteria1(): BelongsTo
    {
        return $this->belongsTo(Criteria::class, 'criteria_id_1');
    }

    public function criteria2(): BelongsTo
    {
        return $this->belongsTo(Criteria::class, 'criteria_id_2');
    }
}
