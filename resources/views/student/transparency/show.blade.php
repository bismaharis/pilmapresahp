<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('student.transparency.index', ['stage' => $stage]) }}" class="text-gray-500 hover:text-cyan-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <span>Detail Transparansi Penilaian AHP (Tahap {{ ucfirst($stage) }})</span>
        </div>
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg p-6 border-t-4 border-cyan-500">
        <div class="mb-6 flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Matriks Hasil Keputusan AHP</h2>
                <p class="text-sm text-gray-600 mt-1">Tahap Peringkat: <span class="font-bold text-cyan-600 uppercase">{{ $stage }}</span></p>
            </div>
            <div class="text-right bg-blue-50 p-4 rounded-lg border border-blue-100">
                <p class="text-xs text-blue-600 font-bold uppercase tracking-widest">Total Skor Akhir</p>
                <p class="text-3xl font-extrabold text-blue-700">
                    {{ number_format($stage == 'fakultas' ? $myRegistration->total_score_fakultas : $myRegistration->total_score_univ, 2) }}
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-white text-left">
                        <th class="p-3 border border-gray-700 rounded-tl-lg">Hierarki Kriteria</th>
                        <th class="p-3 border border-gray-700 text-center w-24">Bobot Global</th>
                        <th class="p-3 border border-gray-700 text-center w-32">Nilai Mentah</th>
                        <th class="p-3 border border-gray-700 text-center w-32">Skor Terbobot</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800">
                    
                    @foreach($criterias as $induk)
                        <tr class="bg-gray-200 border-b border-gray-300">
                            <td class="p-3 font-bold text-base uppercase">1. {{ $induk->name }}</td>
                            <td class="p-3 text-center font-bold">{{ $induk->weight * 100 }}%</td>
                            <td class="p-3 text-center text-xs text-gray-500 italic">Terakumulasi</td>
                            <td class="p-3 text-center font-bold text-blue-700">-</td>
                        </tr>

                        @foreach($induk->children as $sub)
                            <tr class="bg-gray-50 border-b border-gray-200 hover:bg-gray-100">
                                <td class="p-3 pl-10 font-semibold text-gray-700 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    1.{{ $loop->iteration }}. {{ $sub->name }}
                                </td>
                                <td class="p-3 text-center text-gray-600">{{ $sub->weight * 100 }}%</td>
                                
                                @php
                                    $isParent = $sub->children->count() > 0;
                                    $nilaiMentah = 0;

                                    if (!$isParent) {
                                        if ($induk->type == 'cu') {
                                            // LOGIKA CU: Menghitung skor berdasarkan prestasi yang sesuai kategori
                                            $achievements = $myRegistration->achievements->where('category', $sub->name);
                                            // Contoh perhitungan sederhana (bisa disesuaikan dgn engine AHP Anda)
                                            $nilaiMentah = $achievements->count() * 10; 
                                        } else {
                                            // LOGIKA GK: Ambil dari Juri
                                            $assessment = $myRegistration->assessments->where('criteria_id', $sub->id)->first();
                                            $nilaiMentah = $assessment ? $assessment->score : 0;
                                        }
                                    }
                                    $skorTerbobot = $nilaiMentah * $sub->weight;
                                @endphp

                                <td class="p-3 text-center {{ $isParent ? 'text-xs text-gray-400 italic' : 'text-gray-700 font-medium' }}">
                                    {{ $isParent ? 'Menunggu Sub-kriteria' : ($nilaiMentah > 0 ? $nilaiMentah : '0') }}
                                </td>
                                <td class="p-3 text-center text-blue-600 font-bold">
                                    {{ $isParent ? '-' : ($skorTerbobot > 0 ? number_format($skorTerbobot, 2) : '0') }}
                                </td>
                            </tr>

                            @foreach($sub->children as $subsub)
                                <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 pl-20 text-gray-600 text-sm flex items-center {{ $subsub->children->count() > 0 ? 'font-semibold' : '' }}">
                                        @if($subsub->children->count() > 0)
                                            <svg class="w-3 h-3 text-cyan-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        @else
                                            <div class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-3"></div>
                                        @endif
                                        {{ $subsub->name }}
                                    </td>
                                    <td class="p-3 text-center text-gray-600 text-xs">{{ $subsub->weight * 100 }}%</td>
                                    
                                    @php
                                        $isParent3 = $subsub->children->count() > 0;
                                        $assessmentSubSub = $myRegistration->assessments->where('criteria_id', $subsub->id)->first();
                                        $nilaiMentahSubSub = $assessmentSubSub ? $assessmentSubSub->score : 0;
                                        $skorTerbobotSubSub = $nilaiMentahSubSub * $subsub->weight;
                                    @endphp

                                    <td class="p-3 text-center {{ $isParent3 ? 'text-xs text-gray-400 italic' : 'text-gray-700 text-xs' }}">
                                        {{ $isParent3 ? 'Menunggu Sub-kriteria' : ($nilaiMentahSubSub > 0 ? $nilaiMentahSubSub : '0') }}
                                    </td>
                                    <td class="p-3 text-center text-blue-600 font-bold text-xs">
                                        {{ $isParent3 ? '-' : ($skorTerbobotSubSub > 0 ? number_format($skorTerbobotSubSub, 2) : '0') }}
                                    </td>
                                </tr>

                                @foreach($subsub->children as $subsubsub)
                                    <tr class="bg-gray-50/50 border-b border-gray-100 hover:bg-gray-100">
                                        <td class="p-3 pl-28 text-gray-500 text-xs flex items-center italic">
                                            <div class="w-1.5 h-1.5 rounded-sm bg-gray-400 mr-2"></div>
                                            {{ $subsubsub->name }}
                                        </td>
                                        <td class="p-3 text-center text-gray-500 text-[11px]">{{ $subsubsub->weight * 100 }}%</td>
                                        
                                        @php
                                            $assessmentSubSubSub = $myRegistration->assessments->where('criteria_id', $subsubsub->id)->first();
                                            $nilaiMentahSubSubSub = $assessmentSubSubSub ? $assessmentSubSubSub->score : 0;
                                            $skorTerbobotSubSubSub = $nilaiMentahSubSubSub * $subsubsub->weight;
                                        @endphp

                                        <td class="p-3 text-center text-gray-600 text-xs">{{ $nilaiMentahSubSubSub > 0 ? $nilaiMentahSubSubSub : '0' }}</td>
                                        <td class="p-3 text-center text-blue-500 text-xs">{{ $skorTerbobotSubSubSub > 0 ? number_format($skorTerbobotSubSubSub, 2) : '0' }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>