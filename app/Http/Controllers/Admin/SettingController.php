<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $context = $this->resolveSettingsScopeFromRequest(request());

        $selectedScope = $context['scope'];
        $selectedFacultyId = $context['faculty_id'];
        $currentGuidebookUrl = Setting::getGuidebookUrlForScope($selectedScope, $selectedFacultyId);

        $faculties = $user->role === 'super_admin'
            ? Faculty::query()->orderBy('name', 'asc')->get()
            : collect();

        $settings = Setting::all()->keyBy('key');

        return view('admin.settings.index', compact(
            'settings',
            'selectedScope',
            'selectedFacultyId',
            'currentGuidebookUrl',
            'faculties'
        ));
    }

    public function update(Request $request)
    {
        $context = $this->resolveSettingsScopeFromRequest($request);

        $request->validate([
            'guidebook_url' => 'required|url|max:500',
        ], [
            'guidebook_url.required' => 'URL Guide Book wajib diisi.',
            'guidebook_url.url' => 'Format URL tidak valid. Pastikan diawali https://',
        ]);

        Setting::setGuidebookUrl($context['scope'], $request->guidebook_url, $context['faculty_id']);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    private function resolveSettingsScopeFromRequest(Request $request): array
    {
        $user = Auth::user();

        if (! in_array($user->role, ['super_admin', 'admin_univ', 'admin_fakultas'], true)) {
            abort(403);
        }

        if ($user->role === 'admin_univ') {
            return [
                'scope' => Setting::GUIDEBOOK_SCOPE_UNIVERSITY,
                'faculty_id' => null,
            ];
        }

        if ($user->role === 'admin_fakultas') {
            if (! $user->faculty_id) {
                abort(403, 'Akun admin fakultas belum terhubung ke fakultas.');
            }

            return [
                'scope' => Setting::GUIDEBOOK_SCOPE_FACULTY,
                'faculty_id' => (int) $user->faculty_id,
            ];
        }

        $scope = $request->input('scope', Setting::GUIDEBOOK_SCOPE_UNIVERSITY);

        if (! in_array($scope, [Setting::GUIDEBOOK_SCOPE_UNIVERSITY, Setting::GUIDEBOOK_SCOPE_FACULTY], true)) {
            abort(422, 'Scope pengaturan tidak valid.');
        }

        if ($scope === Setting::GUIDEBOOK_SCOPE_UNIVERSITY) {
            return [
                'scope' => Setting::GUIDEBOOK_SCOPE_UNIVERSITY,
                'faculty_id' => null,
            ];
        }

        $request->validate([
            'faculty_id' => ['required', 'integer', 'exists:faculties,id'],
        ]);

        return [
            'scope' => Setting::GUIDEBOOK_SCOPE_FACULTY,
            'faculty_id' => (int) $request->input('faculty_id'),
        ];
    }
}
