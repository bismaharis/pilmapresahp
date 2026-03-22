{{--
    Partial: Matriks AHP rata-rata
    Variabel yang dibutuhkan:
    - $assessments  : collection assessments (rata-rata antar juri)
    - $criterias    : collection root criteria (dari parent view)
    - $globalWeights: array [criteria_id => global_weight]
--}}
<div class="overflow-x-auto">
    <table class="w-full text-xs border-collapse">
        <thead>
            <tr class="bg-gray-800 text-white text-[11px] uppercase tracking-wide">
                <th class="p-3 text-left border border-gray-700">Hierarki Kriteria</th>
                <th class="p-3 text-center border border-gray-700 whitespace-nowrap">Bobot Lokal</th>
                <th class="p-3 text-center border border-gray-700 whitespace-nowrap">Bobot Global</th>
                <th class="p-3 text-center border border-gray-700 whitespace-nowrap">Nilai Rata-rata</th>
                <th class="p-3 text-center border border-gray-700 whitespace-nowrap">Nilai Maks</th>
                <th class="p-3 text-center border border-gray-700 whitespace-nowrap">Skor Terbobot</th>
            </tr>
        </thead>
        <tbody class="text-gray-800">
            @foreach($criterias as $root)
                @php
                    $isGkRoot = ($root->type === 'gk');
                    $isCuRoot = ($root->type === 'cu');
                    if ($isCuRoot) {
                        $cuRawAvg = 0;
                        foreach ($root->children as $sub) {
                            $cuRawAvg += $assessments->where('criteria_id', $sub->id)->avg('score') ?? 0;
                        }
                        $rootAccumAvg = ($cuRawAvg / 500) * 100 * $root->weight;
                    } else {
                        $rootAccumAvg = 0;
                        foreach ($root->children as $sub) {
                            $rootAccumAvg += accumScore($sub, $assessments, false, $root->type);
                        }
                        $rootAccumAvg *= $root->weight;
                    }
                @endphp
                {{-- Baris Root --}}
                <tr class="bg-gray-900">
                    <td class="p-3 font-black text-white uppercase tracking-wider text-xs">{{ $root->name }}</td>
                    <td class="p-3 text-center text-cyan-300 font-bold text-xs">{{ $root->weight * 100 }}%</td>
                    <td class="p-3 text-center text-cyan-300 font-bold text-xs">{{ $root->weight * 100 }}%</td>
                    <td class="p-3 text-center text-gray-400 text-xs italic">
                        {{ $isCuRoot ? number_format($cuRawAvg, 2) . ' / 500' : '—' }}
                    </td>
                    <td class="p-3 text-center text-gray-400 text-xs italic">—</td>
                    <td class="p-3 text-center text-yellow-300 font-black text-sm">{{ number_format($rootAccumAvg, 2) }}</td>
                </tr>

                @foreach($root->children as $sub)
                    @php
                        $isSubParent = $sub->children->isNotEmpty();
                        $subGw = $globalWeights[$sub->id] ?? 0;
                        $nilaiSub = $assessments->where('criteria_id', $sub->id)->avg('score') ?? 0;
                        if ($isCuRoot) {
                            $subAccumAvg = ($nilaiSub / 500) * 100 * $root->weight;
                        } elseif (!$isSubParent) {
                            $subAccumAvg = $sub->max_score > 0 ? ($nilaiSub / $sub->max_score) * 100 * $subGw : 0;
                        } else {
                            $subAccumAvg = accumScore($sub, $assessments, false, $root->type) * $root->weight;
                        }
                    @endphp
                    <tr class="bg-gray-700 hover:bg-gray-600">
                        <td class="p-3 pl-8 md:pl-10 font-bold text-gray-100 text-xs">
                            <svg class="w-3 h-3 inline text-cyan-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            {{ $loop->iteration }}. {{ $sub->name }}
                        </td>
                        <td class="p-3 text-center text-gray-300 text-xs whitespace-nowrap">{{ number_format($sub->weight * 100, 2) }}%</td>
                        <td class="p-3 text-center text-cyan-300 font-semibold text-xs whitespace-nowrap">{{ fmtWeight($subGw, 1, $isGkRoot) }}</td>
                        <td class="p-3 text-center text-xs whitespace-nowrap {{ $isSubParent && !$isCuRoot ? 'text-gray-500 italic' : 'text-white font-semibold' }}">
                            {{ ($isSubParent && !$isCuRoot) ? '—' : fmt($nilaiSub, 1, $isGkRoot) }}
                        </td>
                        <td class="p-3 text-center text-gray-400 text-xs whitespace-nowrap">
                            {{ (!$isSubParent && !$isCuRoot) ? $sub->max_score : '—' }}
                        </td>
                        <td class="p-3 text-center text-blue-300 font-bold text-xs whitespace-nowrap">{{ fmt($subAccumAvg, 1, $isGkRoot) }}</td>
                    </tr>

                    @foreach($sub->children as $subsub)
                        @php
                            $isSubSubParent = $subsub->children->isNotEmpty();
                            $subsubGw = $globalWeights[$subsub->id] ?? 0;
                            $nilaiSubSub = $assessments->where('criteria_id', $subsub->id)->avg('score') ?? 0;
                            if (!$isSubSubParent) {
                                $subsubAccumAvg = $subsub->max_score > 0 ? ($nilaiSubSub / $subsub->max_score) * 100 * $subsubGw : 0;
                            } else {
                                $subsubAccumAvg = accumScore($subsub, $assessments, false, $root->type) * $sub->weight * $root->weight;
                            }
                        @endphp
                        <tr class="bg-gray-200 hover:bg-gray-300">
                            <td class="p-3 pl-12 md:pl-16 text-gray-800 text-xs font-medium">
                                <div class="w-1.5 h-1.5 rounded-full bg-gray-500 inline-block mr-2"></div>
                                {{ $subsub->name }}
                            </td>
                            <td class="p-3 text-center text-gray-600 text-xs whitespace-nowrap">{{ number_format($subsub->weight * 100, 2) }}%</td>
                            <td class="p-3 text-center text-cyan-700 font-semibold text-xs whitespace-nowrap">{{ fmtWeight($subsubGw, 2, $isGkRoot) }}</td>
                            <td class="p-3 text-center text-xs whitespace-nowrap {{ $isSubSubParent ? 'text-gray-400 italic' : 'text-gray-800' }}">
                                {{ $isSubSubParent ? '—' : fmt($nilaiSubSub, 2, $isGkRoot) }}
                            </td>
                            <td class="p-3 text-center text-gray-500 text-xs whitespace-nowrap">
                                {{ !$isSubSubParent ? $subsub->max_score : '—' }}
                            </td>
                            <td class="p-3 text-center text-blue-600 font-semibold text-xs whitespace-nowrap">{{ fmt($subsubAccumAvg, 2, $isGkRoot) }}</td>
                        </tr>

                        @foreach($subsub->children as $l4)
                            @php
                                $l4Gw = $globalWeights[$l4->id] ?? 0;
                                $nilaiL4 = $assessments->where('criteria_id', $l4->id)->avg('score') ?? 0;
                                $l4AccumAvg = $l4->max_score > 0 ? ($nilaiL4 / $l4->max_score) * 100 * $l4Gw : 0;
                            @endphp
                            <tr class="bg-white hover:bg-gray-50">
                                <td class="p-3 pl-20 md:pl-24 text-gray-600 text-xs italic">
                                    <div class="w-1 h-1 rounded-sm bg-gray-400 inline-block mr-2"></div>
                                    {{ $l4->name }}
                                </td>
                                <td class="p-3 text-center text-gray-500 text-[11px] whitespace-nowrap">{{ number_format($l4->weight * 100, 4) }}%</td>
                                <td class="p-3 text-center text-cyan-600 font-semibold text-[11px] whitespace-nowrap">{{ fmtWeight($l4Gw, 3, true) }}</td>
                                <td class="p-3 text-center text-gray-700 text-[11px] whitespace-nowrap">{{ number_format($nilaiL4, 2) }}</td>
                                <td class="p-3 text-center text-gray-500 text-[11px] whitespace-nowrap">{{ $l4->max_score }}</td>
                                <td class="p-3 text-center text-blue-500 text-[11px] whitespace-nowrap">{{ number_format($l4AccumAvg, 4) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>