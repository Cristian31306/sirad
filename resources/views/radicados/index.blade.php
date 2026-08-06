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

        @php
            $estadoLabels = [
                'pendiente'  => 'Pendiente',
                'alerta'     => 'Alerta',
                'vencido'    => 'Vencido',
                'completado' => 'Completado',
                'anulado'    => 'Anulado',
            ];

            // Filtros del panel avanzado (todo menos el buscador, que ya está siempre visible)
            $panelKeys = ['estado', 'prioridad', 'fecha_inicio', 'fecha_fin'];
            $panelFilterCount = collect($panelKeys)->filter(fn ($k) => request($k))->count();

            // Chips de todos los filtros activos (incluyendo búsqueda) para poder quitarlos uno a uno
            $activeFilters = collect([
                'search'       => request('search') ? 'Buscar: "' . request('search') . '"' : null,
                'estado'       => request('estado') ? 'Estado: ' . ($estadoLabels[request('estado')] ?? request('estado')) : null,
                'prioridad'    => request('prioridad') ? 'Prioridad: ' . request('prioridad') : null,
                'fecha_inicio' => request('fecha_inicio') ? 'Desde: ' . \Carbon\Carbon::parse(request('fecha_inicio'))->format('d/m/Y') : null,
                'fecha_fin'    => request('fecha_fin') ? 'Hasta: ' . \Carbon\Carbon::parse(request('fecha_fin'))->format('d/m/Y') : null,
            ])->filter();
        @endphp

        <!-- Toolbar -->
        <div class="border-b border-gray-100">
            <div class="p-4 flex items-center gap-2">

                <form method="GET" action="{{ route('radicados.index') }}" id="filtrosForm" class="flex items-center gap-2 flex-1 min-w-0">

                    <!-- Buscador: único control siempre visible -->
                    <div class="relative flex-1 min-w-0">
                        <i class="ph ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                               placeholder="Buscar por radicado, remitente o empresa..."
                               autocomplete="off"
                               class="pl-10 pr-9 py-2.5 w-full rounded-xl text-sm border border-transparent bg-gray-50 focus:bg-white focus:border-blue-300 focus:ring-4 focus:ring-blue-500/10 transition-all">
                        @if(request('search'))
                            <a href="{{ route('radicados.index', request()->except('search')) }}"
                               title="Borrar búsqueda"
                               class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="ph ph-x-circle"></i>
                            </a>
                        @endif
                    </div>

                    <!-- Filtros avanzados: escondidos por defecto -->
                    <details class="relative shrink-0" {{ $panelFilterCount > 0 ? 'open' : '' }}>
                        <summary class="list-none cursor-pointer select-none flex items-center gap-1.5 h-[42px] px-3.5 rounded-xl text-sm font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors [&::-webkit-details-marker]:hidden">
                            <i class="ph ph-sliders-horizontal"></i>
                            <span class="hidden sm:inline">Filtros</span>
                            @if($panelFilterCount > 0)
                                <span class="bg-blue-600 text-white text-[11px] font-semibold rounded-full w-4.5 h-4.5 min-w-[18px] flex items-center justify-center leading-none">
                                    {{ $panelFilterCount }}
                                </span>
                            @endif
                        </summary>

                        <div class="absolute right-0 mt-2 w-[320px] sm:w-[340px] bg-white border border-gray-100 shadow-xl rounded-2xl p-4 z-20 space-y-4">
                            <div>
                                <label for="estado" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Estado</label>
                                <div class="relative">
                                    <select id="estado" name="estado" data-autosubmit
                                            class="appearance-none w-full border border-gray-200 rounded-xl text-sm bg-white py-2 pl-3 pr-8 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Todos</option>
                                        @foreach($estadoLabels as $value => $label)
                                            <option value="{{ $value }}" {{ request('estado') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                                </div>
                            </div>

                            <div>
                                <label for="prioridad" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Prioridad</label>
                                <div class="relative">
                                    <select id="prioridad" name="prioridad" data-autosubmit
                                            class="appearance-none w-full border border-gray-200 rounded-xl text-sm bg-white py-2 pl-3 pr-8 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Todas</option>
                                        <option value="Alta" {{ request('prioridad') == 'Alta' ? 'selected' : '' }}>Alta</option>
                                        <option value="Media" {{ request('prioridad') == 'Media' ? 'selected' : '' }}>Media</option>
                                        <option value="Baja" {{ request('prioridad') == 'Baja' ? 'selected' : '' }}>Baja</option>
                                    </select>
                                    <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Rango de fechas</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="{{ request('fecha_inicio') }}"
                                           data-autosubmit title="Desde"
                                           class="w-full border border-gray-200 rounded-xl text-sm py-2 px-2 text-gray-700 focus:border-blue-500 focus:ring-blue-500">
                                    <input type="date" id="fecha_fin" name="fecha_fin" value="{{ request('fecha_fin') }}"
                                           min="{{ request('fecha_inicio') }}" data-autosubmit title="Hasta"
                                           class="w-full border border-gray-200 rounded-xl text-sm py-2 px-2 text-gray-700 focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-1 border-t border-gray-50">
                                <a href="{{ route('radicados.index', request()->except($panelKeys)) }}"
                                   class="text-xs font-medium text-gray-400 hover:text-gray-600">
                                    Limpiar filtros
                                </a>
                                <button type="submit" id="aplicarBtn"
                                        class="bg-blue-600 text-white hover:bg-blue-700 font-medium py-1.5 px-3.5 rounded-lg text-xs transition-all">
                                    Aplicar
                                </button>
                            </div>
                        </div>
                    </details>
                </form>

                <div class="w-px h-6 bg-gray-100 shrink-0"></div>

                <a href="{{ route('radicados.export', request()->all()) }}"
                   title="Exportar a Excel"
                   class="shrink-0 h-[42px] w-[42px] flex items-center justify-center rounded-xl border border-gray-200 text-green-600 hover:bg-green-50 transition-colors">
                    <i class="ph ph-microsoft-excel-logo text-lg"></i>
                </a>

                <a href="{{ route('radicados.create') }}"
                   class="shrink-0 bg-blue-600 hover:bg-blue-700 text-white font-semibold h-[42px] px-4 rounded-xl shadow-md shadow-blue-500/25 flex items-center gap-2 transition-all text-sm">
                    <i class="ph ph-plus-circle text-lg"></i>
                    <span class="hidden sm:inline">Nuevo</span>
                </a>
            </div>

            <!-- Chips de filtros activos -->
            @if($activeFilters->isNotEmpty())
                <div class="px-4 pb-3 flex flex-wrap items-center gap-1.5">
                    @foreach($activeFilters as $key => $label)
                        <a href="{{ route('radicados.index', request()->except($key)) }}"
                           class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 text-xs font-medium pl-2.5 pr-1.5 py-1 rounded-full border border-blue-100 hover:bg-blue-100 transition-colors">
                            {{ $label }}
                            <i class="ph ph-x text-xs"></i>
                        </a>
                    @endforeach
                    <a href="{{ route('radicados.index') }}" class="text-xs text-gray-400 hover:text-gray-600 px-1.5">
                        Limpiar todo
                    </a>
                </div>
            @endif
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
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div>
                {{ $radicados->links() }}
            </div>
            <form method="GET" action="{{ route('radicados.index') }}" id="perPageForm" class="flex items-center gap-2 text-sm text-gray-500">
                @foreach(request()->except('per_page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <label for="per_page">Mostrar</label>
                <select id="per_page" name="per_page" onchange="document.getElementById('perPageForm').submit()"
                        class="border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 bg-white py-1.5 px-2">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>
                <span>por página</span>
            </form>
        </div>
    </div>

    <script>
        // Mejora progresiva: auto-envío al cambiar select/fecha, con debounce en el buscador.
        // Todo sigue funcionando sin JS (botón Aplicar + submit normal del formulario).
        (function () {
            const form = document.getElementById('filtrosForm');
            const searchInput = document.getElementById('search');
            let debounceTimer;

            function submitForm() {
                form.submit();
            }

            form.querySelectorAll('[data-autosubmit]').forEach(function (el) {
                el.addEventListener('change', submitForm);
            });

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(submitForm, 600);
                });
            }

            // Cierra el panel de filtros al hacer clic fuera de él
            document.addEventListener('click', function (e) {
                const details = form.querySelector('details[open]');
                if (details && !details.contains(e.target)) {
                    details.removeAttribute('open');
                }
            });
        })();
    </script>
</x-app-layout>
