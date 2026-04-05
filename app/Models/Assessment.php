<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'lecturer_id',
        'criteria_id',
        'score',
        'notes',
    ];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(Criteria::class);
    }
}
