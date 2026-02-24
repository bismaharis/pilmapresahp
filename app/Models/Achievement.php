<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'name',
        'capaian',
        'category',
        'organizer',
        'year',
        'type', 
        'jumlah_peserta',
        'jumlah_penghargaan',
        'level', 
        'file_proof',
        'is_validated',
        'score',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}