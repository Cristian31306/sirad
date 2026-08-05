<x-app-layout>
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('radicados.index') }}" class="text-gray-400 hover:text-gray-600">Radicados</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-500 text-sm font-medium">{{ $radicado->numero_radicado }}</span>
        </div>
        <div class="flex justify-between items-center mt-2" x-data="{ showEditModal: false, showAnularModal: false }">
            <div class="flex items-center gap-4">
                <h2 class="font-bold text-3xl text-gray-900 tracking-tight">
                    Radicado <span class="text-gray-500 font-normal">{{ $radicado->numero_radicado }}</span>
                </h2>
                @if($radicado->estado == 'pendiente')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold bg-green-50 text-green-700 border border-green-200">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span> Pendiente
                    </span>
                @elseif($radicado->estado == 'alerta')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold bg-yellow-50 text-yellow-700 border border-yellow-200">
                        <span class="w-2 h-2 rounded-full bg-yellow-500"></span> En Alerta
                    </span>
                @elseif($radicado->estado == 'vencido')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold bg-red-50 text-red-700 border border-red-200">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span> Vencido
                    </span>
                @elseif($radicado->estado == 'completado')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                        <span class="w-2 h-2 rounded-full bg-gray-500"></span> Completado
                    </span>
                @elseif($radicado->estado == 'anulado')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold bg-gray-100 text-gray-400 border border-gray-200">
                        <span class="w-2 h-2 rounded-full bg-gray-300"></span> Anulado
                    </span>
                @endif
            </div>
            
            <div class="flex items-center gap-3">
                @if(in_array($radicado->estado, ['pendiente', 'alerta', 'vencido']))
                <button @click="showEditModal = true" class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold py-1.5 px-3 text-sm rounded-lg shadow-sm flex items-center gap-2 transition-all">
                    <i class="ph ph-pencil-simple"></i> 
                    @can('radicados.editar')
                        Editar
                    @else
                        Solicitar Edición
                    @endcan
                </button>
                @endif
                
                @can('radicados.anular')
                @if(in_array($radicado->estado, ['pendiente', 'alerta', 'vencido']))
                <button @click="showAnularModal = true" class="bg-red-50 border border-red-200 text-red-700 hover:bg-red-100 font-semibold py-1.5 px-3 text-sm rounded-lg shadow-sm flex items-center gap-2 transition-all">
                    <i class="ph ph-trash"></i> 
                    Anular
                </button>
                @endif
                @endcan
            </div>

            <!-- Modal Solicitar Edición / Editar -->
            <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showEditModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showEditModal = false" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div x-show="showEditModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                        <form action="{{ auth()->user()->can('radicados.editar') ? route('radicados.update', $radicado) : route('solicitudes.store', $radicado) }}" method="POST" class="p-6">
                            @csrf
                            @if(auth()->user()->can('radicados.editar'))
                                @method('PUT')
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Editar Radicado</h3>
                                <p class="text-sm text-gray-500 mb-4">Realice los cambios necesarios en el radicado. Se guardarán de inmediato.</p>
                            @else
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Solicitar Edición de Datos</h3>
                                <p class="text-sm text-gray-500 mb-4">Los cambios que realice aquí serán enviados al administrador para su aprobación.</p>
                            @endif
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Número de Radicado (Manual)</label>
                                    <input type="text" name="numero_radicado" value="{{ $radicado->numero_radicado }}" class="w-full border-gray-300 rounded-xl font-mono" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha de Radicación</label>
                                    <input type="date" name="fecha_radicacion" value="{{ Carbon\Carbon::parse($radicado->fecha_radicacion)->format('Y-m-d') }}" class="w-full border-gray-300 rounded-xl" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Hora de Recepción</label>
                                    <input type="time" name="hora_recepcion" value="{{ $radicado->hora_recepcion ? Carbon\Carbon::parse($radicado->hora_recepcion)->format('H:i') : '' }}" class="w-full border-gray-300 rounded-xl" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Remitente</label>
                                    <input type="text" name="remitente" value="{{ $radicado->remitente }}" class="w-full border-gray-300 rounded-xl" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Empresa / Entidad</label>
                                    <input type="text" name="empresa" value="{{ $radicado->empresa }}" class="w-full border-gray-300 rounded-xl">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo de Trámite</label>
                                    <select name="tipo_tramite_id" class="w-full border-gray-300 rounded-xl" required>
                                        @foreach($tiposTramites as $tipo)
                                            <option value="{{ $tipo->id }}" {{ $radicado->tipo_tramite_id == $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Responsable</label>
                                    <select name="responsable_id" class="w-full border-gray-300 rounded-xl" required>
                                        @foreach($responsables as $resp)
                                            <option value="{{ $resp->id }}" {{ $radicado->responsable_id == $resp->id ? 'selected' : '' }}>{{ $resp->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Medio</label>
                                    <select name="medio" class="w-full border-gray-300 rounded-xl" required>
                                        <option value="Físico" {{ $radicado->medio == 'Físico' ? 'selected' : '' }}>Físico</option>
                                        <option value="Correo Electrónico" {{ $radicado->medio == 'Correo Electrónico' ? 'selected' : '' }}>Correo Electrónico</option>
                                        <option value="Portal Web" {{ $radicado->medio == 'Portal Web' ? 'selected' : '' }}>Portal Web</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Prioridad</label>
                                    <select name="prioridad" class="w-full border-gray-300 rounded-xl" required>
                                        <option value="Baja" {{ $radicado->prioridad == 'Baja' ? 'selected' : '' }}>Baja</option>
                                        <option value="Media" {{ $radicado->prioridad == 'Media' ? 'selected' : '' }}>Media</option>
                                        <option value="Alta" {{ $radicado->prioridad == 'Alta' ? 'selected' : '' }}>Alta</option>
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Asunto</label>
                                    <textarea name="asunto" rows="2" class="w-full border-gray-300 rounded-xl" required>{{ $radicado->asunto }}</textarea>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Observaciones</label>
                                    <textarea name="observaciones" rows="2" class="w-full border-gray-300 rounded-xl">{{ $radicado->observaciones }}</textarea>
                                </div>
                            </div>
                            
                            <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Enviar Solicitud</button>
                                <button type="button" @click="showEditModal = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Anular Trámite -->
            <div x-show="showAnularModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title-anular" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showAnularModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showAnularModal = false" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div x-show="showAnularModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-red-50 p-6">
                            <div class="flex items-center gap-3 text-red-600 mb-4">
                                <i class="ph ph-warning text-2xl"></i>
                                <h3 class="text-lg leading-6 font-bold" id="modal-title-anular">Anular Trámite</h3>
                            </div>
                            <p class="text-sm text-red-600/80 mb-4">Anular este trámite lo eliminará del semáforo y se marcará como cerrado permanentemente. No podrá recuperarse.</p>
                            
                            <form action="{{ route('radicados.anular', $radicado) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="mb-4">
                                    <label for="motivo_anulacion" class="block text-xs font-bold text-red-700 uppercase tracking-wider mb-1">Motivo de Anulación <span class="text-red-500">*</span></label>
                                    <input id="motivo_anulacion" name="motivo_anulacion" type="text" placeholder="Explique el motivo de la anulación..." class="w-full border-red-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-white shadow-sm text-sm" required autofocus />
                                </div>
                                <div class="mt-5 sm:mt-6 flex flex-col sm:flex-row-reverse gap-3">
                                    <button type="submit" class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md shadow-red-500/30 flex items-center justify-center gap-2 transition whitespace-nowrap">
                                        <i class="ph ph-trash"></i> Confirmar Anulación
                                    </button>
                                    <button type="button" @click="showAnularModal = false" class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-6 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:text-sm">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>



    <div class="flex flex-col lg:flex-row gap-6">
        
        <!-- Left Column Wrapper -->
        <div class="w-full lg:w-2/3 space-y-6">
            
            <!-- Información del Trámite -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-800 text-lg border-b border-gray-100 pb-3 mb-4">Información del Trámite</h3>
            
            <div class="space-y-4">
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Remitente</span>
                    <p class="font-medium text-gray-900">{{ $radicado->remitente }}</p>
                    <p class="text-xs text-gray-500">{{ $radicado->empresa }}</p>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Tipo / Medio / Prioridad</span>
                    <p class="font-medium text-gray-900">
                        {{ optional($radicado->tipoTramite)->nombre }}
                    </p>
                    <p class="text-xs text-gray-600">{{ $radicado->medio }} | Prioridad: <span class="font-bold">{{ $radicado->prioridad }}</span></p>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Asunto</span>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $radicado->asunto }}</p>
                </div>
                @if($radicado->observaciones)
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Observaciones</span>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $radicado->observaciones }}</p>
                </div>
                @endif
                <div class="pt-2 border-t border-gray-50">
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Responsable</span>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                            {{ substr($radicado->responsable->nombre ?? '?', 0, 1) }}
                        </div>
                        <div>
                            <span class="font-medium text-gray-900 block leading-tight">{{ $radicado->responsable->nombre ?? 'No asignado' }}</span>
                            @if($radicado->responsable && $radicado->responsable->especialidad)
                                <span class="text-xs text-gray-500">{{ $radicado->responsable->especialidad }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="pt-2 border-t border-gray-50 grid grid-cols-2 gap-4">
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Fecha Radicación</span>
                        <p class="font-medium text-gray-900">{{ $radicado->fecha_radicacion->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-500">{{ $radicado->hora_recepcion ? \Carbon\Carbon::parse($radicado->hora_recepcion)->format('H:i') : '' }}</p>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Fecha Límite</span>
                        <p class="font-medium {{ $radicado->estado == 'vencido' ? 'text-red-600' : 'text-gray-900' }}">{{ $radicado->fecha_limite->format('d/m/Y') }}</p>
                    </div>
                </div>

                @if(in_array($radicado->estado, ['pendiente', 'alerta', 'vencido']))
                    <div class="pt-2 border-t border-gray-50">
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Días Restantes</span>
                        @php
                            $dias = now()->startOfDay()->diffInDays($radicado->fecha_limite->startOfDay(), false);
                        @endphp
                        @if($dias < 0)
                            <p class="font-bold text-red-600 text-lg">Vencido hace {{ abs($dias) }} días</p>
                        @elseif($dias == 0)
                            <p class="font-bold text-yellow-600 text-lg">Vence Hoy</p>
                        @else
                            <p class="font-bold text-blue-600 text-lg">{{ $dias }} {{ $dias == 1 ? 'día hábil' : 'días hábiles' }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        </div> <!-- Fin de Left Column Wrapper -->

        <!-- Right Column Wrapper -->
        <div class="w-full lg:w-1/3 space-y-6">

            <!-- Columna 3: Historial -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 h-fit">
            <h3 class="font-bold text-gray-800 text-lg border-b border-gray-100 pb-3 mb-6">Historial del Trámite</h3>
            
            <div class="relative border-l-2 border-gray-100 ml-3 space-y-8">
                <!-- Paso 1: Radicado -->
                <div class="relative pl-6">
                    <div class="absolute w-4 h-4 bg-green-500 rounded-full border-4 border-white -left-[9px] top-1 shadow-sm"></div>
                    <div class="text-xs text-gray-400 mb-1">{{ $radicado->fecha_radicacion->format('d/m/Y') }}</div>
                    <h4 class="text-sm font-bold text-gray-900">Radicado creado</h4>
                </div>

                <!-- Paso 2: Asignado -->
                <div class="relative pl-6">
                    <div class="absolute w-4 h-4 bg-blue-500 rounded-full border-4 border-white -left-[9px] top-1 shadow-sm"></div>
                    <div class="text-xs text-gray-400 mb-1">{{ $radicado->fecha_radicacion->format('d/m/Y') }}</div>
                    <h4 class="text-sm font-bold text-gray-900">Asignado a</h4>
                    <p class="text-xs text-gray-500 mt-1">{{ $radicado->responsable->nombre ?? 'Responsable' }}</p>
                </div>

                <!-- Paso 3: Estado Actual -->
                @if($radicado->estado == 'completado')
                    <div class="relative pl-6">
                        <div class="absolute w-4 h-4 bg-gray-500 rounded-full border-4 border-white -left-[9px] top-1 shadow-sm"></div>
                        <div class="text-xs text-gray-400 mb-1">{{ optional($radicado->fecha_salida)->format('d/m/Y') ?? 'Reciente' }}</div>
                        <h4 class="text-sm font-bold text-gray-900">Completado</h4>
                        <p class="text-xs text-gray-500 mt-1">Trámite cerrado</p>
                    </div>
                @elseif($radicado->estado == 'anulado')
                    <div class="relative pl-6">
                        <div class="absolute w-4 h-4 bg-gray-400 rounded-full border-4 border-white -left-[9px] top-1 shadow-sm"></div>
                        <div class="text-xs text-gray-400 mb-1">Reciente</div>
                        <h4 class="text-sm font-bold text-gray-900">Anulado</h4>
                    </div>
                @else
                    <div class="relative pl-6">
                        @if($radicado->estado == 'pendiente')
                            <div class="absolute w-4 h-4 bg-green-300 rounded-full border-4 border-white -left-[9px] top-1 shadow-sm animate-pulse"></div>
                        @elseif($radicado->estado == 'alerta')
                            <div class="absolute w-4 h-4 bg-yellow-400 rounded-full border-4 border-white -left-[9px] top-1 shadow-sm animate-pulse"></div>
                        @else
                            <div class="absolute w-4 h-4 bg-red-500 rounded-full border-4 border-white -left-[9px] top-1 shadow-sm animate-pulse"></div>
                        @endif
                        
                        <div class="text-xs font-semibold text-gray-600 mb-1">Estado Actual</div>
                        <h4 class="text-sm font-bold text-gray-900 capitalize">{{ $radicado->estado }}</h4>
                        @if(isset($dias) && $dias > 0)
                            <p class="text-xs text-gray-500 mt-1">Faltan {{ $dias }} días hábiles</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Columna 2: Cierre (Movido a la derecha) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-800 text-lg border-b border-gray-100 pb-3 mb-4">Cierre de Trámite</h3>
            
            <div>
                @if(in_array($radicado->estado, ['pendiente', 'alerta', 'vencido']))
                    @can('radicados.completar')
                        <div class="bg-blue-50/50 border border-dashed border-blue-300 rounded-xl p-6 text-center">
                            <p class="text-sm font-medium text-blue-800 mb-4">El trámite se encuentra abierto y pendiente de gestión.</p>
                            
                            <form action="{{ route('radicados.cierre', $radicado) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2.5 rounded-xl shadow-md hover:bg-blue-700 flex items-center justify-center gap-2 transition">
                                    <i class="ph ph-check-circle text-lg"></i> Marcar como Completado
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
                            <p class="text-sm text-gray-500">Trámite abierto. (Requiere permisos para completar)</p>
                        </div>
                    @endcan
                @elseif($radicado->estado == 'completado')
                    <div class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
                        <i class="ph-fill ph-check-circle text-4xl text-green-500 mb-2"></i>
                        <p class="text-sm font-bold text-green-800">Trámite Completado</p>
                        <p class="text-xs text-green-600 mt-1">Cerrado el {{ optional($radicado->fecha_salida)->format('d/m/Y') }}</p>
                    </div>
                @endif
            </div>
        </div>

    </div> <!-- Fin de Right Column Wrapper -->
</div>

    <!-- Estado Anulado -->
    @if($radicado->estado == 'anulado')
        <div class="mt-8 bg-gray-50 border border-gray-200 rounded-2xl p-6 flex items-start gap-4">
            <div class="bg-gray-200 p-3 rounded-xl text-gray-500">
                <i class="ph ph-prohibit text-2xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">Trámite Anulado</h3>
                <p class="text-sm text-gray-600 mb-2"><strong>Motivo:</strong> {{ $radicado->motivo_anulacion }}</p>
                <p class="text-xs text-gray-500">Anulado por: {{ optional($radicado->anulador)->name ?? 'Admin' }}</p>
            </div>
        </div>
    @endif
</x-app-layout>


