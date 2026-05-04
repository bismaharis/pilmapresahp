<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg md:text-xl text-gray-800 leading-tight">
            {{ __('Capaian Unggulan (CU)') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-7xl mx-auto px-3 md:px-6 lg:px-8">
            
            <x-auth-session-status class="mb-4" :status="session('success')" />
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-3 md:px-4 py-3 rounded text-sm">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
                
                <div class="md:col-span-1 order-2 md:order-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 md:p-6">
                        <h3 class="text-base md:text-lg font-medium text-gray-900 mb-4">Tambah Prestasi Baru</h3>
                        
                        <form action="{{ route('student.achievements.store') }}" method="POST" enctype="multipart/form-data" 
                            x-data="{ isSubmitting: false }" 
                            @submit="if(isSubmitting) { $event.preventDefault(); } else { isSubmitting = true; }">
                            @csrf
                            
                            <div class="mb-4">
                                <label class="block text-xs md:text-sm font-medium text-gray-700">Kegiatan / Organisasi</label>
                                <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md shadow-sm @error('name') border-red-500 @else border-gray-300 @enderror placeholder:text-xs md:placeholder:text-sm text-sm" placeholder="Contoh: KTI Nasional" required>
                                @error('name') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs md:text-sm font-medium text-gray-700">Capaian</label>
                                <input type="text" name="capaian" value="{{ old('capaian') }}" class="mt-1 block w-full rounded-md shadow-sm @error('capaian') border-red-500 @else border-gray-300 @enderror placeholder:text-xs md:placeholder:text-sm text-sm" placeholder="Contoh: Juara 1" required>
                                @error('capaian') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs md:text-sm font-medium text-gray-700">Kategori</label>
                                <select name="category" class="mt-1 block w-full rounded-md shadow-sm @error('category') border-red-500 @else border-gray-300 @enderror text-xs md:text-sm" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="Kompetisi" {{ old('category') == 'Kompetisi' ? 'selected' : '' }}>Kompetisi</option>
                                    <option value="Pengakuan" {{ old('category') == 'Pengakuan' ? 'selected' : '' }}>Pengakuan</option>
                                    <option value="Penghargaan" {{ old('category') == 'Penghargaan' ? 'selected' : '' }}>Penghargaan</option>
                                    <option value="Karir Organisasi" {{ old('category') == 'Karir Organisasi' ? 'selected' : '' }}>Karir Organisasi</option>
                                    <option value="Hasil Karya" {{ old('category') == 'Hasil Karya' ? 'selected' : '' }}>Hasil Karya</option>
                                    <option value="Pemberdayaan / Aksi Kemanusiaan" {{ old('category') == 'Pemberdayaan / Aksi Kemanusiaan' ? 'selected' : '' }}>Pemberdayaan / Aksi Kemanusiaan</option>
                                    <option value="Kewirausahaan" {{ old('category') == 'Kewirausahaan' ? 'selected' : '' }}>Kewirausahaan</option>
                                </select>
                                @error('category') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-3 md:gap-4 mb-4">
                                <div>
                                    <label class="block text-xs md:text-sm font-medium text-gray-700">Tingkat</label>
                                    <select name="level" class="mt-1 block w-full rounded-md shadow-sm @error('level') border-red-500 @else border-gray-300 @enderror text-xs md:text-sm" required>
                                        <option value="">-- Pilih Tingkat --</option>
                                        <option value="Perguruan Tinggi" {{ old('level') == 'Perguruan Tinggi' ? 'selected' : '' }}>Perguruan Tinggi</option>
                                        <option value="Provinsi" {{ old('level') == 'Provinsi' ? 'selected' : '' }}>Provinsi</option>
                                        <option value="Nasional" {{ old('level') == 'Nasional' ? 'selected' : '' }}>Nasional</option>
                                        <option value="Regional" {{ old('level') == 'Regional' ? 'selected' : '' }}>Regional</option>
                                        <option value="Internasional" {{ old('level') == 'Internasional' ? 'selected' : '' }}>Internasional</option>
                                    </select>
                                    @error('level') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs md:text-sm font-medium text-gray-700">Tahun Peroleh</label>
                                    <select name="year" class="mt-1 block w-full rounded-md shadow-sm @error('year') border-red-500 @else border-gray-300 @enderror text-xs md:text-sm" required>
                                        <option value="">-- Pilih Tahun --</option>
                                        @for($i = date('Y'); $i >= 2020; $i--)
                                            <option value="{{ $i }}" {{ old('year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('year') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 md:gap-4 mb-4">
                                <div>
                                    <label class="block text-xs md:text-sm font-medium text-gray-700">Tipe</label>
                                    <select name="type" class="mt-1 block w-full rounded-md shadow-sm @error('type') border-red-500 @else border-gray-300 @enderror text-xs md:text-sm" required>
                                        <option value="">-- Pilih Tipe --</option>
                                        <option value="Individu" {{ old('type') == 'Individu' ? 'selected' : '' }}>Individu</option>
                                        <option value="Kelompok" {{ old('type') == 'Kelompok' ? 'selected' : '' }}>Kelompok</option>
                                    </select>
                                    @error('type') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs md:text-sm font-medium text-gray-700">Jumlah Peserta</label>
                                    <input type="number" name="jumlah_peserta" value="{{ old('jumlah_peserta') }}" class="mt-1 block w-full rounded-md shadow-sm @error('jumlah_peserta') border-red-500 @else border-gray-300 @enderror text-xs md:text-sm" min="1" required>
                                    @error('jumlah_peserta') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs md:text-sm font-medium text-gray-700">Jumlah Penghargaan</label>
                                <input type="text" name="jumlah_penghargaan" value="{{ old('jumlah_penghargaan') }}" class="mt-1 block w-full rounded-md shadow-sm @error('jumlah_penghargaan') border-red-500 @else border-gray-300 @enderror text-xs md:text-sm" placeholder="Contoh: 3 Medali per Kategori" required>
                                @error('jumlah_penghargaan') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs md:text-sm font-medium text-gray-700">Penyelenggara</label>
                                <input type="text" name="organizer" value="{{ old('organizer') }}" class="mt-1 block w-full rounded-md shadow-sm @error('organizer') border-red-500 @else border-gray-300 @enderror text-xs md:text-sm" required>
                                @error('organizer') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4 border-t pt-4">
                                <label class="block text-xs md:text-sm font-medium text-gray-700">Bukti (Sertifikat/SK/Foto)</label>
                                <input type="file" name="file_proof" class="mt-1 block w-full text-xs md:text-sm text-gray-500 file:mr-2 md:file:mr-4 file:py-1 md:file:py-2 file:px-2 md:file:px-4 file:rounded file:border-0 file:text-xs md:file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('file_proof') border border-red-500 @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                @error('file_proof') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                                <p class="text-xs text-gray-500 mt-1">PDF/JPG/PNG. Maks 5MB.</p>
                            </div>

                            <button type="submit" 
                                    x-bind:disabled="isSubmitting" 
                                    x-text="isSubmitting ? 'MENGUNGGAH DATA...' : 'Simpan Prestasi'"
                                    class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded transition disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                                    :class="isSubmitting ? 'bg-gray-500' : 'hover:bg-blue-700'">
                                Simpan Prestasi
                            </button>
                        </form>
                    </div>
                </div>

                <div class="md:col-span-2 order-1 md:order-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 md:p-6">
                        <div class="flex justify-between items-center mb-4 flex-col md:flex-row gap-2">
                            <h3 class="text-base md:text-lg font-medium text-gray-900">Daftar Capaian Unggulan</h3>
                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded flex-shrink-0">
                                Total: {{ $achievements->count() }} / 10
                            </span>
                        </div>

                        @if($achievements->isEmpty())
                            <div class="text-center py-10 text-gray-500 text-sm">
                                Belum ada prestasi yang diunggah.
                            </div>
                        @else
                            <div class="overflow-x-auto -mx-4 md:-mx-0">
                                <table class="min-w-full text-xs md:text-sm text-left text-gray-500">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr>
                                            <th class="px-2 md:px-4 py-3">Kegiatan</th>
                                            <th class="px-2 md:px-4 py-3">Kategori</th>
                                            <th class="px-2 md:px-4 py-3 hidden md:table-cell">Tingkat</th>
                                            <th class="px-2 md:px-4 py-3">Bukti</th>
                                            <th class="px-2 md:px-4 py-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($achievements as $cu)
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-2 md:px-4 py-3 font-medium text-gray-900">
                                                    <div class="truncate">{{ $cu->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $cu->organizer }} ({{ $cu->year }})</div>
                                                </td>
                                                <td class="px-2 md:px-4 py-3 text-xs md:text-sm">{{ $cu->category }}</td>
                                                <td class="px-2 md:px-4 py-3 hidden md:table-cell">
                                                    <span class="px-2 py-1 rounded text-xs 
                                                        {{ $cu->level == 'Internasional' ? 'bg-purple-100 text-purple-800' : 
                                                           ($cu->level == 'Regional' ? 'bg-yellow-100 text-yellow-800' : 
                                                           ($cu->level == 'Nasional' ? 'bg-green-100 text-green-800' : 
                                                           ($cu->level == 'Provinsi' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') )) }}">
                                                        {{ $cu->level }}
                                                    </span>
                                                </td>
                                                <td class="px-2 md:px-4 py-3">
                                                    <a href="{{ Storage::disk(config('filesystems.default_public_disk'))->url($cu->file_proof) }}" target="_blank" class="text-blue-600 hover:underline text-xs md:text-sm">Lihat</a>
                                                </td>
                                                <td class="px-2 md:px-4 py-3 text-right">
                                                    <form action="{{ route('student.achievements.destroy', $cu->id) }}" method="POST" onsubmit="return confirm('Hapus item ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 text-xs md:text-sm">Hapus</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>