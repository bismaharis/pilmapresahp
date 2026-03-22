<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'guidebook_url' => 'required|url|max:500',
        ], [
            'guidebook_url.required' => 'URL Guide Book wajib diisi.',
            'guidebook_url.url'      => 'Format URL tidak valid. Pastikan diawali https://',
        ]);

        Setting::set('guidebook_url', $request->guidebook_url);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}