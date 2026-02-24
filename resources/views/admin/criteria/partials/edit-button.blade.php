<div x-data="{ open: false }">
    <button @click="open = true" class="text-blue-600 hover:text-blue-900 font-medium">
        Edit
    </button>

    <div x-show="open" 
         x-transition 
         class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
         style="display: none;">
         
        <div @click.away="open = false" class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <form action="{{ route('admin.criteria.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Edit Bobot: {{ $item->name }}
                    </h3>
                    <div class="mt-2 max-w-xl text-sm text-gray-500">
                        <p>Masukkan bobot baru dalam persentase (Contoh: 35 untuk 35%).</p>
                    </div>
                    
                    <div class="mt-4">
                        <label for="weight-{{ $item->id }}" class="block text-sm font-medium text-gray-700">Persentase (%)</label>
                        <input type="number" 
                               name="weight" 
                               id="weight-{{ $item->id }}" 
                               value="{{ $item->weight * 100 }}"
                               step="0.01" 
                               min="0" 
                               max="100" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               required>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan
                    </button>
                    <button @click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>