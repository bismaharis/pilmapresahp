<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Jadwal Seleksi Pilmapres Fakultas
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-auth-session-status class="mb-4" :status="session('success')" />

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif

            {{-- FORM TAMBAH JADWAL --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Tambah / Perbarui Jadwal Seleksi</h3>
                <form method="POST" action="{{ route('admin.periods.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @csrf
                    @if($faculties->isNotEmpty())
                        <input type="hidden" name="faculty_id"
                               value="{{ $selectedFacultyId === 'universitas' ? '' : $selectedFacultyId }}">
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                        <input type="text" name="year" value="{{ date('Y') }}" placeholder="2025"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                        @error('year') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                        @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                        @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <button type="submit"
                                class="w-full bg-cyan-600 hover:bg-cyan-700 text-white font-semibold px-4 py-2 rounded-md text-sm shadow">
                            Simpan Jadwal
                        </button>
                    </div>
                </form>
                <p class="text-xs text-gray-400 mt-3">* Menyimpan jadwal baru akan otomatis menonaktifkan jadwal aktif sebelumnya.</p>
            </div>

            {{-- DAFTAR JADWAL --}}
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-4">
                    <h3 class="text-lg font-bold text-gray-800 shrink-0">Riwayat Jadwal</h3>
                    @if($faculties->isNotEmpty())
                        <form method="GET" action="{{ route('admin.periods.index') }}" class="flex items-center gap-2">
                            <label class="text-sm text-gray-600 shrink-0">Lihat jadwal:</label>
                            <select name="faculty_id" onchange="this.form.submit()"
                                    class="border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                                <option value="universitas" {{ $selectedFacultyId === 'universitas' ? 'selected' : '' }}>
                                    Seleksi Universitas
                                </option>
                                @foreach($faculties as $faculty)
                                    <option value="{{ $faculty->id }}" {{ (string) $selectedFacultyId === (string) $faculty->id ? 'selected' : '' }}>
                                        {{ $faculty->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                </div>
                
                <table class="min-w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-6 py-3">Tahun</th>
                            <th class="px-6 py-3">Mulai</th>
                            <th class="px-6 py-3">Selesai</th>
                            <th class="px-6 py-3 text-center">Pendaftar</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periods as $period)
                            <tr class="border-b hover:bg-gray-50 {{ $period->isOpen() ? 'bg-green-50' : '' }}">
                                <td class="px-6 py-4 font-semibold">{{ $period->year }}</td>
                                <td class="px-6 py-4">{{ $period->start_date->format('d M Y') }}</td>
                                <td class="px-6 py-4">{{ $period->end_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-center font-semibold text-gray-700">{{ $period->registrations_count }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($period->isOpen())
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full">&#x25CF; Berjalan</span>
                                    @elseif($period->is_active && now()->lt($period->start_date))
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full">Akan Datang</span>
                                    @elseif($period->is_active && now()->gt($period->end_date))
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded-full">Berakhir</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-500 text-xs rounded-full">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        {{-- Toggle aktif/nonaktif --}}
                                        <form method="POST" action="{{ route('admin.periods.update', $period) }}">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="year" value="{{ $period->year }}">
                                            <input type="hidden" name="start_date" value="{{ $period->start_date->format('Y-m-d') }}">
                                            <input type="hidden" name="end_date" value="{{ $period->end_date->format('Y-m-d') }}">
                                            <input type="hidden" name="is_active" value="{{ $period->is_active ? '0' : '1' }}">
                                            <button type="submit"
                                                    title="{{ $period->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded border transition-colors
                                                        {{ $period->is_active ? 'border-yellow-400 text-yellow-600 hover:bg-yellow-50' : 'border-green-400 text-green-600 hover:bg-green-50' }}">
                                                @if($period->is_active)
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                @endif
                                            </button>
                                        </form>
                                        
                                        <a href="{{ route('admin.periods.show', $period) }}"
                                        title="Histori"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded border border-blue-400 text-blue-600 hover:bg-blue-50 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </a>

                                        <a href="{{ route('admin.periods.export_excel', $period) }}"
                                        title="Export Excel"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded border border-emerald-400 text-emerald-600 hover:bg-emerald-50 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                            </svg>
                                        </a>

                                        @if($period->registrations_count > 0 || now()->greaterThanOrEqualTo($period->start_date))
                                            <span title="Terkunci Histori"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded border border-gray-300 text-gray-400 bg-gray-50">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                            </span>
                                        @else
                                            {{-- Hapus --}}
                                            <form method="POST" action="{{ route('admin.periods.destroy', $period) }}"
                                                  onsubmit="return confirm('Hapus jadwal ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="text-xs px-3 py-1 rounded border border-red-400 text-red-600 hover:bg-red-50">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada jadwal seleksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>