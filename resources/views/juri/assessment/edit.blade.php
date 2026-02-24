<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Penilaian: {{ $registration->student->user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="md:col-span-1 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Informasi Peserta</h3>
                        <div class="space-y-2 text-sm">
                            <p><span class="text-gray-500 block">NIM</span> <span class="font-semibold">{{ $registration->student->nim }}</span></p>
                            <p><span class="text-gray-500 block">Prodi</span> <span class="font-semibold">{{ $registration->student->prodi }}</span></p>
                            <p><span class="text-gray-500 block">Tahap</span> <span class="uppercase font-bold text-blue-600">{{ $registration->stage }}</span></p>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Berkas Pendukung</h3>
                        <div class="space-y-4 text-sm">
                            <div>
                                <span class="text-gray-700 font-semibold block">Naskah Gagasan Kreatif</span>
                                @if($registration->file_gk)
                                    <a href="{{ Storage::url($registration->file_gk) }}" target="_blank" class="inline-flex mt-1 items-center px-3 py-1 bg-red-100 text-red-700 rounded-full hover:bg-red-200 transition">
                                        📄 Buka PDF
                                    </a>
                                @else
                                    <span class="text-gray-400 italic">Belum tersedia</span>
                                @endif
                            </div>

                            @if($registration->file_poster_gk)
                            <div>
                                <span class="text-gray-700 font-semibold block">Poster GK</span>
                                <a href="{{ Storage::url($registration->file_poster_gk) }}" target="_blank" class="inline-flex mt-1 items-center px-3 py-1 bg-purple-100 text-purple-700 rounded-full hover:bg-purple-200 transition">
                                    🖼️ Lihat Poster
                                </a>
                            </div>
                            @endif

                            @if($registration->video_link)
                            <div>
                                <span class="text-gray-700 font-semibold block">Video Bahasa Inggris</span>
                                <a href="{{ $registration->video_link }}" target="_blank" class="inline-flex mt-1 items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 transition">
                                    ▶️ Tonton Video
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Capaian Unggulan (CU)</h3>
                        <div class="space-y-4 text-sm max-h-[500px] overflow-y-auto pr-2">
                            @forelse($registration->achievements as $ach)
                                <div class="p-3 border rounded-md bg-gray-50 hover:bg-blue-50 transition border-l-4 border-l-blue-500">
                                    <p class="font-bold text-gray-800">{{ $ach->name }}</p>
                                    <p class="text-xs text-blue-600 font-semibold mb-2">{{ $ach->category }} | Tingkat {{ $ach->level }}</p>
                                    <p class="text-gray-600"><span class="font-semibold text-gray-500">Pencapaian:</span> {{ $ach->capaian }}</p>
                                    <p class="text-gray-600"><span class="font-semibold text-gray-500">Tahun:</span> {{ $ach->year }}</p>
                                    @if($ach->file_proof)
                                        <a href="{{ Storage::url($ach->file_proof) }}" target="_blank" class="inline-block mt-2 text-blue-600 hover:text-blue-800 hover:underline font-semibold text-xs">
                                            Lihat Sertifikat/Bukti &rarr;
                                        </a>
                                    @endif
                                </div>
                            @empty
                                <p class="text-gray-400 italic text-center py-4">Belum ada data Capaian Unggulan.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <form action="{{ route('juri.assessments.update', $registration->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div class="flex justify-between items-center mb-6 border-b pb-2">
                                <h3 class="text-xl font-bold text-gray-900">Form Input Nilai</h3>
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 font-semibold shadow-sm transition-colors">
                                    Simpan Nilai
                                </button>
                            </div>
                            
                            <p class="text-sm text-gray-600 mb-6 bg-yellow-50 p-3 rounded border border-yellow-200">
                                💡 <strong>Panduan:</strong> Masukkan nilai mentah pada kotak yang tersedia. Jangan melebihi batas <strong>Max Skor</strong>. Kategori dengan warna latar adalah judul kelompok dan tidak perlu diisi.
                            </p>

                            @foreach($criteriaTree as $root)
                                <div class="mb-8">
                                    <h4 class="text-lg font-bold text-white uppercase bg-blue-800 px-4 py-3 rounded-t-md shadow-sm">
                                        {{ $root->name }}
                                    </h4>
                                    
                                    <div class="border-x border-b border-gray-300 rounded-b-md overflow-hidden shadow-sm">
                                        
                                        @if(strtoupper($root->name) == 'CAPAIAN UNGGULAN' || $root->type == 'cu')
                                            <div class="p-4 bg-white">
                                                <p class="text-sm text-gray-600 mb-4 bg-blue-50 p-3 rounded border border-blue-100">
                                                    💡 <strong>Evaluasi Berkas CU:</strong> Berikan nilai untuk setiap sertifikat/bukti yang dilampirkan. Sistem akan <strong>mengakumulasikan total nilai</strong> secara otomatis pada tiap kategori.
                                                </p>
                                                
                                                @foreach($root->children as $kategori)
                                                    @php
                                                        $achievements = $registration->achievements->where('category', $kategori->name);
                                                    @endphp
                                                    
                                                    <div class="mb-6 border border-gray-300 rounded-lg overflow-hidden shadow-sm">
                                                        <div class="bg-gray-200 px-4 py-3 font-bold flex justify-between items-center border-b border-gray-300">
                                                            <span class="text-gray-800">{{ $kategori->name }}</span>
                                                            <span class="text-sm bg-yellow-200 text-yellow-800 px-3 py-1 rounded-full shadow-inner border border-yellow-300">
                                                                Total Skor Kategori: <span id="total-{{ $kategori->id }}" class="text-lg font-black">0.00</span>
                                                            </span>
                                                        </div>

                                                        @if($achievements->isEmpty())
                                                            <div class="p-4 text-center text-sm text-gray-500 italic bg-white">Tidak ada sertifikat di kategori ini.</div>
                                                        @else
                                                            <table class="min-w-full text-sm text-left text-gray-800 border-t border-gray-200">
                                                                <thead class="bg-gray-50 border-b border-gray-300">
                                                                    <tr>
                                                                        <th class="px-4 py-3 w-3/5 text-gray-600">Detail Capaian & Prestasi</th>
                                                                        <th class="px-4 py-3 text-center text-gray-600">Bukti</th>
                                                                        <th class="px-4 py-3 text-center w-32 text-gray-600">Nilai Juri</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="divide-y divide-gray-200 bg-white">
                                                                    @foreach($achievements as $ach)
                                                                        <tr class="hover:bg-blue-50 transition">
                                                                            <td class="pl-4 py-4">
                                                                                <strong class="text-gray-900 text-base block mb-2">{{ $ach->name }}</strong>
                                                                                <div class="text-xs text-gray-600 grid grid-cols-2 gap-y-2 gap-x-4 bg-gray-50 p-3 rounded border border-gray-100">
                                                                                    <div><span class="font-semibold text-gray-500 block mb-0.5">Capaian:</span> {{ $ach->capaian ?? '-' }}</div>
                                                                                    <div><span class="font-semibold text-gray-500 block mb-0.5">Tingkat:</span> <span class="text-blue-600 font-bold">{{ $ach->level ?? '-' }}</span></div>
                                                                                    <div><span class="font-semibold text-gray-500 block mb-0.5">Tahun/Tipe:</span> {{ $ach->year ?? '-' }} ({{ $ach->type ?? '-' }})</div>
                                                                                    <div><span class="font-semibold text-gray-500 block mb-0.5">Peserta:</span> {{ $ach->jumlah_peserta ?? '-' }}</div>
                                                                                    <div><span class="font-semibold text-gray-500 block mb-0.5">Penghargaan:</span> {{ $ach->jumlah_penghargaan ?? '-' }}</div>
                                                                                    <div class="col-span-2"><span class="font-semibold text-gray-500 block mb-0.5">Penyelenggara:</span> {{ $ach->organizer ?? '-' }}</div>
                                                                                </div>
                                                                            </td>
                                                                            <td class="py-4 text-center align-middle">
                                                                                <a href="{{ Storage::url($ach->file_proof) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 text-xs font-bold transition border border-blue-200 shadow-sm">
                                                                                    Lihat Berkas
                                                                                </a>
                                                                            </td>
                                                                            <td class="px-4 py-4 align-middle">
                                                                                <div class="flex flex-col items-center">
                                                                                    <span class="text-[10px] font-bold text-red-500 mb-1 tracking-wider uppercase">Max: 50</span>
                                                                                    
                                                                                    <input type="number" name="achievement_scores[{{ $ach->id }}]" 
                                                                                           value="{{ old('achievement_scores.'.$ach->id, $ach->score ?? '0') }}" 
                                                                                           min="0" max="50" step="0.01" 
                                                                                           class="w-full text-center px-2 py-2 border-2 border-solid border-gray-300 rounded-md text-gray-900 font-bold text-lg focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none transition-all shadow-inner cu-input-{{ $kategori->id }}"
                                                                                           oninput="calculateTotalCU({{ $kategori->id }})">
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>

                                        @else
                                            <table class="min-w-full text-sm text-left text-gray-800">
                                                <thead class="bg-gray-200 border-b-2 border-gray-300">
                                                    <tr>
                                                        <th class="px-4 py-3 w-12 text-center">No</th>
                                                        <th class="px-4 py-3">Kriteria Penilaian</th>
                                                        <th class="px-4 py-3 text-center w-24">Max Skor</th>
                                                        <th class="px-4 py-3 text-center w-32">Nilai Juri</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-300">
                                                    
                                                    @php $noL1 = 1; @endphp
                                                    @foreach($root->children as $level1)
                                                        
                                                        @if($level1->children->isEmpty())
                                                            <tr class="hover:bg-blue-50">
                                                                <td class="px-4 py-3 text-center font-medium">{{ $noL1 }}</td>
                                                                <td class="px-4 py-3">{{ $level1->name }}</td>
                                                                <td class="px-4 py-3 text-center font-bold text-gray-500">{{ $level1->max_score }}</td>
                                                                <td class="px-4 py-2 text-center">
                                                                    <input type="number" name="scores[{{ $level1->id }}]" value="{{ old('scores.'.$level1->id, isset($existingAssessments[$level1->id]) ? $existingAssessments[$level1->id]->score : '') }}" min="0" max="{{ $level1->max_score }}" step="0.01" class="w-full text-center px-2 py-1.5 border-2 border-solid border-gray-300 bg-white rounded-md text-gray-900 font-bold focus:border-gray-600 focus:ring-2 focus:ring-blue-200 focus:bg-white outline-none transition-all" required>
                                                                </td>
                                                            </tr>
                                                        @else
                                                            <tr class="bg-gray-500 border-y-2 border-gray-100">
                                                                <td class="px-4 py-2 text-center font-bold text-white">{{ $noL1 }}</td>
                                                                <td class="px-4 py-2 font-bold text-white" colspan="3">{{ $level1->name }}</td>
                                                            </tr>
                                                            
                                                            @php $noL2 = 1; @endphp
                                                            @foreach($level1->children as $level2)
                                                                
                                                                @if($level2->children->isEmpty())
                                                                    <tr class="hover:bg-blue-50">
                                                                        <td class="px-4 py-3 text-center">{{ $noL1 }}.{{ $noL2 }}</td>
                                                                        <td class="px-4 py-3 pl-6">{{ $level2->name }}</td>
                                                                        <td class="px-4 py-3 text-center font-bold text-gray-500">{{ $level2->max_score }}</td>
                                                                        <td class="px-4 py-2 text-center">
                                                                            <input type="number" name="scores[{{ $level2->id }}]" value="{{ old('scores.'.$level2->id, isset($existingAssessments[$level2->id]) ? $existingAssessments[$level2->id]->score : '') }}" min="0" max="{{ $level2->max_score }}" step="0.01" class="w-full text-center px-2 py-1.5 border-2 border-solid border-gray-300 bg-white rounded-md text-gray-900 font-bold focus:border-blue-600 focus:ring-2 focus:ring-blue-200 focus:bg-white outline-none transition-all" required>
                                                                        </td>
                                                                    </tr>
                                                                @else
                                                                    <tr class="bg-gray-100">
                                                                        <td class="px-4 py-2 text-center font-semibold">{{ $noL1 }}.{{ $noL2 }}</td>
                                                                        <td class="px-4 py-2 font-semibold" colspan="3">{{ $level2->name }}</td>
                                                                    </tr>

                                                                    @php $noL3 = 1; @endphp
                                                                    @foreach($level2->children as $level3)
                                                                        <tr class="hover:bg-yellow-50">
                                                                            <td class="px-4 py-2 text-center text-gray-600">{{ $noL1 }}.{{ $noL2 }}.{{ $noL3 }}</td>
                                                                            <td class="px-4 py-2 pl-10 text-gray-700">{{ $level3->name }}</td>
                                                                            <td class="px-4 py-2 text-center font-bold text-gray-500">{{ $level3->max_score }}</td>
                                                                            <td class="px-4 py-2 text-center">
                                                                                <input type="number" name="scores[{{ $level3->id }}]" value="{{ old('scores.'.$level3->id, isset($existingAssessments[$level3->id]) ? $existingAssessments[$level3->id]->score : '') }}" min="0" max="{{ $level3->max_score }}" step="0.01" class="w-full text-center px-2 py-1.5 border-2 border-gray-300 rounded-md bg-white text-gray-900 font-bold focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none transition-all shadow-inner" required>
                                                                            </td>
                                                                        </tr>
                                                                        @php $noL3++; @endphp
                                                                    @endforeach
                                                                @endif
                                                                @php $noL2++; @endphp
                                                            @endforeach
                                                        @endif
                                                        @php $noL1++; @endphp
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                        
                                        <div class="p-4 bg-gray-50 border-t-2 border-gray-200">
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Komentar/Catatan Evaluasi untuk {{ $root->name }} <span class="text-gray-400 font-normal">(Opsional)</span></label>
                                            <textarea name="notes[{{ $root->id }}]" rows="2" class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm shadow-sm" placeholder="Berikan catatan evaluasi singkat terkait komponen ini...">{{ old('notes.'.$root->id, isset($existingAssessments[$root->id]) ? $existingAssessments[$root->id]->notes : '') }}</textarea>
                                        </div>

                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        function calculateTotalCU(kategoriId) {
            let inputs = document.querySelectorAll('.cu-input-' + kategoriId);
            let total = 0;
            inputs.forEach(input => {
                let val = parseFloat(input.value);
                if(!isNaN(val)) {
                    total += val;
                }
            });
            document.getElementById('total-' + kategoriId).innerText = total.toFixed(2);
        }

        // Jalankan saat halaman dimuat agar total nilai yang sudah tersimpan langsung terkalkulasi
        window.onload = function() {
            @foreach($criteriaTree as $root)
                @if(strtoupper($root->name) == 'CAPAIAN UNGGULAN' || $root->type == 'cu')
                    @foreach($root->children as $kategori)
                        calculateTotalCU({{ $kategori->id }});
                    @endforeach
                @endif
            @endforeach
        };
    </script>
</x-app-layout>