<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIRAD') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
        
        <!-- Icons (Phosphor Icons or Heroicons would be ideal, we'll use SVGs directly in blades for now) -->
        <script src="https://unpkg.com/@phosphor-icons/web"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            [x-cloak] { display: none !important; }
            body { font-family: 'Inter', sans-serif; }
            .bg-sirad-dark { background-color: #0f172a; } /* Slate 900 */
            .bg-sirad-blue { background-color: #1d4ed8; } /* Blue 700 */
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        
        <div class="flex h-screen overflow-hidden">
            
            <!-- Sidebar Navigation -->
            @include('layouts.navigation')

            <!-- Main Content Area -->
            <div class="flex flex-col flex-1 w-full overflow-y-auto overflow-x-hidden">
                
                <!-- Top Header -->
                <header class="bg-white/80 backdrop-blur-md border-b border-gray-200 z-10 sticky top-0">
                    <div class="flex items-center justify-between px-8 py-4">
                        
                        <!-- Page Heading (Dynamic) -->
                        <div class="flex-1">
                            <h1 class="text-2xl font-bold text-gray-800">Hola {{ explode(' ', Auth::user()->name)[0] }}</h1>
                            <p class="text-sm text-gray-500 capitalize mt-0.5">{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
                        </div>

                        <!-- Search and Profile -->
                        <div class="flex items-center gap-6">
                            <!-- Profile Dropdown -->
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0D8ABC&color=fff" class="w-8 h-8 rounded-full border-2 border-white shadow-sm" alt="Avatar">
                                        <div class="hidden sm:block text-left">
                                            <p class="text-gray-800 font-semibold">{{ explode(' ', Auth::user()->name)[0] }}</p>
                                            <p class="text-xs text-gray-500 capitalize">{{ Auth::user()->role }}</p>
                                        </div>
                                        <i class="ph ph-caret-down"></i>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('Perfil') }}
                                    </x-dropdown-link>

                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('logout')"
                                                onclick="event.preventDefault();
                                                            this.closest('form').submit();">
                                            {{ __('Cerrar Sesión') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 p-8">
                    @if (isset($header))
                        <div class="mb-8">
                            {{ $header }}
                        </div>
                    @endif
                    
                    {{ $slot }}
                </main>
                
            </div>
        </div>

        <x-toast />
    </body>
</html>
