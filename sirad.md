# Semaforo

Documento de planificación generado el 04/08/2026, 11:31 a. m.

---

## Fase 1. Descubrimiento y definición del problema

### Documento de visión / Project Charter

**¿Qué problema resuelve el software?**

Documento de visión / Project Charter
¿Qué problema resuelve el software?
Resuelve la falta de seguimiento activo, trazabilidad y control de vencimientos en la recepción y respuesta de correspondencia (Derechos de Petición y Solicitudes de Visita). Actualmente, la gestión la lleva la secretaria en un archivo Excel pasivo dividido entre correspondencia entrante y saliente. Este esquema genera tres fallos críticos:

Riesgo de términos vencidos: No existe un cálculo automático ni alertas activas sobre los 15 días hábiles legales de respuesta, dependiendo del control manual de la secretaria y provocando que los plazos se cumplan sin que la dirección lo note a tiempo.

Registro duplicado y desconectado: La información de entrada y salida está fragmentada en pestañas independientes, requiriendo que la secretaria anote manualmente la entrada en una hoja y la salida en otra sin vinculación automática.

Falta de visibilidad para la dirección: El jefe no cuenta con un panel centralizado en tiempo real para consultar el estado global de los trámites ni la carga asignada a cada funcionario sin tener que pedir reportes manuales.

**¿Para quién es? (usuarios objetivo)**

¿Para quién es? (usuarios objetivo)
Secretaria / Encargada de Radicación (Usuario Operativo Único): Única persona encargada de operar el sistema para radicar oficios entrantes, seleccionar al funcionario responsable, adjuntar documentos en PDF, registrar las salidas cuando le informan la respuesta y marcar los trámites como completados.

Jefe / Director (Usuario Supervisor + Administrador funcional): Usuario con acceso de lectura y supervisión para visualizar el panel de control (Dashboard), el semáforo de vencimientos por colores, el estado de las solicitudes y los reportes de carga de trabajo por funcionario. Adicionalmente, es el único con privilegios para administrar el calendario de festivos (rol `jefe` en el sistema, sin necesidad de un tercer usuario).

(Los funcionarios responsables no son usuarios del sistema; únicamente reciben notificaciones y alertas automáticas a sus correos electrónicos corporativos. El "Administrador del Sistema/Desarrollador" tampoco es un usuario de la aplicación: es un rol técnico externo responsable del servidor, backups y despliegue — no inicia sesión en la plataforma).

> **Nota de diseño (roles):** El sistema mantiene exactamente 2 roles de aplicación: `secretaria` y `jefe`. El rol `jefe` incluye privilegios administrativos (festivos). Esto evita crear un tercer usuario/rol solo para la configuración de festivos y mantiene el alcance simple, tal como se definió originalmente.

**¿Qué valor aporta o qué lo diferencia de alternativas existentes?**

A diferencia de hojas de cálculo (Excel) o plataformas no-code con costos recurrentes de licencias (como AppSheet), este sistema en Laravel local aporta:

Automatización de avisos a terceros: Notifica por correo electrónico al funcionario asignado inmediatamente al radicar y dispara alertas preventivas a los 13 días hábiles (2 días antes del vencimiento) al funcionario, con copia a la secretaria y al jefe.

Flujo centralizado de una sola vía: Unifica la correspondencia entrante y saliente en un solo expediente digital gestionado al 100% por la secretaria.

Cero costos de licencias e infraestructura externa: Desplegado en un servidor local de la oficina, ofreciendo uso continuo, ilimitado y sin cobros mensuales.

Cálculo automático de días hábiles: Incorpora la lógica del calendario laboral colombiano, descontando fines de semana y festivos cargados en el sistema.

**Objetivos medibles**

- 0% de Derechos de Petición o Solicitudes de Visita vencidos por falta de aviso o descuido de seguimiento.
- Reducción a 0 minutos del tiempo que el jefe invierte pidiendo estados de cuenta o revisando hojas de Excel manuales.
- 100% de trazabilidad digital centralizada: Todos los trámites radicados por la secretaria contarán con su respectivo expediente digital (PDF de entrada y PDF de respuesta enlazados en un solo registro).
- Envío instantáneo de notificaciones: Notificación por correo al funcionario responsable en menos de 30 segundos tras la radicación realizada por la secretaria.

### Análisis de stakeholders

**Stakeholders**

