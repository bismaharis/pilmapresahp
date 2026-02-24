<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Data Juri</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm rounded-lg">
                <form action="{{ route('admin.juries.update', $user->id) }}" method="POST">
                    @csrf @method('PUT')
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium">Nama Lengkap & Gelar</label>
                        <input type="text" name="name" value="{{ $user->name }}" class="w-full mt-1 rounded-md border-gray-300" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium">NIP / NIDN</label>
                        <input type="text" name="nip" value="{{ $user->lecturer->nip ?? '' }}" class="w-full mt-1 rounded-md border-gray-300">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium">Unit Kerja / Fakultas</label>
                        <input type="text" name="unit_kerja" value="{{ $user->lecturer->unit_kerja ?? '' }}" class="w-full mt-1 rounded-md border-gray-300" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium">Email Login</label>
                        <input type="email" name="email" value="{{ $user->email }}" class="w-full mt-1 rounded-md border-gray-300" required>
                    </div>
                    <div class="mb-6 p-4 bg-yellow-50 rounded border border-yellow-200">
                        <label class="block text-sm font-medium text-yellow-800">Ganti Password (Opsional)</label>
                        <p class="text-xs text-yellow-600 mb-2">Kosongkan jika tidak ingin mengubah password.</p>
                        <input type="password" name="password" class="w-full rounded-md border-gray-300" minlength="8">
                    </div>
                    
                    <div class="flex justify-between">
                        <a href="{{ route('admin.juries.index') }}" class="text-gray-500 hover:underline mt-2">Batal</a>
                        <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded-md hover:bg-blue-700">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>