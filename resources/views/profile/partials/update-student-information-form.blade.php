<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Informasi Akademik Mahasiswa</h2>
        <p class="mt-1 text-sm text-gray-600">Perbarui data Fakultas, NIM, Program Studi, Semester, dan IPK Anda.</p>
    </header>

    @if(session('success') && session('status') === 'academic-updated')
        <div class="mt-4 p-3 bg-green-100 text-green-700 text-sm rounded">{{ session('success') }}</div>
    @endif

    <form method="post" action="{{ route('profile.academic.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="faculty_id" value="Fakultas" />
            <select id="faculty_id" name="faculty_id" required
                    class="mt-1 block w-full bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm px-3 py-2">
                <option value="">-- Pilih Fakultas --</option>
                @foreach($faculties as $faculty)
                    <option value="{{ $faculty->id }}"
                        {{ old('faculty_id', $user->student->faculty_id ?? $user->faculty_id) == $faculty->id ? 'selected' : '' }}>
                        {{ $faculty->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('faculty_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="nim" value="NIM" />
            <x-text-input id="nim" name="nim" type="text" class="mt-1 block w-full bg-gray-50" :value="old('nim', $user->student->nim ?? '')" required />
            <x-input-error :messages="$errors->get('nim')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="prodi" value="Program Studi" />
            <x-text-input id="prodi" name="prodi" type="text" class="mt-1 block w-full bg-gray-50" :value="old('prodi', $user->student->prodi ?? '')" required />
            <x-input-error :messages="$errors->get('prodi')" class="mt-2" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="semester" value="Semester Saat Ini" />
                <x-text-input id="semester" name="semester" type="number" class="mt-1 block w-full bg-gray-50" :value="old('semester', $user->student->semester ?? '')" required />
                <x-input-error :messages="$errors->get('semester')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="ipk" value="IPK Terakhir" />
                <x-text-input id="ipk" name="ipk" type="number" step="0.01" class="mt-1 block w-full bg-gray-50" :value="old('ipk', $user->student->ipk ?? '')" required />
                <x-input-error :messages="$errors->get('ipk')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan Data Akademik') }}</x-primary-button>
        </div>
    </form>
</section>