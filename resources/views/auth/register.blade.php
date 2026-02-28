<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi - PILMAPRES UNRAM</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo-unram.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .bg-unram {
            background-image: url('{{ asset('images/bg-unram.png') }}'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .overlay {
            background-color: rgba(43, 65, 140, 0.85); 
        }
    </style>
</head>
<body class="antialiased bg-unram relative min-h-screen font-sans">
    
    <div class="overlay absolute inset-0 z-0"></div>

    <div class="relative z-10 flex items-center justify-center min-h-screen px-4 py-10">
        
        <div class="bg-white p-8 w-full max-w-[400px] shadow-2xl relative">
            
            <img src="{{ asset('images/logo-unram.png') }}" alt="Logo Unram" class="w-20 h-20 mx-auto mb-4 object-contain">
            <h2 class="text-2xl font-extrabold text-gray-800 mb-6 text-center">Registrasi Peserta</h2>

            @if ($errors->any())
                <div class="mb-5 bg-red-100 text-red-600 p-3 text-sm border border-red-200">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                
                <div class="mb-4 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-[#00b0f0]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                    </div>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Nama Lengkap" class="w-full pl-10 pr-3 py-2.5 border border-[#00b0f0] focus:outline-none focus:ring-1 focus:ring-[#00b0f0] focus:border-[#00b0f0] transition text-gray-800 font-medium">
                </div>

                <div class="mb-4 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-[#00b0f0]" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path></svg>
                    </div>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="Email Aktif" class="w-full pl-10 pr-3 py-2.5 border border-[#00b0f0] focus:outline-none focus:ring-1 focus:ring-[#00b0f0] focus:border-[#00b0f0] transition text-gray-800 font-medium">
                </div>

                <div class="mb-4 relative">
                    <input type="password" name="password" required placeholder="Password Baru" class="w-full px-3 py-2.5 border border-[#00b0f0] focus:outline-none focus:ring-1 focus:ring-[#00b0f0] focus:border-[#00b0f0] transition tracking-widest placeholder:tracking-normal text-gray-800 text-center font-bold">
                </div>

                <div class="mb-8 relative">
                    <input type="password" name="password_confirmation" required placeholder="Konfirmasi Password" class="w-full px-3 py-2.5 border border-[#00b0f0] focus:outline-none focus:ring-1 focus:ring-[#00b0f0] focus:border-[#00b0f0] transition tracking-widest placeholder:tracking-normal text-gray-800 text-center font-bold">
                </div>

                <div class="-mx-8 -mb-8 mt-4">
                    <button type="submit" class="w-full bg-[#00b0f0] hover:bg-[#0096cc] text-white font-bold py-4 text-lg flex items-center justify-center transition">
                        Daftar Sekarang <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    </button>
                </div>
            </form>
            
            <div class="mt-12 text-center text-sm text-gray-500">
                Sudah punya akun? <a href="{{ route('login') }}" class="text-[#00b0f0] font-bold hover:underline">Sign On</a>
            </div>

        </div>
    </div>
</body>
</html>