<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-2xl text-gray-800 tracking-tight">
                Historial de Radicados
            </h2>
            <p class="text-gray-500 text-sm mt-1">Consulta y gestiona todos los trámites del sistema.</p>
        </div>
    </x-slot>



    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        
        <!-- Toolbar / Filters -->
        <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-end justify-between gap-4">
            <form method="GET" action="{{ route('radicados.index') }}" class="flex flex-wrap items-end gap-4 flex-1">
                
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Buscar</label>
                    <div class="relative">
                        <i class="ph ph-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar radicado, remitente..." class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm w-full focus:border-blue-500 focus:ring-blue-500 bg-white shadow-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Estado</label>
                    <select name="estado" class="border-gray-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 bg-white shadow-sm py-2 px-4 pr-8">
                        <option value="">Todos</option>
                        <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="alerta" {{ request('estado') == 'alerta' ? 'selected' : '' }}>Alerta</option>
                        <option value="vencido" {{ request('estado') == 'vencido' ? 'selected' : '' }}>Vencido</option>
                        <option value="completado" {{ request('estado') == 'completado' ? 'selected' : '' }}>Completado</option>
                        <option value="anulado" {{ request('estado') == 'anulado' ? 'selected' : '' }}>Anulado</option>
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
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Desde</label>
                    <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="border border-gray-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 bg-white shadow-sm py-2 px-3">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Hasta</label>
                    <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="border border-gray-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 bg-white shadow-sm py-2 px-3">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="bg-blue-600 text-white hover:bg-blue-700 font-medium py-2 px-4 rounded-xl shadow-sm flex items-center gap-2 transition-all h-10">
                        <i class="ph ph-funnel"></i> Filtrar
                    </button>
                    @if(request()->hasAny(['search', 'estado', 'fecha_inicio', 'fecha_fin']) && (request('search') != '' || request('estado') != '' || request('fecha_inicio') != '' || request('fecha_fin') != ''))
                    <a href="{{ route('radicados.index') }}" class="bg-gray-100 text-gray-600 hover:bg-gray-200 font-medium py-2 px-4 rounded-xl flex items-center gap-2 transition-all h-10" title="Limpiar filtros">
                        <i class="ph ph-x"></i> Limpiar
                    </a>
                    @endif
                </div>
            </form>
            
            <div class="flex gap-2">
                <a href="{{ route('radicados.export', request()->all()) }}" class="bg-green-600 text-white hover:bg-green-700 font-medium py-2 px-4 rounded-xl shadow-sm flex items-center gap-2 transition-all h-10">
                    <i class="ph ph-microsoft-excel-logo"></i> Exportar
                </a>
                <a href="{{ route('radicados.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-5 rounded-xl shadow-md shadow-blue-500/30 flex items-center gap-2 transition-all text-sm h-10">
                    <i class="ph ph-plus-circle text-lg"></i> Nuevo Radicado
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-xs text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'numero_radicado', 'direction' => request('sort') == 'numero_radicado' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-800">
                                No. Radicado
                                @if(request('sort') == 'numero_radicado')
                                    <i class="ph ph-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4 font-semibold">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort', 'created_at') == 'created_at' && request('direction', 'desc') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-800">
                                Recepción
                                @if(request('sort', 'created_at') == 'created_at')
                                    <i class="ph ph-caret-{{ request('direction', 'desc') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4 font-semibold">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'remitente', 'direction' => request('sort') == 'remitente' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-800">
                                Remitente / Empresa
                                @if(request('sort') == 'remitente')
                                    <i class="ph ph-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4 font-semibold">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'responsable', 'direction' => request('sort') == 'responsable' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-800">
                                Responsable
                                @if(request('sort') == 'responsable')
                                    <i class="ph ph-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4 font-semibold">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'prioridad', 'direction' => request('sort') == 'prioridad' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-800">
                                Prioridad
                                @if(request('sort') == 'prioridad')
                                    <i class="ph ph-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
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
                        <th class="px-6 py-4 font-semibold">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'fecha_limite', 'direction' => request('sort') == 'fecha_limite' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-800">
                                Vence
                                @if(request('sort') == 'fecha_limite')
                                    <i class="ph ph-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm text-gray-700">
                    @forelse($radicados as $radicado)
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="px-6 py-4 font-bold text-gray-900">{{ $radicado->numero_radicado }}</td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $radicado->fecha_radicacion->format('d/m/Y') }}<br>
                                <span class="text-xs">{{ $radicado->hora_recepcion ? \Carbon\Carbon::parse($radicado->hora_recepcion)->format('H:i') : '' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                {{ Str::limit($radicado->remitente, 25) }}<br>
                                <span class="text-xs text-gray-500">{{ Str::limit($radicado->empresa, 25) }}</span>
                            </td>
                            <td class="px-6 py-4">{{ $radicado->responsable->nombre ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $radicado->prioridad }}</td>
                            <td class="px-6 py-4">
                                @if($radicado->estado == 'pendiente')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Pendiente
                                    </span>
                                @elseif($radicado->estado == 'alerta')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-700 border border-yellow-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span> En Alerta
                                    </span>
                                @elseif($radicado->estado == 'vencido')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Vencido
                                    </span>
                                @elseif($radicado->estado == 'completado')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span> Completado
                                    </span>
                                @elseif($radicado->estado == 'anulado')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-400 border border-gray-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span> Anulado
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $radicado->fecha_limite->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-right flex items-center justify-end gap-3">
                                <a href="{{ route('radicados.show', $radicado) }}" class="text-blue-600 hover:text-blue-900 transition" title="Ver Expediente">
                                    <i class="ph ph-eye text-lg"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="ph ph-files text-5xl mb-3 text-gray-300"></i>
                                    <p class="text-base font-medium text-gray-500">No se encontraron radicados</p>
                                    <p class="text-sm">Intenta ajustar los filtros o crea uno nuevo.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            {{ $radicados->links() }}
        </div>
    </div>
</x-app-layout>
