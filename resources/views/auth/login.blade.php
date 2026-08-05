<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Correo Electrónico</label>
            <div class="relative">
                <i class="ph ph-envelope-simple absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-lg"></i>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm font-medium text-gray-800 shadow-sm" placeholder="ejemplo@gobernacion.gov.co">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div x-data="{ show: false }">
            <div class="flex justify-between items-center mb-1.5">
                <label for="password" class="block text-sm font-semibold text-gray-700">Contraseña</label>
                <a class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition" href="{{ route('password.request') }}">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>
            <div class="relative">
                <i class="ph ph-lock-key absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-lg"></i>
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" class="w-full pl-11 pr-12 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm font-medium text-gray-800 shadow-sm" placeholder="••••••••">
                <button type="button" @click="show = !show" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                    <i class="ph text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <label for="remember_me" class="flex items-center cursor-pointer group">
                <div class="relative flex items-center justify-center">
                    <input id="remember_me" type="checkbox" class="peer h-5 w-5 cursor-pointer rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500" name="remember">
                </div>
                <span class="ms-2 text-sm font-medium text-gray-600 group-hover:text-gray-800 transition">Mantener sesión iniciada</span>
            </label>
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-md shadow-blue-500/30 transition-all">
                Ingresar al Sistema
                <i class="ph ph-arrow-right font-bold"></i>
            </button>
        </div>
    </form>
</x-guest-layout>
