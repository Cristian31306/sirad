<x-app-layout>
    <div class="mb-6">
        <h2 class="font-bold text-2xl text-gray-800 tracking-tight">
            Nuevo Radicado
        </h2>
        <p class="text-gray-500 text-sm mt-1">Complete la información para radicar un nuevo trámite.</p>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative mb-6 flex items-start gap-3" role="alert">
            <i class="ph ph-warning-circle text-xl text-red-500 mt-0.5"></i>
            <div>
                <strong class="font-bold block mb-1">Por favor corrige los siguientes errores:</strong>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('radicados.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @csrf
        
        <div class="p-8 sm:p-12">
            <div class="max-w-4xl mx-auto space-y-12">
                
                <!-- Sección 1 -->
                <div>
                    <h3 class="font-bold text-gray-800 text-xl border-b border-gray-100 pb-4 mb-6 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">1</div>
                        Información General
                    </h3>
                    
                    <!-- Número de Radicado (Manual) -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Número de Radicado (Manual) <span class="text-red-500">*</span></label>
                        <input type="text" name="numero_radicado" value="{{ old('numero_radicado') }}" placeholder="Ej: RAD-20260805-ABCD" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-white px-4 py-2.5 font-mono" required autofocus>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8" style="gap: 2rem;">
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Radicación <span class="text-red-500">*</span></label>
                            <input type="date" name="fecha_radicacion" max="{{ date('Y-m-d') }}" value="{{ old('fecha_radicacion', date('Y-m-d')) }}" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-gray-50 px-4 py-2.5" required>
                        </div>

                        <div>
                            <label for="remitente" class="block text-sm font-semibold text-gray-700 mb-2">Remitente <span class="text-red-500">*</span></label>
                            <input id="remitente" type="text" name="remitente" value="{{ old('remitente') }}" placeholder="Nombre completo" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-gray-50 px-4 py-2.5" required autofocus>
                        </div>

                        <div>
                            <label for="empresa" class="block text-sm font-semibold text-gray-700 mb-2">Empresa / Entidad</label>
                            <input id="empresa" type="text" name="empresa" value="{{ old('empresa') }}" placeholder="Nombre de la empresa (Opcional)" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-gray-50 px-4 py-2.5">
                        </div>

                        <div>
                            <label for="tipo_tramite_id" class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Trámite <span class="text-red-500">*</span></label>
                            <select id="tipo_tramite_id" name="tipo_tramite_id" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-gray-50 px-4 py-2.5" required>
                                <option value="">Seleccione un tipo</option>
                                @foreach($tiposTramites as $tipo)
                                    <option value="{{ $tipo->id }}" {{ old('tipo_tramite_id') == $tipo->id ? 'selected' : '' }}>
                                        {{ $tipo->nombre }} ({{ $tipo->dias_habiles }} días hábiles)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="medio" class="block text-sm font-semibold text-gray-700 mb-2">Medio de Recepción <span class="text-red-500">*</span></label>
                            <select id="medio" name="medio" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-gray-50 px-4 py-2.5" required>
                                <option value="Físico" {{ old('medio') == 'Físico' ? 'selected' : '' }}>Físico</option>
                                <option value="Correo Electrónico" {{ old('medio') == 'Correo Electrónico' ? 'selected' : '' }}>Correo Electrónico</option>
                                <option value="Portal Web" {{ old('medio') == 'Portal Web' ? 'selected' : '' }}>Portal Web</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label for="asunto" class="block text-sm font-semibold text-gray-700 mb-2">Asunto <span class="text-red-500">*</span></label>
                            <textarea id="asunto" name="asunto" rows="3" placeholder="Asunto o descripción breve" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-gray-50 px-4 py-3" required>{{ old('asunto') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Sección 2 -->
                <div>
                    <h3 class="font-bold text-gray-800 text-xl border-b border-gray-100 pb-4 mb-6 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">2</div>
                        Asignación y Detalles
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        
                        <div x-data="{
                            items: {{ $responsables->map(function($r) { return ['id' => $r->id, 'nombre' => $r->nombre, 'especialidad' => $r->especialidad]; })->toJson() }},
                            selectedIds: {{ json_encode(old('responsables', [])) }}.map(id => parseInt(id)),
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
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Responsable(s) (Destinatario) <span class="text-red-500">*</span></label>
                            
                            <!-- Inputs ocultos para envío -->
                            <template x-for="id in selectedIds" :key="id">
                                <input type="hidden" name="responsables[]" :value="id">
                            </template>
                            
                            <!-- Tags de seleccionados -->
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
                        
                            <!-- Buscador -->
                            <div class="relative">
                                <input type="text" x-model="search" x-ref="searchInput" placeholder="Buscar y agregar responsable..." class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-gray-50 px-4 py-2.5" @focus="open = true" @click.away="open = false" @keydown.escape="open = false">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="ph ph-magnifying-glass text-gray-400"></i>
                                </div>
                            </div>
                            
                            <!-- Lista de opciones desplegable -->
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
                                No se encontraron responsables que coincidan.
                            </div>
                            
                            @if($responsables->isEmpty())
                                <p class="text-xs text-red-500 mt-2 font-semibold">⚠️ No hay responsables registrados. Solicita a un administrador que agregue uno.</p>
                            @endif
                        </div>

                        <div>
                            <label for="prioridad" class="block text-sm font-semibold text-gray-700 mb-2">Prioridad <span class="text-red-500">*</span></label>
                            <select id="prioridad" name="prioridad" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-gray-50 px-4 py-2.5" required>
                                <option value="Baja" {{ old('prioridad') == 'Baja' ? 'selected' : '' }}>Baja</option>
                                <option value="Media" {{ old('prioridad', 'Media') == 'Media' ? 'selected' : '' }}>Media</option>
                                <option value="Alta" {{ old('prioridad') == 'Alta' ? 'selected' : '' }}>Alta</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label for="observaciones" class="block text-sm font-semibold text-gray-700 mb-2">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" rows="2" placeholder="Notas adicionales o comentarios" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-gray-50 px-4 py-3">{{ old('observaciones') }}</textarea>
                        </div>

                        <!-- Documentos Adjuntos Iniciales (Entrada) -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Documento(s) Inicial(es) / Entrada (Opcional)</label>
                            <div class="border-2 border-dashed border-gray-300 hover:border-blue-500 rounded-2xl p-6 text-center bg-gray-50/60 transition group cursor-pointer" onclick="document.getElementById('archivos_entrada').click()">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-2xl bg-blue-100/70 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 transition">
                                        <i class="ph ph-files text-2xl"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-800 mb-1">Haz clic para seleccionar o arrastra los archivos aquí</p>
                                    <p class="text-xs text-gray-500">Puedes seleccionar múltiples archivos (PDF, Word, Excel, Imágenes, ZIP)</p>
                                    <input id="archivos_entrada" type="file" name="archivos_entrada[]" multiple class="hidden" onchange="const count = this.files.length; document.getElementById('selected-filename').textContent = count > 0 ? count + ' archivo(s) seleccionado(s)' : '';">
                                    <span id="selected-filename" class="text-xs font-bold text-blue-600 mt-2"></span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <div class="px-8 py-5 border-t border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <a href="{{ route('radicados.index') }}" class="text-gray-600 hover:text-gray-900 font-medium px-4 py-2 border border-transparent rounded-xl hover:bg-gray-200 transition">Cancelar</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-8 rounded-xl shadow-md shadow-blue-500/30 flex items-center gap-2 transition">
                Radicar Documento <i class="ph ph-check-circle"></i>
            </button>
        </div>
    </form>
</x-app-layout>
