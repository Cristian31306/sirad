<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 tracking-tight">
            Auditoría del Sistema
        </h2>
        <p class="text-gray-500 text-sm mt-1">Registro de las actividades de los usuarios en el sistema.</p>
    </x-slot>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        
        <!-- Toolbar / Filters -->
        <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-end justify-between gap-4">
            <form method="GET" action="{{ route('auditoria.index') }}" class="flex flex-wrap items-end gap-4 flex-1">
                
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Buscar</label>
                    <div class="relative">
                        <i class="ph ph-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Usuario, acción, módulo..." class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm w-full focus:border-blue-500 focus:ring-blue-500 bg-white shadow-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Mostrar</label>
                    <select name="per_page" class="border-gray-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 bg-white shadow-sm py-2 px-4 pr-8">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 por página</option>
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 por página</option>
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
                        <th class="px-6 py-4 font-semibold">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort', 'created_at') == 'created_at' && request('direction', 'desc') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-800">
                                Fecha / Hora
                                @if(request('sort', 'created_at') == 'created_at')
                                    <i class="ph ph-caret-{{ request('direction', 'desc') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4 font-semibold">Usuario</th>
                        <th class="px-6 py-4 font-semibold">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'accion', 'direction' => request('sort') == 'accion' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-800">
                                Acción
                                @if(request('sort') == 'accion')
                                    <i class="ph ph-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4 font-semibold">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'modelo', 'direction' => request('sort') == 'modelo' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-800">
                                Módulo
                                @if(request('sort') == 'modelo')
                                    <i class="ph ph-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4 font-semibold">Detalles</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm text-gray-700">
                    @forelse($auditorias as $auditoria)
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-xs">{{ $auditoria->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-6 py-4 font-medium">{{ optional($auditoria->user)->name ?? 'Sistema' }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $auditoria->accion }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $auditoria->modelo }} #{{ $auditoria->modelo_id }}</td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                <div class="max-w-xs overflow-hidden text-ellipsis whitespace-nowrap" title="{{ json_encode($auditoria->detalles) }}">
                                    {{ json_encode($auditoria->detalles) }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="ph ph-clock-counter-clockwise text-4xl mb-2 text-gray-300"></i>
                                    <p>No hay registros de auditoría.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            {{ $auditorias->links() }}
        </div>
    </div>
</x-app-layout>
