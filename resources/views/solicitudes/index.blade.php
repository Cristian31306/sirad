<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 tracking-tight">
            Solicitudes de Edición
        </h2>
        <p class="text-gray-500 text-sm mt-1">Aprueba o rechaza los cambios sugeridos por los usuarios a los radicados.</p>
    </x-slot>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        
        <!-- Toolbar / Filters -->
        <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-end justify-between gap-4">
            <form method="GET" action="{{ route('solicitudes.index') }}" class="flex flex-wrap items-end gap-4 flex-1">
                
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Buscar</label>
                    <div class="relative">
                        <i class="ph ph-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="No. de Radicado..." class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm w-full focus:border-blue-500 focus:ring-blue-500 bg-white shadow-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Estado</label>
                    <select name="estado" class="border-gray-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 bg-white shadow-sm py-2 px-4 pr-8">
                        <option value="">Todos</option>
                        <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="aprobada" {{ request('estado') == 'aprobada' ? 'selected' : '' }}>Aprobada</option>
                        <option value="rechazada" {{ request('estado') == 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Mostrar</label>
                    <select name="per_page" class="border-gray-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 bg-white shadow-sm py-2 px-4 pr-8">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 por página</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 por página</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 por página</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-medium py-2 px-4 rounded-xl shadow-sm flex items-center gap-2 transition-all h-10">
                        <i class="ph ph-funnel"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold">Radicado</th>
                        <th class="px-6 py-4 font-semibold">Solicitante</th>
                        <th class="px-6 py-4 font-semibold">Nuevos Datos (JSON)</th>
                        <th class="px-6 py-4 font-semibold">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort', 'created_at') == 'created_at' && request('direction', 'desc') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-800">
                                Fecha
                                @if(request('sort', 'created_at') == 'created_at')
                                    <i class="ph ph-caret-{{ request('direction', 'desc') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4 font-semibold">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'estado', 'direction' => request('sort') == 'estado' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-800">
                                Estado
                                @if(request('sort') == 'estado')
                                    <i class="ph ph-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4 font-semibold text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm text-gray-700">
                    @forelse($solicitudes as $solicitud)
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="px-6 py-4 font-bold text-gray-900">
                                <a href="{{ route('radicados.show', $solicitud->radicado_id) }}"
                                    class="text-blue-600 hover:underline">
                                    {{ optional($solicitud->radicado)->numero_radicado }}
                                </a>
                            </td>
                            <td class="px-6 py-4">{{ optional($solicitud->user)->name }}</td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                <pre
                                    class="bg-gray-100 p-2 rounded max-w-xs overflow-auto">{{ json_encode($solicitud->datos_propuestos, JSON_PRETTY_PRINT) }}</pre>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $solicitud->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">
                                @if($solicitud->estado == 'pendiente')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-700 border border-yellow-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span> Pendiente
                                    </span>
                                @elseif($solicitud->estado == 'aprobada')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Aprobada
                                    </span>
                                @elseif($solicitud->estado == 'rechazada')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rechazada
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right flex items-center justify-end gap-2">
                                @if($solicitud->estado == 'pendiente')
                                    <form action="{{ route('solicitudes.update', $solicitud) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="action" value="aprobar">
                                        <button type="submit"
                                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition">
                                            Aprobar
                                        </button>
                                    </form>
                                    <form action="{{ route('solicitudes.update', $solicitud) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="action" value="rechazar">
                                        <button type="submit"
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition">
                                            Rechazar
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-xs font-medium uppercase tracking-wider">Gestionada</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="ph ph-check-circle text-4xl mb-2 text-green-400"></i>
                                    <p>No se encontraron solicitudes.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            {{ $solicitudes->links() }}
        </div>
    </div>
</x-app-layout>