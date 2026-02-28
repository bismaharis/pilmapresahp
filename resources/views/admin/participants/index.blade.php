<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelola Akun Peserta (Mahasiswa)</h2>
    </x-slot>

    <div class="py-12" x-data="{ 
        showModal: false, 
        modalTitle: '', 
        formAction: '', 
        formMethod: 'POST',
        name: '', 
        email: '', 
        nim: '', 
        faculty_id: '',
        prodi: '',
        
        openAddModal() {
            this.modalTitle = 'Tambah Peserta Baru';
            this.formAction = '{{ route('admin.participants.store') }}';
            this.formMethod = 'POST';
            this.name = ''; this.email = ''; this.nim = ''; this.faculty_id = ''; this.prodi = '';
            this.showModal = true;
        },
        
        openEditModal(user) {
            this.modalTitle = 'Edit Peserta: ' + user.name;
            this.formAction = '/admin/participants/' + user.id;
            this.formMethod = 'PUT';
            this.name = user.name; 
            this.email = user.email; 
            this.nim = user.student ? user.student.nim : '';
            this.faculty_id = user.student ? user.student.faculty_id : '';
            this.prodi = user.student ? user.student.prodi : '';
            this.showModal = true;
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <x-auth-session-status class="mb-4" :status="session('success')" />

            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                
                <form method="GET" action="{{ url()->current() }}" class="w-full sm:w-1/2 flex items-center space-x-3">
                    <svg class="w-5 h-5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    
                    @if(Auth::user()->role !== 'admin_fakultas')
                    <select name="faculty_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 text-sm font-semibold text-gray-700 transition" onchange="this.form.submit()">
                        <option value="">-- Tampilkan Semua Fakultas --</option>
                        @foreach($faculties as $faculty)
                            <option value="{{ $faculty->id }}" {{ request('faculty_id') == $faculty->id ? 'selected' : '' }}>
                                {{ $faculty->name }}
                            </option>
                        @endforeach
                    </select>
                    @else
                        <span class="font-bold text-gray-700 bg-gray-100 px-4 py-2 rounded-md w-full">Fakultas Anda</span>
                    @endif
                </form>

                <button type="button" @click="openAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow flex items-center shrink-0">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Peserta
                </button>
            </div>

            <div class="bg-white p-6 shadow-sm rounded-lg">
                <table class="min-w-full text-sm text-left border-collapse">
                    <thead class="bg-gray-100 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-gray-700">Identitas Peserta</th>
                            <th class="px-4 py-3 text-gray-700">Fakultas & Prodi</th>
                            <th class="px-4 py-3 text-gray-700">Email Login</th>
                            <th class="px-4 py-3 text-center text-gray-700 w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($participants as $participant)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <div class="font-bold text-gray-900">{{ $participant->name }}</div>
                                <div class="text-xs text-gray-500">NIM: {{ $participant->student->nim ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $participant->student->faculty->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $participant->student->prodi ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-blue-600">{{ $participant->email }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center space-x-2">
                                    <button type="button" @click='openEditModal(@json($participant->load("student.faculty")))' class="text-blue-600 hover:text-blue-800 font-semibold text-xs">Edit</button>
                                    <span class="text-gray-300">|</span>
                                    <form action="{{ route('admin.participants.destroy', $participant->id) }}" method="POST" onsubmit="return confirm('Hapus peserta ini? Semua data pendaftaran dan nilainya akan ikut terhapus secara permanen!')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-semibold text-xs">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500 italic">Belum ada peserta yang mendaftar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div x-show="showModal" @click="showModal = false" class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-50" aria-hidden="true"></div>
                <div x-show="showModal" class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl relative z-50">
                    <div class="flex justify-between items-center mb-5 border-b pb-3">
                        <h3 class="text-lg font-bold text-gray-900" x-text="modalTitle"></h3>
                        <button type="button" @click="showModal = false" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                    </div>
                    
                    <form :action="formAction" method="POST">
                        @csrf
                        <input type="hidden" name="_method" :value="formMethod">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                <input type="text" name="name" x-model="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500" required>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">NIM</label>
                                    <input type="text" name="nim" x-model="nim" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Program Studi</label>
                                    <input type="text" name="prodi" x-model="prodi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500" required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fakultas</label>
                                <select name="faculty_id" x-model="faculty_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500" required>
                                    <option value="">-- Pilih Fakultas --</option>
                                    @foreach($faculties as $faculty)
                                        <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="border-t pt-4">
                                <label class="block text-sm font-medium text-gray-700">Email Login</label>
                                <input type="email" name="email" x-model="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Password <span class="text-xs font-normal text-gray-400" x-show="formMethod === 'PUT'">(Kosongkan jika tidak ingin diubah)</span></label>
                                <input type="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500" :required="formMethod === 'POST'" minlength="8">
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