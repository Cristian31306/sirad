<x-app-layout>
    <!-- Tarjetas de Resumen (Semáforo) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

        <!-- Pendientes -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between relative overflow-hidden group hover:shadow-md transition">
            <div class="absolute w-2 h-full bg-green-500 left-0 top-0"></div>
            <div>
                <div class="text-4xl font-bold text-green-600 mb-1">{{ $totales['pendientes'] }}</div>
                <div class="text-sm text-gray-600 font-medium">Pendientes</div>
                <div class="text-xs text-green-500 font-semibold mt-2 flex items-center gap-1">
                    <i class="ph ph-file-plus"></i> {{ $nuevosHoy }} nuevos hoy
                </div>
            </div>
            <div class="bg-green-50 p-4 rounded-full text-green-500 group-hover:scale-110 transition">
                <i class="ph ph-file-text text-3xl"></i>
            </div>
        </div>

        <!-- Alerta -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between relative overflow-hidden group hover:shadow-md transition">
            <div class="absolute w-2 h-full bg-yellow-500 left-0 top-0"></div>
            <div>
                <div class="text-4xl font-bold text-yellow-500 mb-1">{{ $totales['alertas'] }}</div>
                <div class="text-sm text-gray-600 font-medium">En Alerta</div>
                <div class="text-xs text-yellow-500 font-semibold mt-2 flex items-center gap-1">
                    <i class="ph ph-clock"></i> {{ $vencenHoy }} vencen hoy
                </div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-full text-yellow-500 group-hover:scale-110 transition">
                <i class="ph ph-warning text-3xl"></i>
            </div>
        </div>

        <!-- Vencidos -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between relative overflow-hidden group hover:shadow-md transition">
            <div class="absolute w-2 h-full bg-red-500 left-0 top-0"></div>
            <div>
                <div class="text-4xl font-bold text-red-500 mb-1">{{ $totales['vencidos'] }}</div>
                <div class="text-sm text-gray-600 font-medium">Vencidos</div>
                <div class="text-xs text-red-500 font-semibold mt-2 flex items-center gap-1">
                    <i class="ph ph-warning-circle"></i> Requieren atención
                </div>
            </div>
            <div class="bg-red-50 p-4 rounded-full text-red-500 group-hover:scale-110 transition">
                <i class="ph ph-clock text-3xl"></i>
            </div>
        </div>

        <!-- Completados -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between relative overflow-hidden group hover:shadow-md transition">
            <div class="absolute w-2 h-full bg-gray-400 left-0 top-0"></div>
            <div>
                <div class="text-4xl font-bold text-gray-700 mb-1">{{ $totales['completados'] }}</div>
                <div class="text-sm text-gray-600 font-medium">Completados</div>
                <div class="text-xs text-gray-500 font-semibold mt-2 flex items-center gap-1">
                    <i class="ph ph-check-circle"></i> Trámites cerrados este mes
                </div>
            </div>
            <div class="bg-gray-100 p-4 rounded-full text-gray-500 group-hover:scale-110 transition">
                <i class="ph ph-check-circle text-3xl"></i>
            </div>
        </div>
    </div>



    <!-- Tabla Radicados Prioritarios -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="ph ph-star text-yellow-500 text-xl"></i>
                Próximos Vencimientos
            </h3>
            <a href="{{ route('radicados.index') }}"
                class="text-sm text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1 bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm">
                Ver todos <i class="ph ph-arrow-right"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-xs text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold">Radicado</th>
                        <th class="px-6 py-4 font-semibold">Remitente</th>
                        <th class="px-6 py-4 font-semibold">Responsables</th>
                        <th class="px-6 py-4 font-semibold">Prioridad</th>
                        <th class="px-6 py-4 font-semibold">Estado</th>
                        <th class="px-6 py-4 font-semibold">Vence el</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm text-gray-700">
                    @forelse($proximosVencimientos->take(10) as $radicado)
                        <tr class="hover:bg-blue-50/30 transition cursor-pointer"
                            onclick="window.location='{{ route('radicados.show', $radicado) }}'">
                            <td class="px-6 py-4 font-bold text-gray-900">{{ $radicado->numero_radicado }}</td>
                            <td class="px-6 py-4">{{ Str::limit($radicado->remitente, 20) }}</td>
                            <td class="px-6 py-4">
                                @if($radicado->responsables->isNotEmpty())
                                    <span class="font-medium text-gray-700">{{ $radicado->responsables->pluck('nombre')->implode(', ') }}</span>
                                @else
                                    <span class="text-gray-400 italic">No asignado</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($radicado->prioridad == 'Alta')
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-100">
                                        Alta
                                    </span>
                                @elseif($radicado->prioridad == 'Media')
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-700 border border-yellow-100">
                                        Media
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-100">
                                        Baja
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($radicado->estado == 'pendiente')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Pendiente
                                    </span>
                                @elseif($radicado->estado == 'alerta')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-700 border border-yellow-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span> En Alerta
                                    </span>
                                @elseif($radicado->estado == 'vencido')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Vencido
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                @php
                                    $fechaLimite = \Carbon\Carbon::parse($radicado->fecha_limite);
                                @endphp
                                <span
                                    class="font-medium {{ $fechaLimite->isPast() && !$fechaLimite->isToday() ? 'text-red-600 font-bold' : ($fechaLimite->isToday() ? 'text-yellow-600 font-bold' : '') }}">
                                    {{ $fechaLimite->format('d/m/Y') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="ph ph-files text-4xl mb-2 block"></i>
                                    <p>No hay radicados prioritarios.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>