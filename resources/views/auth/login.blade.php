<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign On - PILMAPRES UNRAM</title>
    
    <link rel="icon" type="image/png" href="{{ asset('images/logo-unram.png') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Background Gedung Rektorat */
        .bg-unram {
            background-image: url('{{ asset('images/bg-unram.png') }}'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        /* Overlay biru transparan khas Unram */
        .overlay {
            background-color: rgba(43, 65, 140, 0.85); 
        }
    </style>
</head>
<body class="antialiased bg-unram relative min-h-screen font-sans">
    
    <div class="overlay absolute inset-0 z-0"></div>

    <div class="relative z-10 flex items-center justify-center min-h-screen px-4">
        
        <div class="bg-white p-8 w-full max-w-[360px] shadow-2xl relative">
            
            <img src="{{ asset('images/logo-unram.png') }}" alt="Logo Unram" class="w-24 h-24 mx-auto mb-4 object-contain">
            <h2 class="text-2xl font-extrabold text-gray-800 mb-8 text-center">Sign On</h2>

            @if ($errors->any())
                <div class="mb-5 bg-red-100 text-red-600 p-3 text-sm border border-red-200">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="mb-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-[#00b0f0]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                    </div>
                    <input type="text" name="login" value="{{ old('login') }}" required autofocus placeholder="NIM / Username / Email" class="w-full pl-10 pr-3 py-2.5 border border-[#00b0f0] focus:outline-none focus:ring-1 focus:ring-[#00b0f0] focus:border-[#00b0f0] text-gray-800">
                </div>

                <div class="mb-8 relative">
                    <input type="password" name="password" required placeholder="*****************" class="w-full px-3 py-2.5 border border-[#00b0f0] focus:outline-none focus:ring-1 focus:ring-[#00b0f0] focus:border-[#00b0f0] tracking-widest placeholder:tracking-widest text-gray-800 text-center font-bold">
                </div>

                <input type="hidden" name="remember" value="true">

                <div class="-mx-8 -mb-8 mt-4">
                    <button type="submit" class="w-full bg-[#00b0f0] hover:bg-[#0096cc] text-white font-bold py-4 text-lg flex items-center justify-center transition">
                        Lanjut <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>