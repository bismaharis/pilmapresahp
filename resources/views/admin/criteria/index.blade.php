<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Bobot AHP') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ 
        showModal: false, 
        modalTitle: '', 
        formAction: '', 
        formMethod: 'POST',
        id: '',
        name: '', 
        weight: 0, 
        max_score: 0, 
        type: 'general',
        parent_id: '',
        
        openAddModal(parentId = '', parentName = '') {
            this.modalTitle = parentId ? 'Tambah Sub-Kriteria: ' + parentName : 'Tambah Kriteria Utama';
            this.formAction = '{{ route('admin.criteria.store') }}';
            this.formMethod = 'POST';
            this.name = ''; 
            this.weight = 0; 
            this.max_score = 0; 
            this.type = 'general'; 
            this.parent_id = parentId; 
            this.showModal = true;
        },
        
        openEditModal(item) {
            this.modalTitle = 'Edit Kriteria: ' + item.name;
            this.formAction = '/admin/criteria/' + item.id;
            this.formMethod = 'PUT';
            this.name = item.name; 
            
            // KUNCI PERBAIKAN FORM EDIT: Nilai DB 0.35 disulap jadi 35 lagi
            this.weight = parseFloat((item.weight * 100).toFixed(2)); 
            
            this.max_score = item.max_score; 
            this.type = item.type; 
            this.parent_id = item.parent_id || '';
            this.showModal = true;
        }
    }">
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
                <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Hierarki Bobot & Kriteria</h3>
                    </div>
                    <button type="button" @click="openAddModal('', '')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow flex items-center text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Kriteria Induk
                    </button>
                </div>

                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b-2 border-gray-300">
                                <tr>
                                    <th class="px-6 py-3">Nama Kriteria & Struktur</th>
                                    <th class="px-6 py-3 text-center w-24">Tipe</th>
                                    <th class="px-6 py-3 text-center w-24">Bobot</th>
                                    <th class="px-6 py-3 text-center w-24">Max Skor</th>
                                    <th class="px-6 py-3 text-center w-48">Aksi CRUD</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($criterias as $root)
                                    <tr class="bg-gray-200 font-bold border-b border-gray-300 text-gray-800">
                                        <td class="px-6 py-3">{{ $root->name }}</td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="px-2 py-1 bg-blue-800 text-white rounded text-xs">{{ strtoupper($root->type) }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-center">{{ floatval($root->weight * 100) }}%</td>
                                        <td class="px-6 py-3 text-center">{{ $root->max_score }}</td>
                                        <td class="px-6 py-3 text-center flex justify-center space-x-3">
                                            <button type="button" @click='openAddModal("{{ $root->id }}", @json($root->name))' class="text-green-600 hover:text-green-800" title="Tambah Sub"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></button>
                                            <button type="button" @click='openEditModal(@json($root))' class="text-blue-600 hover:text-blue-800" title="Edit"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                                            <form action="{{ route('admin.criteria.destroy', $root->id) }}" method="POST" onsubmit="return confirm('Hapus kriteria ini?');" class="inline">@csrf @method('DELETE') <button type="submit" class="text-red-600 hover:text-red-800"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button></form>
                                        </td>
                                    </tr>

                                    @foreach($root->children as $child)
                                        <tr class="bg-gray-50 border-b font-semibold text-gray-700 hover:bg-gray-100">
                                            <td class="px-6 py-2 pl-12 flex items-center">
                                                <span class="text-gray-400 mr-2">↳</span> {{ $child->name }}
                                            </td>
                                            <td class="px-6 py-2 text-center text-xs text-gray-400">Sub</td>
                                            <td class="px-6 py-3 text-center">{{ floatval($child->weight * 100) }}%</td>
                                            <td class="px-6 py-2 text-center">{{ $child->max_score }}</td>
                                            <td class="px-6 py-2 text-center flex justify-center space-x-3">
                                                <button type="button" @click='openAddModal("{{ $child->id }}", @json($child->name))' class="text-green-500 hover:text-green-700" title="Tambah Sub"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></button>
                                                <button type="button" @click='openEditModal(@json($child))' class="text-blue-500 hover:text-blue-700" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                                                <form action="{{ route('admin.criteria.destroy', $child->id) }}" method="POST" onsubmit="return confirm('Hapus?');" class="inline">@csrf @method('DELETE') <button type="submit" class="text-red-500 hover:text-red-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button></form>
                                            </td>
                                        </tr>

                                        @foreach($child->children as $grandChild)
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-2 pl-20 flex items-center text-gray-600">
                                                    <span class="text-gray-300 mr-2">↳</span> {{ $grandChild->name }}
                                                </td>
                                                <td class="px-6 py-2 text-center text-xs text-gray-400">Kat</td>
                                                <td class="px-6 py-3 text-center">{{ floatval($grandChild->weight * 100) }}%</td>
                                                <td class="px-6 py-2 text-center">{{ $grandChild->max_score }}</td>
                                                <td class="px-6 py-2 text-center flex justify-center space-x-3">
                                                    <button type="button" @click='openAddModal("{{ $grandChild->id }}", @json($grandChild->name))' class="text-green-400 hover:text-green-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></button>
                                                    <button type="button" @click='openEditModal(@json($grandChild))' class="text-blue-400 hover:text-blue-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                                                    <form action="{{ route('admin.criteria.destroy', $grandChild->id) }}" method="POST" onsubmit="return confirm('Hapus?');" class="inline">@csrf @method('DELETE') <button type="submit" class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button></form>
                                                </td>
                                            </tr>

                                            @foreach($grandChild->children as $greatGrandChild)
                                                <tr class="bg-yellow-50/30 border-b border-gray-100 hover:bg-yellow-50">
                                                    <td class="px-6 py-1.5 pl-32 flex items-center text-sm italic text-gray-500">
                                                        <span class="text-gray-300 mr-2">•</span> {{ $greatGrandChild->name }}
                                                    </td>
                                                    <td class="px-6 py-1.5 text-center text-xs text-gray-400">Item</td>
                                                    <td class="px-6 py-1.5 text-center text-gray-600">{{ floatval($greatGrandChild->weight * 100) }}%</td>
                                                    <td class="px-6 py-1.5 text-center text-gray-600">{{ $greatGrandChild->max_score }}</td>
                                                    <td class="px-6 py-1.5 text-center flex justify-center space-x-3">
                                                        <button type="button" @click='openEditModal(@json($greatGrandChild))' class="text-blue-300 hover:text-blue-500"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                                                        <form action="{{ route('admin.criteria.destroy', $greatGrandChild->id) }}" method="POST" onsubmit="return confirm('Hapus?');" class="inline">@csrf @method('DELETE') <button type="submit" class="text-red-300 hover:text-red-500"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button></form>
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

        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div x-show="showModal" @click="showModal = false" class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-50" aria-hidden="true"></div>
                <div x-show="showModal" class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl relative z-50">
                    <div class="flex justify-between items-center mb-5 border-b pb-3">
                        <h3 class="text-lg font-bold text-gray-900" x-text="modalTitle"></h3>
                        <button type="button" @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <form :action="formAction" method="POST">
                        @csrf
                        <input type="hidden" name="_method" :value="formMethod">
                        <input type="hidden" name="parent_id" x-model="parent_id">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Kriteria / Sub-Kriteria</label>
                                <input type="text" name="name" x-model="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Bobot Persentase (%)</label>
                                    <input type="number" name="weight" x-model="weight" min="0" max="100" step="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Skor Maksimal</label>
                                    <input type="number" name="max_score" x-model="max_score" min="0" step="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                </div>
                            </div>

                            <div x-show="!parent_id">
                                <label class="block text-sm font-medium text-gray-700">Kode Tipe Penilaian</label>
                                <select name="type" x-model="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="general">Umum / General</option>
                                    <option value="cu">Capaian Unggulan (CU)</option>
                                    <option value="gk">Gagasan Kreatif (GK)</option>
                                    <option value="bi">Bahasa Inggris (BI)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 font-medium">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-bold shadow">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>