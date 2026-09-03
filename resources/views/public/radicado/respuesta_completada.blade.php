<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respuesta Completada - Radicado {{ $radicado->numero_radicado }} - SIRAD</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between text-slate-800 antialiased selection:bg-blue-500 selection:text-white pb-12">
    
    <!-- Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-black text-xl shadow-md shadow-blue-500/20">
                    S
                </div>
                <div>
                    <h1 class="font-bold text-base text-slate-900 leading-tight">SIRAD</h1>
                    <p class="text-[11px] text-slate-500 font-medium">Sistema de Radicación y Correspondencia</p>
                </div>
            </div>
            
            <div class="flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                <i class="ph ph-check-circle text-base text-emerald-600"></i>
                <span>Trámite Cerrado</span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        
        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3.5 rounded-2xl flex items-center gap-3 text-sm shadow-xs">
                <i class="ph ph-check-circle text-xl shrink-0 text-emerald-600"></i>
                <p class="font-bold">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Success Hero Card -->
        <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-md text-center mb-6">
            <div class="w-16 h-16 rounded-3xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mx-auto mb-4 shadow-sm">
                <i class="ph ph-check font-bold"></i>
            </div>
            
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                ¡Respuesta Registrada con Éxito!
            </h2>
            <p class="text-sm text-slate-500 mt-2 max-w-lg mx-auto leading-relaxed">
                El radicado <strong class="text-slate-800">{{ $radicado->numero_radicado }}</strong> ha sido completado y cerrado formalmente en el sistema SIRAD.
            </p>
            <div class="mt-4 inline-flex items-center gap-2 text-xs font-semibold text-slate-600 bg-slate-100 px-3.5 py-1.5 rounded-full">
                <span>Tu sesión:</span>
                <strong class="text-slate-900">{{ $responsable->nombre }}</strong>
            </div>
        </div>

        <!-- Card: Equipo de Responsables -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-xs mb-6">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                <i class="ph-bold ph-users-three text-blue-600 text-base"></i>
                Equipo de Responsables Asignados ({{ $radicado->responsables->count() }})
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                @foreach($radicado->responsables as $resp)
                    <div class="p-2.5 rounded-xl border {{ $resp->id === $responsable->id ? 'border-blue-300 bg-blue-50/50 ring-1 ring-blue-200' : 'border-slate-100 bg-slate-50' }} flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg {{ $resp->id === $responsable->id ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600' }} flex items-center justify-center font-bold text-xs shrink-0">
                            {{ substr($resp->nombre, 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="font-bold text-xs text-slate-800 truncate block">{{ $resp->nombre }}</span>
                            <span class="text-[10px] text-slate-400 truncate block">{{ $resp->correo }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @php
            $salidas = $radicado->adjuntos()->where('tipo', 'salida')->with('responsable')->get();
            $entradas = $radicado->adjuntos()->where('tipo', 'entrada')->get();
            $notas = $radicado->notas()->latest()->get();
        @endphp

        <div class="space-y-6">
            
            <!-- Documentos de Respuesta Entregados -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="ph ph-paper-plane-tilt text-emerald-600 text-lg"></i>
                        Documento(s) de Respuesta del Expediente
                        <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-bold ml-1">
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

                <div class="space-y-2.5">
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
                        <div class="p-3 bg-emerald-50/40 border border-emerald-200/70 rounded-2xl flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl shrink-0 {{ $iconClass }}">
                                    <i class="ph {{ explode(' ', $iconClass)[0] }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate" title="{{ $adjunto->nombre_original }}">
                                        {{ $adjunto->nombre_original }}
                                    </p>
                                    <span class="text-[10px] text-slate-500">
                                        Subido por: <strong>{{ optional($adjunto->responsable)->nombre ?? 'Equipo' }}</strong> • {{ $adjunto->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="{{ URL::signedRoute('radicados.public.adjuntos.ver', ['radicado' => $radicado->id, 'responsable' => $responsable->id, 'adjunto' => $adjunto->id]) }}" 
                                   target="_blank"
                                   class="inline-flex items-center gap-1 text-xs font-bold text-slate-700 hover:text-emerald-700 bg-white border border-slate-200 px-2.5 py-1.5 rounded-xl shadow-2xs transition">
                                    <i class="ph ph-eye text-sm"></i>
                                    <span>Ver</span>
                                </a>
                                <a href="{{ URL::signedRoute('radicados.public.adjuntos.descargar', ['radicado' => $radicado->id, 'responsable' => $responsable->id, 'adjunto' => $adjunto->id]) }}" 
                                   class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 hover:text-white hover:bg-emerald-600 bg-white border border-emerald-200 px-2.5 py-1.5 rounded-xl shadow-2xs transition">
                                    <i class="ph ph-download-simple text-sm"></i>
                                    <span>Descargar</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Bitácora de Notas Registradas -->
            @if($notas->isNotEmpty())
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs">
                <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
                    <i class="ph ph-chats-circle text-blue-600 text-lg"></i>
                    Historial de Notas y Observaciones Registradas
                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold ml-1">
                        {{ $notas->count() }}
                    </span>
                </h3>

                <div class="space-y-2.5">
                    @foreach($notas as $nota)
                        <div class="p-3 bg-slate-50 border border-slate-200/70 rounded-xl text-xs">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="font-bold text-slate-800">{{ $nota->autor_nombre }}</span>
                                <span class="text-[10px] text-slate-400">{{ $nota->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-slate-600 leading-relaxed whitespace-pre-line">{{ $nota->contenido }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Documentos de Entrada Recibidos Originalmente -->
            @if($entradas->isNotEmpty())
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs">
                <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                    <i class="ph ph-folder text-slate-400"></i>
                    Documentos de Entrada Iniciales
                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-bold ml-1">
                        {{ $entradas->count() }}
                    </span>
                </h3>

                <div class="space-y-2">
                    @foreach($entradas as $adjunto)
                        <div class="p-2.5 bg-slate-50 border border-slate-200/60 rounded-xl flex items-center justify-between gap-3 text-xs">
                            <span class="font-medium text-slate-700 truncate" title="{{ $adjunto->nombre_original }}">
                                {{ $adjunto->nombre_original }}
                            </span>
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ URL::signedRoute('radicados.public.adjuntos.ver', ['radicado' => $radicado->id, 'responsable' => $responsable->id, 'adjunto' => $adjunto->id]) }}" 
                                   target="_blank"
                                   class="text-blue-600 hover:underline font-semibold">
                                    Ver
                                </a>
                                <span class="text-slate-300">|</span>
                                <a href="{{ URL::signedRoute('radicados.public.adjuntos.descargar', ['radicado' => $radicado->id, 'responsable' => $responsable->id, 'adjunto' => $adjunto->id]) }}" 
                                   class="text-blue-600 hover:underline font-semibold">
                                    Descargar
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

    </main>

    <!-- Footer -->
    <footer class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-400 py-6 border-t border-slate-200 mt-12 w-full">
        <p>© {{ date('Y') }} SIRAD - Sistema de Radicación y Gestión Documental. Ya puede cerrar esta pestaña de forma segura.</p>
    </footer>

</body>
</html>
