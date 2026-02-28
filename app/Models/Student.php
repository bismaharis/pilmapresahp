<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'faculty_id', 'nim', 'prodi', 'semester', 'ipk'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
}