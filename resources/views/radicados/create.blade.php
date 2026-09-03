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
                        <div class="md:col-span-2" x-data="{
                            files: [],
                            isDragging: false,
                            addFiles(fileList) {
                                const currentKeys = this.files.map(f => f.name + '-' + f.size);
                                for (let i = 0; i < fileList.length; i++) {
                                    const file = fileList[i];
                                    if (!currentKeys.includes(file.name + '-' + file.size)) {
                                        this.files.push(file);
                                    }
                                }
                                this.syncInput();
                            },
                            removeFile(index) {
                                this.files.splice(index, 1);
                                this.syncInput();
                            },
                            clearAll() {
                                this.files = [];
                                this.syncInput();
                            },
                            syncInput() {
                                const dt = new DataTransfer();
                                this.files.forEach(f => dt.items.add(f));
                                this.$refs.submitInput.files = dt.files;
                            },
                            formatBytes(bytes) {
                                if (!bytes || bytes === 0) return '0 B';
                                const k = 1024;
                                const sizes = ['B', 'KB', 'MB', 'GB'];
                                const i = Math.floor(Math.log(bytes) / Math.log(k));
                                return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
                            },
                            getTotalSize() {
                                const total = this.files.reduce((acc, f) => acc + f.size, 0);
                                return this.formatBytes(total);
                            },
                            getFileExt(name) {
                                return name.split('.').pop().toUpperCase();
                            },
                            getFileTheme(name) {
                                const ext = name.split('.').pop().toLowerCase();
                                if (ext === 'pdf') return { icon: 'ph-file-pdf', color: 'text-red-600 bg-red-50 border-red-200' };
                                if (['doc', 'docx'].includes(ext)) return { icon: 'ph-file-doc', color: 'text-blue-600 bg-blue-50 border-blue-200' };
                                if (['xls', 'xlsx', 'csv'].includes(ext)) return { icon: 'ph-file-xls', color: 'text-emerald-600 bg-emerald-50 border-emerald-200' };
                                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) return { icon: 'ph-file-image', color: 'text-indigo-600 bg-indigo-50 border-indigo-200' };
                                if (['zip', 'rar', '7z', 'tar', 'gz'].includes(ext)) return { icon: 'ph-file-zip', color: 'text-amber-600 bg-amber-50 border-amber-200' };
                                return { icon: 'ph-file-text', color: 'text-slate-600 bg-slate-50 border-slate-200' };
                            }
                        }">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    Documento(s) Inicial(es) / Entrada (Opcional)
                                </label>
                                <template x-if="files.length > 0">
                                    <span class="text-xs font-medium text-gray-500">
                                        <span class="font-bold text-blue-600" x-text="files.length"></span> archivo(s) seleccionado(s) 
                                        (<span x-text="getTotalSize()"></span>)
                                    </span>
                                </template>
                            </div>

                            <!-- Input real que se envía en el POST del formulario -->
                            <input 
                                x-ref="submitInput" 
                                id="archivos_entrada" 
                                type="file" 
                                name="archivos_entrada[]" 
                                multiple 
                                class="hidden">

                            <!-- Input auxiliar para abrir el diálogo de selección -->
                            <input 
                                x-ref="pickerInput" 
                                type="file" 
                                multiple 
                                class="hidden" 
                                @change="addFiles($event.target.files); $event.target.value = ''">

                            <!-- Dropzone interactiva -->
                            <div 
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="isDragging = false; addFiles($event.dataTransfer.files)"
                                @click="$refs.pickerInput.click()"
                                :class="isDragging ? 'border-blue-500 bg-blue-50/60 ring-2 ring-blue-400/30' : 'border-gray-300 hover:border-blue-400 bg-gray-50/60'"
                                class="border-2 border-dashed rounded-2xl p-6 text-center transition-all cursor-pointer group relative">
                                
                                <div class="flex flex-col items-center justify-center pointer-events-none">
                                    <div class="w-12 h-12 rounded-2xl bg-blue-100/80 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                                        <i class="ph ph-upload-simple text-2xl"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-800 mb-1">
                                        Haz clic para seleccionar o arrastra los archivos aquí
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Soporta múltiples archivos: PDF, Word, Excel, Imágenes, ZIP (hasta 25 MB c/u)
                                    </p>
                                </div>
                            </div>

                            <!-- Lista de archivos seleccionados -->
                            <template x-if="files.length > 0">
                                <div class="mt-4 space-y-3">
                                    <div class="flex items-center justify-between text-xs text-gray-500 border-b border-gray-100 pb-2">
                                        <span class="font-semibold text-gray-700 flex items-center gap-1.5">
                                            <i class="ph ph-files text-blue-600 text-sm"></i>
                                            Archivos listos para subir (<span x-text="files.length"></span>)
                                        </span>
                                        <button type="button" @click="clearAll()" class="text-red-500 hover:text-red-700 font-medium hover:underline flex items-center gap-1">
                                            <i class="ph ph-trash text-xs"></i> Limpiar todos
                                        </button>
                                    </div>

                                    <!-- Grid responsivo para 1, 2 o 10 archivos -->
                                    <div :class="files.length > 4 ? 'max-h-72 overflow-y-auto pr-1' : ''" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        <template x-for="(file, index) in files" :key="file.name + '-' + file.size">
                                            <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-xl shadow-sm hover:border-blue-300 hover:shadow transition group">
                                                <div class="flex items-center gap-3 min-w-0 pr-2">
                                                    <div :class="getFileTheme(file.name).color" class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 border font-bold text-lg">
                                                        <i :class="'ph ' + getFileTheme(file.name).icon"></i>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-xs font-medium text-gray-900 truncate" x-text="file.name" :title="file.name"></p>
                                                        <div class="flex items-center gap-2 mt-0.5">
                                                            <span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded" x-text="getFileExt(file.name)"></span>
                                                            <span class="text-[11px] text-gray-400" x-text="formatBytes(file.size)"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button 
                                                    type="button" 
                                                    @click="removeFile(index)" 
                                                    class="text-gray-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition flex-shrink-0"
                                                    title="Quitar archivo">
                                                    <i class="ph-bold ph-x text-sm"></i>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
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
