<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Peserta Penilaian') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-auth-session-status class="mb-4" :status="session('success')" />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <table class="min-w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Nama Mahasiswa</th>
                                <th class="px-6 py-3">NIM & Prodi</th>
                                <th class="px-6 py-3 text-center">Berkas GK</th>
                                <th class="px-6 py-3 text-center">Berkas CU</th>
                                <th class="px-6 py-3 text-center">Aksi Penilaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registrations as $reg)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ $reg->student->user->name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $reg->student->nim }} <br>
                                        <span class="text-xs text-gray-400">{{ $reg->student->prodi }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($reg->file_gk)
                                            <a href="{{ Storage::url($reg->file_gk) }}" target="_blank" class="text-blue-600 hover:underline">Lihat Naskah</a>
                                        @else
                                            <span class="text-red-500 text-xs">Belum Upload</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">
                                            {{ $reg->achievements->count() }} Sertifikat
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            // BENAR: Menggunakan $reg sesuai variabel forelse
                                            $isConflict = (!$juri->is_univ_judge && $reg->student->prodi == $juri->unit_kerja);
                                        @endphp

                                        @if($isConflict)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                                Conflict of Interest (Satu Prodi)
                                            </span>
                                        @else
                                            <a href="{{ route('juri.assessments.edit', $reg->id) }}" class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded shadow text-sm">
                                                Beri Nilai
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada peserta yang dapat dinilai.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>