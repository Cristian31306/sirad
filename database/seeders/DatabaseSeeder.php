<?php

namespace Database\Seeders;

use App\Models\Auditoria;
use App\Models\Festivo;
use App\Models\Radicado;
use App\Models\Responsable;
use App\Models\SolicitudEdicion;
use App\Models\TipoTramite;
use App\Models\User;
use App\Services\DiasHabilesService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $diasService = new DiasHabilesService();

        // 1. Usuarios Principales
        $admin = User::firstOrCreate(
            ['email' => 'admin@sirad.gov.co'],
            [
                'name' => 'Director General (Admin)',
                'password' => Hash::make('Admin1234!'),
                'role' => 'admin',
                'permisos' => [],
                'must_change_password' => false,
                'email_verified_at' => now(),
            ]
        );

        $secretaria = User::firstOrCreate(
            ['email' => 'secretaria@sirad.gov.co'],
            [
                'name' => 'Secretaria General',
                'password' => Hash::make('Secretaria1234!'),
                'role' => 'usuario',
                'permisos' => [
                    'radicados.editar',
                    'radicados.anular',
                    'radicados.completar',
                    'responsables.gestionar',
                    'tipos_tramites.gestionar',
                    'solicitudes.gestionar',
                    'auditoria.ver',
                ],
                'must_change_password' => false,
                'email_verified_at' => now(),
            ]
        );

        // 2. Sincronización de Días Festivos Colombianos (Autónoma y Multi-año)
        $diasService->sincronizarFestivos(2025);
        $diasService->sincronizarFestivos(2026);
        $diasService->sincronizarFestivos(2027);

        // 3. Tipos de Trámite Oficiales
        $tramitePeticion = TipoTramite::firstOrCreate(
            ['nombre' => 'Derecho de Petición'],
            ['dias_habiles' => 15, 'activo' => true]
        );

        $tramiteInformacion = TipoTramite::firstOrCreate(
            ['nombre' => 'Solicitud de Información'],
            ['dias_habiles' => 10, 'activo' => true]
        );

        $tramiteVisita = TipoTramite::firstOrCreate(
            ['nombre' => 'Solicitud de Visita Técnica'],
            ['dias_habiles' => 10, 'activo' => true]
        );

        $tramiteConsulta = TipoTramite::firstOrCreate(
            ['nombre' => 'Consulta Jurídica Especial'],
            ['dias_habiles' => 30, 'activo' => true]
        );

        $tramiteQueja = TipoTramite::firstOrCreate(
            ['nombre' => 'Queja o Reclamo'],
            ['dias_habiles' => 15, 'activo' => true]
        );

        // 4. Funcionarios Responsables por Dependencia
        $respBenito = Responsable::firstOrCreate(
            ['correo' => 'benito.perez@sirad.gov.co'],
            ['nombre' => 'Ing. Benito Pérez', 'especialidad' => 'Infraestructura y Vías']
        );

        $respJuan = Responsable::firstOrCreate(
            ['correo' => 'juancarlos.gomez@sirad.gov.co'],
            ['nombre' => 'Dr. Juan Carlos Gómez', 'especialidad' => 'Asuntos Jurídicos y Normatividad']
        );

        $respMaria = Responsable::firstOrCreate(
            ['correo' => 'maria.fernandez@sirad.gov.co'],
            ['nombre' => 'Dra. María Claudia Fernández', 'especialidad' => 'Atención al Ciudadano y PQR']
        );

        $respAndres = Responsable::firstOrCreate(
            ['correo' => 'andres.restrepo@sirad.gov.co'],
            ['nombre' => 'Arq. Andrés Restrepo', 'especialidad' => 'Planeación y Control Urbano']
        );

        $respDiana = Responsable::firstOrCreate(
            ['correo' => 'diana.morales@sirad.gov.co'],
            ['nombre' => 'Lic. Diana Patricia Morales', 'especialidad' => 'Gestión Ambiental y Sostenibilidad']
        );

        $respCarlos = Responsable::firstOrCreate(
            ['correo' => 'carlos.castano@sirad.gov.co'],
            ['nombre' => 'C.P. Carlos Castaño', 'especialidad' => 'Hacienda y Presupuesto Municipal']
        );

        // Generar archivos demo en storage
        Storage::disk('public')->makeDirectory('radicados/entradas');
        Storage::disk('public')->makeDirectory('radicados/salidas');

        $pdfDemoEntrada = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj 3 0 obj<</Type/Page/MediaBox[0 0 595 842]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000009 00000 n\n0000000052 00000 n\n0000000101 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n178\n%%EOF";
        Storage::disk('public')->put('radicados/entradas/solicitud_oficial_comunidad.pdf', $pdfDemoEntrada);
        Storage::disk('public')->put('radicados/entradas/planos_hidrosanitarios_vias.pdf', $pdfDemoEntrada);
        Storage::disk('public')->put('radicados/salidas/oficio_respuesta_ofi2026.pdf', $pdfDemoEntrada);
        Storage::disk('public')->put('radicados/salidas/concepto_tecnico_favorable.pdf', $pdfDemoEntrada);

        // 5. Generación de Base Masiva de Radicados
        $hoy = Carbon::today();

        $radicadosData = [
            // --- GRUPO 1: PENDIENTES (VERDES) ---
            [
                'numero' => 'RAD-2026-001-DP',
                'sub_days' => 1,
                'tramite' => $tramitePeticion,
                'remitente' => 'Alfonso Jaramillo Correa',
                'empresa' => 'Junta de Acción Comunal Sector Centro',
                'asunto' => 'Solicitud de pavimentación y mantenimiento preventivo en la vía principal de acceso al barrio La Esperanza.',
                'medio' => 'Físico',
                'prioridad' => 'Alta',
                'observaciones' => 'Se adjunta memorial con 45 firmas de residentes.',
                'estado' => 'pendiente',
                'archivo_entrada_path' => 'radicados/entradas/solicitud_oficial_comunidad.pdf',
                'archivo_entrada_nombre' => 'memorial_firmas_comunidad.pdf',
                'responsables' => [$respBenito->id, $respAndres->id],
            ],
            [
                'numero' => 'RAD-2026-002-INF',
                'sub_days' => 2,
                'tramite' => $tramiteInformacion,
                'remitente' => 'Dra. Carolina Mendoza',
                'empresa' => 'Consorcio Vías del Norte',
                'asunto' => 'Copia de planos hidrosanitarios y certificado de uso de suelo del predio Calle 45 # 12-30.',
                'medio' => 'Correo Electrónico',
                'prioridad' => 'Media',
                'observaciones' => 'Solicitud remitida directamente a la Secretaría de Planeación.',
                'estado' => 'pendiente',
                'archivo_entrada_path' => 'radicados/entradas/planos_hidrosanitarios_vias.pdf',
                'archivo_entrada_nombre' => 'solicitud_copia_planos.pdf',
                'responsables' => [$respAndres->id],
            ],
            [
                'numero' => 'RAD-2026-003-CJ',
                'sub_days' => 3,
                'tramite' => $tramiteConsulta,
                'remitente' => 'Héctor Fabio Ramírez',
                'empresa' => 'Asociación de Comerciantes Formales',
                'asunto' => 'Concepto jurídico sobre aplicación del nuevo estatuto tributario y cobro de sobretasa ambiental.',
                'medio' => 'Portal Web',
                'prioridad' => 'Media',
                'observaciones' => 'Para estudio de la Dirección Jurídica y Secretaría de Hacienda.',
                'estado' => 'pendiente',
                'responsables' => [$respJuan->id, $respCarlos->id],
            ],
            [
                'numero' => 'RAD-2026-004-DP',
                'sub_days' => 2,
                'tramite' => $tramitePeticion,
                'remitente' => 'Lucía Restrepo de Valencia',
                'empresa' => 'Colegio Bilingüe San Jerónimo',
                'asunto' => 'Petición para instalación de señalización vial escolar y reductor de velocidad en zona peatonal.',
                'medio' => 'Físico',
                'prioridad' => 'Alta',
                'observaciones' => 'Urgente por alto flujo de niños en horas de la mañana.',
                'estado' => 'pendiente',
                'responsables' => [$respBenito->id],
            ],
            [
                'numero' => 'RAD-2026-005-INF',
                'sub_days' => 1,
                'tramite' => $tramiteInformacion,
                'remitente' => 'Esteban Morales Castro',
                'empresa' => 'Fundación Tierra Viva',
                'asunto' => 'Estadísticas del plan de gestión integral de residuos sólidos (PGIRS) vigencia 2025.',
                'medio' => 'Correo Electrónico',
                'prioridad' => 'Baja',
                'observaciones' => 'Información de carácter público para investigación académica.',
                'estado' => 'pendiente',
                'responsables' => [$respDiana->id],
            ],
            [
                'numero' => 'RAD-2026-006-VIS',
                'sub_days' => 3,
                'tramite' => $tramiteVisita,
                'remitente' => 'Mauricio Beltrán',
                'empresa' => 'Edificio Residencial Las Palmas',
                'asunto' => 'Inspección de muro de contención por presunto asentamiento diferencial.',
                'medio' => 'Físico',
                'prioridad' => 'Alta',
                'observaciones' => 'Comisión técnica de ingenieros estructurales asignada.',
                'estado' => 'pendiente',
                'responsables' => [$respBenito->id, $respAndres->id],
            ],
            [
                'numero' => 'RAD-2026-007-DP',
                'sub_days' => 2,
                'tramite' => $tramitePeticion,
                'remitente' => 'Sonia Marcela Gutiérrez',
                'empresa' => 'Asociación de Madres Comunitarias',
                'asunto' => 'Dotación de mobiliario y adecuación de baterías sanitarias en centro de atención infantil.',
                'medio' => 'Físico',
                'prioridad' => 'Media',
                'observaciones' => 'Proyecto priorizado en el plan de desarrollo.',
                'estado' => 'pendiente',
                'responsables' => [$respMaria->id],
            ],
            [
                'numero' => 'RAD-2026-008-CJ',
                'sub_days' => 4,
                'tramite' => $tramiteConsulta,
                'remitente' => 'Álvaro José Uribe',
                'empresa' => 'Fiduciaria Central S.A.',
                'asunto' => 'Viabilidad legal de cesión de áreas públicas en desarrollo urbanístico Plan Parcial Sur.',
                'medio' => 'Correo Electrónico',
                'prioridad' => 'Alta',
                'observaciones' => 'Revisión minuciosa del POT vigente.',
                'estado' => 'pendiente',
                'responsables' => [$respJuan->id, $respAndres->id],
            ],

            // --- GRUPO 2: EN ALERTA (AMARILLOS - PRÓXIMOS A VENCER) ---
            [
                'numero' => 'RAD-2026-009-VIS',
                'sub_days' => 8,
                'tramite' => $tramiteVisita,
                'remitente' => 'Roberto Carlos Vélez',
                'empresa' => 'Colegio San José Campestre',
                'asunto' => 'Inspección técnica por agrietamiento de muro colindante con la quebrada El Salado.',
                'medio' => 'Portal Web',
                'prioridad' => 'Alta',
                'observaciones' => 'Urgente por temporada de lluvias y riesgo de socavación.',
                'estado' => 'alerta',
                'custom_limit_days' => 2,
                'responsables' => [$respBenito->id, $respDiana->id],
            ],
            [
                'numero' => 'RAD-2026-010-QR',
                'sub_days' => 12,
                'tramite' => $tramiteQueja,
                'remitente' => 'Patricia Helena Gómez',
                'empresa' => 'Comunidad Barrio El Pinar',
                'asunto' => 'Queja por exceso de ruido y ocupación indebida de espacio público en zona residencial.',
                'medio' => 'Físico',
                'prioridad' => 'Media',
                'observaciones' => 'Se requiere pronunciamiento jurídico y sancionatorio antes del vencimiento.',
                'estado' => 'alerta',
                'custom_limit_days' => 1,
                'responsables' => [$respJuan->id, $respMaria->id],
            ],
            [
                'numero' => 'RAD-2026-011-INF',
                'sub_days' => 8,
                'tramite' => $tramiteInformacion,
                'remitente' => 'Jorge Eliécer Gaitán Jr.',
                'empresa' => 'Periódico El Informador Regional',
                'asunto' => 'Copia digital de contratos de concesión vial vigentes y actas de interventoría.',
                'medio' => 'Portal Web',
                'prioridad' => 'Alta',
                'observaciones' => 'Petición con término preferente de 10 días.',
                'estado' => 'alerta',
                'custom_limit_days' => 2,
                'responsables' => [$respCarlos->id, $respBenito->id],
            ],
            [
                'numero' => 'RAD-2026-012-DP',
                'sub_days' => 13,
                'tramite' => $tramitePeticion,
                'remitente' => 'Clara Inés Montoya',
                'empresa' => 'Comité de Veeduría en Salud',
                'asunto' => 'Informe sobre dotación de medicamentos y atención en puestos de salud rurales.',
                'medio' => 'Físico',
                'prioridad' => 'Alta',
                'observaciones' => 'Vence en 3 días hábiles.',
                'estado' => 'alerta',
                'custom_limit_days' => 3,
                'responsables' => [$respMaria->id],
            ],
            [
                'numero' => 'RAD-2026-013-QR',
                'sub_days' => 11,
                'tramite' => $tramiteQueja,
                'remitente' => 'Rodrigo de Jesús Arango',
                'empresa' => 'Gremio de Transportadores Urbanos',
                'asunto' => 'Inconformidad por estado de los semáforos y falta de señalización en la Carrera 10.',
                'medio' => 'Correo Electrónico',
                'prioridad' => 'Media',
                'observaciones' => 'Se requiere respuesta técnica de la Secretaría de Tránsito.',
                'estado' => 'alerta',
                'custom_limit_days' => 4,
                'responsables' => [$respBenito->id],
            ],
            [
                'numero' => 'RAD-2026-014-VIS',
                'sub_days' => 7,
                'tramite' => $tramiteVisita,
                'remitente' => 'Gloria Esperanza Cárdenas',
                'empresa' => 'Junta de Propietarios Urbanización Los Álamos',
                'asunto' => 'Evaluación de riesgo por árbol de gran tamaño inclinado sobre líneas eléctricas de alta tensión.',
                'medio' => 'Físico',
                'prioridad' => 'Alta',
                'observaciones' => 'Prioridad máxima por riesgo eléctrico.',
                'estado' => 'alerta',
                'custom_limit_days' => 1,
                'responsables' => [$respDiana->id, $respBenito->id],
            ],

            // --- GRUPO 3: VENCIDOS (ROJOS) ---
            [
                'numero' => 'RAD-2026-015-DP',
                'sub_days' => 25,
                'tramite' => $tramitePeticion,
                'remitente' => 'Fernando Soto Mayor',
                'empresa' => 'Veeduría Ciudadana por la Transparencia',
                'asunto' => 'Informe detallado de ejecución presupuestal del contrato de alumbrado público 2026.',
                'medio' => 'Correo Electrónico',
                'prioridad' => 'Alta',
                'observaciones' => 'Trámite vencido hace 4 días. Requiere atención inmediata.',
                'estado' => 'vencido',
                'custom_limit_past' => 4,
                'responsables' => [$respJuan->id, $respCarlos->id],
            ],
            [
                'numero' => 'RAD-2026-016-INF',
                'sub_days' => 18,
                'tramite' => $tramiteInformacion,
                'remitente' => 'Dra. Vanessa Carvajal',
                'empresa' => 'Bufete Jurídico Carvajal & Asociados',
                'asunto' => 'Certificado de paz y salvo y estado de cuenta de contribución por valorización.',
                'medio' => 'Portal Web',
                'prioridad' => 'Alta',
                'observaciones' => 'Vencido hace 3 días.',
                'estado' => 'vencido',
                'custom_limit_past' => 3,
                'responsables' => [$respCarlos->id],
            ],
            [
                'numero' => 'RAD-2026-017-QR',
                'sub_days' => 22,
                'tramite' => $tramiteQueja,
                'remitente' => 'Bernardo Echeverri Osorio',
                'empresa' => 'Comunidad Vereda El Tambo',
                'asunto' => 'Reclamo por contaminación de fuente hídrica por vertimientos no autorizados.',
                'medio' => 'Físico',
                'prioridad' => 'Alta',
                'observaciones' => 'Vencido hace 2 días. Trasladado a control ambiental.',
                'estado' => 'vencido',
                'custom_limit_past' => 2,
                'responsables' => [$respDiana->id, $respJuan->id],
            ],
            [
                'numero' => 'RAD-2026-018-VIS',
                'sub_days' => 17,
                'tramite' => $tramiteVisita,
                'remitente' => 'Gonzalo Alberto Maya',
                'empresa' => 'Asociación de Parceladores La Selva',
                'asunto' => 'Visita técnica de delimitación de ronda hídrica en quebrada La Doctora.',
                'medio' => 'Físico',
                'prioridad' => 'Media',
                'observaciones' => 'Término de ley vencido.',
                'estado' => 'vencido',
                'custom_limit_past' => 5,
                'responsables' => [$respDiana->id, $respAndres->id],
            ],
            [
                'numero' => 'RAD-2026-019-DP',
                'sub_days' => 28,
                'tramite' => $tramitePeticion,
                'remitente' => 'Margarita Rosa Londoño',
                'empresa' => 'Comité de Usuarios de Servicios Públicos',
                'asunto' => 'Petición de subsidios para estratos 1 y 2 en tarifas de acueducto y alcantarillado.',
                'medio' => 'Correo Electrónico',
                'prioridad' => 'Alta',
                'observaciones' => 'Vencido hace 6 días.',
                'estado' => 'vencido',
                'custom_limit_past' => 6,
                'responsables' => [$respCarlos->id, $respMaria->id],
            ],

            // --- GRUPO 4: COMPLETADOS / CERRADOS (CON RESPUESTA ADJUNTA) ---
            [
                'numero' => 'RAD-2026-020-AMB',
                'sub_days' => 12,
                'tramite' => $tramiteVisita,
                'remitente' => 'Corporación Ecológica del Valle',
                'empresa' => 'EcoValle ONG',
                'asunto' => 'Solicitud de permiso de poda técnica y evaluación forestal de especies nativas.',
                'medio' => 'Físico',
                'prioridad' => 'Media',
                'observaciones' => 'Concepto técnico emitido favorablemente y notificado al solicitante.',
                'estado' => 'completado',
                'fecha_salida' => $hoy->copy()->subDays(2),
                'archivo_entrada_path' => 'radicados/entradas/solicitud_oficial_comunidad.pdf',
                'archivo_entrada_nombre' => 'solicitud_poda_ecovalle.pdf',
                'archivo_salida_path' => 'radicados/salidas/concepto_tecnico_favorable.pdf',
                'archivo_salida_nombre' => 'concepto_forestal_089_2026.pdf',
                'responsables' => [$respDiana->id],
            ],
            [
                'numero' => 'RAD-2026-021-INF',
                'sub_days' => 16,
                'tramite' => $tramiteInformacion,
                'remitente' => 'Gabriel Jaime Arango',
                'empresa' => 'Constructora Bolívar S.A.',
                'asunto' => 'Certificación de estratificación socioeconómica para predio manzana 8 lote 3.',
                'medio' => 'Portal Web',
                'prioridad' => 'Baja',
                'observaciones' => 'Oficio de respuesta No. OFI-2026-089 radicado y enviado con firma digital.',
                'estado' => 'completado',
                'fecha_salida' => $hoy->copy()->subDays(4),
                'archivo_entrada_path' => 'radicados/entradas/planos_hidrosanitarios_vias.pdf',
                'archivo_entrada_nombre' => 'formulario_solicitud_estrato.pdf',
                'archivo_salida_path' => 'radicados/salidas/oficio_respuesta_ofi2026.pdf',
                'archivo_salida_nombre' => 'oficio_respuesta_OFI_2026_089.pdf',
                'responsables' => [$respMaria->id, $respAndres->id],
            ],
            [
                'numero' => 'RAD-2026-022-DP',
                'sub_days' => 14,
                'tramite' => $tramitePeticion,
                'remitente' => 'Humberto Antonio Quintero',
                'empresa' => 'Cooperativa de Cafeteros',
                'asunto' => 'Mantenimiento de placa huella en el tramo Vereda La Cabaña.',
                'medio' => 'Físico',
                'prioridad' => 'Alta',
                'observaciones' => 'Obra incluida en el cronograma de maquinaria del mes en curso.',
                'estado' => 'completado',
                'fecha_salida' => $hoy->copy()->subDays(1),
                'archivo_salida_path' => 'radicados/salidas/oficio_respuesta_ofi2026.pdf',
                'archivo_salida_nombre' => 'resolucion_asignacion_maquinaria.pdf',
                'responsables' => [$respBenito->id],
            ],
            [
                'numero' => 'RAD-2026-023-CJ',
                'sub_days' => 20,
                'tramite' => $tramiteConsulta,
                'remitente' => 'Santiago Botero Ruiz',
                'empresa' => 'Sociedad de Ingenieros de Antioquia',
                'asunto' => 'Concepto sobre obligatoriedad de pólizas de estabilidad en contratos menores.',
                'medio' => 'Correo Electrónico',
                'prioridad' => 'Media',
                'observaciones' => 'Concepto jurídico No. CJ-2026-012 expedido.',
                'estado' => 'completado',
                'fecha_salida' => $hoy->copy()->subDays(3),
                'archivo_salida_path' => 'radicados/salidas/concepto_tecnico_favorable.pdf',
                'archivo_salida_nombre' => 'concepto_juridico_012_2026.pdf',
                'responsables' => [$respJuan->id],
            ],

            // --- GRUPO 5: ANULADOS ---
            [
                'numero' => 'RAD-2026-024-DUP',
                'sub_days' => 5,
                'tramite' => $tramitePeticion,
                'remitente' => 'Alfonso Jaramillo Correa',
                'empresa' => 'Junta de Acción Comunal Sector Centro',
                'asunto' => 'Mantenimiento de vía en La Esperanza (Registro duplicado por ventanilla)',
                'medio' => 'Físico',
                'prioridad' => 'Alta',
                'observaciones' => 'Registro duplicado del radicado RAD-2026-001-DP.',
                'estado' => 'anulado',
                'motivo_anulacion' => 'Documento ingresado doblemente por la ventanilla única. El original se tramita bajo el consecutivo RAD-2026-001-DP.',
                'anulado_por' => $admin->id,
                'responsables' => [$respBenito->id],
            ],
            [
                'numero' => 'RAD-2026-025-ERR',
                'sub_days' => 7,
                'tramite' => $tramiteInformacion,
                'remitente' => 'Pedro Nel Ospina',
                'empresa' => 'Comunidad Santa Elena',
                'asunto' => 'Solicitud erróneamente dirigida a este despacho (Competencia departamental)',
                'medio' => 'Correo Electrónico',
                'prioridad' => 'Baja',
                'observaciones' => 'El trámite corresponde a la Gobernación.',
                'estado' => 'anulado',
                'motivo_anulacion' => 'Remitido por incompetencia funcional a la Gobernación de Antioquia conforme al artículo 21 del CPACA.',
                'anulado_por' => $admin->id,
                'responsables' => [$respMaria->id],
            ],
        ];

        foreach ($radicadosData as $item) {
            $fRad = $hoy->copy()->subDays($item['sub_days']);

            if (isset($item['custom_limit_days'])) {
                $fLim = $diasService->calcularFechaLimite($hoy, $item['custom_limit_days']);
            } elseif (isset($item['custom_limit_past'])) {
                $fLim = $hoy->copy()->subDays($item['custom_limit_past']);
            } else {
                $fLim = $diasService->calcularFechaLimite($fRad, $item['tramite']->dias_habiles);
            }

            $radicado = Radicado::create([
                'numero_radicado' => $item['numero'],
                'fecha_radicacion' => $fRad->toDateString(),
                'remitente' => $item['remitente'],
                'empresa' => $item['empresa'],
                'tipo_tramite_id' => $item['tramite']->id,
                'medio' => $item['medio'],
                'prioridad' => $item['prioridad'],
                'asunto' => $item['asunto'],
                'observaciones' => $item['observaciones'],
                'archivo_entrada_path' => $item['archivo_entrada_path'] ?? null,
                'archivo_entrada_nombre' => $item['archivo_entrada_nombre'] ?? null,
                'archivo_salida_path' => $item['archivo_salida_path'] ?? null,
                'archivo_salida_nombre' => $item['archivo_salida_nombre'] ?? null,
                'fecha_limite' => $fLim->toDateString(),
                'fecha_salida' => isset($item['fecha_salida']) ? $item['fecha_salida']->toDateString() : null,
                'estado' => $item['estado'],
                'motivo_anulacion' => $item['motivo_anulacion'] ?? null,
                'anulado_por' => $item['anulado_por'] ?? null,
            ]);

            if (! empty($item['responsables'])) {
                $radicado->responsables()->attach($item['responsables']);
            }
        }

        // 6. Solicitud de Edición Pendiente para demostración en vivo
        $radicadoR2 = Radicado::where('numero_radicado', 'RAD-2026-002-INF')->first();
        if ($radicadoR2) {
            SolicitudEdicion::create([
                'radicado_id' => $radicadoR2->id,
                'user_id' => $secretaria->id,
                'datos_propuestos' => [
                    'empresa' => 'Consorcio Vías del Norte & Asociados S.A.S.',
                    'asunto' => 'Copia de planos hidrosanitarios y certificado de uso de suelo del predio Calle 45 # 12-30 (Ampliación para planos estructurales).',
                    'medio' => 'Correo Electrónico',
                    'prioridad' => 'Alta',
                    'observaciones' => 'Se solicita incluir al Arq. Restrepo y al Ing. Benito para la validación estructural urgente.',
                    'responsables' => [$respAndres->id, $respBenito->id],
                ],
                'estado' => 'pendiente',
            ]);
        }

        // 7. Registros de Auditoría
        Auditoria::create([
            'user_id' => $secretaria->id,
            'accion' => 'Creó un radicado',
            'modelo' => 'Radicado',
            'modelo_id' => 1,
            'detalles' => ['numero_radicado' => 'RAD-2026-001-DP', 'remitente' => 'Alfonso Jaramillo Correa'],
            'created_at' => $hoy->copy()->subDays(2),
        ]);

        Auditoria::create([
            'user_id' => $secretaria->id,
            'accion' => 'Creó solicitud de edición',
            'modelo' => 'SolicitudEdicion',
            'modelo_id' => 1,
            'detalles' => ['radicado_id' => 2],
            'created_at' => $hoy->copy()->subHours(2),
        ]);

        Auditoria::create([
            'user_id' => $secretaria->id,
            'accion' => 'Completó un radicado con respuesta adjunta',
            'modelo' => 'Radicado',
            'modelo_id' => 20,
            'detalles' => ['fecha_salida' => $hoy->copy()->subDays(2)->toDateString(), 'archivo_salida' => 'concepto_forestal_089_2026.pdf'],
            'created_at' => $hoy->copy()->subDays(2),
        ]);

        Auditoria::create([
            'user_id' => $admin->id,
            'accion' => 'Anuló un radicado',
            'modelo' => 'Radicado',
            'modelo_id' => 24,
            'detalles' => ['motivo_anulacion' => 'Documento ingresado doblemente por la ventanilla única.'],
            'created_at' => $hoy->copy()->subDays(1),
        ]);
    }
}
