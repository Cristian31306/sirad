<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('tipos-tramites.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <i class="ph ph-arrow-left text-2xl"></i>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-800 tracking-tight">
                    Editar Tipo de Trámite
                </h2>
                <p class="text-gray-500 text-sm mt-1">Actualiza la información del trámite "{{ $tipoTramite->nombre }}".</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <form action="{{ route('tipos-tramites.update', $tipoTramite) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="p-8 sm:p-12">
                    <div class="space-y-8">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3 mb-6 flex items-center gap-2">
                                <i class="ph ph-file-text text-blue-500"></i>
                                Detalles del Trámite
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="col-span-2">
                                    <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-2">Nombre del Trámite <span class="text-red-500">*</span></label>
                                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $tipoTramite->nombre) }}" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-gray-50 px-4 py-2.5" placeholder="Ej. Derecho de Petición" required autofocus>
                                    @error('nombre')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="dias_habiles" class="block text-sm font-semibold text-gray-700 mb-2">Cantidad de Días <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="number" id="dias_habiles" name="dias_habiles" value="{{ old('dias_habiles', $tipoTramite->dias_habiles) }}" min="1" max="365" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-gray-50 px-4 py-2.5 pr-12" required>
                                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">días</span>
                                    </div>
                                    @error('dias_habiles')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="tipo_dias" class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Días <span class="text-red-500">*</span></label>
                                    <select id="tipo_dias" name="tipo_dias" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-gray-50 px-4 py-2.5" required>
                                        <option value="habiles" {{ old('tipo_dias', $tipoTramite->tipo_dias) == 'habiles' ? 'selected' : '' }}>Días Hábiles (L a V)</option>
                                        <option value="calendario" {{ old('tipo_dias', $tipoTramite->tipo_dias) == 'calendario' ? 'selected' : '' }}>Días Calendario (L a D)</option>
                                    </select>
                                    @error('tipo_dias')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-5 bg-gray-50/80 border-t border-gray-100 flex justify-end gap-3 rounded-b-2xl">
                    <a href="{{ route('tipos-tramites.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                        Cancelar
                    </a>
                    <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 border border-transparent rounded-xl shadow-sm shadow-blue-500/30 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all flex items-center gap-2">
                        <i class="ph ph-floppy-disk"></i>
                        Actualizar Trámite
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
