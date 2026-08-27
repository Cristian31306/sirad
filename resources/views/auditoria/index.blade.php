<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span class="bg-gray-200 px-2 rounded">Auditoría</span> del sistema
                </h2>
                <p class="text-gray-500 text-sm mt-1">Registro técnico de eventos, transacciones y modificaciones de datos</p>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="text-center">
                    <p class="text-xs text-gray-500 font-medium">Alertas hoy</p>
                    <p class="text-xl font-bold text-red-600">{{ $todayCount }}</p>
                </div>
                <div class="text-center border-l border-gray-200 pl-6">
                    <p class="text-xs text-gray-500 font-medium">Eventos totales</p>
                    <p class="text-xl font-bold text-gray-900">{{ number_format($totalCount) }}</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="mt-6 space-y-4">
        
        <!-- Toolbar / Filters -->
        <form method="GET" action="{{ route('auditoria.index') }}" class="flex flex-wrap items-center gap-3 w-full">
            <div class="flex-grow md:max-w-md relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar usuario, acción, módulo..." class="w-full pl-4 pr-10 py-2.5 rounded-xl border-gray-200 text-sm focus:border-gray-400 focus:ring-0 shadow-sm text-gray-700">
                <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <i class="ph ph-magnifying-glass text-lg"></i>
                </button>
            </div>
            
            <select name="accion_tipo" onchange="this.form.submit()" class="rounded-xl border-gray-200 py-2.5 pl-4 pr-8 text-sm text-gray-700 focus:border-gray-400 focus:ring-0 shadow-sm">
                <option value="">Todas las acciones</option>
                <option value="creacion" {{ request('accion_tipo') == 'creacion' ? 'selected' : '' }}>Creación</option>
                <option value="actualizacion" {{ request('accion_tipo') == 'actualizacion' ? 'selected' : '' }}>Actualización</option>
                <option value="eliminacion" {{ request('accion_tipo') == 'eliminacion' ? 'selected' : '' }}>Eliminación</option>
            </select>
            
            <select name="modelo" onchange="this.form.submit()" class="rounded-xl border-gray-200 py-2.5 pl-4 pr-8 text-sm text-gray-700 focus:border-gray-400 focus:ring-0 shadow-sm">
                <option value="">Todas las entidades</option>
                @foreach($modelos as $m)
                    <option value="{{ $m }}" {{ request('modelo') == $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
            
            <div class="relative flex items-center">
                <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" onchange="this.form.submit()" class="rounded-xl border-gray-200 py-2.5 pl-4 pr-3 text-sm text-gray-700 focus:border-gray-400 focus:ring-0 shadow-sm w-40">
            </div>
            
            <div class="ml-auto flex gap-2">
                @if(request()->anyFilled(['search', 'accion_tipo', 'modelo', 'fecha_inicio']))
                    <a href="{{ route('auditoria.index') }}" class="px-4 py-2.5 text-sm text-gray-500 hover:text-gray-700 font-medium transition" title="Limpiar filtros">
                        <i class="ph ph-x-circle text-lg"></i>
                    </a>
                @endif
                <a href="{{ route('auditoria.export', request()->query()) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-50 transition shadow-sm">
                    <i class="ph ph-download-simple"></i> Exportar
                </a>
            </div>
        </form>

        <!-- Data Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="text-xs text-gray-400 font-medium border-b border-gray-100">
                            <th class="px-6 py-4 w-40">Fecha y hora</th>
                            <th class="px-6 py-4">Actor / acción</th>
                            <th class="px-6 py-4">Entidad</th>
                            <th class="px-6 py-4">Tipo</th>
                            <th class="px-6 py-4 text-center">Firma HMAC</th>
                            <th class="px-6 py-4 w-12 text-center"></th>
                        </tr>
                    </thead>
                    @forelse($auditorias as $auditoria)
                        @php
                            $accionStr = strtolower($auditoria->accion);
                            $isCreation = str_contains($accionStr, 'creó') || str_contains($accionStr, 'creado');
                            $isDeletion = str_contains($accionStr, 'elimin') || str_contains($accionStr, 'borr');
                            $isAlert = str_contains($accionStr, 'alerta');
                            
                            $badgeText = 'Actualización';
                            $badgeColor = 'bg-gray-100 text-gray-600';
                            
                            if($isCreation) {
                                $badgeText = 'Creación';
                                $badgeColor = 'bg-green-100 text-green-700';
                            } elseif($isDeletion) {
                                $badgeText = 'Eliminación';
                                $badgeColor = 'bg-red-100 text-red-700';
                            } elseif($isAlert) {
                                $badgeText = 'Alerta';
                                $badgeColor = 'bg-amber-100 text-amber-800';
                            } else {
                                $badgeColor = 'bg-blue-50 text-blue-700';
                            }
                        @endphp
                        
                        <x-auditoria-row :auditoria="$auditoria" :badgeText="$badgeText" :badgeColor="$badgeColor" />
                        
                    @empty
                        <tbody>
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="bg-gray-50 p-4 rounded-full mb-3">
                                            <i class="ph ph-magnifying-glass text-4xl text-gray-300"></i>
                                        </div>
                                        <p class="font-medium text-gray-500">No hay eventos que coincidan</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
                </table>
            </div>
        </div>
        
        <!-- Paginator -->
        <div class="flex items-center justify-between text-sm text-gray-500 py-2">
            <div>
                Mostrando {{ $auditorias->firstItem() ?? 0 }} de {{ $auditorias->total() }} registros
            </div>
            <div>
                {{ $auditorias->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</x-app-layout>