| Nombre / rol | Interés en el proyecto | Tipo |
| --- | --- | --- |
| Jefe / Director General | Contar con un panel de supervisión en tiempo real (semáforo de vencimientos), asegurar el cumplimiento de los 15 días hábiles y recibir alertas preventivas antes de que se venzan los plazos. | Sponsor / Usuario Supervisor (rol `jefe`, con privilegios de administración de festivos) |
| Secretaria / Encargada de Correspondencia | Agilizar el proceso de radicación, eliminar la doble digitación entre entradas/salidas, dejar que el sistema envíe los recordatorios por correo a los funcionarios y centralizar la carga de archivos PDF. | Usuario Operativo Principal (rol `secretaria`) |
| Funcionarios Responsables (Benito, Juan Carlos, etc.) | Recibir en sus correos la notificación clara de lo que se les asignó con la fecha límite exacta, así como el aviso de urgencia cuando falten 2 días hábiles, sin necesidad de ingresar al sistema. | Destinatarios de alertas (sin acceso al sistema, sin usuario/contraseña) |
| Administrador del Sistema / Desarrollador | Mantener el servidor local encendido, garantizar las copias de seguridad de la base de datos SQLite, el worker de colas de correo, y apoyar la carga inicial del calendario de festivos. | Rol técnico externo / Soporte (no es usuario de la aplicación) |

## Fase 2. Levantamiento de requerimientos

### Requerimientos funcionales

**Requerimientos funcionales**

| ID | El sistema debe permitir que... | Prioridad |
| --- | --- | --- |
| RF01 | Gestión de radicados: La secretaria debe poder registrar nuevos documentos de entrada con campos: número de radicado, fecha, remitente, asunto, tipo de trámite y adjunto PDF. | Alta |
| RF02 | Asignación de responsables: El sistema permitirá asociar un funcionario responsable a cada radicado durante el registro. | Alta |
| RF03 | Notificación automática: Envío de correo electrónico inmediato al funcionario responsable al asignar el radicado, incluyendo detalles y fecha límite. | Alta |
| RF04 | Cálculo de vencimientos: Sistema automático de cálculo de 15 días hábiles legales basado en un calendario de festivos configurable. | Alta |
| RF05 | Alertas preventivas: Disparo automático de correo electrónico al funcionario, secretaria y jefe faltando 2 días hábiles para el vencimiento. | Alta |
| RF06 | Dashboard del jefe: Panel de supervisión visual con semáforo (Verde, Amarillo, Rojo) indicando el estado de cumplimiento de los plazos de cada trámite. | Alta |
| RF07 | Cierre de trámite: Funcionalidad para registrar la respuesta del funcionario, adjuntar el PDF de respuesta y cambiar el estado del radicado a completado. | Alta |
| RF08 | Trazabilidad completa: Vista única por expediente que muestra el documento de entrada, el funcionario responsable, el estado y el documento de respuesta. | Media |
| RF09 | Reportes de carga: Generación de listados sobre la cantidad de trámites asignados por funcionario para el jefe. | Media |
| RF10 | Configuración de calendario: Módulo administrativo (accesible solo al rol `jefe`) para actualizar la lista de festivos anuales. | Baja |
| RF11 | Corrección/anulación de radicados: La secretaria debe poder marcar como "Anulado" un radicado creado por error (dato incorrecto, duplicado), indicando un motivo obligatorio. El registro permanece en el sistema (no se elimina físicamente) y se excluye de los cálculos del semáforo y de los reportes activos. | Media |

**Historias de usuario**

| Como (rol) | Quiero (acción) | Para (beneficio) |
| --- | --- | --- |
| Secretaria | registrar nuevos documentos de entrada con sus datos y archivo PDF adjunto | centralizar la correspondencia en un solo expediente digital |
| Secretaria | asignar un funcionario responsable al radicar un documento | delegar la respuesta y establecer un canal claro de comunicación |
| Funcionario responsable | recibir una notificación automática por correo con los detalles del trámite y su fecha límite | estar informado oportunamente sobre mis responsabilidades asignadas |
| Jefe / Director | visualizar un dashboard con un semáforo de vencimientos (verde, amarillo, rojo) | supervisar el cumplimiento de los términos legales en tiempo real |
| Secretaria | que el sistema calcule automáticamente los 15 días hábiles considerando festivos | asegurar que los plazos legales se controlen sin errores manuales |
| Funcionario, Secretaria y Jefe | recibir una alerta automática 2 días antes del vencimiento | tomar acciones preventivas y evitar que el término se venza |
| Secretaria | adjuntar el archivo de respuesta y marcar el trámite como completado | finalizar la trazabilidad del expediente digital |
| Jefe / Director | consultar un reporte de carga de trabajo por funcionario | gestionar de forma equitativa el volumen de solicitudes |
| Jefe / Director | acceder a un módulo de configuración de festivos | mantener actualizado el calendario laboral del sistema |
| Secretaria | anular un radicado creado por error, indicando el motivo | corregir equivocaciones sin perder la trazabilidad ni eliminar registros de auditoría |

