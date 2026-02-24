<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Bobot AHP') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3">Nama Kriteria</th>
                                    <th class="px-6 py-3">Tipe</th>
                                    <th class="px-6 py-3 text-center">Bobot (%)</th>
                                    <th class="px-6 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($criterias as $root)
                                    <tr class="bg-gray-200 font-bold border-b border-gray-300">
                                        <td class="px-6 py-4">{{ $root->name }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-blue-800 text-white rounded text-xs">{{ strtoupper($root->type) }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">{{ $root->weight * 100 }}%</td>
                                        <td class="px-6 py-4 text-center">
                                            @include('admin.criteria.partials.edit-button', ['item' => $root])
                                        </td>
                                    </tr>

                                    @foreach($root->children as $child)
                                        <tr class="bg-gray-50 border-b font-semibold text-gray-800">
                                            <td class="px-6 py-4 pl-12 flex items-center">
                                                <span class="text-gray-400 mr-2">↳</span> {{ $child->name }}
                                            </td>
                                            <td class="px-6 py-4 text-xs text-gray-500 uppercase">Sub-Kriteria</td>
                                            <td class="px-6 py-4 text-center">{{ $child->weight * 100 }}%</td>
                                            <td class="px-6 py-4 text-center">
                                                @include('admin.criteria.partials.edit-button', ['item' => $child])
                                            </td>
                                        </tr>

                                        @foreach($child->children as $grandChild)
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4 pl-20 flex items-center text-gray-700">
                                                    <span class="text-gray-300 mr-2">↳</span> {{ $grandChild->name }}
                                                </td>
                                                <td class="px-6 py-4 text-xs text-gray-400 uppercase">Kategori</td>
                                                <td class="px-6 py-4 text-center">{{ $grandChild->weight * 100 }}%</td>
                                                <td class="px-6 py-4 text-center">
                                                    @include('admin.criteria.partials.edit-button', ['item' => $grandChild])
                                                </td>
                                            </tr>

                                            @foreach($grandChild->children as $greatGrandChild)
                                                <tr class="bg-white border-b border-gray-100 hover:bg-yellow-50">
                                                    <td class="px-6 py-3 pl-32 flex items-center text-sm italic text-gray-600">
                                                        <span class="text-gray-200 mr-2">•</span> {{ $greatGrandChild->name }}
                                                    </td>
                                                    <td class="px-6 py-3 text-xs text-gray-400">Item Penilaian</td>
                                                    <td class="px-6 py-3 text-center text-gray-600">{{ $greatGrandChild->weight * 100 }}%</td>
                                                    <td class="px-6 py-3 text-center">
                                                        @include('admin.criteria.partials.edit-button', ['item' => $greatGrandChild])
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @endforeach
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>