<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Data Juri</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white p-6 shadow-sm rounded-lg border border-gray-200">
                <form action="{{ route('admin.juries.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Lengkap & Gelar</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">NIP / NIDN</label>
                            <input type="text" name="nip" value="{{ old('nip', $user->lecturer->nip ?? '') }}" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unit Kerja / Program Studi</label>
                            <input type="text" name="unit_kerja" value="{{ old('unit_kerja', $user->lecturer->unit_kerja ?? '') }}" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fakultas</label>
                            <select name="faculty_id" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                @foreach($faculties as $faculty)
                                    <option value="{{ $faculty->id }}" {{ (int) old('faculty_id', $user->lecturer->faculty_id ?? $user->faculty_id) === (int) $faculty->id ? 'selected' : '' }}>
                                        {{ $faculty->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Email Login</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    </div>

                    <div class="mt-6 p-4 bg-yellow-50 rounded border border-yellow-200">
                        <label class="block text-sm font-medium text-yellow-800">Ganti Password (Opsional)</label>
                        <p class="text-xs text-yellow-600 mb-2">Kosongkan jika tidak ingin mengubah password.</p>
                        <input type="password" name="password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500" minlength="8">
                    </div>

                    <div class="flex justify-between mt-6">
                        <a href="{{ route('admin.juries.index') }}" class="text-gray-500 hover:underline mt-2">Batal</a>
                        <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded-md hover:bg-blue-700 shadow-sm">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>