### Requerimientos no funcionales

**Requerimientos no funcionales**

| ID | Categoría | Descripción |
| --- | --- | --- |
| RNF01 | Disponibilidad | El sistema debe estar disponible para la consulta y radicación en el horario laboral de la entidad, con un tiempo de actividad (uptime) del 99%. |
| RNF02 | Seguridad | Acceso restringido mediante autenticación de usuario y contraseña cifrada (hashing) para la secretaria y el jefe. |
| RNF03 | Compatibilidad | El sistema debe ser accesible desde cualquier navegador web moderno (Chrome, Edge, Firefox) sin necesidad de instalaciones adicionales en los equipos de los usuarios. |
| RNF04 | Rendimiento | El tiempo de carga del Dashboard de supervisión no debe superar los 2 segundos, incluso con un volumen acumulado de hasta 5,000 registros. |
| RNF05 | Seguridad | El sistema debe garantizar la integridad de los archivos PDF adjuntos, almacenándolos en una ruta protegida con permisos de acceso restringidos al servidor web. |
| RNF06 | Otro | El sistema debe realizar una copia de seguridad automática de la base de datos SQLite diariamente a una carpeta externa o unidad de red, conservando al menos los últimos 30 backups (retención rotativa). El Administrador debe probar la restauración de un backup al menos una vez por trimestre para validar su integridad. |
| RNF07 | Escalabilidad | La arquitectura debe permitir la adición de nuevos tipos de documentos o categorías de trámite a través de una configuración sencilla sin modificar el código fuente. |
| RNF08 | Confiabilidad | El worker de colas (`queue:work`) debe configurarse para reiniciarse automáticamente ante fallos o reinicios del servidor (supervisor de proceso), garantizando que las notificaciones encoladas no se pierdan ni se acumulen indefinidamente. |

### Reglas de negocio

**Reglas de negocio**

- Los plazos legales de respuesta se calculan estrictamente sobre 15 días hábiles a partir de la fecha de radicación.
- El cómputo de días hábiles excluye sábados, domingos y días festivos registrados en el calendario del sistema.
- La fecha límite de respuesta se marca como vencida si, al finalizar el día 15 hábil, no existe un documento de respuesta adjunto.
- El estado del trámite cambia automáticamente a "Alerta" cuando faltan exactamente 2 días hábiles para el cumplimiento del plazo.
- Todo radicado debe tener obligatoriamente un funcionario responsable asignado para permitir su registro en el sistema.
- El documento de entrada debe cargarse en formato PDF exclusivamente.
- El cierre de un radicado requiere obligatoriamente la carga del archivo PDF de respuesta y el registro de la fecha de salida.
- Los correos electrónicos de notificación y alerta deben enviarse de forma asíncrona al correo corporativo del funcionario, secretaria y jefe.
- La modificación del calendario de festivos solo podrá ser realizada por el usuario con rol `jefe` (privilegios de administración funcional).
- Ningún trámite puede ser eliminado físicamente del sistema tras su radicación, para garantizar la integridad de la auditoría. Un radicado registrado por error solo puede pasar al estado "Anulado" (soft-delete lógico), con motivo obligatorio y registro de quién y cuándo lo anuló; nunca se borra de la base de datos.
- Los radicados en estado "Anulado" no generan alertas, no se contabilizan en el semáforo del Dashboard ni en los reportes de carga de trabajo, pero siguen siendo consultables en el historial.

## Fase 3. Análisis y especificación

### Matriz de trazabilidad de requerimientos

**Trazabilidad**

| Requerimiento | Origen (quién lo pidió) | Prioridad | Estado |
| --- | --- | --- | --- |
| RF01 | Secretaria | Alta | Aprobado |
| RF02 | Secretaria | Alta | Aprobado |
| RF03 | Funcionario responsable | Alta | Aprobado |
| RF04 | Secretaria | Alta | Aprobado |
| RF05 | Funcionario, Secretaria y Jefe | Alta | Aprobado |
| RF06 | Jefe / Director | Alta | Aprobado |
| RF07 | Secretaria | Alta | Aprobado |
| RF08 | Secretaria | Media | Aprobado |
| RF09 | Jefe / Director | Media | Aprobado |
| RF10 | Jefe / Director | Baja | Aprobado |
| RF11 | Secretaria | Media | Aprobado |
| RNF01 | Jefe / Director | Alta | Aprobado |
| RNF02 | Administrador | Alta | Aprobado |
| RNF03 | Secretaria | Media | Aprobado |
| RNF04 | Jefe / Director | Media | Aprobado |
| RNF05 | Administrador | Alta | Aprobado |
| RNF06 | Administrador | Media | Aprobado |
| RNF07 | Administrador | Baja | Aprobado |
| RNF08 | Administrador | Alta | Aprobado |

