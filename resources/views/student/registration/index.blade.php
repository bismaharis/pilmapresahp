<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg md:text-xl text-gray-800 leading-tight">
            {{ __('Pendaftaran Pilmapres') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-7xl mx-auto px-3 md:px-6 lg:px-8">
            <x-auth-session-status class="mb-4" :status="session('success')" />
            <x-auth-session-status class="mb-4" :status="session('error')" />

            @php
                $fileGkButtonClass = $registration && $registration->file_gk
                    ? 'file:bg-green-600 hover:file:bg-green-700'
                    : 'file:bg-blue-600 hover:file:bg-blue-700';
                $fileTranskripButtonClass = $registration && $registration->file_transkrip
                    ? 'file:bg-green-600 hover:file:bg-green-700'
                    : 'file:bg-blue-600 hover:file:bg-blue-700';
                $filePosterGkButtonClass = $registration && $registration->file_poster_gk
                    ? 'file:bg-green-600 hover:file:bg-green-700'
                    : 'file:bg-blue-600 hover:file:bg-blue-700';
                $filePosterDiriButtonClass = $registration && $registration->file_poster_diri
                    ? 'file:bg-green-600 hover:file:bg-green-700'
                    : 'file:bg-blue-600 hover:file:bg-blue-700';
            @endphp

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 md:p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        
                        <h3 class="text-base md:text-lg font-medium text-gray-900 mb-4">
                            Biodata Peserta
                        </h3>
                        <div class="space-y-3 text-sm">
                            <div class="w-20 md:w-24 h-20 md:h-24 bg-white overflow-hidden mb-3 shadow-inner flex items-center justify-center flex-shrink-0">
                                @if(Auth::user()->photo)
                                    <img src="{{ asset('storage/' . Auth::user()->photo) }}" alt="Foto Profil" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-12 md:w-16 h-12 md:h-16 text-gray-500" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                @endif
                            </div>
                            <div>
                                <span class="text-gray-500 block text-xs md:text-sm"
                                    >Nama Lengkap</span
                                >
                                <span class="font-semibold"
                                    >{{ Auth::user()->name }}</span
                                >
                            </div>
                            <div>
                                <span class="text-gray-500 block text-xs md:text-sm">NIM</span>
                                <span class="font-semibold"
                                    >{{ $student->nim }}</span
                                >
                            </div>
                            <div>
                                <span class="text-gray-500 block text-xs md:text-sm"
                                    >Program Studi</span
                                >
                                <span class="font-semibold"
                                    >{{ $student->prodi }}</span
                                >
                            </div>
                            <div>
                                <span class="text-gray-500 block text-xs md:text-sm">IPK</span>
                                <span class="font-semibold"
                                    >{{ $student->ipk }}</span
                                >
                            </div>
                        </div>
                    </div>

                    @if($activePeriod || ($registration && $registration->stage === 'universitas'))
                    <div>
                        <h3 class="text-base md:text-lg font-medium text-gray-900 mb-2">
                            Berkas Persyaratan
                        </h3>

                        @php
                            $showRegistrationBanner = $registration && (
                                ($registration->stage === 'universitas' && $registration->submitted_universitas_at) ||
                                ($registration->stage !== 'universitas' && $registration->submitted_fakultas_at)
                            );
                        @endphp

                        {{-- Notifikasi sudah terdaftar --}}
                        @if($showRegistrationBanner)
                            @if($registration->stage === 'universitas')
                                <div class="mb-4 bg-green-50 border border-green-300 rounded-lg p-3 flex items-start gap-3">
                                    {{-- <span class="text-purple-600 text-lg mt-0.5">🎓</span> --}}
                                    <div>
                                        <p class="text-sm font-semibold text-green-800">Anda telah terdaftar — Tahap Universitas</p>
                                        <p class="text-xs text-green-700 mt-0.5">Lengkapi berkas tahap universitas di bawah, lalu pastikan <span class="font-bold">Capaian Unggulan</span> Anda sudah diisi.</p>
                                    </div>
                                </div>
                            @else
                                <div class="mb-4 bg-green-50 border border-green-300 rounded-lg p-3 flex items-start gap-3">
                                    <span class="text-green-600 text-lg mt-0.5">✅</span>
                                    <div>
                                        <p class="text-sm font-semibold text-green-800">Anda telah terdaftar — Tahap Fakultas</p>
                                        <p class="text-xs text-green-700 mt-0.5">Anda Telah Terdaftar Pada Tahap Fakultas, Jangan Lupa Mengisi <span class="font-bold">Capaian Unggulan (CU)</span> melalui tombol di bagian bawah halaman ini.</p>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <h4 class="text-sm md:text-base font-medium text-blue-900 mb-1">
                            Tahap Fakultas
                        </h4>

                        <form
                            action="{{ route('student.registration.update') }}"
                            method="POST"
                            enctype="multipart/form-data"
                        >
                            @csrf @method('PUT')

                            <div class="mb-4">
                                <label class="block text-xs md:text-sm font-medium text-blue-700">Naskah Gagasan Kreatif</label>
                                <div class="mt-1">
                                    <div class="@if($registration && $registration->file_gk)  rounded-lg pt-1 pb-1  @endif">
                                        <input
                                        type="file"
                                        name="file_gk"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-sm file:border-0 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white file:transition-colors {{ $fileGkButtonClass }}"
                                        />
                                    </div>
                                </div>
                                @error('file_gk')
                                <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 italic">Format PDF. Max 5MB.</p>
                                @if($registration && $registration->file_gk)
                                    <p class="text-xs font-semibold text-green-700 mb-1 flex items-center gap-1">
                                        
                                        <a href="{{ Storage::url($registration->file_gk) }}" target="_blank" class="underline">Lihat File</a>
                                        <span class="text-gray-500 font-normal">(unggah ulang untuk mengganti)</span>
                                    </p>
                                @endif
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-blue-700">Transkrip Nilai</label>
                                <div class="mt-1">
                                    <div class="@if($registration && $registration->file_transkrip)  rounded-lg pt-1 pb-1 @endif">
                                        <input
                                        type="file"
                                        name="file_transkrip"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-sm file:border-0 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white file:transition-colors {{ $fileTranskripButtonClass }}"
                                        />
                                    </div>
                                </div>
                                @error('file_transkrip')
                                <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1 italic">Format PDF. Max 5MB.</p>
                                @if($registration && $registration->file_transkrip)
                                    <p class="text-xs font-semibold text-green-700 mb-1 flex items-center gap-1">
                                        
                                        <a href="{{ Storage::url($registration->file_transkrip) }}" target="_blank" class="underline">Lihat File</a>
                                        <span class="text-gray-500 font-normal">(unggah ulang untuk mengganti)</span>
                                    </p>
                                @endif
                            </div>

                            @if($registration->stage == 'universitas')
                                <div class="border-t border-gray-200 my-6 pt-4 bg-blue-50 p-4 rounded-lg">
                                    <div class="flex items-center mb-4">
                                        <div class="bg-blue-100 text-blue-800 p-2 rounded-full mr-3">
                                            🎉
                                        </div>
                                        <div>
                                            <h4 class="text-md font-bold text-blue-800">Selamat! Anda Lolos ke Tahap Universitas</h4>
                                            <p class="text-xs text-gray-600">Silakan lengkapi berkas tambahan di bawah ini.</p>
                                            <p class="text-xs text-blue-700 mt-1">Jangan lupa juga perbarui <span class="font-bold">Capaian Unggulan (CU)</span> Anda melalui tombol di bawah.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 border-t border-gray-200 my-6 pt-4 bg-slate-50 p-4 rounded-lg">
                                    
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-blue-700">Poster Gagasan Kreatif</label>
                                        <div class="mt-1">
                                            <div class="@if($registration->file_poster_gk) rounded-lg pt-1 pb-1 @endif">
                                                <input type="file" name="file_poster_gk"
                                                class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-sm file:border-0 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white file:transition-colors {{ $filePosterGkButtonClass }}">
                                            </div>
                                        </div>
                                        @error('file_poster_gk')
                                        <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>
                                        @enderror
                                        <p class="text-xs text-gray-500 mt-1 italic">Format PDF, JPG, PNG. Max 5MB.</p>
                                        @if($registration->file_poster_gk)
                                            <p class="text-xs font-semibold text-green-700 mb-1 flex items-center gap-1">
                                                
                                                <a href="{{ Storage::url($registration->file_poster_gk) }}" target="_blank" class="underline">Lihat File</a>
                                                <span class="text-gray-500 font-normal">(unggah ulang untuk mengganti)</span>
                                            </p>
                                        @endif
                                    </div>

                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700">Poster Diri</label>
                                        <div class="mt-1">
                                            <div class="@if($registration->file_poster_diri)  rounded-lg pt-1 pb-1 @endif">
                                                <input type="file" name="file_poster_diri"
                                                class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-sm file:border-0 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white file:transition-colors {{ $filePosterDiriButtonClass }}">
                                            </div>
                                        </div>
                                        @error('file_poster_diri')
                                        <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>
                                        @enderror
                                        <p class="text-xs text-gray-500 mt-1 italic">Format PDF, JPG, PNG. Max 5MB.</p>
                                        @if($registration->file_poster_diri)
                                            <p class="text-xs font-semibold text-green-700 mb-1 flex items-center gap-1">
                                                
                                                <a href="{{ Storage::url($registration->file_poster_diri) }}" target="_blank" class="underline">Lihat File</a>
                                                <span class="text-gray-500 font-normal">(unggah ulang untuk mengganti)</span>
                                            </p>
                                        @endif
                                    </div>

                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-blue-700">Link Video Bahasa Inggris</label>
                                        <input type="url" name="video_link" value="{{ old('video_link', $registration->video_link) }}" class="mt-1 p-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="https://youtube.com/...">
                                    </div>
                                </div>
                            @else
                                <div class="border-t border-gray-200 my-6 pt-4">
                                    <div class="bg-gray-50 border-l-4 border-gray-400 p-4">
                                        <div class="flex">
                                            <div class="ml-3">
                                                <p class="text-sm text-gray-700">
                                                    Persyaratan tahap Universitas hanya akan terbuka jika Anda dinyatakan 
                                                    <span class="font-bold">Lolos Seleksi Fakultas</span> oleh Panitia.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="flex justify-between gap-4">
                                <button
                                    type="submit"
                                    name="action"
                                    value="save"
                                    class="text-white px-4 py-2 rounded-md {{ $showRegistrationBanner ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-800 hover:bg-gray-700' }}"
                                >
                                    Simpan & Daftar
                                </button>
                                <a
                                    href="{{ route('student.achievements.index') }}"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    Lanjut: Isi Capaian Unggulan (CU) &rarr;
                                </a>
                            </div>
                            
                        </form>
                    </div>
                    @else
                    <div class="bg-red-50 border border-red-300 rounded p-4 md:p-6">
                        <div class="flex items-start mb-4">
                            <svg class="w-6 h-6 text-red-600 mr-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <h4 class="text-lg font-bold text-red-800 mb-2">Periode Pendaftaran Belum Dibuka</h4>
                                <p class="text-red-700 mb-3">
                                    Maaf, saat ini tidak ada periode pendaftaran yang aktif untuk Fakultas Anda. 
                                    Periode pendaftaran belum dimulai atau sudah berakhir.
                                </p>
                                <p class="text-red-700 font-semibold">
                                    Silakan hubungi admin Fakultas Anda untuk informasi lebih lanjut.
                                </p>
                                
                                @if($activePeriod)
                                <div class="mt-4 bg-red-100 p-3 rounded text-sm text-red-800">
                                    <strong>Info Jadwal Pendaftaran:</strong><br>
                                    Mulai: {{ $activePeriod->start_date->format('d M Y') }}<br>
                                    Berakhir: {{ $activePeriod->end_date->format('d M Y') }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- <div class="mt-8 border-t pt-4 flex justify-end">
                    <a
                        href="{{ route('student.achievements.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Lanjut: Isi Capaian Unggulan (CU) &rarr;
                    </a>
                </div> --}}
            </div>
        </div>
    </div>
</x-app-layout>
