<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respuesta Completada - Radicado {{ $radicado->numero_radicado }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #f3f4f6; }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 text-center">
            
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 mb-2">¡Respuesta recibida exitosamente!</h2>
            
            <p class="text-sm text-gray-600 mb-6">
                El documento de respuesta para el radicado <strong>{{ $radicado->numero_radicado }}</strong> ha sido registrado correctamente en el sistema SIRAD. 
                El equipo encargado será notificado.
            </p>
            
            <p class="text-xs text-gray-500 mt-6">
                Ya puede cerrar esta pestaña de forma segura.
            </p>
        </div>
    </div>
</body>
</html>
