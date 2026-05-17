<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'label'];

    public const GUIDEBOOK_SCOPE_UNIVERSITY = 'universitas';

    public const GUIDEBOOK_SCOPE_FACULTY = 'fakultas';

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            return self::query()
                ->where('key', '=', $key)
                ->value('value') ?? $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting_{$key}");
    }

    public static function setGuidebookUrl(string $scope, string $url, ?int $facultyId = null): void
    {
        $key = self::resolveGuidebookKey($scope, $facultyId);
        self::set($key, $url);
    }

    public static function getGuidebookUrlForScope(string $scope, ?int $facultyId = null): ?string
    {
        if ($scope === self::GUIDEBOOK_SCOPE_FACULTY && empty($facultyId)) {
            return null;
        }

        $key = self::resolveGuidebookKey($scope, $facultyId);

        return self::get($key);
    }

    public static function getGuidebookUrlForUser(User $user): ?string
    {
        if ($user->role === 'admin_univ') {
            return self::getGuidebookUrlForScope(self::GUIDEBOOK_SCOPE_UNIVERSITY);
        }

        if ($user->role === 'admin_fakultas' || $user->role === 'mahasiswa') {
            $facultyId = $user->faculty_id ? (int) $user->faculty_id : null;

            return self::getGuidebookUrlForScope(self::GUIDEBOOK_SCOPE_FACULTY, $facultyId)
                ?? self::getGuidebookUrlForScope(self::GUIDEBOOK_SCOPE_UNIVERSITY);
        }

        if ($user->role === 'dosen' && $user->lecturer) {
            if ($user->lecturer->is_univ_judge) {
                return self::getGuidebookUrlForScope(self::GUIDEBOOK_SCOPE_UNIVERSITY);
            }

            $facultyId = $user->lecturer->faculty_id ? (int) $user->lecturer->faculty_id : null;

            return self::getGuidebookUrlForScope(self::GUIDEBOOK_SCOPE_FACULTY, $facultyId)
                ?? self::getGuidebookUrlForScope(self::GUIDEBOOK_SCOPE_UNIVERSITY);
        }

        return self::getGuidebookUrlForScope(self::GUIDEBOOK_SCOPE_UNIVERSITY)
            ?? self::get('guidebook_url');
    }

    private static function resolveGuidebookKey(string $scope, ?int $facultyId = null): string
    {
        if ($scope === self::GUIDEBOOK_SCOPE_UNIVERSITY) {
            return 'guidebook_url_universitas';
        }

        if ($scope === self::GUIDEBOOK_SCOPE_FACULTY) {
            if (! $facultyId) {
                throw new InvalidArgumentException('facultyId wajib diisi untuk scope fakultas.');
            }

            return 'guidebook_url_fakultas_'.$facultyId;
        }

        throw new InvalidArgumentException('Scope guidebook tidak dikenal.');
    }
}
