<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelola Panitia Pilmapres</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <div class="bg-white p-6 shadow-sm rounded-lg h-fit">
                <h3 class="font-bold text-lg mb-4">Tambah Panitia Baru</h3>
                <form action="{{ route('superadmin.committees.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium">Nama Lengkap</label>
                        <input type="text" name="name" class="w-full mt-1 rounded-md border-gray-300" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium">Email</label>
                        <input type="email" name="email" class="w-full mt-1 rounded-md border-gray-300" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium">Password Login</label>
                        <input type="password" name="password" class="w-full mt-1 rounded-md border-gray-300" required minlength="8">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium">Tingkatan Kewenangan (Role)</label>
                        <select name="role" class="w-full mt-1 rounded-md border-gray-300" required>
                            <option value="admin_fakultas">Panitia / Admin Fakultas</option>
                            <option value="admin_univ">Panitia / Admin Universitas</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded-md hover:bg-blue-700">Simpan Panitia</button>
                </form>
            </div>

            <div class="md:col-span-2 bg-white p-6 shadow-sm rounded-lg">
                <x-auth-session-status class="mb-4" :status="session('success')" />
                <h3 class="font-bold text-lg mb-4">Daftar Panitia Aktif</h3>
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2">Nama</th>
                            <th class="px-4 py-2">Email</th>
                            <th class="px-4 py-2">Role</th>
                            <th class="px-4 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($committees as $committee)
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $committee->name }}</td>
                            <td class="px-4 py-3">{{ $committee->email }}</td>
                            <td class="px-4 py-3 uppercase text-xs font-bold text-purple-600">{{ str_replace('_', ' ', $committee->role) }}</td>
                            <td class="px-4 py-3 text-center flex justify-center space-x-2">
                                <a href="{{ route('superadmin.committees.edit', $committee->id) }}" class="text-blue-600 hover:underline">Edit</a>
                                <form action="{{ route('superadmin.committees.destroy', $committee->id) }}" method="POST" onsubmit="return confirm('Hapus panitia ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
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