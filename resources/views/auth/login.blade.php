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

                <div class="mb-4 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-[#00b0f0]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 8a5 5 0 1110 0v1h.5A1.5 1.5 0 0117 10.5v6A1.5 1.5 0 0115.5 18h-11A1.5 1.5 0 013 16.5v-6A1.5 1.5 0 014.5 9H5V8zm2 1h6V8a3 3 0 10-6 0v1z" clip-rule="evenodd"></path></svg>
                    </div>
                    <input id="password" type="password" name="password" required placeholder="*****************" class="w-full pl-10 pr-10 py-2.5 border border-[#00b0f0] focus:outline-none focus:ring-1 focus:ring-[#00b0f0] focus:border-[#00b0f0] text-gray-800">
                    <button type="button" data-toggle-password="password" class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#00b0f0] hover:text-[#0096cc]" aria-label="Lihat password">
                        <svg data-eye-open class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z"></path></svg>
                        <svg data-eye-closed class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.27-2.943-9.543-7a9.965 9.965 0 012.21-3.592m3.06-2.302A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.004 10.004 0 01-4.132 5.411M15 12a3 3 0 00-4.243-2.829M9.88 9.88A3 3 0 0014.12 14.12"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"></path></svg>
                    </button>
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

    <script>
        document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
            button.addEventListener('click', function () {
                var inputId = button.getAttribute('data-toggle-password');
                var input = document.getElementById(inputId);
                var eyeOpen = button.querySelector('[data-eye-open]');
                var eyeClosed = button.querySelector('[data-eye-closed]');

                if (!input) {
                    return;
                }

                var isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');

                eyeOpen.classList.toggle('hidden', isPassword);
                eyeClosed.classList.toggle('hidden', !isPassword);
            });
        });
    </script>
</body>
</html>