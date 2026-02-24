<aside :class="sidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full w-0'" class="bg-[#3d3d3d] text-gray-300 flex flex-col h-screen transition-all duration-300 ease-in-out z-20 overflow-y-auto shrink-0">
    
    <div class="p-6 flex flex-col items-center border-b border-gray-600">
        <div class="w-24 h-24 bg-gray-300 rounded overflow-hidden border-2 border-gray-400 mb-3 shadow-inner flex items-center justify-center">
            @if(Auth::user()->photo)
                <img src="{{ asset('storage/' . Auth::user()->photo) }}" alt="Foto Profil" class="w-full h-full object-cover">
            @else
                <svg class="w-16 h-16 text-gray-500" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            @endif
        </div>
        <h3 class="font-bold text-white text-center leading-tight">{{ Auth::user()->name }}</h3>
        <p class="text-xs text-gray-400 mt-1 uppercase tracking-wider">{{ str_replace('_', ' ', Auth::user()->role) }}</p>
    </div>

    <nav class="flex-1 py-4 space-y-1">

        <a href="{{ route('profile.edit') }}" class="flex items-center px-6 py-3 {{ request()->routeIs('profile.edit') ? 'bg-gray-600 text-white border-l-4 border-cyan-500' : 'hover:bg-gray-700 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            Biodata
        </a>

        @if(Auth::user()->role === 'mahasiswa')
            <div x-data="{ open: {{ request()->routeIs('student.registration.*', 'student.achievements.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-3 hover:bg-gray-700 hover:text-white transition">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Pendaftaran
                    </div>
                    <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" x-collapse class="bg-[#333333]">
                    <a href="{{ route('student.registration.index') }}" class="block px-14 py-2 text-sm {{ request()->routeIs('student.registration.index') ? 'bg-gray-600 text-white border-l-4 border-cyan-500' : 'text-gray-400 hover:text-white'}}">Fakultas (Berkas)</a>
                    <a href="{{ route('student.achievements.index') }}" class="block px-14 py-2 text-sm {{ request()->routeIs('student.achievements.*') ? 'bg-gray-600 text-white border-l-4 border-cyan-500' : 'text-gray-400 hover:text-white'}}">Capaian Unggulan</a>
                </div>
            </div>
            
            <a href="{{ route('transparency.index') }}" class="flex items-center px-6 py-3 {{ request()->routeIs('transparency.*') ? 'bg-gray-600 text-white border-l-4 border-cyan-500' : 'hover:bg-gray-700 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Peringkat & Transparansi
            </a>
        @endif

        @if(in_array(Auth::user()->role, ['super_admin', 'admin_univ', 'admin_fakultas']))
            
            <div x-data="{ open: {{ request()->routeIs('admin.juries.*', 'superadmin.committees.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-3 hover:bg-gray-700 hover:text-white transition">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Kelola Pengguna
                    </div>
                    <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" x-collapse class="bg-[#333333]">
                    @if(Auth::user()->role === 'super_admin')
                        <a href="{{ route('superadmin.committees.index') }}" class="block px-14 py-2 text-sm {{ request()->routeIs('superadmin.committees.*') ? 'bg-gray-600 text-white border-l-4 border-cyan-500' : 'text-gray-400 hover:text-white'}}">Data Panitia</a>
                    @endif
                    <a href="{{ route('admin.juries.index') }}" class="block px-14 py-2 text-sm {{ request()->routeIs('admin.juries.*') ? 'bg-gray-600 text-white border-l-4 border-cyan-500' : 'text-gray-400 hover:text-white'}}">Data Juri</a>
                    
                    <a href="#" class="block px-14 py-2 text-sm text-gray-500 italic cursor-not-allowed" title="Route admin.participants belum dibuat">Data Peserta (Soon)</a>
                </div>
            </div>

            <a href="{{ route('admin.ranking.index') }}" class="flex items-center px-6 py-3 {{ request()->routeIs('admin.ranking.index') ? 'bg-gray-600 text-white border-l-4 border-cyan-500' : 'hover:bg-gray-700 hover:text-white'}}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Pendelegasian Peserta
            </a>
            
            @if(in_array(Auth::user()->role, ['super_admin', 'admin_univ']))
                <a href="{{ route('admin.criteria.index') }}" class="flex items-center px-6 py-3 {{ request()->routeIs('admin.criteria.*') ? 'bg-gray-600 text-white border-l-4 border-cyan-500' : 'hover:bg-gray-700 hover:text-white'}}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Master Data Kriteria
                </a>
            @endif

            <a href="{{ route('transparency.index') }}" class="flex items-center px-6 py-3 {{ request()->routeIs('transparency.*') ? 'bg-gray-600 text-white border-l-4 border-cyan-500' : 'hover:bg-gray-700 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Peringkat & Transparansi
            </a>
            
        @endif

        @if(Auth::user()->role === 'dosen')
            <a href="{{ route('juri.assessments.index') }}" class="flex items-center px-6 py-3 {{ request()->routeIs('juri.assessments.*') ? 'bg-gray-600 text-white border-l-4 border-cyan-500' : 'hover:bg-gray-700 hover:text-white'}}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Penilaian Juri
            </a>
            
            <a href="{{ route('transparency.index') }}" class="flex items-center px-6 py-3 {{ request()->routeIs('transparency.*') ? 'bg-gray-600 text-white border-l-4 border-cyan-500' : 'hover:bg-gray-700 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Peringkat & Transparansi
            </a>
        @endif

        <a href="https://lldikti6.id/wp-content/uploads/2025/05/Panduan-Pilmapres-Program-Sarjana-2025-1.pdf" target="_blank" class="flex items-center px-6 py-3 mt-4 border-t border-gray-600 hover:bg-gray-700 hover:text-white">
            <svg class="w-5 h-5 mr-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            Guide Book 2025
        </a>

    </nav>
</aside>