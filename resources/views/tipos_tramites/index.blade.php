<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 tracking-tight">
                    Tipos de Trámite
                </h2>
                <p class="text-gray-500 text-sm mt-1">Configuración de los diferentes trámites y sus días hábiles.</p>
            </div>
        </div>
    </x-slot>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        
        <!-- Toolbar (Search, Filter, Create) -->
        <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-end justify-between gap-4">
            <form action="{{ route('tipos-tramites.index') }}" method="GET" class="flex flex-wrap items-end gap-4 flex-1">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Buscar</label>
                    <div class="relative">
                        <i class="ph ph-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre del trámite..." class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm w-full focus:border-blue-500 focus:ring-blue-500 bg-white shadow-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Mostrar</label>
                    <select name="per_page" class="border-gray-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 bg-white shadow-sm py-2 px-4 pr-8">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 por página</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 por página</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 por página</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-medium py-2 px-4 rounded-xl shadow-sm flex items-center gap-2 transition-all h-10">
                        <i class="ph ph-funnel"></i> Filtrar
                    </button>
                </div>
            </form>
            
            <div>
                <a href="{{ route('tipos-tramites.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-5 rounded-xl shadow-md shadow-blue-500/30 flex items-center gap-2 transition-all text-sm h-10">
                    <i class="ph ph-plus-circle text-lg"></i> Nuevo Trámite
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-xs text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'nombre', 'direction' => request('sort', 'nombre') == 'nombre' && request('direction', 'asc') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-800">
                                Nombre
                                @if(request('sort', 'nombre') == 'nombre')
                                    <i class="ph ph-caret-{{ request('direction', 'asc') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4 font-semibold">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'dias_habiles', 'direction' => request('sort') == 'dias_habiles' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-800">
                                Días Hábiles
                                @if(request('sort') == 'dias_habiles')
                                    <i class="ph ph-caret-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4 font-semibold text-center">Estado</th>
                        <th class="px-6 py-4 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm text-gray-700">
                    @forelse($tipos as $tipo)
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="px-6 py-4 font-bold text-gray-900">{{ $tipo->nombre }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $tipo->dias_habiles }} días</td>
                            <td class="px-6 py-4 text-center">
                                @if($tipo->activo)
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Activo</span>
                                @else
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Suspendido</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('tipos-tramites.edit', $tipo) }}" class="text-blue-500 hover:text-blue-700 transition" title="Editar">
                                        <i class="ph ph-pencil-simple text-lg"></i>
                                    </a>
                                    
                                    <form action="{{ route('tipos-tramites.toggle', $tipo) }}" method="POST" onsubmit="return confirm('¿Estás seguro de {{ $tipo->activo ? 'suspender' : 'activar' }} este tipo de trámite?');" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="{{ $tipo->activo ? 'text-orange-500 hover:text-orange-700' : 'text-green-500 hover:text-green-700' }} transition" title="{{ $tipo->activo ? 'Suspender' : 'Activar' }}">
                                            <i class="ph {{ $tipo->activo ? 'ph-pause-circle' : 'ph-play-circle' }} text-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="ph ph-file-dashed text-4xl mb-2 text-gray-300"></i>
                                    <p>No se encontraron tipos de trámites.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            {{ $tipos->links() }}
        </div>
    </div>
</x-app-layout>
