<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengaturan Sistem</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-auth-session-status class="mb-4" :status="session('success')" />

            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-1">Guide Book Pilmapres</h3>
                <p class="text-sm text-gray-500 mb-6">URL ini akan ditampilkan sebagai link unduhan di sidebar untuk semua pengguna (mahasiswa, juri, admin).</p>

                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                URL Guide Book
                                <span class="text-xs text-gray-400 font-normal ml-1">(Google Drive, OneDrive, link langsung PDF, dll)</span>
                            </label>
                            <input type="url" name="guidebook_url"
                                   value="{{ old('guidebook_url', $settings['guidebook_url']->value ?? '') }}"
                                   placeholder="https://drive.google.com/..."
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                            @error('guidebook_url')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Preview link saat ini --}}
                        @if(isset($settings['guidebook_url']) && $settings['guidebook_url']->value)
                            <div class="bg-gray-50 border border-gray-200 rounded-md px-4 py-3 flex items-center justify-between">
                                <span class="text-xs text-gray-500 truncate mr-4">Link aktif: {{ $settings['guidebook_url']->value }}</span>
                                <a href="{{ $settings['guidebook_url']->value }}" target="_blank"
                                   class="text-xs text-cyan-600 hover:underline whitespace-nowrap font-semibold">
                                    Buka ↗
                                </a>
                            </div>
                        @endif

                        <div class="flex items-center gap-3 pt-2">
                            <button type="submit"
                                    class="bg-cyan-600 hover:bg-cyan-700 text-white font-semibold px-5 py-2 rounded-md text-sm shadow transition">
                                Simpan Perubahan
                            </button>
                            @if (session('success'))
                                <p class="text-sm text-green-600 font-medium">✓ {{ session('success') }}</p>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>