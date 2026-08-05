<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-800">Restablecer Contraseña</h2>
        <p class="text-gray-600 text-sm mt-1">
            Ingresa tu nueva contraseña a continuación.
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Correo Electrónico</label>
            <div class="relative">
                <i class="ph ph-envelope-simple absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-lg"></i>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" class="w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm font-medium text-gray-800 shadow-sm" readonly>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div x-data="{ show: false }">
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Nueva Contraseña</label>
            <div class="relative">
                <i class="ph ph-lock-key absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-lg"></i>
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="new-password" class="w-full pl-11 pr-12 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm font-medium text-gray-800 shadow-sm" placeholder="Mínimo 8 caracteres">
                <button type="button" @click="show = !show" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                    <i class="ph text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div x-data="{ show: false }">
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmar Nueva Contraseña</label>
            <div class="relative">
                <i class="ph ph-lock-key absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-lg"></i>
                <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" class="w-full pl-11 pr-12 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm font-medium text-gray-800 shadow-sm" placeholder="••••••••">
                <button type="button" @click="show = !show" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                    <i class="ph text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-md shadow-blue-500/30 transition-all">
                <i class="ph ph-floppy-disk text-lg"></i>
                Restablecer Contraseña
            </button>
        </div>
    </form>
</x-guest-layout>
