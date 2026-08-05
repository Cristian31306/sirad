<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 tracking-tight flex items-center gap-2">
                    Días Festivos y No Laborables <i class="ph-fill ph-crown text-yellow-500 text-xl" title="Administración exclusiva del Jefe"></i>
                </h2>
                <p class="text-gray-500 text-sm mt-1">Gestione las fechas que no se considerarán hábiles matemáticamente.</p>
            </div>
            <a href="{{ route('festivos.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-5 rounded-xl shadow-md shadow-blue-500/30 flex items-center gap-2 transition-all">
                <i class="ph ph-plus-circle text-lg"></i> Nuevo Festivo
            </a>
        </div>
    </x-slot>



    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden max-w-4xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-xs text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold w-1/4">Fecha</th>
                        <th class="px-6 py-4 font-semibold w-1/2">Motivo</th>
                        <th class="px-6 py-4 font-semibold text-right w-1/4">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm text-gray-700">
                    @forelse($festivos as $festivo)
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $festivo->fecha->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">{{ $festivo->descripcion }}</td>
                            <td class="px-6 py-4 text-right flex items-center justify-end gap-2">
                                <a href="{{ route('festivos.edit', $festivo) }}" class="p-2 text-gray-400 hover:text-blue-600 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow transition" title="Editar">
                                    <i class="ph ph-pencil-simple text-lg"></i>
                                </a>
                                <form action="{{ route('festivos.destroy', $festivo) }}" method="POST" onsubmit="return confirm('¿Eliminar este festivo?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-600 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow transition" title="Eliminar">
                                        <i class="ph ph-trash text-lg"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="ph ph-calendar-blank text-5xl mb-3 text-gray-300"></i>
                                    <p class="text-base font-medium text-gray-500">No hay festivos registrados</p>
                                    <p class="text-sm">Agregue fechas para excluirlas del conteo de días hábiles.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
