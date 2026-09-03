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
        [x-cloak] { display: none !important; }
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
                    <p class="text-xs text-emerald-700 mt-0.5">La información fue registrada correctamente y está disponible para el equipo.</p>
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
        <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-blue-900/10 mb-6 relative overflow-hidden">
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
                        @if($radicado->estado_respuesta === 'lista_para_revision')
                            <span class="text-xs font-bold px-2.5 py-1 rounded-md bg-emerald-400 text-emerald-950 border border-emerald-300 inline-flex items-center gap-1">
                                <i class="ph-bold ph-check"></i> Respuesta Lista para Revisión
                            </span>
                        @elseif($radicado->estado_respuesta === 'en_tramite')
                            <span class="text-xs font-bold px-2.5 py-1 rounded-md bg-amber-400 text-amber-950 border border-amber-300 inline-flex items-center gap-1">
                                <i class="ph-bold ph-clock"></i> En Trámite / Avance
                            </span>
                        @endif
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $radicado->numero_radicado }}</h2>
                    <p class="text-sm text-blue-100 mt-1 max-w-2xl font-medium leading-relaxed">
                        {{ $radicado->asunto }}
                    </p>
                </div>

                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/15 min-w-[210px] shrink-0">
                    <span class="block text-[11px] font-semibold text-blue-200 uppercase tracking-wider">Fecha Límite</span>
                    <span class="block text-lg font-bold text-white mt-0.5">
                        {{ \Carbon\Carbon::parse($radicado->fecha_limite)->format('d/m/Y') }}
                    </span>
                    <span class="block text-[11px] text-blue-200 mt-1">
                        Tu sesión: <strong class="text-white">{{ $responsable->nombre }}</strong>
                    </span>
                </div>
            </div>
        </div>

        <!-- Card: Equipo de Responsables Asignados -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-base">
                        <i class="ph-bold ph-users-three"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Equipo de Funcionarios Asignados ({{ $radicado->responsables->count() }})</h3>
                        <p class="text-[11px] text-slate-500">Este radicado puede ser gestionado por cualquiera de los funcionarios de este equipo.</p>
                    </div>
                </div>
                @if($radicado->estado_respuesta === 'lista_para_revision')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 self-start sm:self-auto">
                        <i class="ph-bold ph-check-circle"></i> Respuesta Lista para Revisión
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($radicado->responsables as $resp)
                    <div class="p-3 rounded-2xl border {{ $resp->id === $responsable->id ? 'border-blue-300 bg-blue-50/50 shadow-xs ring-1 ring-blue-200' : 'border-slate-100 bg-slate-50/60' }} flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl {{ $resp->id === $responsable->id ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/30' : 'bg-slate-200 text-slate-700' }} flex items-center justify-center font-bold text-sm shrink-0">
                            {{ substr($resp->nombre, 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-bold text-xs text-slate-900 truncate">{{ $resp->nombre }}</span>
                                @if($resp->id === $responsable->id)
                                    <span class="text-[9px] uppercase tracking-wider font-extrabold px-1.5 py-0.5 rounded bg-blue-600 text-white">Tú</span>
                                @endif
                            </div>
                            <span class="text-[11px] text-slate-500 block truncate mt-0.5" title="{{ $resp->correo }}">{{ $resp->correo }}</span>
                            @if($resp->especialidad)
                                <span class="text-[10px] text-slate-400 block truncate">{{ $resp->especialidad }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Grid Layout: Info + Received Documents (Left) and Response Uploader (Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Column: Detalles, Documentos y Bitácora (7 cols) -->
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
                            <dd class="font-bold text-slate-800 text-sm">{{ $radicado->empresa ?: 'Particular' }}</dd>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl">
                            <dt class="text-slate-400 font-semibold mb-0.5">Tipo de Trámite</dt>
                            <dd class="font-bold text-slate-800">{{ optional($radicado->tipoTramite)->nombre ?: 'General' }}</dd>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl">
                            <dt class="text-slate-400 font-semibold mb-0.5">Medio de Recepción</dt>
                            <dd class="font-bold text-slate-800">{{ $radicado->medio }}</dd>
                        </div>
                    </dl>

                    @if($radicado->observaciones)
                        <div class="mt-4 p-3 bg-blue-50/50 rounded-xl border border-blue-100/60 text-xs">
                            <span class="font-semibold text-blue-900 block mb-0.5">Observaciones de Radicación:</span>
                            <p class="text-blue-800 leading-relaxed">{{ $radicado->observaciones }}</p>
                        </div>
                    @endif
                </div>

                <!-- Card: Documentos Recibidos (Entrada) -->
                @php
                    $entradas = $radicado->adjuntos->where('tipo', 'entrada');
                    $salidas = $radicado->adjuntos->where('tipo', 'salida');
                @endphp

                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="ph ph-file-arrow-down text-blue-600 text-lg"></i>
                            Documentos Recibidos del Ciudadano
                            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 font-bold ml-1">
                                {{ $entradas->count() }}
                            </span>
                        </h3>

                        @if($entradas->count() >= 2)
                        <a href="{{ URL::signedRoute('radicados.public.adjuntos.descargar-todos', ['radicado' => $radicado->id, 'responsable' => $responsable->id, 'tipo' => 'entrada']) }}" 
                           class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-xl border border-blue-200 transition">
                            <i class="ph ph-archive text-sm"></i>
                            Descargar todos (.ZIP)
                        </a>
                        @endif
                    </div>

                    @if($entradas->isEmpty())
                        <p class="text-xs text-slate-400 italic">No se adjuntaron documentos iniciales para este radicado.</p>
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
                                @endphp
                                <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-2xl flex items-center justify-between gap-3 text-xs hover:bg-slate-100/70 transition">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg shrink-0 {{ $iconClass }}">
                                            <i class="ph {{ explode(' ', $iconClass)[0] }}"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-800 truncate" title="{{ $adjunto->nombre_original }}">
                                                {{ $adjunto->nombre_original }}
                                            </p>
                                            <span class="text-[10px] text-slate-400">Documento Inicial</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <a href="{{ URL::signedRoute('radicados.public.adjuntos.ver', ['radicado' => $radicado->id, 'responsable' => $responsable->id, 'adjunto' => $adjunto->id]) }}" 
                                           target="_blank"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl transition">
                                            <i class="ph ph-eye text-sm"></i>
                                            <span>Ver</span>
                                        </a>
                                        <a href="{{ URL::signedRoute('radicados.public.adjuntos.descargar', ['radicado' => $radicado->id, 'responsable' => $responsable->id, 'adjunto' => $adjunto->id]) }}" 
                                           class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition">
                                            <i class="ph ph-download-simple text-sm"></i>
                                            <span>Descargar</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Card: Documentos de Respuesta ya Enviados (Salida) -->
                @if($salidas->isNotEmpty())
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="ph ph-paper-plane-tilt text-emerald-600 text-lg"></i>
                            Documentos de Respuesta Cargados
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
                        Archivos de soporte o respuesta cargados por los responsables para este radicado.
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
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-base shrink-0 {{ $iconClass }}">
                                        <i class="ph {{ explode(' ', $iconClass)[0] }}"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-semibold text-slate-800 block truncate" title="{{ $adjunto->nombre_original }}">
                                            {{ $adjunto->nombre_original }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 block truncate">
                                            Subido por: <strong class="text-slate-600">{{ optional($adjunto->responsable)->nombre ?? 'Equipo' }}</strong> • {{ $adjunto->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
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

                <!-- Card: Bitácora de Notas y Avances de los Responsables -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="ph ph-chats-circle text-blue-600 text-lg"></i>
                            Bitácora de Notas y Avances
                            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 font-bold ml-1">
                                {{ $radicado->notas->count() }}
                            </span>
                        </h3>
                    </div>

                    <p class="text-xs text-slate-500 mb-4">
                        Comentarios, avances preliminares y observaciones compartidas entre los funcionarios asignados y el personal de correspondencia.
                    </p>

                    <div class="space-y-3">
                        @forelse($radicado->notas as $nota)
                            <div class="p-3.5 rounded-2xl border {{ $nota->responsable_id === $responsable->id ? 'bg-blue-50/50 border-blue-200' : 'bg-slate-50 border-slate-200/70' }} text-xs">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-bold text-slate-900">{{ $nota->autor_nombre }}</span>
                                        @if($nota->responsable_id === $responsable->id)
                                            <span class="text-[9px] uppercase tracking-wider font-extrabold px-1.5 py-0.5 rounded bg-blue-600 text-white">Tú</span>
                                        @endif
                                        @if($nota->user_id)
                                            <span class="text-[9px] uppercase tracking-wider font-extrabold px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-800">SIRAD</span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] text-slate-400 shrink-0">{{ $nota->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $nota->contenido }}</p>
                            </div>
                        @empty
                            <div class="text-center py-6 px-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200 text-slate-400 text-xs">
                                <i class="ph ph-chat-slash text-2xl block mb-1 text-slate-300"></i>
                                No hay notas u observaciones registradas todavía. Puedes agregar una utilizando el formulario de la derecha.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- Right Column: Formulario de Carga y Nota (5 cols) -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-lg shadow-slate-200/50 sticky top-24"
                     x-data="{
                        files: [],
                        isDragging: false,
                        notaTexto: '',
                        estadoEntrega: 'avance',
                        addFiles(fileList) {
                            const currentKeys = this.files.map(f => f.name + '-' + f.size);
                            const maxSizeBytes = 10 * 1024 * 1024; // 10MB
                            for (let i = 0; i < fileList.length; i++) {
                                const file = fileList[i];
                                if (file.size > maxSizeBytes) {
                                    alert(`El archivo "${file.name}" supera el límite de 10 MB.`);
                                    continue;
                                }
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
                            this.files.forEach(file => dt.items.add(file));
                            this.$refs.submitInput.files = dt.files;
                        },
                        formatBytes(bytes) {
                            if (bytes === 0) return '0 Bytes';
                            const k = 1024;
                            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
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
                        },
                        canSubmit() {
                            return this.files.length > 0 || (this.notaTexto && this.notaTexto.trim().length > 0);
                        }
                     }">
                    
                    <div class="mb-5">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-3">
                            <i class="ph ph-upload-simple font-bold"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 leading-tight">
                            {{ $salidas->isNotEmpty() ? 'Adjuntar Documentos Adicionales' : 'Cargar Respuesta o Avance' }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Adjunta documentos de soporte o registra notas de avance para la resolución de este trámite.
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
                            class="border-2 border-dashed rounded-2xl p-5 text-center transition-all cursor-pointer group relative">
                            
                            <div class="flex flex-col items-center justify-center pointer-events-none">
                                <div class="w-11 h-11 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center mb-2.5 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-xs">
                                    <i class="ph ph-cloud-arrow-up text-2xl"></i>
                                </div>
                                <p class="text-xs font-bold text-slate-800 mb-0.5">
                                    Arrastra tus archivos aquí o haz clic
                                </p>
                                <p class="text-[11px] text-slate-500">
                                    PDF, Word, Excel, Imágenes, ZIP (hasta 25 MB c/u)
                                </p>
                            </div>
                        </div>

                        <!-- Selected Files Preview List -->
                        <template x-if="files.length > 0">
                            <div class="mt-3.5 space-y-2">
                                <div class="flex items-center justify-between text-xs text-slate-500 border-b border-slate-100 pb-1.5">
                                    <span class="font-bold text-slate-700 flex items-center gap-1">
                                        <i class="ph ph-check-circle text-emerald-600"></i>
                                        <span x-text="files.length + ' archivo(s) listo(s)'"></span>
                                        (<span x-text="getTotalSize()"></span>)
                                    </span>
                                    <button type="button" @click="clearAll()" class="text-red-500 hover:text-red-700 font-semibold hover:underline text-[11px]">
                                        Quitar todos
                                    </button>
                                </div>

                                <div class="max-h-48 overflow-y-auto space-y-1.5 pr-1">
                                    <template x-for="(file, index) in files" :key="file.name + '-' + file.size">
                                        <div class="p-2 bg-slate-50 border border-slate-200/80 rounded-xl flex items-center justify-between gap-2 text-xs">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-sm shrink-0" :class="getFileTheme(file.name).color">
                                                    <i class="ph" :class="getFileTheme(file.name).icon"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-slate-800 truncate" x-text="file.name"></p>
                                                    <span class="text-[10px] text-slate-400 font-medium" x-text="formatBytes(file.size)"></span>
                                                </div>
                                            </div>
                                            <button type="button" @click="removeFile(index)" class="text-slate-400 hover:text-red-600 p-1 rounded-md transition" title="Eliminar">
                                                <i class="ph ph-trash text-sm"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Textarea de Nota u Observación -->
                        <div class="mt-4">
                            <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                                <span>Nota u Observación (Opcional):</span>
                                <span class="text-[10px] text-slate-400 font-normal">Visible para el equipo</span>
                            </label>
                            <textarea 
                                name="nota" 
                                rows="3" 
                                x-model="notaTexto"
                                placeholder="Ej: Se adjunta borrador preliminar, aún faltan firmas o soportes..." 
                                class="w-full text-xs rounded-xl border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 resize-none p-3"></textarea>
                        </div>

                        <!-- Selector de Estado de Entrega -->
                        <div class="mt-4 p-3.5 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-2">
                            <span class="block text-xs font-bold text-slate-800">Tipo de Envío:</span>
                            
                            <label class="flex items-start gap-2.5 p-2 rounded-xl border transition cursor-pointer"
                                   :class="estadoEntrega === 'avance' ? 'bg-amber-50/70 border-amber-300 ring-1 ring-amber-200' : 'bg-white border-slate-200 hover:bg-slate-50'">
                                <input type="radio" name="estado_entrega" value="avance" x-model="estadoEntrega" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                                <div class="text-xs">
                                    <strong class="text-slate-900 block font-semibold">Guardar como avance / En trámite</strong>
                                    <span class="text-[11px] text-slate-500 block leading-tight">Queda en la bitácora para el equipo. <strong class="text-slate-700">No</strong> enviará correo a correspondencia.</span>
                                </div>
                            </label>

                            <label class="flex items-start gap-2.5 p-2 rounded-xl border transition cursor-pointer"
                                   :class="estadoEntrega === 'finalizar' ? 'bg-emerald-50/70 border-emerald-300 ring-1 ring-emerald-200' : 'bg-white border-slate-200 hover:bg-slate-50'">
                                <input type="radio" name="estado_entrega" value="finalizar" x-model="estadoEntrega" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                                <div class="text-xs">
                                    <strong class="text-slate-900 block font-semibold">Marcar respuesta FINALIZADA (Lista para revisión)</strong>
                                    <span class="text-[11px] text-slate-500 block leading-tight">Confirma que la respuesta está lista. <strong class="text-emerald-700">Notificará por correo</strong> a correspondencia para que procedan al cierre.</span>
                                </div>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-5">
                            <button type="submit" 
                                    :disabled="!canSubmit()"
                                    :class="!canSubmit() 
                                        ? 'opacity-50 cursor-not-allowed bg-slate-400' 
                                        : (estadoEntrega === 'finalizar' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/20' : 'bg-blue-600 hover:bg-blue-700 shadow-blue-500/20')"
                                    class="w-full flex items-center justify-center gap-2 py-3.5 px-4 rounded-xl text-white font-bold text-xs shadow-md transition-all cursor-pointer">
                                <i :class="estadoEntrega === 'finalizar' ? 'ph-bold ph-check-circle text-base' : 'ph-bold ph-floppy-disk text-base'"></i>
                                <span x-text="estadoEntrega === 'finalizar' ? 'Enviar y Marcar Lista para Revisión' : 'Guardar Avance / Documentos'"></span>
                            </button>
                            <p class="text-[10px] text-slate-400 text-center mt-2">
                                <span x-show="estadoEntrega === 'finalizar'">Se enviará una alerta inmediata por correo al personal de correspondencia de SIRAD.</span>
                                <span x-show="estadoEntrega === 'avance'">Solo guardará la información de avance sin enviar alertas de cierre.</span>
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
