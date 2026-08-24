<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 tracking-tight">
                    Solicitudes de Edición
                </h2>
                <p class="text-gray-500 text-sm mt-1">Revisa las solicitudes de modificación antes de que se apliquen a los radicados.</p>
            </div>
            <div class="hidden sm:flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                    <i class="ph ph-git-diff text-base"></i> Comparador Visual Antes vs Después
                </span>
            </div>
        </div>
    </x-slot>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ 
        modalOpen: false, 
        selectedItem: null,
        openModal(data) {
            this.selectedItem = data;
            this.modalOpen = true;
        }
    }">
        
        <!-- Toolbar / Filters -->
        <div class="p-4 sm:p-6 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-end justify-between gap-4">
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
                        <option value="">Todos los Estados</option>
                        <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendientes de Aprobación</option>
                        <option value="aprobada" {{ request('estado') == 'aprobada' ? 'selected' : '' }}>Aprobadas</option>
                        <option value="rechazada" {{ request('estado') == 'rechazada' ? 'selected' : '' }}>Rechazadas</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Mostrar</label>
                    <select name="per_page" class="border-gray-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500 bg-white shadow-sm py-2 px-4 pr-8">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 registros</option>
                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25 registros</option>
                        <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50 registros</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-5 rounded-xl shadow-sm shadow-blue-500/20 flex items-center gap-2 transition-all h-10">
                        <i class="ph ph-funnel"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 text-xs text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold">Radicado</th>
                        <th class="px-6 py-4 font-semibold">Solicitante</th>
                        <th class="px-6 py-4 font-semibold">Resumen de Cambios</th>
                        <th class="px-6 py-4 font-semibold">Motivo del Solicitante</th>
                        <th class="px-6 py-4 font-semibold">Fecha</th>
                        <th class="px-6 py-4 font-semibold">Estado</th>
                        <th class="px-6 py-4 font-semibold text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($solicitudes as $solicitud)
                        @php
                            $datos = $solicitud->datos_propuestos ?? [];
                            $rad = $solicitud->radicado;

                            // Nombres propuestos
                            $nombresRespPropuestos = [];
                            if (isset($datos['responsables']) && is_array($datos['responsables'])) {
                                foreach ($datos['responsables'] as $idResp) {
                                    if (isset($responsables[$idResp])) {
                                        $nombresRespPropuestos[] = $responsables[$idResp]->nombre;
                                    }
                                }
                            }
                            $nombresActuales = $rad ? $rad->responsables->pluck('nombre')->toArray() : [];

                            // Detección de cambios específicos
                            $cambioEmpresa = $rad && isset($datos['empresa']) && $rad->empresa !== $datos['empresa'];
                            $cambioAsunto = $rad && isset($datos['asunto']) && $rad->asunto !== $datos['asunto'];
                            $cambioMedio = $rad && isset($datos['medio']) && $rad->medio !== $datos['medio'];
                            $cambioPrioridad = $rad && isset($datos['prioridad']) && $rad->prioridad !== $datos['prioridad'];
                            $cambioResp = $nombresActuales != $nombresRespPropuestos;

                            $comparisonData = [
                                'id' => $solicitud->id,
                                'numero_radicado' => $rad ? $rad->numero_radicado : 'N/A',
                                'solicitante_nombre' => optional($solicitud->user)->name ?? 'Usuario',
                                'solicitante_email' => optional($solicitud->user)->email ?? '',
                                'fecha_solicitud' => $solicitud->created_at->format('d/m/Y h:i A'),
                                'motivo' => $datos['observaciones'] ?? 'Sin observación',
                                'estado' => $solicitud->estado,
                                'approve_url' => route('solicitudes.update', $solicitud),
                                'fields' => [
                                    [
                                        'label' => 'Asunto',
                                        'actual' => $rad ? $rad->asunto : 'N/A',
                                        'propuesto' => $datos['asunto'] ?? 'N/A',
                                        'changed' => $cambioAsunto,
                                    ],
                                    [
                                        'label' => 'Empresa / Entidad',
                                        'actual' => ($rad && $rad->empresa) ? $rad->empresa : '(Ninguna)',
                                        'propuesto' => !empty($datos['empresa']) ? $datos['empresa'] : '(Ninguna)',
                                        'changed' => $cambioEmpresa,
                                    ],
                                    [
                                        'label' => 'Prioridad',
                                        'actual' => $rad ? $rad->prioridad : 'N/A',
                                        'propuesto' => $datos['prioridad'] ?? 'N/A',
                                        'changed' => $cambioPrioridad,
                                    ],
                                    [
                                        'label' => 'Medio de Recepción',
                                        'actual' => $rad ? $rad->medio : 'N/A',
                                        'propuesto' => $datos['medio'] ?? 'N/A',
                                        'changed' => $cambioMedio,
                                    ],
                                    [
                                        'label' => 'Responsables Asignados',
                                        'actual' => !empty($nombresActuales) ? implode(', ', $nombresActuales) : 'Sin asignar',
                                        'propuesto' => !empty($nombresRespPropuestos) ? implode(', ', $nombresRespPropuestos) : 'Sin asignar',
                                        'changed' => $cambioResp,
                                    ],
                                ]
                            ];
                        @endphp
                        <tr class="hover:bg-blue-50/20 transition">
                            <!-- Radicado -->
                            <td class="px-6 py-4 font-bold text-gray-900 whitespace-nowrap">
                                @if($rad)
                                    <a href="{{ route('radicados.show', $rad->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1.5">
                                        {{ $rad->numero_radicado }}
                                        <i class="ph ph-arrow-square-out text-xs"></i>
                                    </a>
                                    <span class="text-xs text-gray-500 font-normal block">{{ optional($rad->tipoTramite)->nombre }}</span>
                                @else
                                    <span class="text-gray-400 italic">Radicado Eliminado</span>
                                @endif
                            </td>

                            <!-- Solicitante -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                                        {{ substr(optional($solicitud->user)->name ?? 'U', 0, 2) }}
                                    </div>
                                    <div>
                                        <span class="font-semibold text-gray-900 block leading-tight text-xs">{{ optional($solicitud->user)->name }}</span>
                                        <span class="text-[11px] text-gray-400">{{ optional($solicitud->user)->email }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Resumen de Cambios (Badges Claros) -->
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @if($cambioAsunto)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-md">
                                            <i class="ph-bold ph-pencil-simple"></i> Asunto
                                        </span>
                                    @endif
                                    @if($cambioResp)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded-md">
                                            <i class="ph-bold ph-users"></i> Responsables ({{ count($nombresRespPropuestos) }})
                                        </span>
                                    @endif
                                    @if($cambioPrioridad)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200 px-2 py-0.5 rounded-md">
                                            <i class="ph-bold ph-flag"></i> Prioridad: {{ $datos['prioridad'] }}
                                        </span>
                                    @endif
                                    @if($cambioEmpresa)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200 px-2 py-0.5 rounded-md">
                                            <i class="ph-bold ph-buildings"></i> Empresa
                                        </span>
                                    @endif
                                    @if($cambioMedio)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-gray-100 text-gray-700 border border-gray-200 px-2 py-0.5 rounded-md">
                                            <i class="ph-bold ph-paper-plane-tilt"></i> Medio
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Motivo del Solicitante -->
                            <td class="px-6 py-4 max-w-xs">
                                <p class="text-xs text-gray-600 truncate" title="{{ $datos['observaciones'] ?? 'Sin motivo especificado' }}">
                                    {{ !empty($datos['observaciones']) ? $datos['observaciones'] : 'Sin observación registrada' }}
                                </p>
                            </td>

                            <!-- Fecha -->
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                <div>{{ $solicitud->created_at->format('d/m/Y') }}</div>
                                <span class="text-[11px] text-gray-400">{{ $solicitud->created_at->format('h:i A') }}</span>
                            </td>

                            <!-- Estado -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($solicitud->estado == 'pendiente')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Pendiente
                                    </span>
                                @elseif($solicitud->estado == 'aprobada')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="ph-bold ph-check text-xs"></i> Aprobada
                                    </span>
                                @elseif($solicitud->estado == 'rechazada')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i class="ph-bold ph-x text-xs"></i> Rechazada
                                    </span>
                                @endif
                            </td>

                            <!-- Acción: Botón para Abrir Comparador -->
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <button type="button" @click="openModal({{ json_encode($comparisonData) }})" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-600 hover:text-white shadow-sm transition group">
                                    <i class="ph-bold ph-git-diff text-sm group-hover:scale-110 transition"></i>
                                    <span>Revisar y Decidir</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-3xl mb-3">
                                        <i class="ph ph-check-circle"></i>
                                    </div>
                                    <h4 class="font-bold text-gray-700 text-base mb-1">Sin solicitudes pendientes</h4>
                                    <p class="text-xs text-gray-500 max-w-sm">No hay solicitudes de modificación pendientes por revisar en este momento.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($solicitudes->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                {{ $solicitudes->links() }}
            </div>
        @endif

        <!-- MODAL INTERACTIVO DE COMPARACIÓN (ANTES VS DESPUÉS) -->
        <div x-show="modalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title-diff" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
                <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="modalOpen = false" aria-hidden="true"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-gray-100">
                    
                    <!-- Header del Modal -->
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-5 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl">
                                <i class="ph-bold ph-git-diff"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold leading-tight" x-text="'Revisión de Cambios: ' + (selectedItem ? selectedItem.numero_radicado : '')"></h3>
                                <p class="text-xs text-blue-100 mt-0.5" x-text="'Solicitado por ' + (selectedItem ? selectedItem.solicitante_nombre : '') + ' el ' + (selectedItem ? selectedItem.fecha_solicitud : '')"></p>
                            </div>
                        </div>
                        <button type="button" @click="modalOpen = false" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition">
                            <i class="ph-bold ph-x text-lg"></i>
                        </button>
                    </div>

                    <!-- Contenido del Modal -->
                    <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                        
                        <!-- Tarjeta de Motivo de Solicitud -->
                        <div class="bg-amber-50/70 border border-amber-200/80 rounded-xl p-4 flex items-start gap-3">
                            <i class="ph-fill ph-chat-centered-text text-amber-600 text-xl mt-0.5"></i>
                            <div>
                                <span class="font-bold text-xs text-amber-900 uppercase tracking-wider block">Motivo indicado por el solicitante:</span>
                                <p class="text-sm text-amber-800 font-medium mt-1 leading-relaxed" x-text="selectedItem ? selectedItem.motivo : ''"></p>
                            </div>
                        </div>

                        <!-- Comparativa Lado a Lado -->
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <i class="ph-bold ph-arrows-left-right text-blue-600"></i>
                                Comparación de Datos (Antes vs Después)
                            </h4>

                            <div class="border border-gray-200 rounded-xl overflow-hidden divide-y divide-gray-100 text-sm">
                                <template x-for="(field, index) in (selectedItem ? selectedItem.fields : [])" :key="index">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 p-3.5 items-center" :class="field.changed ? 'bg-amber-50/30' : 'bg-white'">
                                        
                                        <!-- Nombre del Campo -->
                                        <div class="md:col-span-3 flex items-center gap-2">
                                            <span class="font-bold text-xs uppercase tracking-wider text-gray-700" x-text="field.label"></span>
                                            <span x-show="field.changed" class="inline-block w-2 h-2 rounded-full bg-amber-500" title="Campo modificado"></span>
                                        </div>

                                        <!-- Valor Actual (Antes) -->
                                        <div class="md:col-span-4 bg-gray-50 border border-gray-200/80 rounded-lg p-2.5 text-xs text-gray-600">
                                            <span class="block text-[10px] font-bold text-gray-400 uppercase mb-0.5">Valor Actual:</span>
                                            <span class="break-words font-medium" x-text="field.actual"></span>
                                        </div>

                                        <!-- Flecha -->
                                        <div class="md:col-span-1 text-center hidden md:block text-gray-400">
                                            <i class="ph-bold ph-arrow-right" :class="field.changed ? 'text-amber-500 font-bold' : 'text-gray-300'"></i>
                                        </div>

                                        <!-- Valor Propuesto (Después) -->
                                        <div class="md:col-span-4 rounded-lg p-2.5 text-xs" :class="field.changed ? 'bg-emerald-50 border border-emerald-300 text-emerald-900 font-semibold' : 'bg-gray-50 border border-gray-200 text-gray-600'">
                                            <div class="flex items-center justify-between mb-0.5">
                                                <span class="block text-[10px] font-bold uppercase" :class="field.changed ? 'text-emerald-700' : 'text-gray-400'">Valor Propuesto:</span>
                                                <span x-show="field.changed" class="text-[10px] bg-emerald-200/80 text-emerald-800 px-1.5 py-0.2 rounded font-bold">Modificado</span>
                                            </div>
                                            <span class="break-words" x-text="field.propuesto"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>

                    <!-- Footer / Botones de Decisión -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 shadow-sm transition">
                            Cerrar
                        </button>

                        <div class="flex items-center gap-3" x-show="selectedItem && selectedItem.estado === 'pendiente'">
                            <!-- Rechazar -->
                            <form :action="selectedItem ? selectedItem.approve_url : '#'" method="POST" onsubmit="return confirm('¿Confirmas el RECHAZO de esta solicitud?');">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="action" value="rechazar">
                                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded-xl text-sm font-bold shadow-md shadow-rose-500/20 flex items-center gap-2 transition">
                                    <i class="ph-bold ph-x"></i> Rechazar Solicitud
                                </button>
                            </form>

                            <!-- Aprobar -->
                            <form :action="selectedItem ? selectedItem.approve_url : '#'" method="POST" onsubmit="return confirm('¿Confirmas la APROBACIÓN de estos cambios? Se actualizará el radicado inmediatamente.');">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="action" value="aprobar">
                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-md shadow-emerald-500/20 flex items-center gap-2 transition">
                                    <i class="ph-bold ph-check"></i> Aprobar y Aplicar Cambios
                                </button>
                            </form>
                        </div>

                        <div x-show="selectedItem && selectedItem.estado !== 'pendiente'">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-500 bg-gray-200 px-3 py-1.5 rounded-xl">
                                <i class="ph ph-lock-key"></i> Esta solicitud ya ha sido gestionada (<span x-text="selectedItem ? selectedItem.estado : ''"></span>)
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</x-app-layout>