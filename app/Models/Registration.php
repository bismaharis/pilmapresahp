<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_id',
        'student_id',
        'stage', 
        'status', 
        'file_gk',
        'file_transkrip',
        'file_poster_gk',
        'file_poster_diri',
        'video_link',
        'total_score_fakultas',
        'total_score_univ'
    ];

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
 
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }
}