### Priorización (MoSCoW)

**Priorización**

| Ítem / funcionalidad | Categoría |
| --- | --- |
| RF01 | Must have |
| RF02 | Must have |
| RF03 | Must have |
| RF04 | Must have |
| RF05 | Must have |
| RF06 | Must have |
| RF07 | Must have |
| RF08 | Should have |
| RF09 | Should have |
| RF10 | Could have |
| RF11 | Should have |
| RNF01 | Must have |
| RNF02 | Must have |
| RNF03 | Should have |
| RNF04 | Should have |
| RNF05 | Must have |
| RNF06 | Should have |
| RNF07 | Could have |
| RNF08 | Must have |

### Diagrama de flujo de procesos

**Describe el flujo principal paso a paso**

### Diagrama de flujo de procesos

**Describe el flujo principal paso a paso**

1.  **Radicación:** La secretaria registra el documento (entrada PDF + metadatos) en el sistema.
2.  **Asignación:** El sistema genera el número de radicado, calcula la fecha límite (15 días hábiles) y asigna al funcionario responsable.
3.  **Notificación Inicial:** El sistema envía un correo automático al funcionario responsable con el enlace al documento y la fecha límite.
4.  **Monitoreo:** El sistema evalúa diariamente el estado de los radicados pendientes:
    *   Si faltan 2 días hábiles para el vencimiento, dispara la **Alerta Preventiva** (notificación por correo a funcionario, secretaria y jefe).
    *   Si el plazo se cumple y no hay respuesta, el registro se marca como "Vencido" en el Dashboard.
5.  **Respuesta:** La secretaria recibe el documento de respuesta, lo adjunta al radicado original y marca el trámite como "Completado".
6.  **Cierre:** El sistema actualiza el estado, finaliza el ciclo de vida del expediente y registra la fecha de salida.
7.  **Supervisión:** El Jefe accede al Dashboard en cualquier momento para visualizar el semáforo de estados y descargar reportes de carga de trabajo.

## Fase 4. Diseño

### Wireframes / Mockups

**Listado de pantallas necesarias**

- Login
- Dashboard de supervisión
- Formulario de radicación de documentos
- Listado maestro de radicados
- Detalle de expediente digital
- Formulario de cierre de trámite
- Panel de configuración de festivos
- Panel de reportes y carga de trabajo

**Notas de diseño / enlace a Figma u otra herramienta**

### Notas de diseño

El diseño de la interfaz se basará en los siguientes lineamientos para asegurar una experiencia operativa eficiente:

