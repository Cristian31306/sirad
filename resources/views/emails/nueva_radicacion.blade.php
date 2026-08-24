<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Nueva Radicación</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; line-height: 1.6; background-color: #f8fafc; color: #334155; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .header { background-color: #1e293b; padding: 24px 32px; text-align: center; }
        .header img { height: 32px; vertical-align: middle; }
        .header span { color: #ffffff; font-size: 24px; font-weight: bold; vertical-align: middle; margin-left: 8px; letter-spacing: 1px; }
        .content { padding: 32px; }
        .title { font-size: 20px; font-weight: bold; color: #0f172a; margin-bottom: 8px; margin-top: 0; }
        .subtitle { font-size: 15px; color: #64748b; margin-bottom: 24px; margin-top: 0; }
        .card { background-color: #f1f5f9; border-radius: 12px; padding: 24px; margin-bottom: 32px; }
        .meta-table { width: 100%; }
        .meta-table td { padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .meta-table td:last-child { border-bottom: none; }
        .meta-label { font-weight: 600; color: #475569; width: 40%; }
        .meta-value { color: #0f172a; font-weight: 500; }
        .badge-green { background-color: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 9999px; font-size: 12px; font-weight: bold; }
        .action { text-align: center; margin-top: 32px; }
        .btn { display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; }
        .footer { padding: 24px 32px; text-align: center; font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <!-- Fallback a texto si no hay logo disponible -->
                <span>SIRAD</span>
            </div>
            <div class="content">
                @php
                    $nombreDestinatario = $responsable 
                        ? explode(' ', $responsable->nombre)[0] 
                        : ($radicado->responsables->isNotEmpty() ? explode(' ', $radicado->responsables->first()->nombre)[0] : 'Funcionario');
                @endphp
                <h1 class="title">Hola {{ $nombreDestinatario }},</h1>
                <p class="subtitle">Se le ha asignado un nuevo trámite en el sistema SIRAD.</p>
                
                <div class="card">
                    <table class="meta-table" cellspacing="0" cellpadding="0">
                        <tr>
                            <td class="meta-label">Radicado</td>
                            <td class="meta-value">{{ $radicado->numero_radicado }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Tipo de Trámite</td>
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
                                <span class="badge-green">{{ \Carbon\Carbon::parse($radicado->fecha_limite)->format('d/m/Y') }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="footer">
                Este es un mensaje automático, por favor no responda este correo.
            </div>
        </div>
    </div>
</body>
</html>
