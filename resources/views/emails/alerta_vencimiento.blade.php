<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Alerta de Vencimiento</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; line-height: 1.6; background-color: #f8fafc; color: #334155; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border-top: 6px solid #ef4444; }
        .header { background-color: #ffffff; padding: 32px 32px 0 32px; text-align: center; }
        .header span { color: #0f172a; font-size: 24px; font-weight: bold; letter-spacing: 1px; }
        .alert-box { background-color: #fef2f2; border-radius: 8px; padding: 16px; margin: 24px 32px; text-align: center; }
        .alert-title { color: #b91c1c; font-size: 18px; font-weight: bold; margin: 0; text-transform: uppercase; }
        .content { padding: 0 32px 32px 32px; }
        .subtitle { font-size: 15px; color: #64748b; margin-bottom: 24px; margin-top: 0; text-align: center; }
        .card { background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 32px; }
        .meta-table { width: 100%; }
        .meta-table td { padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .meta-table td:last-child { border-bottom: none; }
        .meta-label { font-weight: 600; color: #475569; width: 40%; }
        .meta-value { color: #0f172a; font-weight: 500; }
        .badge-red { background-color: #fee2e2; color: #b91c1c; padding: 2px 8px; border-radius: 9999px; font-size: 12px; font-weight: bold; }
        .action { text-align: center; margin-top: 32px; }
        .btn { display: inline-block; background-color: #ef4444; color: #ffffff; text-decoration: none; padding: 12px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; }
        .footer { padding: 24px 32px; text-align: center; font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; background-color: #f8fafc; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <span>SIRAD</span>
            </div>
            
            <div class="alert-box">
                <p class="alert-title">
                    @if($radicado->estado === 'vencido')
                        ¡ATENCIÓN! Trámite Vencido
                    @elseif(isset($diasFaltantes) && $diasFaltantes === 0)
                        🚨 ¡ATENCIÓN! ESTE TRÁMITE VENCE HOY
                    @else
                        ¡ATENCIÓN! Trámite próximo a vencer
                    @endif
                </p>
            </div>

            <div class="content">
                <p class="subtitle">
                    @if($radicado->estado === 'vencido')
                        El siguiente trámite ha superado su fecha límite legal de respuesta.
                    @elseif(isset($diasFaltantes) && $diasFaltantes === 0)
                        <strong>¡Hoy es el último día de plazo legal!</strong> Se requiere radicar y formalizar la respuesta de inmediato antes del cierre de jornada.
                    @else
                        @php
                            $tipoDiasTexto = ($radicado->tipoTramite && $radicado->tipoTramite->tipo_dias === 'calendario') ? 'días calendario' : 'días hábiles';
                            $diasTxt = isset($diasFaltantes) ? $diasFaltantes : 2;
                        @endphp
                        El siguiente trámite está a <strong>{{ $diasTxt }} {{ $tipoDiasTexto }}</strong> de vencer.
                    @endif
                </p>
                
                <div class="card">
                    <table class="meta-table" cellspacing="0" cellpadding="0">
                        <tr>
                            <td class="meta-label">Radicado</td>
                            <td class="meta-value">{{ $radicado->numero_radicado }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Tipo</td>
                            <td class="meta-value">{{ $radicado->tipoTramite->nombre }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Remitente</td>
                            <td class="meta-value">{{ $radicado->remitente }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Asunto</td>
                            <td class="meta-value">{{ $radicado->asunto }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Prioridad</td>
                            <td class="meta-value">{{ ucfirst($radicado->prioridad) }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Observaciones</td>
                            <td class="meta-value">{{ $radicado->observaciones ?: 'Ninguna' }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label" style="border-bottom: none; padding-bottom: 0;">Fecha Límite</td>
                            <td class="meta-value" style="border-bottom: none; padding-bottom: 0;">
                                <span class="badge-red">{{ \Carbon\Carbon::parse($radicado->fecha_limite)->format('d/m/Y') }}</span>
                            </td>
                        </tr>
                    </table>
                </div>

                @if(isset($responsable) && $responsable)
                <div class="action">
                    <a href="{{ URL::signedRoute('radicados.public.respuesta', ['radicado' => $radicado->id, 'responsable' => $responsable->id]) }}" style="display: inline-block; background-color: #ef4444; color: #ffffff !important; text-decoration: none; padding: 12px 32px; border-radius: 8px; font-weight: 600; font-size: 16px;">Subir Respuesta</a>
                    <p style="margin-top: 12px; font-size: 13px; color: #64748b;">A través de este enlace seguro podrá adjuntar su documento de respuesta directamente.</p>
                </div>
                @endif
            </div>
            <div class="footer">
                Este es un mensaje automático, por favor no responda este correo.
            </div>
        </div>
    </div>
</body>
</html>
