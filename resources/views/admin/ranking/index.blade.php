<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Peringkat Peserta (Leaderboard)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-auth-session-status class="mb-4" :status="session('success')" />

            <div class="mb-4 flex justify-between items-center">
                <div class="flex space-x-2">
                    <a href="{{ route('admin.ranking.index', ['stage' => 'fakultas']) }}" 
                       class="px-4 py-2 rounded-md font-bold {{ $stage == 'fakultas' ? 'bg-blue-600 text-white shadow' : 'bg-white text-gray-600 border' }}">
                        Tahap Fakultas
                    </a>
                    <a href="{{ route('admin.ranking.index', ['stage' => 'universitas']) }}" 
                       class="px-4 py-2 rounded-md font-bold {{ $stage == 'universitas' ? 'bg-purple-600 text-white shadow' : 'bg-white text-gray-600 border' }}">
                        Tahap Universitas
                    </a>
                </div>

                <a href="{{ route('admin.ranking.pdf', ['stage' => $stage]) }}" target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-red-700 shadow-sm transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Cetak PDF Leaderboard
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-4 py-3 text-center">Peringkat</th>
                            <th class="px-4 py-3">Nama Mahasiswa</th>
                            <th class="px-4 py-3 text-center">Nilai AHP</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
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
                                <td class="px-4 py-3 text-center">
                                    @if($stage == 'fakultas' && $reg->stage == 'fakultas')
                                        <form action="{{ route('admin.ranking.delegate', $reg->id) }}" method="POST" onsubmit="return confirm('Delegasikan mahasiswa ini ke tingkat Universitas?')">
                                            @csrf
                                            <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700 font-bold shadow-sm">
                                                Loloskan ke Univ ➔
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Telah Didelegasikan</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada data peringkat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>