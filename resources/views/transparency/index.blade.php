<x-app-layout>
    <x-slot name="header">
        Peringkat & Transparansi AHP
    </x-slot>

    <div class="space-y-6">
        
        @if($role === 'mahasiswa' && isset($myRegistration))
        <div class="bg-gradient-to-r from-cyan-600 to-blue-700 rounded-lg shadow-lg p-6 flex flex-col md:flex-row items-center justify-between text-white border-l-4 border-yellow-400">
            <div>
                <h3 class="text-xl font-bold mb-1">Transparansi Penilaian AHP Anda</h3>
                <p class="text-sm text-cyan-100">Lihat rincian perhitungan matematis dari nilai akhir Anda.</p>
            </div>
            <a href="{{ route('transparency.show', ['id' => $myRegistration->id, 'stage' => $stage]) }}" class="mt-4 md:mt-0 bg-white text-blue-700 hover:bg-gray-100 font-bold py-2 px-6 rounded-full shadow transition flex items-center">
                Lihat Detail Transparansi
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
            </a>
        </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Leaderboard Peserta</h2>
            
            <div class="flex space-x-2 mb-4 border-b pb-2">
                <a href="{{ route('transparency.index', ['stage' => 'fakultas']) }}" class="px-4 py-2 rounded-md font-bold {{ $stage == 'fakultas' ? 'bg-cyan-500 text-white shadow' : 'text-gray-500 hover:bg-gray-100' }}">Tingkat Fakultas</a>
                <a href="{{ route('transparency.index', ['stage' => 'universitas']) }}" class="px-4 py-2 rounded-md font-bold {{ $stage == 'universitas' ? 'bg-cyan-500 text-white shadow' : 'text-gray-500 hover:bg-gray-100' }}">Tingkat Universitas</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 border-b-2 border-gray-300">
                            <th class="p-3 text-center w-16">Rank</th>
                            <th class="p-3">Nama Mahasiswa</th>
                            <th class="p-3 text-center">Program Studi</th>
                            <th class="p-3 text-center">Nilai Rata-rata AHP</th>
                            <th class="p-3 text-center">Rincian Transparansi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rankings as $index => $rank)
                            <tr class="border-b hover:bg-gray-50 {{ ($role === 'mahasiswa' && $rank->student_id == $user->student->id) ? 'bg-yellow-50 font-bold border-l-4 border-yellow-400' : '' }}">
                                <td class="p-3 text-center text-lg">{{ $index + 1 }}</td>
                                <td class="p-3 flex items-center space-x-3">
                                    <img src="{{ $rank->student->user->photo ? asset('storage/' . $rank->student->user->photo) : 'https://ui-avatars.com/api/?name='.urlencode($rank->student->user->name) }}" class="w-8 h-8 rounded-full object-cover shadow-sm">
                                    <span>{{ $rank->student->user->name }}</span>
                                </td>
                                <td class="p-3 text-center">{{ $rank->student->prodi }}</td>
                                <td class="p-3 text-center text-blue-600 text-lg font-bold">
                                    {{ number_format($stage == 'fakultas' ? $rank->total_score_fakultas : $rank->total_score_univ, 2) }}
                                </td>
                                <td class="p-3 text-center">
                                    @php
                                        // LOGIKA TOMBOL: Menyala jika mhs itu diri sendiri, ATAU jika user adalah admin/juri
                                        $canView = false;
                                        if ($role === 'mahasiswa' && $rank->student_id == $user->student->id) $canView = true;
                                        elseif (in_array($role, ['super_admin', 'admin_univ'])) $canView = true;
                                        elseif (in_array($role, ['admin_fakultas', 'dosen'])) {
                                            $isUnivJudge = ($role === 'dosen' && $user->lecturer && $user->lecturer->is_univ_judge);
                                            if ($isUnivJudge || $rank->student->faculty_id == $user->faculty_id) $canView = true;
                                        }
                                    @endphp

                                    @if($canView)
                                        <a href="{{ route('transparency.show', ['id' => $rank->id, 'stage' => $stage]) }}" class="inline-block bg-cyan-100 text-cyan-700 px-3 py-1 rounded text-xs font-bold hover:bg-cyan-200 border border-cyan-300">
                                            Buka Rincian
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Terkunci</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-4 text-center text-gray-500">Belum ada data peringkat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>