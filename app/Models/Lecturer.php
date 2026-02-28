<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lecturer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'faculty_id',
        'nip',
        'unit_kerja',
        'is_univ_judge',
    ];

    protected $casts = [
        'is_univ_judge' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
}