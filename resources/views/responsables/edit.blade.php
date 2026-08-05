<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('responsables.index') }}" class="text-gray-400 hover:text-gray-600">Responsables</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-500 text-sm font-medium">Editar</span>
        </div>
        <h2 class="font-bold text-2xl text-gray-800 tracking-tight mt-2">
            Editar Responsable
        </h2>
    </x-slot>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative mb-6 flex items-start gap-3" role="alert">
            <i class="ph ph-warning-circle text-xl text-red-500 mt-0.5"></i>
            <div>
                <strong class="font-bold block mb-1">Por favor corrige los siguientes errores:</strong>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="max-w-xl mx-auto">
        <form action="{{ route('responsables.update', $responsable) }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            @csrf
            @method('PATCH')
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre Completo <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" value="{{ old('nombre', $responsable->nombre) }}" class="w-full border-gray-300 rounded-xl focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Especialidad (Opcional)</label>
                    <input type="text" list="especialidades" name="especialidad" value="{{ old('especialidad', $responsable->especialidad) }}" placeholder="Ej. Abogado, Ingeniero..." class="w-full border-gray-300 rounded-xl focus:border-blue-500 focus:ring-blue-500">
                    <datalist id="especialidades">
                        @foreach($especialidades as $esp)
                            <option value="{{ $esp }}">
                        @endforeach
                    </datalist>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Correo Electrónico <span class="text-red-500">*</span></label>
                    <input type="email" name="correo" value="{{ old('correo', $responsable->correo) }}" class="w-full border-gray-300 rounded-xl focus:border-blue-500 focus:ring-blue-500" required>
                </div>

                <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-50">
                    <a href="{{ route('responsables.index') }}" class="text-gray-500 hover:text-gray-700 font-medium">Cancelar</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md shadow-blue-500/30 transition">
                        Actualizar Datos
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
