<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Atur Pairwise Comparisons') }}</h2>
    </x-slot>

    <div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold">Masukkan Nilai Asli Kriteria</h1>
            <h2 class="text-lg text-gray-600 mt-2">Parent: <span class="font-semibold">{{ $parent->name }}</span></h2>
        </div>

        <form method="POST" action="{{ route('admin.pairwise-comparisons.update', $parent->id) }}" class="mb-6">
            @csrf
            @method('PUT')

            <h3 class="text-lg font-semibold mb-3">Input Nilai Asli Kriteria</h3>
            <p class="mb-4 text-gray-600">
                Masukkan nilai asli untuk setiap sub-kriteria. Sistem akan otomatis menghitung matriks perbandingan berpasangan (pairwise) dan bobot AHP-nya.
            </p>
            <div class="grid grid-cols-2 gap-4">
                @foreach($children as $child)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $child->name }}</label>
                        <input type="number" min="0.001" step="0.01" name="raw_values[{{ $child->id }}]"
                            value="{{ old('raw_values.'.$child->id, $rawWeights[$child->id] ?? '') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Simpan & Hitung AHP
                </button>
                <a href="{{ route('admin.criteria.index') }}" class="ml-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-block">
                    Kembali ke Criteria
                </a>
            </div>
        </form>

        @if(isset($preview))
            <div class="mt-8 bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h3 class="text-lg font-bold mb-3">Hasil Perhitungan AHP (preview)</h3>

                <p class="mb-2"><strong>λmax:</strong> {{ $preview['lambda_max'] }} • CI: {{ $preview['ci'] }} • RI: {{ $preview['ri'] }} • CR: {{ $preview['cr'] }} • Status: <span class="font-bold {{ $preview['is_consistent'] ? 'text-green-600' : 'text-red-600' }}">{{ $preview['is_consistent'] ? 'consistent' : 'inconsistent' }}</span></p>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left border border-gray-300 mb-4">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-2 py-2 border">Kriteria</th>
                                @foreach($children as $child)
                                    <th class="px-2 py-2 border text-center">{{ $child->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($children as $i => $criteria1)
                                <tr>
                                    <td class="px-2 py-1 border font-semibold">{{ $criteria1->name }}</td>
                                    @foreach($children as $j => $criteria2)
                                        <td class="px-2 py-1 border text-center">
                                            @if($i === $j)
                                                1
                                            @else
                                                @php
                                                $value = $preview['matrix'][$i][$j] ?? 0;
                                                $normalized = $preview['normalized'][$i][$j] ?? 0;
                                                $raw1 = $children[$i]->weight * 100;
                                                $raw2 = $children[$j]->weight * 100;
                                                $formula = $raw2 > 0 ? number_format($raw1 / $raw2, 6) : 0;
                                            @endphp
                                            {{ number_format($value, 4) }}
                                            <div class="text-xs text-gray-500">[{{ number_format($raw1, 2) }} / {{ number_format($raw2, 2) }} = {{ number_format($formula, 4) }}]</div>
                                            <div class="text-xs text-gray-500">N: {{ number_format($normalized, 4) }}</div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mb-2"><strong>Bobot lokal (priority vector):</strong></div>
                <ul class="list-disc pl-5 text-sm">
                    @foreach($preview['weights'] as $id => $weight)
                        <li>{{ $children->firstWhere('id', $id)->name ?? $id }}: {{ number_format($weight, 6) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update reciprocal values dynamically
    const inputs = document.querySelectorAll('input[type="number"]');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            const [criteria1, criteria2] = this.name.replace('comparison_', '').split('_');
            const reciprocalSpan = document.getElementById(`reciprocal_${criteria2}_${criteria1}`);
            if (reciprocalSpan && this.value) {
                const reciprocal = (1 / parseFloat(this.value)).toFixed(3);
                reciprocalSpan.textContent = reciprocal;
            }
        });
    });
});
</script>
</div>
</x-app-layout>