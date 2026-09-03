<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respuesta a Radicado {{ $radicado->numero_radicado }} - SIRAD</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between text-slate-800 antialiased selection:bg-blue-500 selection:text-white pb-12">
    
    <!-- Top Bar Navigation / Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-black text-xl shadow-md shadow-blue-500/20">
                    S
                </div>
                <div>
                    <h1 class="font-bold text-base text-slate-900 leading-tight">SIRAD</h1>
                    <p class="text-[11px] text-slate-500 font-medium">Sistema de Radicación y Correspondencia</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                <i class="ph ph-shield-check text-base text-emerald-600"></i>
                <span class="hidden sm:inline">Enlace Seguro y Verificado</span>
                <span class="sm:hidden">Seguro</span>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        
        <!-- Alerts -->
        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl flex items-center gap-3 text-sm shadow-xs">
                <i class="ph ph-check-circle text-2xl text-emerald-600 shrink-0"></i>
                <div>
                    <p class="font-bold text-emerald-900">{{ session('success') }}</p>
                    <p class="text-xs text-emerald-700 mt-0.5">El radicado permanece abierto y el equipo de correspondencia ya fue notificado.</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3.5 rounded-2xl flex items-center gap-3 text-sm shadow-xs">
                <i class="ph ph-warning-circle text-xl shrink-0 text-red-600"></i>
                <p class="font-medium">{{ session('error') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 p-4 rounded-2xl text-sm shadow-xs">
                <div class="flex items-center gap-2 font-bold mb-2">
                    <i class="ph ph-warning-circle text-lg"></i>
                    <span>Por favor corrija los siguientes errores:</span>
                </div>
                <ul class="list-disc list-inside space-y-1 pl-1 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Radicado Banner -->
        <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-blue-900/10 mb-8 relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="flex flex-wrap items-center gap-2.5 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md bg-white/20 text-white backdrop-blur-xs">
                            Radicado Oficial
                        </span>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-emerald-500/30 text-emerald-200 border border-emerald-400/30">
                            {{ ucfirst($radicado->prioridad) }} Prioridad
                        </span>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-blue-500/30 text-blue-100 border border-blue-400/30">
                            Estado: {{ ucfirst($radicado->estado) }}
                        </span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $radicado->numero_radicado }}</h2>
                    <p class="text-sm text-blue-100 mt-1 max-w-2xl font-medium leading-relaxed">
                        {{ $radicado->asunto }}
                    </p>
                </div>

                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/15 min-w-[200px] shrink-0">
                    <span class="block text-[11px] font-semibold text-blue-200 uppercase tracking-wider">Fecha Límite</span>
                    <span class="block text-lg font-bold text-white mt-0.5">
                        {{ \Carbon\Carbon::parse($radicado->fecha_limite)->format('d/m/Y') }}
                    </span>
                    <span class="block text-[11px] text-blue-200 mt-1">
                        Asignado a: <strong class="text-white">{{ $responsable->nombre }}</strong>
                    </span>
                </div>
            </div>
        </div>

        <!-- Grid Layout: Info + Received Documents (Left) and Response Uploader (Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Column: Detalles y Documentos (7 cols) -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- Card: Información del Radicado -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs">
                    <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="ph ph-info text-blue-600 text-lg"></i>
                        Detalles del Trámite
                    </h3>

                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="p-3 bg-slate-50 rounded-xl">
                            <dt class="text-slate-400 font-semibold mb-0.5">Remitente</dt>
                            <dd class="font-bold text-slate-800 text-sm">{{ $radicado->remitente }}</dd>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl">
                            <dt class="text-slate-400 font-semibold mb-0.5">Empresa / Entidad</dt>
                            <dd class="font-bold text-slate-800 text-sm">{{ $radicado->empresa ?: 'Persona Natural' }}</dd>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl">
                            <dt class="text-slate-400 font-semibold mb-0.5">Tipo de Trámite</dt>
                            <dd class="font-bold text-slate-800 text-sm">{{ optional($radicado->tipoTramite)->nombre ?: 'General' }}</dd>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl">
                            <dt class="text-slate-400 font-semibold mb-0.5">Fecha de Radicación</dt>
                            <dd class="font-bold text-slate-800 text-sm">{{ \Carbon\Carbon::parse($radicado->fecha_radicacion)->format('d/m/Y') }}</dd>
                        </div>
                        @if($radicado->observaciones)
                        <div class="sm:col-span-2 p-3 bg-slate-50 rounded-xl">
                            <dt class="text-slate-400 font-semibold mb-0.5">Observaciones</dt>
                            <dd class="text-slate-700 leading-relaxed">{{ $radicado->observaciones }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Card: Documentos Adjuntos Recibidos (Entrada) -->
                @php
                    $entradas = $radicado->adjuntos()->where('tipo', 'entrada')->get();
                    $salidas = $radicado->adjuntos()->where('tipo', 'salida')->get();
                @endphp

                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="ph ph-folder text-blue-600 text-lg"></i>
                            Documentos Iniciales Recibidos (Entrada)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold ml-1">
                                {{ $entradas->count() }}
                            </span>
                        </h3>

                        @if($entradas->count() >= 2)
                        <a href="{{ URL::signedRoute('radicados.public.adjuntos.descargar-todos', ['radicado' => $radicado->id, 'responsable' => $responsable->id, 'tipo' => 'entrada']) }}" 
                           class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-xl border border-blue-200/60 transition">
                            <i class="ph ph-archive text-sm"></i>
                            Descargar todos (.ZIP)
                        </a>
                        @endif
                    </div>

                    @if($entradas->isEmpty())
                        <div class="p-6 text-center bg-slate-50 rounded-2xl border border-slate-100">
                            <i class="ph ph-file-dashed text-3xl text-slate-400 mb-2"></i>
                            <p class="text-xs text-slate-500">No se adjuntaron documentos iniciales en este radicado.</p>
                        </div>
                    @else
                        <div class="space-y-2.5">
                            @foreach($entradas as $adjunto)
                                @php
                                    $ext = strtolower(pathinfo($adjunto->nombre_original, PATHINFO_EXTENSION));
                                    $iconClass = 'ph-file-text text-slate-600 bg-slate-100';
                                    if ($ext === 'pdf') $iconClass = 'ph-file-pdf text-red-600 bg-red-50';
                                    elseif (in_array($ext, ['doc', 'docx'])) $iconClass = 'ph-file-doc text-blue-600 bg-blue-50';
                                    elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) $iconClass = 'ph-file-xls text-emerald-600 bg-emerald-50';
                                    elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) $iconClass = 'ph-file-image text-indigo-600 bg-indigo-50';
                                    elseif (in_array($ext, ['zip', 'rar', '7z'])) $iconClass = 'ph-file-zip text-amber-600 bg-amber-50';

                                    $sizeBytes = 0;
                                    if (\Illuminate\Support\Facades\Storage::disk('local')->exists($adjunto->path)) {
                                        $sizeBytes = filesize(\Illuminate\Support\Facades\Storage::disk('local')->path($adjunto->path));
                                    }
                                    $sizeStr = $sizeBytes > 1048576 
                                        ? round($sizeBytes / 1048576, 1) . ' MB' 
                                        : round($sizeBytes / 1024, 0) . ' KB';
                                @endphp
                                <div class="p-3 bg-slate-50 hover:bg-blue-50/40 border border-slate-200/80 rounded-2xl flex items-center justify-between gap-3 transition group">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl shrink-0 {{ $iconClass }}">
                                            <i class="ph {{ explode(' ', $iconClass)[0] }}"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-slate-800 truncate group-hover:text-blue-700 transition" title="{{ $adjunto->nombre_original }}">
                                                {{ $adjunto->nombre_original }}
                                            </p>
                                            <div class="flex items-center gap-2 text-[10px] text-slate-400 font-medium">
                                                <span class="uppercase font-bold text-slate-500">{{ $ext }}</span>
                                                <span>•</span>
                                                <span>{{ $sizeStr }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <!-- Ver archivo -->
                                        <a href="{{ URL::signedRoute('radicados.public.adjuntos.ver', ['radicado' => $radicado->id, 'responsable' => $responsable->id, 'adjunto' => $adjunto->id]) }}" 
                                           target="_blank"
                                           class="inline-flex items-center gap-1 text-xs font-bold text-slate-700 hover:text-blue-600 bg-white hover:bg-slate-100 border border-slate-200 px-2.5 py-1.5 rounded-xl shadow-2xs transition"
                                           title="Ver en el navegador">
                                            <i class="ph ph-eye text-sm"></i>
                                            <span class="hidden sm:inline">Ver</span>
                                        </a>

                                        <!-- Descargar archivo -->
                                        <a href="{{ URL::signedRoute('radicados.public.adjuntos.descargar', ['radicado' => $radicado->id, 'responsable' => $responsable->id, 'adjunto' => $adjunto->id]) }}" 
                                           class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-white hover:bg-blue-600 bg-white border border-blue-200 px-2.5 py-1.5 rounded-xl shadow-2xs transition"
                                           title="Descargar archivo">
                                            <i class="ph ph-download-simple text-sm"></i>
                                            <span class="hidden sm:inline">Descargar</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Card: Documentos de Respuesta ya Enviados (Si existen) -->
                @if($salidas->isNotEmpty())
                <div class="bg-white rounded-3xl p-6 border border-emerald-200/80 shadow-xs">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="ph ph-paper-plane-tilt text-emerald-600 text-lg"></i>
                            Documentos de Respuesta Enviados
                            <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold ml-1">
                                {{ $salidas->count() }}
                            </span>
                        </h3>

                        @if($salidas->count() >= 2)
                        <a href="{{ URL::signedRoute('radicados.public.adjuntos.descargar-todos', ['radicado' => $radicado->id, 'responsable' => $responsable->id, 'tipo' => 'salida']) }}" 
                           class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-xl border border-emerald-200 transition">
                            <i class="ph ph-archive text-sm"></i>
                            Descargar respuestas (.ZIP)
                        </a>
                        @endif
                    </div>

                    <p class="text-xs text-slate-500 mb-3">
                        Ya has enviado los siguientes archivos para este radicado. Puedes consultarlos abajo o agregar más documentos utilizando el panel de la derecha.
                    </p>

                    <div class="space-y-2">
                        @foreach($salidas as $adjunto)
                            @php
                                $ext = strtolower(pathinfo($adjunto->nombre_original, PATHINFO_EXTENSION));
                                $iconClass = 'ph-file-text text-slate-600 bg-slate-100';
                                if ($ext === 'pdf') $iconClass = 'ph-file-pdf text-red-600 bg-red-50';
                                elseif (in_array($ext, ['doc', 'docx'])) $iconClass = 'ph-file-doc text-blue-600 bg-blue-50';
                                elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) $iconClass = 'ph-file-xls text-emerald-600 bg-emerald-50';
                                elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) $iconClass = 'ph-file-image text-indigo-600 bg-indigo-50';
                                elseif (in_array($ext, ['zip', 'rar', '7z'])) $iconClass = 'ph-file-zip text-amber-600 bg-amber-50';
                            @endphp
                            <div class="p-2.5 bg-emerald-50/40 border border-emerald-200/60 rounded-xl flex items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-base shrink-0 {{ $iconClass }}">
                                        <i class="ph {{ explode(' ', $iconClass)[0] }}"></i>
                                    </div>
                                    <span class="font-semibold text-slate-800 truncate" title="{{ $adjunto->nombre_original }}">
                                        {{ $adjunto->nombre_original }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="{{ URL::signedRoute('radicados.public.adjuntos.ver', ['radicado' => $radicado->id, 'responsable' => $responsable->id, 'adjunto' => $adjunto->id]) }}" 
                                       target="_blank"
                                       class="text-emerald-700 hover:underline font-bold">
                                        Ver
                                    </a>
                                    <span class="text-slate-300">|</span>
                                    <a href="{{ URL::signedRoute('radicados.public.adjuntos.descargar', ['radicado' => $radicado->id, 'responsable' => $responsable->id, 'adjunto' => $adjunto->id]) }}" 
                                       class="text-emerald-700 hover:underline font-bold">
                                        Descargar
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

            <!-- Right Column: Subida de Respuesta (5 cols) -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-lg shadow-slate-200/50 sticky top-24"
                     x-data="{
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
                        getFileTheme(name) {
                            const ext = name.split('.').pop().toLowerCase();
                            if (ext === 'pdf') return { icon: 'ph-file-pdf', color: 'text-red-600 bg-red-50' };
                            if (['doc', 'docx'].includes(ext)) return { icon: 'ph-file-doc', color: 'text-blue-600 bg-blue-50' };
                            if (['xls', 'xlsx', 'csv'].includes(ext)) return { icon: 'ph-file-xls', color: 'text-emerald-600 bg-emerald-50' };
                            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) return { icon: 'ph-file-image', color: 'text-indigo-600 bg-indigo-50' };
                            if (['zip', 'rar', '7z'].includes(ext)) return { icon: 'ph-file-zip', color: 'text-amber-600 bg-amber-50' };
                            return { icon: 'ph-file-text', color: 'text-slate-600 bg-slate-50' };
                        }
                     }">
                    
                    <div class="mb-5">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-3">
                            <i class="ph ph-upload-simple font-bold"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 leading-tight">
                            {{ $salidas->isNotEmpty() ? 'Adjuntar Documentos Adicionales' : 'Subir Documento(s) de Respuesta' }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            {{ $salidas->isNotEmpty() 
                                ? 'Puedes seguir subiendo más oficios o soportes mientras el trámite no haya sido cerrado formalmente.' 
                                : 'Adjunta uno o varios documentos oficiales que den respuesta al radicado (oficios, memorandos, actas, etc.).' }}
                        </p>
                    </div>

                    <form action="{{ URL::signedRoute('radicados.public.respuesta.store', ['radicado' => $radicado->id, 'responsable' => $responsable->id]) }}" 
                          method="POST" 
                          enctype="multipart/form-data">
                        @csrf

                        <!-- Real file input submitted in POST -->
                        <input 
                            x-ref="submitInput" 
                            type="file" 
                            name="archivos_salida[]" 
                            multiple 
                            class="hidden">

                        <!-- Hidden picker input to trigger system dialog without clearing submitInput -->
                        <input 
                            x-ref="pickerInput" 
                            type="file" 
                            multiple 
                            class="hidden" 
                            @change="addFiles($event.target.files); $event.target.value = ''">

                        <!-- Dropzone Area -->
                        <div 
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="isDragging = false; addFiles($event.dataTransfer.files)"
                            @click="$refs.pickerInput.click()"
                            :class="isDragging ? 'border-blue-500 bg-blue-50/70 ring-2 ring-blue-400/30' : 'border-slate-300 hover:border-blue-400 bg-slate-50/60'"
                            class="border-2 border-dashed rounded-2xl p-6 text-center transition-all cursor-pointer group relative">
                            
                            <div class="flex flex-col items-center justify-center pointer-events-none">
                                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-xs">
                                    <i class="ph ph-cloud-arrow-up text-2xl"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-800 mb-1">
                                    Arrastra tus archivos aquí o haz clic
                                </p>
                                <p class="text-xs text-slate-500">
                                    Soporta múltiples archivos: PDF, Word, Excel, Imágenes, ZIP (hasta 25 MB c/u)
                                </p>
                            </div>
                        </div>

                        <!-- Selected Files Preview List -->
                        <template x-if="files.length > 0">
                            <div class="mt-4 space-y-2.5">
                                <div class="flex items-center justify-between text-xs text-slate-500 border-b border-slate-100 pb-2">
                                    <span class="font-bold text-slate-700 flex items-center gap-1.5">
                                        <i class="ph ph-check-circle text-emerald-600"></i>
                                        <span x-text="files.length + ' archivo(s) listo(s)'"></span>
                                        (<span x-text="getTotalSize()"></span>)
                                    </span>
                                    <button type="button" @click="clearAll()" class="text-red-500 hover:text-red-700 font-semibold hover:underline">
                                        Quitar todos
                                    </button>
                                </div>

                                <div class="max-h-56 overflow-y-auto space-y-2 pr-1">
                                    <template x-for="(file, index) in files" :key="file.name + '-' + file.size">
                                        <div class="p-2.5 bg-slate-50 border border-slate-200/80 rounded-xl flex items-center justify-between gap-3 text-xs">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-lg shrink-0" :class="getFileTheme(file.name).color">
                                                    <i class="ph" :class="getFileTheme(file.name).icon"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-slate-800 truncate" x-text="file.name"></p>
                                                    <span class="text-[10px] text-slate-400 font-medium" x-text="formatBytes(file.size)"></span>
                                                </div>
                                            </div>
                                            <button type="button" @click="removeFile(index)" class="text-slate-400 hover:text-red-600 p-1 rounded-md transition" title="Eliminar">
                                                <i class="ph ph-trash text-base"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Submit Button -->
                        <div class="mt-6">
                            <button type="submit" 
                                    :disabled="files.length === 0"
                                    :class="files.length === 0 ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-blue-600 hover:bg-blue-700 active:scale-[0.99]'"
                                    class="w-full flex items-center justify-center gap-2 py-3.5 px-4 rounded-xl text-white font-bold text-sm shadow-md shadow-blue-500/20 transition-all">
                                <i class="ph ph-paper-plane-tilt text-lg"></i>
                                <span>Enviar Respuesta(s)</span>
                            </button>
                            <p class="text-[11px] text-slate-400 text-center mt-2.5">
                                Se notificará automáticamente al equipo de correspondencia de SIRAD.
                            </p>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-400 py-6 border-t border-slate-200 mt-12 w-full">
        <p>© {{ date('Y') }} SIRAD - Sistema de Radicación y Gestión Documental.</p>
    </footer>

</body>
</html>
