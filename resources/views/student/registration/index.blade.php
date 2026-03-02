<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pendaftaran Pilmapres') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-auth-session-status class="mb-4" :status="session('success')" />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Biodata Peserta
                        </h3>
                        <div class="space-y-3 text-sm">
                            <div class="w-24 h-24 bg-white overflow-hidden mb-3 shadow-inner flex items-center justify-center">
                                @if(Auth::user()->photo)
                                    <img src="{{ asset('storage/' . Auth::user()->photo) }}" alt="Foto Profil" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-16 h-16 text-gray-500" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                @endif
                            </div>
                            <div>
                                <span class="text-gray-500 block"
                                    >Nama Lengkap</span
                                >
                                <span class="font-semibold"
                                    >{{ Auth::user()->name }}</span
                                >
                            </div>
                            <div>
                                <span class="text-gray-500 block">NIM</span>
                                <span class="font-semibold"
                                    >{{ $student->nim }}</span
                                >
                            </div>
                            <div>
                                <span class="text-gray-500 block"
                                    >Program Studi</span
                                >
                                <span class="font-semibold"
                                    >{{ $student->prodi }}</span
                                >
                            </div>
                            <div>
                                <span class="text-gray-500 block">IPK</span>
                                <span class="font-semibold"
                                    >{{ $student->ipk }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                            Berkas Persyaratan
                        </h3>
                        <h4 class="text-md font-medium text-gray-900 mb-1">
                            Tahap Fakultas
                        </h4>

                        <form
                            action="{{ route('student.registration.update') }}"
                            method="POST"
                            enctype="multipart/form-data"
                        >
                            @csrf @method('PUT')

                            <div class="mb-4">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Naskah Gagasan Kreatif</label
                                >
                                <div class="mt-1 flex items-center">
                                    <input
                                        type="file"
                                        name="file_gk"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                    />
                                </div>
                                @error('file_gk') 
                                    <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> 
                                @enderror
                                
                                <p class="text-xs text-gray-500 mt-1 italic">Format PDF. Max 10MB.</p>
                                @if($registration->file_gk)
                                <p class="mt-1 text-xs text-green-600">
                                    ✓ Terunggah:
                                    <a
                                        href="{{ Storage::url($registration->file_gk) }}"
                                        target="_blank"
                                        class="underline"
                                        >Lihat File</a
                                    >
                                </p>
                                @endif
                            </div>

                            <div class="mb-6">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Transkrip Nilai</label
                                >
                                <div class="mt-1 flex items-center">
                                    <input
                                        type="file"
                                        name="file_transkrip"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                    />
                                </div>
                                @error('file_transkrip') 
                                    <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> 
                                @enderror
                                
                                <p class="text-xs text-gray-500 mt-1 italic">Format PDF. Max 10MB.</p>
                                @if($registration->file_transkrip)
                                <p class="mt-1 text-xs text-green-600">
                                    ✓ Terunggah:
                                    <a
                                        href="{{ Storage::url($registration->file_transkrip) }}"
                                        target="_blank"
                                        class="underline"
                                        >Lihat File</a
                                    >
                                </p>
                                @endif
                            </div>

                            @if($registration->stage == 'universitas')
                                <div class="border-t border-gray-200 my-6 pt-4 bg-purple-50 p-4 rounded-lg">
                                    <div class="flex items-center mb-4">
                                        <div class="bg-purple-100 text-purple-800 p-2 rounded-full mr-3">
                                            🎉
                                        </div>
                                        <div>
                                            <h4 class="text-md font-bold text-blue-800">Selamat! Anda Lolos ke Tahap Universitas</h4>
                                            <p class="text-xs text-gray-600">Silakan lengkapi berkas tambahan di bawah ini.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700">Poster Gagasan Kreatif</label>
                                        <div class="mt-1 flex items-center">
                                            <input type="file" name="file_poster_gk" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        </div>
                                        @error('file_poster_gk') 
                                            <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> 
                                        @enderror
                                        
                                        <p class="text-xs text-gray-500 mt-1 italic">Format PDF, JPG, PNG. Max 5MB.</p>
                                        @if($registration->file_poster_gk)
                                            <p class="mt-1 text-xs text-green-600 font-bold flex items-center">
                                                ✓ Terunggah: 
                                                <a href="{{ Storage::url($registration->file_poster_gk) }}" target="_blank" class="underline ml-1">Lihat File</a>
                                            </p>
                                        @endif
                                    </div>

                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700">Poster Diri</label>
                                        <div class="mt-1 flex items-center">
                                            <input type="file" name="file_poster_diri" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        </div>
                                        @error('file_poster_diri') 
                                            <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> 
                                        @enderror
                                        
                                        <p class="text-xs text-gray-500 mt-1 italic">Format PDF, JPG, PNG. Max 5MB.</p>
                                        @if($registration->file_poster_diri)
                                            <p class="mt-1 text-xs text-green-600 font-bold flex items-center">
                                                ✓ Terunggah: 
                                                <a href="{{ Storage::url($registration->file_poster_diri) }}" target="_blank" class="underline ml-1">Lihat File</a>
                                            </p>
                                        @endif
                                    </div>

                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700">Link Video Bahasa Inggris</label>
                                        <input type="url" name="video_link" value="{{ old('video_link', $registration->video_link) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="https://youtube.com/...">
                                    </div>
                                </div>
                            @else
                                <div class="border-t border-gray-200 my-6 pt-4">
                                    <div class="bg-gray-50 border-l-4 border-gray-400 p-4">
                                        <div class="flex">
                                            <div class="ml-3">
                                                <p class="text-sm text-gray-700">
                                                    Persyaratan tahap Universitas (Poster & Video) hanya akan terbuka jika Anda dinyatakan 
                                                    <span class="font-bold">Lolos Seleksi Fakultas</span> oleh Panitia.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <button
                                type="submit"
                                class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700"
                            >
                                Simpan Berkas
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-8 border-t pt-4 flex justify-end">
                    <a
                        href="{{ route('student.achievements.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Lanjut: Isi Capaian Unggulan (CU) &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
