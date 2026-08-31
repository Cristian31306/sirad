<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Bienvenido a SIRAD</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; line-height: 1.6; background-color: #f8fafc; color: #334155; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .header { background-color: #1e293b; padding: 24px 32px; text-align: center; }
        .header span { color: #ffffff; font-size: 24px; font-weight: bold; letter-spacing: 1px; }
        .content { padding: 32px; text-align: center; }
        .title { font-size: 20px; font-weight: bold; color: #0f172a; margin-bottom: 8px; margin-top: 0; }
        .subtitle { font-size: 15px; color: #64748b; margin-bottom: 24px; margin-top: 0; }
        .card { background-color: #f1f5f9; border-radius: 12px; padding: 24px; margin-bottom: 32px; text-align: left; }
        .password-box { background-color: #e2e8f0; padding: 12px; border-radius: 8px; font-family: monospace; font-size: 18px; font-weight: bold; text-align: center; color: #0f172a; margin-top: 8px; letter-spacing: 2px;}
        .footer { padding: 24px 32px; text-align: center; font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        .btn { display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; margin-top: 16px;}
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <span>SIRAD</span>
            </div>
            <div class="content">
                <h1 class="title">¡Bienvenido al sistema, {{ explode(' ', $user->name)[0] }}!</h1>
                <p class="subtitle">Se ha creado una cuenta para ti en el Sistema de Radicación (SIRAD).</p>
                
                <div class="card">
                    <p style="margin-top: 0;">Tus credenciales de acceso son las siguientes:</p>
                    <p><strong>Usuario/Correo:</strong> {{ $user->email }}</p>
                    <p style="margin-bottom: 4px;"><strong>Contraseña Temporal:</strong></p>
                    <div class="password-box">
                        {{ $password }}
                    </div>
                    <p style="font-size: 13px; color: #64748b; margin-top: 16px; margin-bottom: 0;">
                        Por razones de seguridad, deberás cambiar esta contraseña inmediatamente después de iniciar sesión por primera vez.
                    </p>
                </div>

                <a href="{{ route('login') }}" class="btn" style="color: #ffffff !important; text-decoration: none;">Iniciar Sesión Ahora</a>
            </div>
            <div class="footer">
                Este es un mensaje automático, por favor no responda este correo.
            </div>
        </div>
    </div>
</body>
</html>