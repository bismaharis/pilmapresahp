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
                        <input type="text" name="name" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium">NIP / NIDN (Opsional)</label>
                        <input type="text" name="nip" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium">Unit Kerja / Fakultas</label>
                        <select name="faculty_id" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            <option value="">-- Pilih Fakultas --</option>
                            @foreach($faculties as $faculty)
                                <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-4 border-t pt-4">
                        <label class="block text-sm font-medium text-blue-600">Email Login</label>
                        <input type="email" name="email" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-blue-600">Password</label>
                        <input type="password" name="password" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required minlength="8">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded-md hover:bg-blue-700 shadow transition">Simpan Juri</button>
                </form>
            </div>

            <div class="md:col-span-2 space-y-6">
                
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        <span class="font-bold text-gray-700">Filter Data:</span>
                    </div>
                    
                    <form method="GET" action="{{ url()->current() }}" class="w-full sm:w-1/2">
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

                <div class="bg-white p-6 shadow-sm rounded-lg">
                    <x-auth-session-status class="mb-4" :status="session('success')" />
                    <h3 class="font-bold text-lg mb-4 text-gray-800">Daftar Juri Aktif</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-left border-collapse">
                            <thead class="bg-gray-100 border-b-2 border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-gray-700">Data Dosen</th>
                                    <th class="px-4 py-3 text-gray-700">Unit Kerja</th>
                                    <th class="px-4 py-3 text-gray-700">Akses Login</th>
                                    <th class="px-4 py-3 text-center text-gray-700 w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($juries as $juri)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-900">{{ $juri->name }}</div>
                                        <div class="text-xs text-gray-500">NIP: {{ $juri->lecturer->nip ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-700">{{ $juri->lecturer->faculty->name ?? '-' }}</td>
                                    
                                    <td class="px-4 py-3 text-blue-600">{{ $juri->email }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('admin.juries.edit', $juri->id) }}" class="bg-blue-100 text-blue-700 px-3 py-1.5 rounded hover:bg-blue-200 text-xs font-bold transition">Edit</a>
                                            <form action="{{ route('admin.juries.destroy', $juri->id) }}" method="POST" onsubmit="return confirm('Hapus juri ini beserta seluruh data nilainya?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="bg-red-100 text-red-700 px-3 py-1.5 rounded hover:bg-red-200 text-xs font-bold transition">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500 italic">Belum ada data juri yang terdaftar.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</x-app-layout>