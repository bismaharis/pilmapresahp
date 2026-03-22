<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
        $today = Carbon::today()->toDateString();

        return self::where('faculty_id', $facultyId)
            ->where('is_active', true)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();
    }
}
