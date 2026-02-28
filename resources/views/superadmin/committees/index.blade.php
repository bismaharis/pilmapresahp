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
                <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        <span class="font-bold text-gray-700">Filter Data:</span>
                    </div>
                    
                    <form method="GET" action="{{ url()->current() }}" class="w-full sm:w-1/3">
                        @if(request('stage'))
                            <input type="hidden" name="stage" value="{{ request('stage') }}">
                        @endif
                        
                        <select name="faculty_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 text-sm font-semibold text-gray-700 transition" onchange="this.form.submit()">
                            <option value="">-- Tampilkan Semua Fakultas --</option>
                            @foreach($faculties as $faculty)
                                <option value="{{ $faculty->id }}" {{ request('faculty_id') == $faculty->id ? 'selected' : '' }}>
                                    {{ $faculty->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
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