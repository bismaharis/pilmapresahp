<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PilmapresPeriod extends Model
{
    protected $fillable = [
        'faculty_id',
        'year',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'period_id');
    }

    /**
     * Cek apakah periode ini sedang berjalan (aktif + dalam rentang tanggal).
     */
    public function isOpen(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        $today = Carbon::today();

        return $today->between($this->start_date, $this->end_date);
    }

    /**
     * Ambil periode aktif yang sedang berjalan untuk fakultas tertentu.
     */
    public static function getActivePeriodForFaculty(int $facultyId): ?self
    {
        $today = Carbon::today();

        return self::query()
            ->where('faculty_id', $facultyId)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->latest('id')
            ->first();
    }

    /**
     * Ambil periode aktif yang sedang berjalan untuk tingkat universitas.
     */
    public static function getActiveUniversityPeriod(): ?self
    {
        $today = Carbon::today();

        return self::query()
            ->whereNull('faculty_id')
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->latest('id')
            ->first();
    }
}
