@props(['auditoria', 'badgeText', 'badgeColor'])

@php
    $isSystem = empty($auditoria->user_id);
    $actorName = $isSystem ? 'system_process' : optional($auditoria->user)->name;
    $actorIcon = $isSystem ? 'ph-robot' : 'ph-user';
@endphp

<tbody x-data="{ expanded: false }" class="divide-y divide-gray-100 text-gray-700">
    <tr class="hover:bg-gray-50/50 transition cursor-pointer group" @click="expanded = !expanded">
        <td class="px-6 py-4 whitespace-nowrap text-gray-500">
            <div class="font-mono text-sm">{{ $auditoria->created_at->format('Y-m-d') }}</div>
            <div class="font-mono text-xs text-gray-400 mt-0.5">{{ $auditoria->created_at->format('H:i:s') }}</div>
        </td>
        <td class="px-6 py-4">
            <div class="font-bold text-gray-800 text-sm mb-1">
                {{ $auditoria->accion }}
            </div>
            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                <i class="ph {{ $actorIcon }} text-gray-400"></i> 
                <span>{{ $actorName }}</span>
            </div>
        </td>
        <td class="px-6 py-4">
            <div class="font-bold text-gray-800 text-sm">
                {{ $auditoria->modelo }} <span class="text-gray-400 font-normal">#{{ $auditoria->modelo_id }}</span>
            </div>
        </td>
        <td class="px-6 py-4">
            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $badgeColor }}">
                {{ $badgeText }}
            </span>
        </td>
        <td class="px-6 py-4 text-center">
            @if($auditoria->firma_hash)
                @if($auditoria->isFirmaValida())
                    <i class="ph ph-shield-check text-xl text-green-500" title="Registro Íntegro (Firma Válida)"></i>
                @else
                    <i class="ph ph-shield-warning text-xl text-red-500 animate-pulse" title="¡ALERTA! El registro ha sido manipulado directamente en la base de datos"></i>
                @endif
            @else
                <i class="ph ph-shield text-xl text-gray-300" title="Registro antiguo (sin firma)"></i>
            @endif
        </td>
        <td class="px-6 py-4 text-right text-gray-400">
            <i class="ph ph-caret-down text-lg transition-transform duration-200 inline-block" :class="{ 'rotate-180': expanded }"></i>
        </td>
    </tr>

    <!-- Expanded JSON View -->
    <tr x-show="expanded" x-transition style="display: none;">
        <td colspan="6" class="p-0 border-b border-gray-100 bg-gray-50">
            <div class="px-8 py-6 font-mono text-xs">
                @if($auditoria->detalles)
                    <div class="flex items-center justify-between text-gray-500 mb-4 border-b border-gray-200 pb-2">
                        <span class="flex items-center gap-1 font-semibold text-gray-700"><i class="ph ph-brackets-curly"></i> Detalles del Payload</span>
                        <span class="text-[10px] uppercase">Firma HMAC SHA-256</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if(isset($auditoria->detalles['original']) && isset($auditoria->detalles['nuevo']))
                            <div>
                                <div class="text-red-500 mb-2 font-semibold flex items-center gap-1"><i class="ph ph-minus-circle"></i> Estado Original</div>
                                <pre class="text-gray-700 bg-white p-4 rounded-xl border border-red-100 overflow-x-auto m-0 shadow-sm"><code>{{ json_encode($auditoria->detalles['original'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>
                            <div>
                                <div class="text-green-500 mb-2 font-semibold flex items-center gap-1"><i class="ph ph-plus-circle"></i> Nuevo Estado</div>
                                <pre class="text-gray-700 bg-white p-4 rounded-xl border border-green-100 overflow-x-auto m-0 shadow-sm"><code>{{ json_encode($auditoria->detalles['nuevo'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>
                        @else
                            <div class="md:col-span-2">
                                <pre class="text-gray-700 bg-white p-4 rounded-xl border border-gray-200 overflow-x-auto m-0 shadow-sm"><code>{{ json_encode($auditoria->detalles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>
                        @endif
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-200 text-gray-500 flex flex-wrap gap-6">
                        <div><span class="text-gray-400 font-semibold">IP:</span> {{ $auditoria->ip_address ?? 'Desconocida' }}</div>
                        <div class="truncate max-w-md" title="{{ $auditoria->user_agent }}"><span class="text-gray-400 font-semibold">Dispositivo:</span> {{ $auditoria->user_agent ?? 'Desconocido' }}</div>
                        <div class="truncate max-w-xs" title="{{ $auditoria->firma_hash }}"><span class="text-gray-400 font-semibold">Hash:</span> {{ $auditoria->firma_hash ?? 'N/A' }}</div>
                    </div>
                @else
                    <div class="text-gray-500 italic py-4 text-center bg-white rounded-xl border border-gray-200">No hay detalles adicionales registrados para este evento.</div>
                @endif
            </div>
        </td>
    </tr>
</tbody>
