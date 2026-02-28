<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Peringkat Peserta (Leaderboard)') }}
        </h2>
    </x-slot>

    @php
        $isSuperLevel = in_array($role, ['super_admin', 'admin_univ']);
        $isAdminRole = in_array($role, ['super_admin', 'admin_univ', 'admin_fakultas']);
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-auth-session-status class="mb-4" :status="session('success')" />

            <div class="mb-4 flex justify-between items-center">
                <div class="flex space-x-2">
                    @if($isSuperLevel)
                        <a href="{{ route('admin.ranking.index', ['stage' => 'fakultas', 'faculty_id' => request('faculty_id')]) }}" 
                           class="px-4 py-2 rounded-md font-bold {{ $stage == 'fakultas' ? 'bg-blue-600 text-white shadow' : 'bg-white text-gray-600 border' }}">
                            Tahap Fakultas
                        </a>
                        <a href="{{ route('admin.ranking.index', ['stage' => 'universitas', 'faculty_id' => request('faculty_id')]) }}" 
                           class="px-4 py-2 rounded-md font-bold {{ $stage == 'universitas' ? 'bg-purple-600 text-white shadow' : 'bg-white text-gray-600 border' }}">
                            Tahap Universitas
                        </a>
                    @elseif($isUnivJudge)
                        <span class="px-4 py-2 rounded-md font-bold bg-purple-600 text-white shadow">Tahap Universitas</span>
                    @else
                        <span class="px-4 py-2 rounded-md font-bold bg-blue-600 text-white shadow">Tahap Fakultas</span>
                    @endif
                </div>

            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @if($isSuperLevel || $isUnivJudge)
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
                @endif

                <table class="min-w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-4 py-3 text-center">Peringkat</th>
                            <th class="px-4 py-3">Nama Mahasiswa</th>
                            <th class="px-4 py-3 text-center">Nilai AHP</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            @if($isAdminRole)
                            <th class="px-4 py-3 text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($rankings as $index => $reg)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-center font-bold text-lg {{ $index < 3 ? 'text-yellow-600' : 'text-gray-500' }}">
                                    #{{ $index + 1 }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-900">
                                    {{ $reg->student->user->name }}
                                    <div class="text-xs text-gray-400">{{ $reg->student->nim }} - {{ $reg->student->prodi }}</div>
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-blue-600 text-lg">
                                    {{ number_format($reg->$scoreColumn, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 rounded text-xs {{ $reg->stage == 'universitas' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ strtoupper($reg->stage) }}
                                    </span>
                                </td>
                                
                                @if($isAdminRole)
                                <td class="px-4 py-3 text-center">
                                    @if($stage == 'fakultas' && $reg->stage == 'fakultas')
                                        <form action="{{ route('admin.ranking.delegate', $reg->id) }}" method="POST" onsubmit="return confirm('Delegasikan mahasiswa ini ke tingkat Universitas?')">
                                            @csrf
                                            <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700 font-bold shadow-sm transition">
                                                Loloskan ke Univ ➔
                                            </button>
                                        </form>
                                    @elseif($stage == 'universitas' && $reg->stage == 'universitas')
                                        <form action="{{ route('admin.ranking.cancel_delegate', $reg->id) }}" method="POST" onsubmit="return confirm('Batalkan delegasi dan kembalikan mahasiswa ini ke tingkat Fakultas?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="bg-red-50 text-red-600 px-3 py-1 rounded text-xs font-bold hover:bg-red-100 border border-red-200 transition">
                                                Batalkan Delegasi
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $isAdminRole ? 5 : 4 }}" class="px-4 py-4 text-center text-gray-500">Belum ada data peringkat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>