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
                                <div x-data="{
                                    items: {{ $responsables->map(function($r) { return ['id' => $r->id, 'nombre' => $r->nombre, 'especialidad' => $r->especialidad]; })->toJson() }},
                                    selectedIds: {{ json_encode($radicado->responsables->pluck('id')->toArray()) }}.map(id => parseInt(id)),
                                    search: '',
                                    open: false,
                                    
                                    get selectedItems() {
                                        return this.selectedIds.map(id => this.items.find(i => i.id === id)).filter(Boolean);
                                    },
                                    
                                    get filteredAvailable() {
                                        if (this.search === '') {
                                            return this.items.filter(item => !this.selectedIds.includes(item.id));
                                        }
                                        return this.items.filter(item => !this.selectedIds.includes(item.id))
                                            .filter(item => item.nombre.toLowerCase().includes(this.search.toLowerCase()) || 
                                                           (item.especialidad && item.especialidad.toLowerCase().includes(this.search.toLowerCase())));
                                    },
                                    
                                    toggle(id) {
                                        if (this.selectedIds.includes(id)) {
                                            this.selectedIds = this.selectedIds.filter(i => i !== id);
                                        } else {
                                            this.selectedIds.push(id);
                                        }
                                        this.search = '';
                                        this.$refs.searchInput.focus();
                                    }
                                }" class="relative w-full">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Responsables</label>
                                    
                                    <template x-for="id in selectedIds" :key="id">
                                        <input type="hidden" name="responsables[]" :value="id">
                                    </template>
                                    
                                    <div class="mb-2 flex flex-wrap gap-2">
                                        <template x-for="item in selectedItems" :key="item.id">
                                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium border border-blue-200 shadow-sm transition-all">
                                                <span x-text="item.nombre"></span>
                                                <button type="button" @click.prevent="toggle(item.id)" class="text-blue-500 hover:text-blue-900 focus:outline-none flex items-center justify-center p-0.5 rounded-full hover:bg-blue-200">
                                                    <i class="ph-bold ph-x text-xs"></i>
                                                </button>
                                            </span>
                                        </template>
                                        <span x-show="selectedIds.length === 0" class="text-sm text-gray-500 italic py-1">Ninguno seleccionado</span>
                                    </div>
                                
                                    <div class="relative">
                                        <input type="text" x-model="search" x-ref="searchInput" placeholder="Buscar y agregar..." class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-white px-4 py-2" @focus="open = true" @click.away="open = false" @keydown.escape="open = false">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <i class="ph ph-magnifying-glass text-gray-400"></i>
                                        </div>
                                    </div>
                                    
                                    <div x-show="open && filteredAvailable.length > 0" x-transition x-cloak class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                        <ul class="py-1">
                                            <template x-for="item in filteredAvailable" :key="item.id">
                                                <li>
                                                    <button type="button" @click.prevent="toggle(item.id)" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex flex-col focus:bg-blue-50 transition-colors">
                                                        <span class="font-medium" x-text="item.nombre"></span>
                                                        <span class="text-xs text-gray-500" x-text="item.especialidad" x-show="item.especialidad"></span>
                                                    </button>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                    
                                    <div x-show="open && filteredAvailable.length === 0 && search !== ''" x-cloak class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg p-4 text-sm text-gray-500 text-center">
                                        No se encontraron responsables.
                                    </div>
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
                                <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                    {{ auth()->user()->can('radicados.editar') ? 'Guardar Cambios' : 'Enviar Solicitud' }}
                                </button>
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
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Responsables</span>
                    <div class="flex flex-col gap-2 mt-1">
                        @forelse($radicado->responsables as $resp)
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                                    {{ substr($resp->nombre, 0, 1) }}
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900 block leading-tight">
                                        {{ $resp->nombre }}
                                        @if($resp->pivot->hubo_rebote)
                                            <span class="ml-1 text-xs font-bold text-red-600 bg-red-100 px-2 py-0.5 rounded-full" title="Rebotó el correo de notificación. Fecha: {{ optional($resp->pivot->fecha_rebote)->format('Y-m-d H:i') }}">
                                                <i class="ph ph-warning-circle"></i> Correo Rebotado
                                            </span>
                                        @endif
                                    </span>
                                    @if($resp->especialidad)
                                        <span class="text-xs text-gray-500">{{ $resp->especialidad }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <span class="text-sm text-gray-500">No asignado</span>
                        @endforelse
                    </div>
                </div>
                
                <div class="pt-2 border-t border-gray-50 grid grid-cols-2 gap-4">
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Fecha Radicación</span>
                        <p class="font-medium text-gray-900">{{ $radicado->fecha_radicacion->format('d/m/Y') }}</p>
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

        <!-- Documentos y Anexos -->
        @php
            $entradas = $radicado->adjuntos->where('tipo', 'entrada');
            $salidas = $radicado->adjuntos->where('tipo', 'salida');
            $totalAdjuntos = $radicado->adjuntos->count();

            $formatFileInfo = function($filename, $path) {
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $sizeStr = '';
                if ($path && \Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
                    $bytes = \Illuminate\Support\Facades\Storage::disk('local')->size($path);
                    $sizeStr = $bytes > 1048576 ? round($bytes / 1048576, 1) . ' MB' : round($bytes / 1024, 0) . ' KB';
                }

                $theme = match($ext) {
                    'pdf' => ['icon' => 'ph-file-pdf', 'color' => 'text-red-600 bg-red-50 border-red-200', 'badge' => 'bg-red-100 text-red-700'],
                    'doc', 'docx' => ['icon' => 'ph-file-doc', 'color' => 'text-blue-600 bg-blue-50 border-blue-200', 'badge' => 'bg-blue-100 text-blue-700'],
                    'xls', 'xlsx', 'csv' => ['icon' => 'ph-file-xls', 'color' => 'text-emerald-600 bg-emerald-50 border-emerald-200', 'badge' => 'bg-emerald-100 text-emerald-700'],
                    'jpg', 'jpeg', 'png', 'gif', 'webp' => ['icon' => 'ph-file-image', 'color' => 'text-indigo-600 bg-indigo-50 border-indigo-200', 'badge' => 'bg-indigo-100 text-indigo-700'],
                    'zip', 'rar', '7z', 'tar', 'gz' => ['icon' => 'ph-file-zip', 'color' => 'text-amber-600 bg-amber-50 border-amber-200', 'badge' => 'bg-amber-100 text-amber-700'],
                    default => ['icon' => 'ph-file-text', 'color' => 'text-slate-600 bg-slate-50 border-slate-200', 'badge' => 'bg-slate-100 text-slate-700'],
                };

                return [
                    'ext' => strtoupper($ext ?: 'FILE'),
                    'size' => $sizeStr,
                    'theme' => $theme
                ];
            };
        @endphp

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="border-b border-gray-100 pb-3 mb-6 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        <i class="ph ph-paperclip text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg leading-tight">Documentos y Anexos</h3>
                        <p class="text-xs text-gray-400">Archivos adjuntos vinculados a este radicado</p>
                    </div>
                </div>

                @if($totalAdjuntos >= 2)
                    <a href="{{ route('radicados.adjuntos.descargar-todos', $radicado) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 shadow-sm transition"
                       title="Descargar todos los archivos adjuntos en un solo archivo .ZIP">
                        <i class="ph ph-archive text-sm"></i> Descargar todos (.ZIP)
                    </a>
                @endif
            </div>

            <div class="space-y-6">
                <!-- Documentos Iniciales (Entrada) -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                            <i class="ph ph-tray-arrow-down text-blue-500 text-base"></i>
                            Documentos de Entrada
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-800">
                                {{ $entradas->count() }}
                            </span>
                        </span>

                        @if($entradas->count() >= 2)
                            <a href="{{ route('radicados.adjuntos.descargar-todos', ['radicado' => $radicado, 'tipo' => 'entrada']) }}" 
                               class="text-[11px] font-semibold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1">
                                <i class="ph ph-download-simple"></i> Bajar entrada (.zip)
                            </a>
                        @endif
                    </div>

                    @if($entradas->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($entradas as $entrada)
                                @php $info = $formatFileInfo($entrada->nombre_original, $entrada->path); @endphp
                                <div class="flex flex-col justify-between p-3.5 bg-gray-50/70 hover:bg-white border border-gray-200 hover:border-blue-300 rounded-xl transition shadow-sm hover:shadow group">
                                    <div class="flex items-start gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 border font-bold text-xl {{ $info['theme']['color'] }}">
                                            <i class="ph {{ $info['theme']['icon'] }}"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-gray-900 text-xs break-all line-clamp-2" title="{{ $entrada->nombre_original }}">
                                                {{ $entrada->nombre_original }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded uppercase {{ $info['theme']['badge'] }}">
                                                    {{ $info['ext'] }}
                                                </span>
                                                @if($info['size'])
                                                    <span class="text-[11px] text-gray-400">{{ $info['size'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 pt-2 border-t border-gray-200/60 justify-end">
                                        <a href="{{ route('radicados.archivo.ver', $entrada) }}" target="_blank" 
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition shadow-2xs">
                                            <i class="ph ph-eye"></i> Ver
                                        </a>
                                        <a href="{{ route('radicados.archivo.descargar', $entrada) }}" 
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-600 text-white hover:bg-blue-700 transition shadow-2xs">
                                            <i class="ph ph-download-simple"></i> Descargar
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="border border-dashed border-gray-200 rounded-xl p-4 text-center bg-gray-50/50">
                            <p class="text-xs text-gray-500 italic">No se adjuntaron archivos al momento de radicar</p>
                        </div>
                    @endif
                </div>

                <!-- Documentos de Respuesta (Salida) -->
                <div class="pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                            <i class="ph ph-tray-arrow-up text-emerald-500 text-base"></i>
                            Documentos de Respuesta / Salida
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $salidas->isNotEmpty() ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $salidas->count() }}
                            </span>
                        </span>

                        @if($salidas->count() >= 2)
                            <a href="{{ route('radicados.adjuntos.descargar-todos', ['radicado' => $radicado, 'tipo' => 'salida']) }}" 
                               class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-800 hover:underline flex items-center gap-1">
                                <i class="ph ph-download-simple"></i> Bajar salida (.zip)
                            </a>
                        @endif
                    </div>

                    @if($salidas->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($salidas as $salida)
                                @php $info = $formatFileInfo($salida->nombre_original, $salida->path); @endphp
                                <div class="flex flex-col justify-between p-3.5 bg-emerald-50/30 hover:bg-white border border-emerald-200 hover:border-emerald-300 rounded-xl transition shadow-sm hover:shadow group">
                                    <div class="flex items-start gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 border font-bold text-xl {{ $info['theme']['color'] }}">
                                            <i class="ph {{ $info['theme']['icon'] }}"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-gray-900 text-xs break-all line-clamp-2" title="{{ $salida->nombre_original }}">
                                                {{ $salida->nombre_original }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded uppercase {{ $info['theme']['badge'] }}">
                                                    {{ $info['ext'] }}
                                                </span>
                                                @if($info['size'])
                                                    <span class="text-[11px] text-gray-400">{{ $info['size'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 pt-2 border-t border-emerald-100 justify-end">
                                        <a href="{{ route('radicados.archivo.ver', $salida) }}" target="_blank" 
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 hover:text-emerald-600 transition shadow-2xs">
                                            <i class="ph ph-eye"></i> Ver
                                        </a>
                                        <a href="{{ route('radicados.archivo.descargar', $salida) }}" 
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-2xs">
                                            <i class="ph ph-download-simple"></i> Descargar
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="border border-dashed border-gray-200 rounded-xl p-4 text-center bg-gray-50/50">
                            @if($radicado->estado === 'completado')
                                <p class="text-xs text-gray-500 italic">Trámite completado sin archivos adjuntos de respuesta</p>
                            @else
                                <p class="text-xs text-gray-500 italic">Pendiente de cierre y respuesta</p>
                            @endif
                        </div>
                    @endif
                </div>
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
                    <p class="text-xs text-gray-500 mt-1">{{ $radicado->responsables->pluck('nombre')->implode(', ') ?: 'Responsable' }}</p>
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
                        <div class="bg-blue-50/50 border border-dashed border-blue-300 rounded-xl p-5 text-center">
                            <p class="text-sm font-medium text-blue-800 mb-3">El trámite se encuentra abierto y pendiente de gestión.</p>
                            
                            <form action="{{ route('radicados.cierre', $radicado) }}" method="POST" enctype="multipart/form-data" x-data="{
                                salidaFiles: [],
                                addFiles(fl) {
                                    const current = this.salidaFiles.map(f => f.name + '-' + f.size);
                                    for (let i = 0; i < fl.length; i++) {
                                        if (!current.includes(fl[i].name + '-' + fl[i].size)) {
                                            this.salidaFiles.push(fl[i]);
                                        }
                                    }
                                    this.sync();
                                },
                                removeFile(idx) {
                                    this.salidaFiles.splice(idx, 1);
                                    this.sync();
                                },
                                sync() {
                                    const dt = new DataTransfer();
                                    this.salidaFiles.forEach(f => dt.items.add(f));
                                    this.$refs.salidaInput.files = dt.files;
                                },
                                formatBytes(bytes) {
                                    if (!bytes) return '0 B';
                                    const k = 1024;
                                    const sizes = ['B', 'KB', 'MB', 'GB'];
                                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                                    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
                                }
                            }">
                                @csrf
                                @method('PATCH')

                                @if($radicado->hasArchivoSalida())
                                    <div class="mb-4 bg-green-100/70 border border-green-200 rounded-xl p-3 text-left">
                                        <div class="flex items-start gap-2">
                                            <i class="ph-fill ph-check-circle text-green-600 mt-0.5 text-lg"></i>
                                            <div>
                                                <p class="text-xs font-bold text-green-800 uppercase tracking-wider mb-1">¡Respuesta(s) recibida(s)!</p>
                                                <p class="text-xs text-green-700 leading-tight mb-2">Ya se cuenta con los siguientes archivos:</p>
                                                <div class="flex flex-col gap-1.5">
                                                    @foreach($salidas as $salida)
                                                    <a href="{{ route('radicados.archivo.descargar', $salida) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-700 hover:text-blue-900 bg-white/80 px-2 py-1 rounded-md border border-green-200">
                                                        <i class="ph ph-file-arrow-down"></i> {{ $salida->nombre_original }}
                                                    </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-4 text-left">
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        {{ $radicado->hasArchivoSalida() ? 'Añadir más Documentos de Cierre (Opcional)' : 'Adjuntar Documento(s) de Respuesta (Opcional)' }}
                                    </label>
                                    <input 
                                        x-ref="salidaInput"
                                        type="file" 
                                        name="archivos_salida[]" 
                                        multiple 
                                        class="hidden"
                                        @change="addFiles($event.target.files); $event.target.value = ''">
                                    
                                    <div @click="$refs.salidaInput.click()" class="border-2 border-dashed border-gray-300 hover:border-blue-400 bg-white rounded-xl p-3.5 text-center cursor-pointer transition">
                                        <div class="flex items-center justify-center gap-2 text-xs font-semibold text-blue-600">
                                            <i class="ph ph-upload-simple text-base"></i>
                                            <span>Seleccionar archivos de respuesta...</span>
                                        </div>
                                        <p class="text-[10px] text-gray-400 mt-0.5">PDF, Word, Excel, Imágenes, ZIP (hasta 25 MB c/u)</p>
                                    </div>

                                    <!-- Lista de archivos seleccionados para salida -->
                                    <template x-if="salidaFiles.length > 0">
                                        <div class="mt-2.5 space-y-1.5">
                                            <div class="text-[11px] font-semibold text-gray-600 flex justify-between">
                                                <span x-text="salidaFiles.length + ' archivo(s) listo(s)'"></span>
                                                <button type="button" @click="salidaFiles = []; sync()" class="text-red-500 hover:underline">Limpiar</button>
                                            </div>
                                            <div class="max-h-36 overflow-y-auto space-y-1 pr-1">
                                                <template x-for="(f, i) in salidaFiles" :key="f.name + '-' + f.size">
                                                    <div class="flex items-center justify-between p-2 bg-blue-50/50 border border-blue-100 rounded-lg text-xs">
                                                        <div class="min-w-0 flex-1 pr-2">
                                                            <p class="font-medium text-gray-800 truncate" x-text="f.name"></p>
                                                            <span class="text-[10px] text-gray-400" x-text="formatBytes(f.size)"></span>
                                                        </div>
                                                        <button type="button" @click="removeFile(i)" class="text-gray-400 hover:text-red-500 p-0.5">
                                                            <i class="ph-bold ph-x"></i>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>

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
                        @if($salidas->isNotEmpty())
                            <div class="mt-3 pt-3 border-t border-green-100 flex flex-col items-center gap-2">
                                @foreach($salidas as $salida)
                                <a href="{{ route('radicados.archivo.descargar', $salida) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 hover:text-green-900 underline">
                                    <i class="ph ph-file-arrow-down"></i> Descargar {{ $salida->nombre_original }}
                                </a>
                                @endforeach
                            </div>
                        @endif
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