*   **Lenguaje de diseño:** Se utilizará **Tailwind CSS** con componentes de **Blade** (UI Kit tipo *AdminLTE* o *Flowbite*), priorizando la legibilidad, el uso de tipografía limpia y un contraste visual claro para los estados del semáforo.
*   **Código de colores (Semáforo):**
    *   **Verde (#22C55E):** Trámite en curso con tiempo suficiente.
    *   **Amarillo (#EAB308):** Alerta preventiva (a 2 días hábiles del vencimiento).
    *   **Rojo (#EF4444):** Trámite vencido o en plazo crítico.
    *   **Gris (#6B7280):** Trámite completado.
*   **Diseño centrado en el usuario:**
    *   **Secretaria:** Interfaz orientada a formularios rápidos con validación en tiempo real y carga de archivos mediante *drag-and-drop*.
    *   **Jefe:** Dashboard con *cards* de resumen (totales, vencimientos próximos) y una tabla interactiva con filtros rápidos por funcionario y estado.
*   **Responsividad:** La aplicación debe ser funcional en monitores de escritorio (tamaño estándar de oficina). Se prioriza el modo oscuro/claro para reducir la fatiga visual.
*   **Enlace de prototipado:** [Incluir aquí el enlace al tablero de Figma o Wireframes de baja fidelidad una vez realizados].
*   **Recursos:** Se utilizará la librería de iconos *Heroicons* para mejorar la navegación intuitiva entre módulos de radicación y reportes.

### Modelo de datos

**Diccionario de datos**

| Tabla | Campo | Tipo de dato | Obligatorio | Relación (Hacia qué otra tabla) |
| --- | --- | --- | --- | --- |
| users | id | Entero (Int) | Sí | N/A |
| users | name | Texto (Varchar) | Sí | N/A |
| users | email | Texto (Varchar) | Sí | N/A |
| users | password | Texto (Varchar) | Sí | N/A |
| users | role | Enum (`secretaria`, `jefe`) | Sí | N/A |
| radicados | id | Entero (Int) | Sí | N/A |
| radicados | numero_radicado | Texto (Varchar) | Sí | N/A |
| radicados | fecha_radicacion | Fecha (Date) | Sí | N/A |
| radicados | remitente | Texto (Varchar) | Sí | N/A |
| radicados | asunto | Texto Largo (Text) | Sí | N/A |
| radicados | tipo_tramite | Enum (`derecho_peticion`, `solicitud_visita`) | Sí | N/A |
| radicados | pdf_entrada_path | Texto (Varchar) | Sí | N/A |
| radicados | fecha_limite | Fecha (Date) | Sí | N/A |
| radicados | funcionario_id | Entero (Int) | Sí | users |
| radicados | estado | Enum (`pendiente`, `alerta`, `vencido`, `completado`, `anulado`) | Sí | N/A |
| radicados | pdf_respuesta_path | Texto (Varchar) | No | N/A |
| radicados | fecha_salida | Fecha (Date) | No | N/A |
| radicados | motivo_anulacion | Texto (Varchar) | No | N/A |
| radicados | anulado_por | Entero (Int) | No | users |
| festivos | id | Entero (Int) | Sí | N/A |
| festivos | fecha | Fecha (Date) | Sí | N/A |
| festivos | descripcion | Texto (Varchar) | Sí | N/A |

### Arquitectura del sistema

**Stack tecnológico**

### Stack tecnológico

Para garantizar la estabilidad, mantenibilidad y el cumplimiento de los requerimientos de un servidor local, se ha seleccionado el siguiente stack:

*   **Framework de backend:** Laravel 11 (PHP 8.2+).
*   **Base de datos:** SQLite (almacenamiento local ligero, sin necesidad de servidores de base de datos complejos).
*   **Frontend:** Blade templates con **Tailwind CSS** para el diseño de la interfaz y **Alpine.js** para la interactividad reactiva en el cliente.
*   **Servidor web:** Apache o Nginx (configurado en el servidor local de la oficina).
*   **Control de versiones:** Git.
*   **Gestión de dependencias:** Composer (PHP) y NPM (Frontend).
*   **Gestión de archivos:** Sistema de archivos local del servidor para almacenamiento de PDFs, con soporte de rutas privadas.
*   **Notificaciones:** Servicio de correo vía SMTP corporativo mediante el driver de correo de Laravel (`mailgun` o configuración SMTP estándar de la entidad).
*   **Cola de trabajos (Queue):** Driver `database` de Laravel Queue para el envío asíncrono de correos (RF03, RF05). Requiere un **worker persistente** (`php artisan queue:work`) corriendo como servicio del sistema operativo (systemd en Linux o NSSM/Tarea programada en Windows), **independiente** del Scheduler. Si el worker se detiene, los correos quedan encolados sin enviarse — ver riesgo asociado en Fase 5.
*   **Planificador de tareas:** Laravel Scheduler para ejecutar los procesos automáticos de cálculo de vencimientos y alertas (ejecutado vía Cron jobs o Task Scheduler).
*   **Autenticación de API interna:** Laravel Sanctum en modo SPA (autenticación por cookie de sesión, mismo dominio) para las llamadas que Alpine.js hace a los endpoints `/api/*` desde el propio Dashboard. No se usan tokens de API expuestos públicamente, ya que no hay consumidores externos del sistema.

**Componentes / capas del sistema**

- Capa de presentación: Vistas Blade con componentes Tailwind y lógica reactiva mediante Alpine.js
- Capa de control: Controladores Laravel para la gestión de solicitudes, radicados y lógica de negocio
- Capa de servicios: Clases de servicio para el cálculo de días hábiles, manejo de festivos y lógica de notificaciones
- Capa de persistencia: Modelos Eloquent ORM interactuando con la base de datos SQLite
- Capa de infraestructura: Scheduler de Laravel para tareas programadas (cron jobs), worker de colas (`queue:work`) para envío asíncrono de correos, y sistema de archivos local para almacenamiento de documentos PDF
- Capa de transporte: Cliente SMTP integrado para el envío de notificaciones y alertas automáticas por correo electrónico

### Diseño de API

**Contrato de endpoints**

| Método | Ruta | Parámetros | Respuesta esperada | Códigos de error |
| --- | --- | --- | --- | --- |
| GET | /api/radicados | filtro_estado, funcionario_id | Listado de radicados con datos básicos y estado actual del semáforo | 401 Unauthorized |
| POST | /api/radicados | numero_radicado, fecha_radicacion, remitente, asunto, tipo_tramite, pdf_entrada (file), funcionario_id | Objeto radicado creado con fecha_limite calculada | 422 Unprocessable Entity, 400 Bad Request |
| GET | /api/radicados/{id} | id | Detalle completo del expediente incluyendo archivos y trazabilidad | 404 Not Found |
| PATCH | /api/radicados/{id}/cierre | pdf_respuesta (file), fecha_salida | Radicado actualizado a estado completado | 404 Not Found, 422 Unprocessable Entity |
| PATCH | /api/radicados/{id}/anular | motivo_anulacion | Radicado actualizado a estado anulado (soft-delete lógico) | 404 Not Found, 422 Unprocessable Entity |
| GET | /api/dashboard/stats | periodo | Resumen consolidado de vencimientos y carga por funcionario | 401 Unauthorized |
| POST | /api/config/festivos | fecha, descripcion | Registro de festivo exitoso (requiere rol `jefe`) | 403 Forbidden, 422 Unprocessable Entity |
| GET | /api/reportes/carga | funcionario_id, fecha_inicio, fecha_fin | Reporte detallado de volumen de trámites | 401 Unauthorized |

## Fase 5. Planeación del proyecto

### Cronograma / Plan de trabajo

**Tareas**

| Tarea | Responsable | Estimación (días o horas) | Estado |
| --- | --- | --- | --- |
| Configuración del entorno de desarrollo Laravel 11 y servidor local | Cristian | 8 horas | Pendiente |
| Diseño y migración de base de datos SQLite y modelos Eloquent | Cristian | 12 horas | Pendiente |
| Implementación del módulo de autenticación de usuarios (Login/Roles) | Cristian | 6 horas | Pendiente |
| Desarrollo del formulario de radicación y carga de archivos PDF | Cristian | 16 horas | Pendiente |
| Implementación de lógica para cálculo de días hábiles y festivos | Cristian | 10 horas | Pendiente |
| Configuración del sistema de notificaciones por correo SMTP y cola de trabajos (queue worker como servicio) | Cristian | 8 horas | Pendiente |
| Implementación del flujo de corrección/anulación de radicados (RF11) | Cristian | 5 horas | Pendiente |
| Desarrollo del Dashboard del jefe con visualización de semáforo | Cristian | 20 horas | Pendiente |
| Implementación de alertas automáticas (Scheduler/Cron Jobs) | Cristian | 8 horas | Pendiente |
| Módulo de gestión de cierre de trámites y adjuntos de respuesta | Cristian | 10 horas | Pendiente |
| Generación de reportes de carga de trabajo para el Jefe | Cristian | 8 horas | Pendiente |
| Módulo de administración de calendario de festivos | Cristian | 4 horas | Pendiente |
| Pruebas unitarias, funcionales y despliegue final en servidor local | Cristian | 12 horas | Pendiente |

### Plan de gestión de riesgos

**Riesgos**

| Riesgo | Probabilidad | Impacto | Plan de mitigación |
| --- | --- | --- | --- |
| Fallo en el servidor local por corte de energía o hardware | Media | Alto | Implementar un plan de respaldo diario (backup) automatizado y uso de UPS en el servidor físico. |
| Envío fallido de notificaciones por errores en la configuración del servidor SMTP corporativo | Media | Alto | Realizar pruebas de envío de correo en el entorno de desarrollo y crear logs detallados de errores de mail en Laravel. |
| Pérdida de archivos PDF por problemas de permisos en la ruta de almacenamiento local | Baja | Alto | Configurar permisos estrictos del sistema de archivos al nivel del SO y realizar sincronización periódica a una unidad externa. |
| Cálculo incorrecto de fechas debido a festivos no cargados en el sistema | Media | Medio | Cargar el calendario anual de festivos al iniciar el año y permitir edición administrativa con validación. |
| Acceso no autorizado al sistema por parte de personal ajeno a la secretaría o dirección | Baja | Alto | Implementar autenticación robusta mediante hashing de contraseñas y restringir el acceso a nivel de red interna (VPN o IP local). |
| Corrupción de la base de datos SQLite por escritura simultánea o cierre inesperado | Baja | Alto | Habilitar el Journal Mode WAL (Write-Ahead Logging) en SQLite y realizar copias de seguridad incrementales. |
| Resistencia al cambio por parte de la secretaria o funcionarios frente al nuevo software | Media | Medio | Realizar sesiones de capacitación breves y asegurar que la interfaz sea intuitiva y simplifique el flujo manual actual. |
| El worker de colas (`queue:work`) se detiene silenciosamente (reinicio del servidor, error no capturado) y las notificaciones dejan de enviarse sin que nadie lo note | Media | Alto | Configurar el worker como servicio con reinicio automático (systemd/NSSM) y un log/alerta simple que el Administrador revise periódicamente (o un job de "heartbeat"). |

### Definición de "Hecho"

**Criterios para considerar una funcionalidad terminada**

- [ ] El código fuente ha sido subido al repositorio y fusionado en la rama principal tras superar el code review.
- [ ] La funcionalidad cuenta con pruebas unitarias o de integración que validan el comportamiento esperado.
- [ ] La interfaz de usuario cumple con los lineamientos de diseño, es responsiva y ha sido validada en navegadores Chrome y Edge.
- [ ] Se ha verificado la correcta persistencia de datos en la base de datos SQLite sin errores de integridad.
- [ ] El usuario final (secretaria o jefe) ha validado la funcionalidad en el entorno de pruebas local.
- [ ] No existen incidencias abiertas de severidad alta o crítica relacionadas con la funcionalidad.
- [ ] Los archivos generados o cargados se almacenan correctamente en la ruta definida con los permisos de lectura/escritura adecuados.
- [ ] Las notificaciones automáticas (correos electrónicos) se disparan en el tiempo y condiciones establecidas por la lógica de negocio.
- [ ] La documentación técnica mínima necesaria ha sido actualizada.

## Fase 6. Preparación técnica del entorno

### Configuración de repositorio

**Estrategia de ramas, convenciones de commits, etc.**

### Estrategia de ramas (Git Flow simplificado)

Para este proyecto, se utilizará un modelo de flujo de trabajo basado en ramas para mantener la estabilidad del código:

*   **`main`**: Rama principal. Contiene solo código probado, estable y desplegado en producción (servidor local).
*   **`develop`**: Rama de integración donde se consolidan los nuevos desarrollos antes de pasar a `main`.
*   **`feature/*`**: Ramas temporales para cada requerimiento funcional (ej. `feature/modulo-radicacion`). Se crean desde `develop` y se eliminan tras ser integradas.
*   **`hotfix/*`**: Ramas para corregir errores críticos encontrados en `main`.

### Convenciones de commits (Conventional Commits)

Cada mensaje de commit debe seguir el formato: `<tipo>(<alcance>): <descripción corta>`

**Tipos permitidos:**
*   **feat**: Nueva funcionalidad.
*   **fix**: Corrección de errores.
*   **docs**: Cambios en documentación.
*   **style**: Cambios de formato (espacios, indentación) que no afectan lógica.
*   **refactor**: Cambios de código que no corrigen bugs ni añaden funcionalidades.
*   **test**: Adición o modificación de pruebas.
*   **chore**: Tareas rutinarias de mantenimiento (configuración de servidor, dependencias).

**Ejemplos:**
*   `feat(radicacion): agregar validacion de PDF para formulario de entrada`
*   `fix(dashboard): corregir calculo de color en semaforo de vencimientos`
*   `chore(deps): actualizar dependencias de Laravel`

### Proceso de integración
1. Todo desarrollo se realiza en ramas `feature/*`.
2. Para integrar a `develop`, se requiere verificar que los tests pasen y que el código cumpla con los estándares definidos.
3. El paso de `develop` a `main` se realiza únicamente mediante *merge* tras validar la estabilidad en el entorno de pruebas.

### Estándares de código

**Guía de estilo, convenciones de nombres**

### Estándares de código

Para mantener la legibilidad, mantenibilidad y calidad del proyecto, se establecen las siguientes guías de estilo:

#### 1. Convenciones de Nombres
*   **PHP (Laravel):**
    *   **Clases y Traits:** `PascalCase` (ej. `RadicadoController`, `CheckVencimiento`).
    *   **Métodos y Funciones:** `camelCase` (ej. `calcularFechaLimite()`).
    *   **Variables y Propiedades:** `camelCase` (ej. `$numeroRadicado`, `$funcionarioId`).
    *   **Nombres de tablas:** `snake_case` en plural (ej. `radicados`, `users`).
    *   **Nombres de columnas:** `snake_case` (ej. `fecha_radicacion`, `pdf_entrada_path`).
*   **Archivos:**
    *   **Clases:** Nombre del archivo igual al nombre de la clase, terminando en `.php`.
    *   **Vistas:** `snake_case` (ej. `show_detalle.blade.php`).
*   **CSS (Tailwind):** Uso de clases de utilidad ordenadas según la recomendación oficial de Tailwind (layout, spacing, sizing, typography, etc.).

#### 2. Guía de Estilo PHP
*   **Estándar:** Cumplimiento estricto de **PSR-12** (Extended Coding Style).
*   **Tipado:** Uso obligatorio de *Type Hinting* en parámetros y retornos de funciones (ej. `public function store(Request $request): RedirectResponse`).
*   **Espaciado:** Indentación de 4 espacios (no usar tabulaciones).
*   **Comentarios:** Uso de PHPDoc para explicar la lógica compleja, servicios, parámetros y valores de retorno en controladores y modelos.

#### 3. Estructura de Proyecto
*   **Controllers:** Contener solo lógica de orquestación (recibir petición, llamar servicio, retornar respuesta).
*   **Services:** Toda la lógica de negocio (cálculo de fechas, validación de festivos, lógica de semáforo) debe residir en clases de servicio dedicadas en `app/Services`.
*   **Requests:** Uso de *Form Requests* en Laravel para centralizar la validación de formularios.

#### 4. Buenas Prácticas
*   **DRY (Don't Repeat Yourself):** Reutilización de componentes Blade para elementos repetitivos (botones, campos de entrada, alertas).
*   **Seguridad:** Uso de las directivas de Blade `@csrf` y `@method` en todos los formularios. Sanitización automática de entradas mediante Eloquent ORM para prevenir inyección SQL.
*   **Git:** Mensajes de commit descriptivos siguiendo la convención acordada en la fase anterior.

### Plan de pruebas

**Plan de pruebas**

| Tipo de prueba | Qué se prueba | Criterio de aceptación |
| --- | --- | --- |
| Unitaria | Cálculo de fechas hábiles | La función de cálculo debe excluir correctamente sábados, domingos y fechas configuradas en la tabla de festivos |
| Unitaria | Validación de formularios | El formulario de radicación debe rechazar archivos que no sean PDF y campos obligatorios vacíos |
| Integración | Flujo de notificación | Al radicar un documento, el sistema debe disparar el evento de correo y registrar la entrada en la base de datos simultáneamente |
| Integración | Persistencia de archivos | El archivo PDF cargado debe almacenarse en la ruta física asignada y permitir su descarga desde el detalle del expediente |
| Aceptación | Dashboard semáforo | El usuario Jefe debe visualizar correctamente los colores (verde, amarillo, rojo) según la proximidad de los 15 días hábiles |
| Aceptación | Cierre de trámite | Al subir la respuesta, el estado debe cambiar a completado y la fecha de salida debe registrarse correctamente |
| Otra | Backup automático | El proceso programado debe generar un archivo .sqlite de respaldo en la ubicación externa configurada sin errores |
| Otra | Restauración de backup | Un backup tomado al azar debe poder restaurarse en un entorno de pruebas y arrancar la aplicación sin pérdida de datos |
| Integración | Envío de alertas preventivas | El Scheduler debe detectar los radicados a 2 días del vencimiento y enviar correos masivos a los involucrados |
| Integración | Cola de correos | Si el worker `queue:work` se detiene y se reinicia, los correos pendientes en la cola deben enviarse sin duplicarse ni perderse |
| Aceptación | Anulación de radicado | Un radicado anulado por la secretaria debe desaparecer del semáforo activo y de los reportes, pero seguir visible en el historial con su motivo |

### Configuración de entornos

**Entornos**

| Entorno | URL / configuración | Notas |
| --- | --- | --- |
| Desarrollo | Entorno local en máquina del desarrollador (Windows/Linux) usando Laravel Herd o XAMPP con PHP 8.2 y base de datos SQLite. | Entorno para codificación, pruebas unitarias y validación de componentes aislados. |
| Pruebas / Staging | Instancia espejo del servidor de producción instalada en una máquina de oficina dedicada con los mismos permisos y configuraciones. | Entorno destinado a la validación de usuario final (Secretaria/Jefe) antes del despliegue en la operación real. |
| Producción | Servidor local dedicado de la entidad (oficina) con configuración de servidor web Apache, rutas protegidas y acceso restringido vía red interna. | Ambiente de ejecución donde se gestionan los radicados reales y se ejecutan las tareas programadas (Cron jobs). |

## Fase 7. Validación final

### Checklist final antes de programar

**Checklist final**

- [ ] Documento de requerimientos funcionales y no funcionales aprobado
- [ ] Historias de usuario o casos de uso priorizados
- [ ] Wireframes de pantallas principales
- [ ] Modelo de datos (DER) definido
- [ ] Arquitectura y stack tecnológico decididos y justificados
- [ ] Backlog o cronograma con tareas desglosadas
- [ ] Repositorio configurado con convenciones claras
- [ ] Definición de "Hecho" acordada
- [ ] Entornos de desarrollo listos

