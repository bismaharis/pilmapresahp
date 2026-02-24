<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelola Akun Juri (Dosen)</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <div class="bg-white p-6 shadow-sm rounded-lg h-fit">
                <h3 class="font-bold text-lg mb-4">Tambah Juri Baru</h3>
                <form action="{{ route('admin.juries.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium">Nama Lengkap & Gelar</label>
                        <input type="text" name="name" class="w-full mt-1 rounded-md border-gray-300" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium">NIP / NIDN (Opsional)</label>
                        <input type="text" name="nip" class="w-full mt-1 rounded-md border-gray-300">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium">Unit Kerja / Fakultas</label>
                        <input type="text" name="unit_kerja" class="w-full mt-1 rounded-md border-gray-300" placeholder="Misal: Teknik Informatika" required>
                    </div>
                    <div class="mb-4 border-t pt-4">
                        <label class="block text-sm font-medium text-blue-600">Email Login</label>
                        <input type="email" name="email" class="w-full mt-1 rounded-md border-gray-300" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-blue-600">Password</label>
                        <input type="password" name="password" class="w-full mt-1 rounded-md border-gray-300" required minlength="8">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded-md hover:bg-blue-700">Simpan Juri</button>
                </form>
            </div>

            <div class="md:col-span-2 bg-white p-6 shadow-sm rounded-lg">
                <x-auth-session-status class="mb-4" :status="session('success')" />
                <h3 class="font-bold text-lg mb-4">Daftar Juri Aktif</h3>
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2">Data Dosen</th>
                            <th class="px-4 py-2">Unit Kerja</th>
                            <th class="px-4 py-2">Akses Login</th>
                            <th class="px-4 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($juris as $juri)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-bold text-gray-900">{{ $juri->name }}</div>
                                <div class="text-xs text-gray-500">NIP: {{ $juri->lecturer->nip ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $juri->lecturer->unit_kerja ?? '-' }}</td>
                            <td class="px-4 py-3 text-blue-600">{{ $juri->email }}</td>
                            <td class="px-4 py-3 text-center flex justify-center space-x-2">
                                <a href="{{ route('admin.juries.edit', $juri->id) }}" class="bg-blue-100 text-blue-600 px-3 py-1 rounded hover:bg-blue-200 text-xs font-bold">Edit</a>
                                <form action="{{ route('admin.juries.destroy', $juri->id) }}" method="POST" onsubmit="return confirm('Hapus juri ini beserta seluruh data nilainya?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="bg-red-100 text-red-600 px-3 py-1 rounded hover:bg-red-200 text-xs font-bold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>