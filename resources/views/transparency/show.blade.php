<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-2 md:space-x-4">
            <a href="{{ route('transparency.index', ['stage' => $stage]) }}" class="text-slate-500 hover:text-slate-700 flex-shrink-0">
                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <span class="text-sm md:text-base truncate">Detail Transparansi Penilaian AHP (Tahap {{ ucfirst($stage) }})</span>
        </div>
    </x-slot>

    @php
        $globalWeights = [];
        foreach ($criterias as $root) {
            $globalWeights[$root->id] = $root->weight;
            computeGlobalWeights($root->children, $root->weight, $globalWeights);
        }
    @endphp

    <div class="space-y-4 md:space-y-6">

        {{-- HEADER KARTU PESERTA --}}
        <div class="bg-white shadow-sm rounded-lg p-4 md:p-6 border-t-4 border-blue-400">
            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                <div class="min-w-0">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800">{{ $registration->student->user->name }}</h2>
                    <p class="text-xs md:text-sm text-slate-500 mt-1">{{ $registration->student->prodi }} &bull; Tahap: <span class="font-bold text-slate-700 uppercase">{{ $stage }}</span></p>
                </div>
                <div class="text-center md:text-right bg-blue-100 p-3 md:p-4 rounded-lg border border-slate-200 flex-shrink-0">
                    <p class="text-xs text-blue-600 font-bold uppercase tracking-widest">Total Skor Akhir</p>
                    <p class="text-2xl md:text-3xl font-extrabold text-blue-800">
                        {{ number_format($stage == 'fakultas' ? $registration->total_score_fakultas : $registration->total_score_univ, 2) }}
                    </p>
                    <p class="text-xs text-blue-400 mt-1">Rata-rata dari juri yang telah menilai</p>
                </div>
            </div>
        </div>

        {{-- INFO AHP --}}
        <div class="bg-blue-50 border border-slate-200 rounded-lg px-5 py-4 text-xs text-slate-700 space-y-1">
            <p class="font-bold text-sm text-slate-800">Cara Membaca Tabel AHP</p>
            <p><span class="font-semibold">Bobot Global</span> = perkalian bobot dari root ke node (mis. GK 35% × Naskah 50% × Substansi 70% = 12.25%). Jumlah bobot global semua leaf = 100%.</p>
            <p><span class="font-semibold">Skor Terbobot</span> = (Nilai Mentah ÷ Nilai Maks) × 100 × Bobot Global. Jumlah seluruh skor terbobot = Total Skor Akhir.</p>
            <p><span class="font-semibold">CU</span> dihitung dengan rumus khusus: (Total Raw ÷ 500) × 100 × 35%.</p>
        </div>

        {{-- TRANSPARANSI PER JURI (hanya non-mahasiswa) --}}
        @if($role !== 'mahasiswa')
            @if($allJuriDone)
                @php
                    $juriList = $assessmentsByLecturer->map(function($items, $lecturerId) {
                        $first = $items->first();
                        return ['id' => $lecturerId, 'nama' => $first?->lecturer?->user?->name ?? 'Juri #' . $lecturerId];
                    })->values();
                @endphp

                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="bg-blue-800 px-4 md:px-6 py-3 md:py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                        <div>
                            <h3 class="text-base md:text-lg font-bold text-white">Rincian Nilai Per Juri</h3>
                            <p class="text-xs text-slate-300 mt-0.5">Transparansi penilaian dari juri yang telah menilai &bull; {{ $juriList->count() }} juri</p>
                        </div>
                        <span class="bg-slate-200 text-slate-700 text-xs font-bold px-3 py-1 rounded-full self-start md:self-auto">✓ Tersedia</span>
                    </div>

                    {{-- Tab Buttons --}}
                    <div class="overflow-x-auto border-b border-gray-200 bg-gray-50">
                        <div class="flex gap-2 px-4 md:px-6 py-3 whitespace-nowrap">
                            @foreach($juriList as $idx => $juri)
                                <button onclick="showJuriTab('tab-juri-{{ $juri['id'] }}')"
                                    id="btn-tab-juri-{{ $juri['id'] }}"
                                    class="juri-tab-btn flex items-center space-x-1 md:space-x-2 px-3 py-2 rounded-md text-xs md:text-sm font-semibold border transition flex-shrink-0
                                         {{ $idx === 0 ? 'bg-blue-700 text-white border-blue-700 shadow-sm' : 'bg-white text-blue-600 border-blue-300 hover:bg-blue-100' }}">
                                     <span class="w-5 h-5 rounded-full flex items-center justify-center font-bold text-xs {{ $idx === 0 ? 'bg-white/15' : 'bg-blue-200 text-blue-600' }}">
                                        {{ strtoupper(substr($juri['nama'], 0, 1)) }}
                                    </span>
                                    <span>{{ $juri['nama'] }}</span>
                                </button>
                            @endforeach
                            <button onclick="showJuriTab('tab-matriks-rata')"
                                id="btn-tab-matriks-rata"
                                class="juri-tab-btn flex items-center space-x-1 md:space-x-2 px-3 py-2 rounded-md text-xs md:text-sm font-semibold border transition bg-white text-slate-600 border-slate-300 hover:bg-slate-100 flex-shrink-0 md:ml-auto">
                                <span class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-slate-700 font-bold text-xs">&#x2248;</span>
                                <span>Matriks Rata-rata</span>
                            </button>
                        </div>
                    </div>

                    {{-- Tab Panels Per Juri --}}
                    @foreach($assessmentsByLecturer as $lecturerId => $assessmentsByCriteria)
                        @php
                            $namaJuri  = $assessmentsByCriteria->first()?->lecturer?->user?->name ?? 'Juri #' . $lecturerId;
                            $notesJuri = $assessmentsByCriteria
                                ->filter(function ($assessment) {
                                    if (!is_string($assessment->notes) || trim($assessment->notes) === '') {
                                        return false;
                                    }

                                    $decoded = json_decode($assessment->notes, true);
                                    if (is_array($decoded) && array_key_exists('achievement_scores', $decoded) && count($decoded) === 1) {
                                        return false;
                                    }

                                    return true;
                                });
                        @endphp
                        <div id="tab-juri-{{ $lecturerId }}" class="juri-tab-panel {{ $loop->first ? '' : 'hidden' }}">
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-slate-100 text-slate-700 uppercase tracking-wide text-[11px]">
                                            <th class="px-3 md:px-6 py-3 text-left">Hierarki Kriteria</th>
                                            <th class="px-3 md:px-6 py-3 text-center whitespace-nowrap">Bobot Lokal</th>
                                            <th class="px-3 md:px-6 py-3 text-center whitespace-nowrap">Bobot Global</th>
                                            <th class="px-3 md:px-6 py-3 text-center whitespace-nowrap">Nilai Mentah</th>
                                            <th class="px-3 md:px-6 py-3 text-center whitespace-nowrap">Nilai Maks</th>
                                            <th class="px-3 md:px-6 py-3 text-center whitespace-nowrap">Skor Terbobot</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($criterias as $root)
                                            @php
                                                $isGkRoot = ($root->type === 'gk');
                                                $isCuRoot = ($root->type === 'cu');
                                                // Hitung akumulasi root
                                                if ($isCuRoot) {
                                                    $cuRaw = 0;
                                                    foreach ($root->children as $sub) {
                                                        $cuRaw += $assessmentsByCriteria->get($sub->id)?->score ?? 0;
                                                    }
                                                    $rootAccum = ($cuRaw / 500) * 100 * $root->weight;
                                                } else {
                                                    $rootAccum = 0;
                                                    foreach ($root->children as $sub) {
                                                        $rootAccum += accumScore($sub, $assessmentsByCriteria, true, $root->type);
                                                    }
                                                    $rootAccum *= $root->weight;
                                                }
                                            @endphp
                                            {{-- Baris Root --}}
                                            <tr class="bg-slate-200 border-y border-slate-300">
                                                <td class="px-3 md:px-6 py-3 font-black text-slate-800 uppercase tracking-wider text-xs">{{ $root->name }}</td>
                                                <td class="px-3 md:px-6 py-3 text-center text-slate-700 font-bold text-xs">{{ $root->weight * 100 }}%</td>
                                                <td class="px-3 md:px-6 py-3 text-center text-slate-700 font-bold text-xs">{{ $root->weight * 100 }}%</td>
                                                <td class="px-3 md:px-6 py-3 text-center text-slate-500 text-xs italic">
                                                    {{ $isCuRoot ? number_format($cuRaw, 2) . ' / 500' : '—' }}
                                                </td>
                                                <td class="px-3 md:px-6 py-3 text-center text-slate-500 text-xs italic">—</td>
                                                <td class="px-3 md:px-6 py-3 text-center text-slate-800 font-black text-sm">{{ number_format($rootAccum, 2) }}</td>
                                            </tr>

                                            @foreach($root->children as $sub)
                                                @php
                                                    $isSubParent = $sub->children->isNotEmpty();
                                                    $subGw = $globalWeights[$sub->id] ?? 0;
                                                    $nilaiSub = $assessmentsByCriteria->get($sub->id)?->score ?? 0;
                                                    if ($isCuRoot) {
                                                        $subAccum = ($nilaiSub / 500) * 100 * $root->weight;
                                                    } elseif (!$isSubParent) {
                                                        $subAccum = $sub->max_score > 0 ? ($nilaiSub / $sub->max_score) * 100 * $subGw : 0;
                                                    } else {
                                                        $subAccum = accumScore($sub, $assessmentsByCriteria, true, $root->type) * $root->weight;
                                                    }
                                                @endphp
                                                <tr class="bg-slate-100 hover:bg-slate-200 border-b border-slate-200">
                                                    <td class="px-3 md:px-6 py-2 pl-6 md:pl-10 font-bold text-slate-700 text-xs">
                                                        <svg class="w-3 h-3 inline text-slate-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                        {{ $sub->name }}
                                                    </td>
                                                    <td class="px-3 md:px-6 py-2 text-center text-slate-600 text-xs whitespace-nowrap">{{ number_format($sub->weight * 100, 2) }}%</td>
                                                    <td class="px-3 md:px-6 py-2 text-center text-slate-700 font-semibold text-xs whitespace-nowrap">{{ fmtWeight($subGw, 1, $isGkRoot) }}</td>
                                                    <td class="px-3 md:px-6 py-2 text-center text-xs whitespace-nowrap {{ $isSubParent && !$isCuRoot ? 'text-slate-400 italic' : 'text-slate-800 font-semibold' }}">
                                                        {{ ($isSubParent && !$isCuRoot) ? '—' : fmt($nilaiSub, 1, $isGkRoot) }}
                                                    </td>
                                                    <td class="px-3 md:px-6 py-2 text-center text-slate-500 text-xs whitespace-nowrap">
                                                        {{ (!$isSubParent && !$isCuRoot) ? $sub->max_score : '—' }}
                                                    </td>
                                                    <td class="px-3 md:px-6 py-2 text-center text-slate-700 font-bold text-xs whitespace-nowrap">{{ fmt($subAccum, 1, $isGkRoot) }}</td>
                                                </tr>

                                                @foreach($sub->children as $subsub)
                                                    @php
                                                        $isSubSubParent = $subsub->children->isNotEmpty();
                                                        $subsubGw = $globalWeights[$subsub->id] ?? 0;
                                                        $nilaiSubSub = $assessmentsByCriteria->get($subsub->id)?->score ?? 0;
                                                        if (!$isSubSubParent) {
                                                            $subsubAccum = $subsub->max_score > 0 ? ($nilaiSubSub / $subsub->max_score) * 100 * $subsubGw : 0;
                                                        } else {
                                                            $subsubAccum = accumScore($subsub, $assessmentsByCriteria, true, $root->type) * $sub->weight * $root->weight;
                                                        }
                                                    @endphp
                                                    <tr class="bg-gray-200 hover:bg-gray-300">
                                                        <td class="px-3 md:px-6 py-2 pl-10 md:pl-16 text-gray-800 text-xs font-medium">
                                                            <div class="w-1.5 h-1.5 rounded-full bg-gray-500 inline-block mr-2"></div>
                                                            {{ $subsub->name }}
                                                        </td>
                                                        <td class="px-3 md:px-6 py-2 text-center text-gray-600 text-xs whitespace-nowrap">{{ number_format($subsub->weight * 100, 2) }}%</td>
                                                        <td class="px-3 md:px-6 py-2 text-center text-slate-700 font-semibold text-xs whitespace-nowrap">{{ fmtWeight($subsubGw, 2, $isGkRoot) }}</td>
                                                        <td class="px-3 md:px-6 py-2 text-center text-xs whitespace-nowrap {{ $isSubSubParent ? 'text-gray-400 italic' : 'text-gray-800' }}">
                                                            {{ $isSubSubParent ? '—' : fmt($nilaiSubSub, 2, $isGkRoot) }}
                                                        </td>
                                                        <td class="px-3 md:px-6 py-2 text-center text-gray-500 text-xs whitespace-nowrap">
                                                            {{ !$isSubSubParent ? $subsub->max_score : '—' }}
                                                        </td>
                                                        <td class="px-3 md:px-6 py-2 text-center text-slate-700 font-semibold text-xs whitespace-nowrap">{{ fmt($subsubAccum, 2, $isGkRoot) }}</td>
                                                    </tr>

                                                    @foreach($subsub->children as $l4)
                                                        @php
                                                            $l4Gw = $globalWeights[$l4->id] ?? 0;
                                                            $nilaiL4 = $assessmentsByCriteria->get($l4->id)?->score ?? 0;
                                                            $l4Accum = $l4->max_score > 0 ? ($nilaiL4 / $l4->max_score) * 100 * $l4Gw : 0;
                                                        @endphp
                                                        <tr class="bg-white hover:bg-gray-50">
                                                            <td class="px-3 md:px-6 py-2 pl-14 md:pl-24 text-gray-600 text-xs italic">
                                                                <div class="w-1 h-1 rounded-sm bg-gray-400 inline-block mr-2"></div>
                                                                {{ $l4->name }}
                                                            </td>
                                                            <td class="px-3 md:px-6 py-2 text-center text-gray-500 text-[11px] whitespace-nowrap">{{ number_format($l4->weight * 100, 4) }}%</td>
                                                            <td class="px-3 md:px-6 py-2 text-center text-slate-600 font-semibold text-[11px] whitespace-nowrap">{{ fmtWeight($l4Gw, 3, true) }}</td>
                                                            <td class="px-3 md:px-6 py-2 text-center text-gray-700 text-[11px] whitespace-nowrap">{{ number_format($nilaiL4, 2) }}</td>
                                                            <td class="px-3 md:px-6 py-2 text-center text-gray-500 text-[11px] whitespace-nowrap">{{ $l4->max_score }}</td>
                                                            <td class="px-3 md:px-6 py-2 text-center text-slate-600 text-[11px] whitespace-nowrap">{{ number_format($l4Accum, 4) }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Catatan Juri --}}
                            @if($notesJuri->isNotEmpty())
                                <div class="px-4 md:px-6 py-4 bg-slate-50 border-t border-slate-200">
                                    <p class="text-xs font-bold text-slate-700 uppercase tracking-wide mb-3">&#128172; Catatan Evaluasi dari {{ $namaJuri }}</p>
                                    <div class="space-y-2">
                                        @foreach($notesJuri as $noteAssessment)
                                            <div class="flex items-start space-x-3">
                                                <span class="text-xs font-semibold text-slate-700 shrink-0 w-36">{{ $noteAssessment->criteria?->name ?? 'Umum' }}:</span>
                                                <p class="text-xs text-slate-600 italic">"{{ $noteAssessment->notes }}"</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    {{-- Tab Panel: Matriks Rata-rata --}}
                    <div id="tab-matriks-rata" class="juri-tab-panel hidden">
                        <div class="px-4 md:px-6 py-3 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
                            <span class="text-sm font-bold text-slate-800">Matriks Hasil Keputusan AHP</span>
                            <span class="text-xs text-slate-500 hidden md:inline">&mdash; Nilai rata-rata dari semua juri, digunakan untuk perhitungan AHP final</span>
                        </div>
                        @include('transparency._matriks_table', [
                            'assessments' => $registration->assessments,
                            'globalWeights' => $globalWeights,
                        ])
                    </div>

                </div>{{-- end card per-juri --}}
            @endif
        @endif

        {{-- MATRIKS RATA-RATA untuk MAHASISWA --}}
        @if($role === 'mahasiswa')
            <div class="bg-white shadow-sm rounded-lg overflow-hidden border-t-4 border-slate-500">
                <div class="px-4 md:px-6 py-4 border-b border-gray-200">
                    <h3 class="text-base md:text-lg font-bold text-gray-800">Matriks Hasil Keputusan AHP</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Nilai akhir berdasarkan rata-rata penilaian dari juri yang telah menilai</p>
                </div>
                @include('transparency._matriks_table', [
                    'assessments' => $registration->assessments,
                    'globalWeights' => $globalWeights,
                ])
            </div>
        @endif

    </div>

    <script>
        function showJuriTab(tabId) {
            document.querySelectorAll('.juri-tab-panel').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.juri-tab-btn').forEach(el => {
                el.classList.remove('bg-slate-700', 'text-white', 'border-slate-700', 'shadow-sm');
                el.classList.add('bg-white', 'text-slate-600', 'border-slate-300');
            });
            document.getElementById(tabId)?.classList.remove('hidden');
            const btn = document.getElementById('btn-' + tabId);
            if (btn) {
                btn.classList.add('bg-slate-700', 'text-white', 'border-slate-700', 'shadow-sm');
                btn.classList.remove('bg-white', 'text-slate-600', 'border-slate-300');
            }
        }
    </script>
</x-app-layout>