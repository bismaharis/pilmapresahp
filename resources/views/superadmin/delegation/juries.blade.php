<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pendelegasian Juri ke Tingkat Universitas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-auth-session-status class="mb-4" :status="session('success')" />

            <div class="mb-4 flex justify-between items-center">
                <div class="flex space-x-2">
                    <a href="{{ route('superadmin.delegation.juries.index', ['stage' => 'fakultas', 'faculty_id' => request('faculty_id')]) }}" 
                       class="px-4 py-2 rounded-md font-bold {{ $stage == 'fakultas' ? 'bg-blue-600 text-white shadow' : 'bg-white text-gray-600 border' }}">
                        Juri Tingkat Fakultas
                    </a>
                    <a href="{{ route('superadmin.delegation.juries.index', ['stage' => 'universitas', 'faculty_id' => request('faculty_id')]) }}" 
                       class="px-4 py-2 rounded-md font-bold {{ $stage == 'universitas' ? 'bg-purple-600 text-white shadow' : 'bg-white text-gray-600 border' }}">
                        Juri Tingkat Universitas
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="mb-6 text-sm text-gray-600">
                    <p>Halaman ini digunakan untuk mengelola kewenangan Juri. Anda dapat menaikkan Juri Fakultas menjadi Juri Universitas atau sebaliknya.</p>
                </div>

                <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        <span class="font-bold text-gray-700">Filter Asal Fakultas:</span>
                    </div>
                    
                    <form method="GET" action="{{ url()->current() }}" class="w-full sm:w-1/3">
                        <input type="hidden" name="stage" value="{{ $stage }}">
                        
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

                <table class="min-w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-4 py-3">Nama Juri</th>
                            <th class="px-4 py-3 text-center">NIP</th>
                            <th class="px-4 py-3 text-center">Asal Fakultas</th>
                            <th class="px-4 py-3 text-center">Status Saat Ini</th>
                            <th class="px-4 py-3 text-center">Aksi Pendelegasian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($juries as $juri)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-semibold text-gray-900">
                                    {{ $juri->name }}
                                </td>
                                <td class="px-4 py-3 text-center text-gray-500">
                                    {{ $juri->lecturer->nip ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center text-gray-500">
                                    {{ $juri->lecturer->faculty->name ?? 'Belum Diatur' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($juri->lecturer && $juri->lecturer->is_univ_judge)
                                        <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs font-bold">Juri Universitas</span>
                                    @else
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-bold">Juri Fakultas</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($juri->lecturer)
                                        <form action="{{ route('superadmin.delegation.juries.toggle', $juri->lecturer->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            
                                            @if($juri->lecturer->is_univ_judge)
                                                <button type="submit" class="bg-red-50 text-red-600 px-3 py-1 rounded text-xs font-bold hover:bg-red-100 border border-red-200 transition" onclick="return confirm('Turunkan kembali menjadi Juri Fakultas?')">
                                                    Batalkan Delegasi
                                                </button>
                                            @else
                                                <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-xs font-bold hover:bg-green-700 shadow-sm transition" onclick="return confirm('Angkat dosen ini menjadi Juri tingkat Universitas?')">
                                                    Naikkan ke Univ ➔
                                                </button>
                                            @endif
                                        </form>
                                    @else
                                        <span class="text-xs text-red-500 italic">Data Dosen Belum Lengkap</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">
                                Belum ada Juri di tahap {{ $stage }} ini.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>