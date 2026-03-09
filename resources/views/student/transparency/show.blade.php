<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('student.transparency.index', ['stage' => $stage]) }}" class="text-gray-500 hover:text-cyan-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <span class="font-semibold text-xl text-gray-800 leading-tight">Detail Transparansi Penilaian (Tahap {{ ucfirst($stage) }})</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6 border-t-4 border-cyan-500">
                <div class="mb-6 flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Matriks Hasil Keputusan AHP</h2>
                        <p class="text-sm text-gray-600 mt-1">Status Penilaian: <span class="font-bold text-cyan-600 uppercase">{{ $stage }}</span></p>
                    </div>
                    <div class="text-right bg-blue-50 p-4 rounded-lg border border-blue-100">
                        <p class="text-xs text-blue-600 font-bold uppercase tracking-widest">Total Skor Akhir</p>
                        <p class="text-4xl font-extrabold text-blue-700">
                            {{ number_format($stage == 'fakultas' ? $myRegistration->total_score_fakultas : $myRegistration->total_score_univ, 2) }}
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto border rounded-lg">
                    <table class="w-full text-sm border-collapse bg-white">
                        <thead>
                            <tr class="bg-gray-800 text-white text-left">
                                <th class="p-4 border border-gray-700">Hierarki Kriteria</th>
                                <th class="p-4 border border-gray-700 text-center w-28">Bobot</th>
                                <th class="p-4 border border-gray-700 text-center w-36">Nilai Mentah</th>
                                <th class="p-4 border border-gray-700 text-center w-36">Skor Terbobot</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($criterias as $induk)
                                @php
                                    // Hitung Total untuk Induk
                                    $totalRawInduk = 0;
                                    $maxTotalInduk = 0;
                                    $normalizedInduk = 0;
                                    $totalWeightedInduk = 0;

                                    if ($induk->type == 'cu') {
                                        // Akumulasi Nilai Mentah CU dari semua sub-kriterianya
                                        $totalRawInduk = $myRegistration->assessments()
                                            ->whereHas('criteria', fn($q) => $q->where('type', 'cu')->whereNotNull('parent_id'))
                                            ->sum('score');
                                        $maxTotalInduk = 500;
                                        // CU: normalisasi dulu ke skala 100, baru kali bobot
                                        $normalizedInduk = ($totalRawInduk / 500) * 100;
                                        $totalWeightedInduk = $normalizedInduk * $induk->weight;
                                    } elseif ($induk->type == 'gk') {
                                        // Sum average scores for GK criteria
                                        $gkAssessments = $myRegistration->assessments()
                                            ->whereHas('criteria', fn($q) => $q->where('type', 'gk'))
                                            ->get()
                                            ->groupBy('criteria_id');
                                        foreach ($gkAssessments as $criteriaId => $scores) {
                                            $totalRawInduk += $scores->avg('score') ?? 0;
                                        }
                                        $maxTotalInduk = 200;
                                    } elseif ($induk->type == 'bi') {
                                        // Sum average scores for BI criteria
                                        $biAssessments = $myRegistration->assessments()
                                            ->whereHas('criteria', fn($q) => $q->where('type', 'bi'))
                                            ->get()
                                            ->groupBy('criteria_id');
                                        foreach ($biAssessments as $criteriaId => $scores) {
                                            $totalRawInduk += $scores->avg('score') ?? 0;
                                        }
                                        $maxTotalInduk = 100;
                                    }

                                    // Normalisasi dan skor terbobot untuk GK dan BI
                                    if ($induk->type != 'cu' && $maxTotalInduk > 0) {
                                        $normalizedInduk = ($totalRawInduk / $maxTotalInduk) * 100;
                                        $totalWeightedInduk = $normalizedInduk * $induk->weight;
                                    }
                                @endphp

                                <tr class="bg-gray-100 border-b-2 border-gray-300">
                                    <td class="p-4 font-bold text-gray-900 uppercase">
                                        {{ $loop->iteration }}. {{ $induk->name }}
                                    </td>
                                    <td class="p-4 text-center font-bold text-gray-700">{{ $induk->weight * 100 }}%</td>
                                    <td class="p-4 text-center font-bold">
                                        {{ number_format($totalRawInduk, 0) }}
                                    </td>
                                    <td class="p-4 text-center font-extrabold text-blue-800">
                                        {{ number_format($totalWeightedInduk, 2) }}
                                    </td>
                                </tr>

                                @foreach($induk->children as $sub)
                                    @php
                                        $isParent = $sub->children->count() > 0;
                                        $assessment = $myRegistration->assessments->where('criteria_id', $sub->id)->first();
                                        $nilaiMentah = $assessment ? $assessment->score : 0;
                                        // Jika CU, skor terbobot individual adalah 0 karena bobot sub = 0
                                        $skorTerbobot = $nilaiMentah * $sub->weight;
                                    @endphp

                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="p-3 pl-10 font-semibold text-gray-700">
                                            <span class="text-gray-400 mr-2">▶</span> {{ $loop->parent->iteration }}.{{ $loop->iteration }}. {{ $sub->name }}
                                        </td>
                                        <td class="p-3 text-center text-gray-500">{{ number_format($sub->weight * 100, 2) }}%</td>
                                        <td class="p-3 text-center {{ $isParent ? 'italic text-gray-400' : 'text-gray-900' }}">
                                            {{ $isParent ? 'Sub-kriteria' : number_format($nilaiMentah, 0) }}
                                        </td>
                                        <td class="p-3 text-center font-bold text-blue-600">
                                            {{ $isParent ? '-' : number_format($skorTerbobot, 2) }}
                                        </td>
                                    </tr>

                                    @foreach($sub->children as $subsub)
                                        @php
                                            $isParent3 = $subsub->children->count() > 0;
                                            $assessment3 = $myRegistration->assessments->where('criteria_id', $subsub->id)->first();
                                            $nilaiMentah3 = $assessment3 ? $assessment3->score : 0;
                                            $skorTerbobot3 = $nilaiMentah3 * $subsub->weight;
                                        @endphp
                                        <tr class="border-b border-gray-100 bg-white hover:bg-gray-50">
                                            <td class="p-2 pl-20 text-gray-600 text-sm italic">
                                                — {{ $subsub->name }}
                                            </td>
                                            <td class="p-2 text-center text-gray-400 text-xs">{{ number_format($subsub->weight * 100, 2) }}%</td>
                                            <td class="p-2 text-center text-xs">
                                                {{ $isParent3 ? '...' : number_format($nilaiMentah3, 0) }}
                                            </td>
                                            <td class="p-2 text-center text-blue-500 font-semibold text-xs">
                                                {{ $isParent3 ? '-' : number_format($skorTerbobot3, 2) }}
                                            </td>
                                        </tr>

                                        @foreach($subsub->children as $subsubsub)
                                            @php
                                                $assessment4 = $myRegistration->assessments->where('criteria_id', $subsubsub->id)->first();
                                                $nilaiMentah4 = $assessment4 ? $assessment4->score : 0;
                                                $skorTerbobot4 = $nilaiMentah4 * $subsubsub->weight;
                                            @endphp
                                            <tr class="border-b border-gray-50 bg-gray-50/30">
                                                <td class="p-1 pl-28 text-gray-500 text-xs italic">
                                                    • {{ $subsubsub->name }}
                                                </td>
                                                <td class="p-1 text-center text-gray-400 text-[10px]">{{ number_format($subsubsub->weight * 100, 2) }}%</td>
                                                <td class="p-1 text-center text-[10px]">{{ number_format($nilaiMentah4, 0) }}</td>
                                                <td class="p-1 text-center text-blue-400 text-[10px]">{{ number_format($skorTerbobot4, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>