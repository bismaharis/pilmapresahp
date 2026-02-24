<x-app-layout>
    <x-slot name="header">
        Peringkat Mahasiswa
    </x-slot>

    <div class="space-y-6">
        
        @if($myRegistration)
        <div class="bg-gradient-to-r from-cyan-600 to-blue-700 rounded-lg shadow-lg p-6 flex flex-col md:flex-row items-center justify-between text-white border-l-4 border-yellow-400">
            <div>
                <h3 class="text-xl font-bold mb-1">Transparansi Penilaian AHP</h3>
                <p class="text-sm text-cyan-100">Lihat rincian perhitungan matematis dari nilai akhir Anda (Normalisasi, Bobot, & Uji Konsistensi).</p>
            </div>
            <a href="{{ route('student.transparency.show', ['stage' => $stage]) }}" class="mt-4 md:mt-0 bg-white text-blue-700 hover:bg-gray-10 font-bold py-2 px-6 rounded-full shadow transition-all duration-200 flex items-center">
                Lihat Detail Transparansi
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
            </a>
        </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Leaderboard Peserta</h2>
            
            <div class="flex space-x-2 mb-4 border-b pb-2">
                <a href="{{ route('student.transparency.index', ['stage' => 'fakultas']) }}" 
                   class="px-4 py-2 rounded-md font-bold {{ $stage == 'fakultas' ? 'bg-cyan-500 text-white shadow' : 'text-gray-500 hover:bg-gray-100' }}">
                    Tingkat Fakultas
                </a>
                <a href="{{ route('student.transparency.index', ['stage' => 'universitas']) }}" 
                   class="px-4 py-2 rounded-md font-bold {{ $stage == 'universitas' ? 'bg-cyan-500 text-white shadow' : 'text-gray-500 hover:bg-gray-100' }}">
                    Tingkat Universitas
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 border-b-2 border-gray-300">
                            <th class="p-3 text-center w-16">Rank</th>
                            <th class="p-3">Nama Mahasiswa</th>
                            <th class="p-3 text-center">Program Studi</th>
                            <th class="p-3 text-center">Nilai Rata-rata AHP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rankings as $index => $rank)
                            <tr class="border-b hover:bg-gray-50 {{ $rank->student_id == Auth::user()->student->id ? 'bg-yellow-50 font-bold border-l-4 border-yellow-400' : '' }}">
                                <td class="p-3 text-center text-lg">{{ $index + 1 }}</td>
                                <td class="p-3 flex items-center space-x-3">
                                    <img src="{{ $rank->student->user->photo ? asset('storage/' . $rank->student->user->photo) : 'https://ui-avatars.com/api/?name='.urlencode($rank->student->user->name) }}" class="w-8 h-8 rounded-full object-cover shadow-sm">
                                    <span>{{ $rank->student->user->name }}</span>
                                    @if($rank->student_id == Auth::user()->student->id)
                                        <span class="ml-2 text-xs bg-yellow-400 text-yellow-900 px-2 py-1 rounded font-bold shadow-sm">Anda</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">{{ $rank->student->prodi }}</td>
                                <td class="p-3 text-center text-blue-600 text-lg">
                                    {{ number_format($stage == 'fakultas' ? $rank->total_score_fakultas : $rank->total_score_univ, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-500">Belum ada data peringkat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>