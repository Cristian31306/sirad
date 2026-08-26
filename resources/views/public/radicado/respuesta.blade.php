<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Respuesta - Radicado {{ $radicado->numero_radicado }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #f3f4f6; }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Subir Respuesta a Radicado
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Radicado No. {{ $radicado->numero_radicado }}
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>- {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="space-y-6" action="{{ URL::signedRoute('radicados.public.respuesta.store', ['radicado' => $radicado->id, 'responsable' => $responsable->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Detalles del Trámite</h3>
                    <div class="mt-2 max-w-xl text-sm text-gray-500">
                        <p><strong>Asunto:</strong> {{ $radicado->asunto }}</p>
                        <p><strong>Remitente:</strong> {{ $radicado->remitente }}</p>
                        <p><strong>Responsable:</strong> {{ $responsable->nombre }}</p>
                    </div>
                </div>

                <div>
                    <label for="archivo_salida" class="block text-sm font-medium text-gray-700">
                        Documento de Respuesta (PDF, DOCX, etc.)
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="archivo_salida" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>Seleccionar un archivo</span>
                                    <input id="archivo_salida" name="archivo_salida" type="file" class="sr-only" required>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500" id="file-name">
                                Ningún archivo seleccionado (Max. 10MB)
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Subir y Enviar Respuesta
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('archivo_salida').addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : 'Ningún archivo seleccionado';
            document.getElementById('file-name').textContent = fileName;
        });
    </script>
</body>
</html>
