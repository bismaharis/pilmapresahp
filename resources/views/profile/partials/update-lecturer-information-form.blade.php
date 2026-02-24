<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Informasi Data Pegawai / Juri</h2>
        <p class="mt-1 text-sm text-gray-600">Perbarui data NIP dan Unit Kerja / Fakultas Anda.</p>
    </header>

    <form method="post" action="{{ route('profile.lecturer.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="nip" value="NIP / NIDN" />
            <x-text-input id="nip" name="nip" type="text" class="mt-1 block w-full bg-blue-50" :value="old('nip', $user->lecturer->nip ?? '')" required />
        </div>

        <div>
            <x-input-label for="unit_kerja" value="Unit Kerja / Program Studi" />
            <x-text-input id="unit_kerja" name="unit_kerja" type="text" class="mt-1 block w-full bg-blue-50" :value="old('unit_kerja', $user->lecturer->unit_kerja ?? '')" required />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan Data Pegawai') }}</x-primary-button>
        </div>
    </form>
</section>