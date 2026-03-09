<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('transparency.index', ['stage' => $stage]) }}" class="text-gray-500 hover:text-cyan-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <span>Detail Transparansi Penilaian AHP (Tahap {{ ucfirst($stage) }})</span>
        </div>
    </x-slot>

    <div class="space-y-6">

        {{-- HEADER KARTU PESERTA --}}
        <div class="bg-white shadow-sm rounded-lg p-6 border-t-4 border-cyan-500">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $registration->student->user->name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ $registration->student->prodi }} &bull; Tahap: <span class="font-bold text-cyan-600 uppercase">{{ $stage }}</span></p>
                </div>
                <div class="text-right bg-blue-50 p-4 rounded-lg border border-blue-100">
                    <p class="text-xs text-blue-600 font-bold uppercase tracking-widest">Total Skor Akhir</p>
                    <p class="text-3xl font-extrabold text-blue-700">
                        {{ number_format($stage == 'fakultas' ? $registration->total_score_fakultas : $registration->total_score_univ, 2) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">Rata-rata dari semua juri</p>
                </div>
            </div>
        </div>

        {{-- BAGIAN KHUSUS JURI / ADMIN: TRANSPARANSI PER JURI --}}
        @if($role !== 'mahasiswa')
            @if(!$allJuriDone)
                <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-5 flex items-start space-x-4">
                    <svg class="w-6 h-6 text-yellow-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <div>
                        <p class="font-bold text-yellow-800">Penilaian Belum Selesai</p>
                        <p class="text-sm text-yellow-700 mt-1">Rincian nilai dan komentar per juri baru dapat dilihat setelah <strong>semua juri</strong> menyelesaikan penilaian untuk peserta ini. Hal ini menjaga independensi dan objektivitas proses penilaian.</p>
                        <p class="text-xs text-yellow-600 mt-2">Juri yang sudah menilai: <strong>{{ $assessmentsByLecturer->count() }}</strong> orang</p>
                    </div>
                </div>
            @else
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-cyan-700 to-blue-700 px-6 py-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-white">Rincian Nilai Per Juri</h3>
                            <p class="text-xs text-cyan-200 mt-0.5">Semua juri telah menyelesaikan penilaian &bull; {{ $assessmentsByLecturer->count() }} juri berpartisipasi</p>
                        </div>
                        <span class="bg-green-400 text-green-900 text-xs font-bold px-3 py-1 rounded-full">&#10003; Penilaian Selesai</span>
                    </div>

                    @foreach($assessmentsByLecturer as $lecturerId => $assessmentsByCriteria)
                        @php
                            $firstAssessment = $assessmentsByCriteria->first();
                            $namaJuri = $firstAssessment?->lecturer?->user?->name ?? 'Juri #' . $lecturerId;
                            $notesJuri = $assessmentsByCriteria->filter(fn($a) => !empty($a->notes));
                        @endphp

                        <div class="border-b border-gray-200 last:border-b-0">
                            {{-- Header Juri --}}
                            <div class="bg-gray-50 px-6 py-3 flex items-center space-x-3 border-b border-gray-200">
                                <div class="w-8 h-8 rounded-full bg-cyan-600 flex items-center justify-center text-white font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($namaJuri, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800 text-sm">{{ $namaJuri }}</p>
                                    <p class="text-xs text-gray-400">Juri Penilai</p>
                                </div>
                            </div>

                            {{-- Tabel Nilai Juri --}}
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm border-collapse">
                                    <thead>
                                        <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wide">
                                            <th class="px-6 py-2 text-left">Kriteria</th>
                                            <th class="px-6 py-2 text-center w-28">Bobot Global</th>
                                            <th class="px-6 py-2 text-center w-28">Nilai Mentah</th>
                                            <th class="px-6 py-2 text-center w-28">Skor Terbobot</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($criterias as $induk)
                                            <tr class="bg-gray-200">
                                                <td class="px-6 py-2 font-bold text-gray-700 uppercase text-xs">{{ $induk->name }}</td>
                                                <td class="px-6 py-2 text-center text-xs text-gray-500">{{ $induk->weight * 100 }}%</td>
                                                <td class="px-6 py-2 text-center text-xs text-gray-400 italic">Akumulasi</td>
                                                <td class="px-6 py-2 text-center text-xs text-gray-400">-</td>
                                            </tr>
                                            @foreach($induk->children as $sub)
                                                @php
                                                    $isParent = $sub->children->count() > 0;
                                                    $assessmentSub = $assessmentsByCriteria->get($sub->id);
                                                    $nilaiSub = $assessmentSub ? $assessmentSub->score : 0;
                                                    $skorSub = $nilaiSub * $sub->weight;
                                                @endphp
                                                <tr class="bg-gray-50 hover:bg-gray-100">
                                                    <td class="px-6 py-2 pl-10 font-semibold text-gray-700 text-xs">
                                                        <svg class="w-3 h-3 inline text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                        {{ $sub->name }}
                                                    </td>
                                                    <td class="px-6 py-2 text-center text-xs text-gray-500">{{ $sub->weight * 100 }}%</td>
                                                    <td class="px-6 py-2 text-center text-xs {{ $isParent ? 'text-gray-400 italic' : 'text-gray-700 font-medium' }}">
                                                        {{ $isParent ? 'Sub-kriteria' : number_format($nilaiSub, 2) }}
                                                    </td>
                                                    <td class="px-6 py-2 text-center text-xs text-blue-600 font-semibold">
                                                        {{ $isParent ? '-' : number_format($skorSub, 4) }}
                                                    </td>
                                                </tr>
                                                @foreach($sub->children as $subsub)
                                                    @php
                                                        $isParent2 = $subsub->children->count() > 0;
                                                        $assessmentSubSub = $assessmentsByCriteria->get($subsub->id);
                                                        $nilaiSubSub = $assessmentSubSub ? $assessmentSubSub->score : 0;
                                                        $skorSubSub = $nilaiSubSub * $subsub->weight;
                                                    @endphp
                                                    <tr class="bg-white hover:bg-gray-50">
                                                        <td class="px-6 py-2 pl-16 text-gray-600 text-xs">
                                                            <div class="w-1.5 h-1.5 rounded-full bg-gray-300 inline-block mr-2"></div>
                                                            {{ $subsub->name }}
                                                        </td>
                                                        <td class="px-6 py-2 text-center text-xs text-gray-500">{{ $subsub->weight * 100 }}%</td>
                                                        <td class="px-6 py-2 text-center text-xs {{ $isParent2 ? 'text-gray-400 italic' : 'text-gray-700' }}">
                                                            {{ $isParent2 ? 'Sub-kriteria' : number_format($nilaiSubSub, 2) }}
                                                        </td>
                                                        <td class="px-6 py-2 text-center text-xs text-blue-500">
                                                            {{ $isParent2 ? '-' : number_format($skorSubSub, 4) }}
                                                        </td>
                                                    </tr>
                                                    @foreach($subsub->children as $subsubsub)
                                                        @php
                                                            $assessmentL4 = $assessmentsByCriteria->get($subsubsub->id);
                                                            $nilaiL4 = $assessmentL4 ? $assessmentL4->score : 0;
                                                            $skorL4 = $nilaiL4 * $subsubsub->weight;
                                                        @endphp
                                                        <tr class="bg-gray-50/50 hover:bg-gray-100">
                                                            <td class="px-6 py-2 pl-24 text-gray-500 text-xs italic">
                                                                <div class="w-1.5 h-1.5 rounded-sm bg-gray-400 inline-block mr-2"></div>
                                                                {{ $subsubsub->name }}
                                                            </td>
                                                            <td class="px-6 py-2 text-center text-xs text-gray-500">{{ $subsubsub->weight * 100 }}%</td>
                                                            <td class="px-6 py-2 text-center text-xs text-gray-600">{{ number_format($nilaiL4, 2) }}</td>
                                                            <td class="px-6 py-2 text-center text-xs text-blue-400">{{ number_format($skorL4, 4) }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Komentar/Catatan Juri --}}
                            @if($notesJuri->isNotEmpty())
                                <div class="px-6 py-4 bg-amber-50 border-t border-amber-100">
                                    <p class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-3">&#128172; Catatan Evaluasi dari {{ $namaJuri }}</p>
                                    <div class="space-y-2">
                                        @foreach($notesJuri as $noteAssessment)
                                            <div class="flex items-start space-x-2">
                                                <span class="text-xs font-semibold text-amber-600 shrink-0 mt-0.5 w-36">
                                                    {{ $noteAssessment->criteria?->name ?? 'Umum' }}:
                                                </span>
                                                <p class="text-xs text-gray-700 italic">"{{ $noteAssessment->notes }}"</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        {{-- TABEL RATA-RATA (semua role bisa lihat) --}}
        <div class="bg-white shadow-sm rounded-lg overflow-hidden border-t-4 border-blue-600">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-800">Matriks Hasil Keputusan AHP</h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    @if($role === 'mahasiswa')
                        Nilai akhir berdasarkan rata-rata penilaian seluruh juri
                    @else
                        Nilai rata-rata dari semua juri &mdash; digunakan untuk perhitungan AHP final
                    @endif
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-800 text-white text-left">
                            <th class="p-3 border border-gray-700 rounded-tl-lg">Hierarki Kriteria</th>
                            <th class="p-3 border border-gray-700 text-center w-24">Bobot Global</th>
                            <th class="p-3 border border-gray-700 text-center w-32">Nilai Rata-rata</th>
                            <th class="p-3 border border-gray-700 text-center w-32">Skor Terbobot</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-800">
                        @foreach($criterias as $induk)
                            <tr class="bg-gray-200 border-b border-gray-300">
                                <td class="p-3 font-bold text-base uppercase">{{ $induk->name }}</td>
                                <td class="p-3 text-center font-bold">{{ $induk->weight * 100 }}%</td>
                                <td class="p-3 text-center text-xs text-gray-500 italic">Terakumulasi</td>
                                <td class="p-3 text-center font-bold text-blue-700">-</td>
                            </tr>

                            @foreach($induk->children as $sub)
                                @php
                                    $isParent = $sub->children->count() > 0;
                                    $nilaiMentah = 0;
                                    if (!$isParent) {
                                        if ($induk->type == 'cu') {
                                            $achievements = $registration->achievements->where('category', $sub->name);
                                            $nilaiMentah = $achievements->count() * 10;
                                        } else {
                                            $assessment = $registration->assessments->where('criteria_id', $sub->id)->first();
                                            $nilaiMentah = $assessment ? $assessment->score : 0;
                                        }
                                    }
                                    $skorTerbobot = $nilaiMentah * $sub->weight;
                                @endphp
                                <tr class="bg-gray-50 border-b border-gray-200 hover:bg-gray-100">
                                    <td class="p-3 pl-10 font-semibold text-gray-700 flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        {{ $loop->iteration }}. {{ $sub->name }}
                                    </td>
                                    <td class="p-3 text-center text-gray-600">{{ $sub->weight * 100 }}%</td>
                                    <td class="p-3 text-center {{ $isParent ? 'text-xs text-gray-400 italic' : 'text-gray-700 font-medium' }}">
                                        {{ $isParent ? 'Menunggu Sub-kriteria' : ($nilaiMentah > 0 ? $nilaiMentah : '0') }}
                                    </td>
                                    <td class="p-3 text-center text-blue-600 font-bold">
                                        {{ $isParent ? '-' : ($skorTerbobot > 0 ? number_format($skorTerbobot, 2) : '0') }}
                                    </td>
                                </tr>

                                @foreach($sub->children as $subsub)
                                    @php
                                        $isParent3 = $subsub->children->count() > 0;
                                        $assessmentSubSub = $registration->assessments->where('criteria_id', $subsub->id)->first();
                                        $nilaiMentahSubSub = $assessmentSubSub ? $assessmentSubSub->score : 0;
                                        $skorTerbobotSubSub = $nilaiMentahSubSub * $subsub->weight;
                                    @endphp
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
                                        <td class="p-3 text-center {{ $isParent3 ? 'text-xs text-gray-400 italic' : 'text-gray-700 text-xs' }}">
                                            {{ $isParent3 ? 'Menunggu Sub-kriteria' : ($nilaiMentahSubSub > 0 ? $nilaiMentahSubSub : '0') }}
                                        </td>
                                        <td class="p-3 text-center text-blue-600 font-bold text-xs">
                                            {{ $isParent3 ? '-' : ($skorTerbobotSubSub > 0 ? number_format($skorTerbobotSubSub, 2) : '0') }}
                                        </td>
                                    </tr>

                                    @foreach($subsub->children as $subsubsub)
                                        @php
                                            $assessmentSubSubSub = $registration->assessments->where('criteria_id', $subsubsub->id)->first();
                                            $nilaiMentahSubSubSub = $assessmentSubSubSub ? $assessmentSubSubSub->score : 0;
                                            $skorTerbobotSubSubSub = $nilaiMentahSubSubSub * $subsubsub->weight;
                                        @endphp
                                        <tr class="bg-gray-50/50 border-b border-gray-100 hover:bg-gray-100">
                                            <td class="p-3 pl-28 text-gray-500 text-xs flex items-center italic">
                                                <div class="w-1.5 h-1.5 rounded-sm bg-gray-400 mr-2"></div>
                                                {{ $subsubsub->name }}
                                            </td>
                                            <td class="p-3 text-center text-gray-500 text-[11px]">{{ $subsubsub->weight * 100 }}%</td>
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

    </div>
</x-app-layout>