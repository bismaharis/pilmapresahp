<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengaturan Guide Book</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-auth-session-status class="mb-4" :status="session('success')" />

            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-1">Guide Book Pilmapres</h3>
                <p class="text-sm text-gray-500 mb-6">Atur URL guide book berdasarkan tingkat seleksi. Pengguna akan melihat guide book sesuai peran dan fakultasnya.</p>

                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        @if(auth()->user()->role === 'super_admin')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tingkat Pengaturan</label>
                                    <select name="scope" id="scope-selector" onchange="toggleFacultySelector(this.value)"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                                        <option value="universitas" {{ $selectedScope === 'universitas' ? 'selected' : '' }}>Universitas</option>
                                        <option value="fakultas" {{ $selectedScope === 'fakultas' ? 'selected' : '' }}>Fakultas</option>
                                    </select>
                                </div>
                                <div id="faculty-selector-wrapper" class="{{ $selectedScope === 'fakultas' ? '' : 'hidden' }}">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Fakultas</label>
                                    <select name="faculty_id"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                                        <option value="">-- Pilih Fakultas --</option>
                                        @foreach($faculties as $faculty)
                                            <option value="{{ $faculty->id }}" {{ (int) $selectedFacultyId === (int) $faculty->id ? 'selected' : '' }}>{{ $faculty->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('faculty_id')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @else
                            <div class="bg-gray-50 border border-gray-200 rounded-md px-4 py-3 text-sm text-gray-600">
                                <span class="font-semibold text-gray-800">Scope aktif:</span>
                                @if(auth()->user()->role === 'admin_univ')
                                    Universitas
                                @else
                                    Fakultas Anda
                                @endif
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                URL Guide Book
                                <span class="text-xs text-gray-400 font-normal ml-1">(Google Drive, OneDrive, link langsung PDF, dll)</span>
                            </label>
                            <input type="url" name="guidebook_url"
                                   value="{{ old('guidebook_url', $currentGuidebookUrl ?? '') }}"
                                   placeholder="https://drive.google.com/..."
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                            @error('guidebook_url')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Preview link saat ini --}}
                        @if($currentGuidebookUrl)
                            <div class="bg-gray-50 border border-gray-200 rounded-md px-4 py-3 flex items-center justify-between">
                                <span class="text-xs text-gray-500 truncate mr-4">Link aktif: {{ $currentGuidebookUrl }}</span>
                                <a href="{{ $currentGuidebookUrl }}" target="_blank"
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

    <script>
        function toggleFacultySelector(scope) {
            const wrapper = document.getElementById('faculty-selector-wrapper');
            if (!wrapper) {
                return;
            }

            if (scope === 'fakultas') {
                wrapper.classList.remove('hidden');
                return;
            }

            wrapper.classList.add('hidden');
        }
    </script>
</x-app-layout>