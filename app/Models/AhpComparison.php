<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AhpComparison extends Model
{
    protected $table = 'pairwise_comparisons';

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

    public static function getValue(int $id1, int $id2): float
    {
        if ($id1 === $id2) {
            return 1.0;
        }

        $comparison = self::where('criteria_id_1', $id1)->where('criteria_id_2', $id2)->first();

        if ($comparison) {
            return (float) $comparison->value;
        }

        $comparisonInverse = self::where('criteria_id_1', $id2)->where('criteria_id_2', $id1)->first();

        if ($comparisonInverse && (float) $comparisonInverse->value > 0.0) {
            return 1.0 / (float) $comparisonInverse->value;
        }

        $criteria1 = Criteria::find($id1);
        $criteria2 = Criteria::find($id2);

        if ($criteria1 && $criteria2 && $criteria2->weight > 0) {
            $weight1 = (float) $criteria1->weight;
            $weight2 = (float) $criteria2->weight;
            if ($weight2 <= 0) {
                return 1.0;
            }

            return $weight1 / $weight2;
        }

        return 1.0;
    }
}
