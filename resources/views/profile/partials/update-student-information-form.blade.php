<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Informasi Akademik Mahasiswa</h2>
        <p class="mt-1 text-sm text-gray-600">Perbarui data NIM, Program Studi, Semester, dan IPK Anda.</p>
    </header>

    <form method="post" action="{{ route('profile.academic.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="nim" value="NIM" />
            <x-text-input id="nim" name="nim" type="text" class="mt-1 block w-full bg-gray-50" :value="old('nim', $user->student->nim ?? '')" required />
        </div>

        <div>
            <x-input-label for="prodi" value="Program Studi" />
            <x-text-input id="prodi" name="prodi" type="text" class="mt-1 block w-full bg-gray-50" :value="old('prodi', $user->student->prodi ?? '')" required />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="semester" value="Semester Saat Ini" />
                <x-text-input id="semester" name="semester" type="number" class="mt-1 block w-full bg-gray-50" :value="old('semester', $user->student->semester ?? '')" required />
            </div>
            <div>
                <x-input-label for="ipk" value="IPK Terakhir" />
                <x-text-input id="ipk" name="ipk" type="number" step="0.01" class="mt-1 block w-full bg-gray-50" :value="old('ipk', $user->student->ipk ?? '')" required />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan Data Akademik') }}</x-primary-button>
        </div>
    </form>
</section>