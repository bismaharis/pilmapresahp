<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Histori Periode {{ $period->year }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.periods.export_excel', $period) }}" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Export Excel
                </a>
                <a href="{{ route('admin.periods.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Mulai</p>
                    <p class="mt-1 text-base font-bold text-gray-800">{{ $period->start_date->format('d M Y') }}</p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Selesai</p>
                    <p class="mt-1 text-base font-bold text-gray-800">{{ $period->end_date->format('d M Y') }}</p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Pendaftar</p>
                    <p class="mt-1 text-base font-bold text-gray-800">{{ $rankedRegistrations->count() }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-bold text-gray-800">Pendaftar, Berkas, dan Urutan Pemenang</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-gray-700">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                            <tr>
                                <th class="px-4 py-3">Peringkat</th>
                                <th class="px-4 py-3">Mahasiswa</th>
                                <th class="px-4 py-3">Tahap/Status</th>
                                <th class="px-4 py-3">Skor Akhir</th>
                                <th class="px-4 py-3">Berkas</th>
                                <th class="px-4 py-3">Capaian Unggulan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($rankedRegistrations as $index => $registration)
                                @php
                                    $finalScore = $registration->total_score_univ > 0
                                        ? $registration->total_score_univ
                                        : ($registration->total_score_fakultas ?? 0);
                                @endphp
                                <tr class="align-top">
                                    <td class="px-4 py-4">
                                        <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-700">
                                            #{{ $index + 1 }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <p class="font-semibold text-gray-900">{{ $registration->student->user->name }}</p>
                                        <p class="text-xs text-gray-500">NIM: {{ $registration->student->nim }}</p>
                                        <p class="text-xs text-gray-500">Prodi: {{ $registration->student->prodi }}</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <p class="text-xs font-semibold uppercase text-blue-700">{{ $registration->stage }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $registration->status }}</p>
                                    </td>
                                    <td class="px-4 py-4 font-bold text-gray-900">{{ number_format((float) $finalScore, 2) }}</td>
                                    <td class="px-4 py-4">
                                        <div class="space-y-1 text-xs">
                                            @if($registration->file_gk)
                                                <a href="{{ '/storage/' . ltrim($registration->file_gk, '/') }}" target="_blank" class="block text-blue-600 hover:underline">Naskah GK</a>
                                            @endif
                                            @if($registration->file_transkrip)
                                                <a href="{{ '/storage/' . ltrim($registration->file_transkrip, '/') }}" target="_blank" class="block text-blue-600 hover:underline">Transkrip</a>
                                            @endif
                                            @if($registration->file_poster_gk)
                                                <a href="{{ '/storage/' . ltrim($registration->file_poster_gk, '/') }}" target="_blank" class="block text-blue-600 hover:underline">Poster GK</a>
                                            @endif
                                            @if($registration->file_poster_diri)
                                                <a href="{{ '/storage/' . ltrim($registration->file_poster_diri, '/') }}" target="_blank" class="block text-blue-600 hover:underline">Poster Diri</a>
                                            @endif
                                            @if($registration->video_link)
                                                <a href="{{ $registration->video_link }}" target="_blank" class="block text-indigo-600 hover:underline">Video Bahasa Inggris</a>
                                            @endif
                                            @if(! $registration->file_gk && ! $registration->file_transkrip && ! $registration->file_poster_gk && ! $registration->file_poster_diri && ! $registration->video_link)
                                                <span class="text-gray-400">Tidak ada berkas.</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <p class="text-xs font-semibold text-gray-700">Total: {{ $registration->achievements->count() }}</p>
                                        <div class="mt-1 space-y-1 text-xs">
                                            @forelse($registration->achievements as $achievement)
                                                <div>
                                                    <p class="font-medium text-gray-800">{{ $achievement->name }}</p>
                                                    @if($achievement->file_proof)
                                                        <a href="{{ '/storage/' . ltrim($achievement->file_proof, '/') }}" target="_blank" class="text-blue-600 hover:underline">Lihat Bukti</a>
                                                    @endif
                                                </div>
                                            @empty
                                                <span class="text-gray-400">Tidak ada CU.</span>
                                            @endforelse
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada pendaftar di periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
