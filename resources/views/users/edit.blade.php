<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('users.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <i class="ph ph-arrow-left text-2xl"></i>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-800 tracking-tight">
                    Editar Usuario
                </h2>
                <p class="text-gray-500 text-sm mt-1">Actualiza los datos y permisos de {{ $user->name }}.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto pb-12">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <form action="{{ route('users.update', $user) }}" method="POST" autocomplete="off">
                @csrf
                @method('PUT')
                
                <div class="p-8 sm:p-12 space-y-10">
                    
                    <!-- Información Básica -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3 mb-6 flex items-center gap-2">
                            <i class="ph ph-user text-blue-500"></i>
                            Información Básica
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nombre Completo <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-gray-50 px-4 py-2.5" required autofocus>
                                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Correo Electrónico <span class="text-red-500">*</span></label>
                                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-gray-50 px-4 py-2.5" required>
                                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div x-data="{ show: false }">
                                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Nueva Contraseña</label>
                                <div class="relative">
                                    <input :type="show ? 'text' : 'password'" id="password" name="password" autocomplete="new-password" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-gray-50 pl-4 pr-10 py-2.5" placeholder="Dejar en blanco para no cambiar">
                                    <button type="button" @click="show = !show" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                                        <i class="ph text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                                    </button>
                                </div>
                                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div x-data="{ show: false }">
                                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirmar Nueva Contraseña</label>
                                <div class="relative">
                                    <input :type="show ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-gray-50 pl-4 pr-10 py-2.5">
                                    <button type="button" @click="show = !show" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                                        <i class="ph text-lg" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rol y Permisos -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3 mb-6 flex items-center gap-2">
                            <i class="ph ph-shield-check text-blue-500"></i>
                            Rol y Permisos
                        </h3>

                        <div class="mb-8" x-data="{ currentRole: '{{ old('role', $user->role) }}' }">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Rol del Sistema <span class="text-red-500">*</span></label>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <label class="flex-1 relative cursor-pointer group" @click="currentRole = 'usuario'; togglePermisos(true)">
                                    <input type="radio" name="role" value="usuario" class="sr-only" {{ old('role', $user->role) == 'usuario' ? 'checked' : '' }}>
                                    <div class="p-4 border-2 rounded-xl transition"
                                         :class="currentRole === 'usuario' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white hover:bg-gray-50'">
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="font-bold transition" :class="currentRole === 'usuario' ? 'text-blue-700' : 'text-gray-900'">Usuario</div>
                                            <i class="ph-fill ph-check-circle text-blue-500 text-xl transition-opacity" :class="currentRole === 'usuario' ? 'opacity-100' : 'opacity-0'"></i>
                                        </div>
                                        <p class="text-xs text-gray-500">Acceso limitado según permisos configurados abajo.</p>
                                    </div>
                                </label>

                                <label class="flex-1 relative cursor-pointer group" @click="currentRole = 'admin'; togglePermisos(false)">
                                    <input type="radio" name="role" value="admin" class="sr-only" {{ old('role', $user->role) == 'admin' ? 'checked' : '' }}>
                                    <div class="p-4 border-2 rounded-xl transition"
                                         :class="currentRole === 'admin' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 bg-white hover:bg-gray-50'">
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="font-bold transition" :class="currentRole === 'admin' ? 'text-purple-700' : 'text-gray-900'">Administrador</div>
                                            <i class="ph-fill ph-check-circle text-purple-500 text-xl transition-opacity" :class="currentRole === 'admin' ? 'opacity-100' : 'opacity-0'"></i>
                                        </div>
                                        <p class="text-xs text-gray-500">Acceso total al sistema. Ignora configuraciones específicas.</p>
                                    </div>
                                </label>
                            </div>
                            @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div id="permisos-container" class="space-y-6 {{ old('role', $user->role) == 'admin' ? 'hidden' : '' }}">
                            <p class="text-sm font-semibold text-gray-700">Permisos Específicos</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @foreach($permisosAgrupados as $grupo => $permisos)
                                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                                        <h4 class="font-bold text-gray-800 text-sm mb-4">{{ $grupo }}</h4>
                                        <div class="space-y-3">
                                            @foreach($permisos as $codigo => $datos)
                                                @php $isDefault = $datos['default'] ?? false; @endphp
                                                <label class="flex items-start gap-3 group">
                                                    <div class="mt-0.5">
                                                        <input type="checkbox" name="permisos[]" value="{{ $codigo }}" 
                                                            class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 {{ $isDefault ? 'bg-gray-200 cursor-not-allowed' : 'cursor-pointer' }}"
                                                            {{ in_array($codigo, old('permisos', $user->permisos ?? [])) || $isDefault ? 'checked' : '' }}
                                                            {{ $isDefault ? 'disabled' : '' }}>
                                                        @if($isDefault)
                                                            <input type="hidden" name="permisos[]" value="{{ $codigo }}">
                                                        @endif
                                                    </div>
                                                    <div class="flex flex-col {{ $isDefault ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }}">
                                                        <span class="text-sm font-bold text-gray-800">{{ $datos['label'] }}</span>
                                                        <span class="text-xs text-gray-500 mt-0.5">{{ $datos['description'] }}</span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-5 bg-gray-50/80 border-t border-gray-100 flex justify-end gap-3 rounded-b-2xl">
                    <a href="{{ route('users.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                        Cancelar
                    </a>
                    <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 border border-transparent rounded-xl shadow-sm shadow-blue-500/30 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all flex items-center gap-2">
                        <i class="ph ph-floppy-disk"></i>
                        Actualizar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePermisos(show) {
            const container = document.getElementById('permisos-container');
            if (show) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }
    </script>
</x-app-layout>
