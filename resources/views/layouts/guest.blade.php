<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SIRAD') }} - Iniciar Sesión</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="font-sans antialiased text-gray-900 bg-gray-50 flex items-center justify-center min-h-screen relative overflow-hidden"
    style="font-family: 'Inter', sans-serif;">
    <!-- Background decoration -->
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-blue-400/20 rounded-full blur-3xl mix-blend-multiply">
    </div>
    <div
        class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-indigo-400/20 rounded-full blur-3xl mix-blend-multiply">
    </div>

    <div class="w-full max-w-md z-10 p-6">
        <div class="text-center mb-8">
            <div
                class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto shadow-lg shadow-blue-500/30 mb-4 transform rotate-3 hover:rotate-0 transition-transform">
                <i class="ph ph-folder-open text-3xl text-white"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">SIRAD</h1>
            <p class="text-gray-500 mt-2 font-medium">Sistema de Radicación de Documentos</p>
        </div>

        <div class="bg-white/80 backdrop-blur-xl shadow-xl shadow-gray-200/50 rounded-3xl p-8 border border-white">
            {{ $slot }}
        </div>

        <p class="text-center text-sm text-gray-400 mt-8 font-medium">
            &copy; {{ date('Y') }} Algorah. Todos los derechos reservados.
        </p>
    </div>

    <x-toast />
</body>

</html>