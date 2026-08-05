<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-800">Recuperar Contraseña</h2>
        <p class="text-gray-600 text-sm mt-1">
            ¿Olvidaste tu contraseña? No hay problema. Simplemente indícanos tu dirección de correo electrónico y te enviaremos un enlace que te permitirá elegir una nueva.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" x-data="{ loading: false }" @submit="loading = true">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Correo Electrónico</label>
            <div class="relative">
                <i class="ph ph-envelope-simple absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-lg"></i>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm font-medium text-gray-800 shadow-sm" placeholder="ejemplo@gobernacion.gov.co">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-md shadow-blue-500/30 transition-all" :class="loading ? 'opacity-75 cursor-wait' : ''" :disabled="loading">
                <span x-show="!loading" class="flex items-center gap-2">
                    <i class="ph ph-paper-plane-right font-bold text-lg"></i>
                    Enviar Enlace de Recuperación
                </span>
                <span x-show="loading" class="flex items-center gap-2" x-cloak>
                    <i class="ph ph-spinner animate-spin text-lg"></i>
                    Enviando enlace...
                </span>
            </button>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition">
                Volver al inicio de sesión
            </a>
        </div>
    </form>
</x-guest-layout>
