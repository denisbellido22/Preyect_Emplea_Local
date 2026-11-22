const fs = require('fs');
const path = require('path');
const {
    Document,
    Packer,
    Paragraph,
    TextRun,
    HeadingLevel,
    ImageRun,
    Table,
    TableRow,
    TableCell,
    WidthType,
    BorderStyle,
    AlignmentType,
    ShadingType
} = require('docx');

const SCREENSHOTS_DIR = path.join(__dirname, '..', 'docs', 'manual', 'screenshots');
const OUTPUT_DOCX = path.join(__dirname, '..', 'docs', 'MANUAL_DE_USUARIO_EMPLEA_LOCAL.docx');

const BRAND_TEAL = "0F766E";
const BRAND_DARK = "115E59";
const BRAND_GRAY = "64748B";
const BRAND_LIGHT = "F1F5F9";
const BRAND_AMBER = "D97706";

const views = [
    // PUBLICAS
    {
        num: '01',
        title: 'Portada Principal (Landing Page)',
        file: '01_inicio_landing.png',
        route: '/',
        role: 'Público / Visitante',
        rf: 'RF07, RF15, RF19',
        desc: 'Pantalla de bienvenida y presentación general del sistema para la provincia de Parinacochas.',
        items: [
            'Hero principal con buscador rápido por puesto, empresa o distrito.',
            'Botones de acceso rápido para explorar empleos o ver convocatorias.',
            'Tarjetas de estadísticas en tiempo real (empleos activos, becas, instituciones aliadas y distritos).',
            'Carrusel informativo y anuncios publicitarios de negocios locales.'
        ]
    },
    {
        num: '02',
        title: 'Catálogo de Ofertas Laborales',
        file: '02_catalogo_ofertas.png',
        route: '/ofertas',
        role: 'Público / Visitante',
        rf: 'RF07',
        desc: 'Directorio público con buscador avanzado y filtros dinámicos de oportunidades de trabajo.',
        items: [
            'Filtros por palabra clave, distrito (Coracora, Chumpi, Puquio, etc.), jornada y rango salarial.',
            'Tarjetas informativas de vacantes con puesto, empresa, distrito, modalidad y remuneración.',
            'Paginador de resultados con navegación fluida.'
        ]
    },
    {
        num: '03',
        title: 'Ficha Detallada de Oferta Laboral',
        file: '03_detalle_oferta.png',
        route: '/ofertas/{id}',
        role: 'Público / Visitante',
        rf: 'RF08',
        desc: 'Descripción completa del puesto de trabajo, requisitos específicos y condiciones contractuales.',
        items: [
            'Descripción minuciosa de responsabilidades y actividades del cargo.',
            'Requisitos formativos, años de experiencia y competencias requeridas.',
            'Datos de la empresa contratante, lugar de trabajo y fecha límite de postulación.',
            'Aviso con botón para iniciar sesión o registrarse si el usuario no está autenticado.'
        ]
    },
    {
        num: '04',
        title: 'Directorio de Becas y Oportunidades Formativas',
        file: '04_directorio_oportunidades.png',
        route: '/oportunidades',
        role: 'Público / Visitante',
        rf: 'RF15',
        desc: 'Directorio de programas de formación continua, becas de estudio, talleres técnicos y ferias.',
        items: [
            'Filtrado por tipo de programa: Beca, Curso, Taller, Convocatoria o Feria laboral.',
            'Fichas con imagen promocional, fecha del evento, lugar e institución promotora.',
            'Acceso libre sin necesidad de inicio de sesión.'
        ]
    },
    {
        num: '05',
        title: 'Detalle de Oportunidad Formativa',
        file: '05_detalle_oportunidad.png',
        route: '/oportunidades/{id}',
        role: 'Público / Visitante',
        rf: 'RF15',
        desc: 'Información ampliada del curso, beca o taller con enlaces directos de inscripción.',
        items: [
            'Objetivos pedagógicos o de inserción laboral del programa.',
            'Perfil de postulantes aptos y requisitos de postulación.',
            'Botón de enlace directo al formulario o portal web de la entidad promotora.'
        ]
    },
    {
        num: '06',
        title: 'Formulario de Inicio de Sesión',
        file: '06_iniciar_sesion.png',
        route: '/login',
        role: 'Visitante (Guest)',
        rf: 'RF02',
        desc: 'Módulo de autenticación segura al sistema con soporte para cuentas de prueba.',
        items: [
            'Ingreso de correo electrónico y contraseña con validaciones de seguridad.',
            'Opción "Recordarme" para persistencia segura de sesión.',
            'Panel de acceso rápido con un clic para cuentas de prueba (Admin, Empleador, Postulante).',
            'Enlaces directos a recuperación de contraseña y registro de cuentas nuevas.'
        ]
    },
    {
        num: '07',
        title: 'Formulario de Registro de Usuario',
        file: '07_registro_usuario.png',
        route: '/register',
        role: 'Visitante (Guest)',
        rf: 'RF01',
        desc: 'Creación de nuevas cuentas ciudadanas o empresariales en la plataforma.',
        items: [
            'Selector de rol obligatorio: Postulante o Empleador.',
            'Captura de nombre completo o razón social, correo electrónico y número de teléfono.',
            'Contraseña segura de mínimo 8 caracteres con validación de confirmación.',
            'Envío automático de correo con enlace de verificación de identidad.'
        ]
    },
    {
        num: '08',
        title: 'Solicitud de Recuperación de Contraseña',
        file: '08_recuperar_clave.png',
        route: '/forgot-password',
        role: 'Visitante (Guest)',
        rf: 'RF02',
        desc: 'Generación de enlace criptográfico de recuperación para contraseñas extraviadas.',
        items: [
            'Validación del correo electrónico registrado en la base de datos.',
            'Emisión de un token temporal con caducidad para prevenir accesos no autorizados.',
            'Notificación en pantalla confirmando el envío de las instrucciones.'
        ]
    },
    {
        num: '09',
        title: 'Restablecimiento de Contraseña',
        file: '09_restablecer_clave.png',
        route: '/reset-password/{token}',
        role: 'Visitante con token',
        rf: 'RF02',
        desc: 'Formulario seguro para registrar la nueva contraseña con el token enviado por correo.',
        items: [
            'Comprobación automática de la firma del token recibido.',
            'Campos para definir y confirmar la nueva clave de acceso.',
            'Actualización cifrada con bcrypt en la base de datos y redirección al login.'
        ]
    },

    // POSTULANTE
    {
        num: '10',
        title: 'Panel de Control (Dashboard) del Postulante',
        file: '10_postulante_dashboard.png',
        route: '/dashboard',
        role: 'Postulante',
        rf: 'RF10, RF19',
        desc: 'Centro de mando para candidatos en búsqueda activa de empleo.',
        items: [
            'Tarjetas de métricas: Postulaciones enviadas, pendientes, aceptadas y ofertas vigentes.',
            'Accesos rápidos para buscar empleos, ver postulaciones o consultar oportunidades formativas.',
            'Bandeja de últimas postulaciones realizadas con estado de revisión actualizado.',
            'Lista de ofertas recomendadas según la región de Parinacochas.'
        ]
    },
    {
        num: '11',
        title: 'Historial de Mis Postulaciones',
        file: '11_postulante_mis_postulaciones.png',
        route: '/mis-postulaciones',
        role: 'Postulante',
        rf: 'RF10, RF11',
        desc: 'Seguimiento pormenorizado del estado de cada postulación realizada.',
        items: [
            'Tabla con título de la oferta, empleador contratante y fecha exacta de envío.',
            'Insignias de estado en tiempo real: Pendiente, En revisión, Aceptado o Rechazado.',
            'Botón "Ver oferta" para volver a examinar las condiciones de la vacante.'
        ]
    },
    {
        num: '12',
        title: 'Ficha de Perfil del Postulante',
        file: '12_postulante_perfil.png',
        route: '/perfil',
        role: 'Postulante',
        rf: 'RF03',
        desc: 'Currículum digital y datos de contacto visibles para las empresas donde se postule.',
        items: [
            'Identificación oficial: Nombre completo, DNI y distrito de residencia.',
            'Datos de contacto directo: Teléfono y correo electrónico verificado.',
            'Perfil profesional: Especialidad técnica, resumen de experiencia y descripción personal.'
        ]
    },
    {
        num: '13',
        title: 'Formulario de Edición de Perfil Laboral',
        file: '13_postulante_perfil_editar.png',
        route: '/perfil/editar',
        role: 'Postulante',
        rf: 'RF03',
        desc: 'Actualización periódica de datos personales, experiencia y competencias del candidato.',
        items: [
            'Formulario con validación en servidor para actualizar teléfono y distrito.',
            'Campos de texto enriquecido para redactar la trayectoria profesional y aptitudes.',
            'Sincronización instantánea con el perfil visible para los empleadores.'
        ]
    },
    {
        num: '14',
        title: 'Módulo de Cambio Seguro de Contraseña',
        file: '14_postulante_cambiar_password.png',
        route: '/perfil/password',
        role: 'Autenticado (Cualquier rol)',
        rf: 'RF03',
        desc: 'Gestión de credenciales y seguridad de acceso para la cuenta del usuario.',
        items: [
            'Validación obligatoria de la contraseña actual del usuario.',
            'Ingreso de la nueva contraseña y confirmación coincidente.',
            'Protección ante modificaciones accidentales o no autorizadas.'
        ]
    },
    {
        num: '15',
        title: 'Postulación Activa a Oferta Laboral',
        file: '15_postulante_oferta_postulacion.png',
        route: '/ofertas/{id}',
        role: 'Postulante verificado',
        rf: 'RF09',
        desc: 'Interacción directa con la vacante desde la cuenta autenticada del postulante.',
        items: [
            'Botón interactivo de postulación habilitado tras verificar el correo.',
            'Opción para redactar un mensaje de presentación personalizado hacia el empleador.',
            'Registro inmediato en la base de datos y notificación al panel del empleador.'
        ]
    },

    // EMPLEADOR
    {
        num: '16',
        title: 'Panel de Control (Dashboard) del Empleador',
        file: '16_empleador_dashboard.png',
        route: '/dashboard',
        role: 'Empleador',
        rf: 'RF19',
        desc: 'Tablero de gestión de recursos humanos y convocatorias de la empresa.',
        items: [
            'Métricas del negocio: Ofertas totales, pendientes de moderación, activas y postulaciones recibidas.',
            'Botonera con accesos rápidos: "+ Nueva oferta", "Mis ofertas", "Postulantes" y "Solicitar publicidad".',
            'Bandeja de últimas postulaciones recibidas con datos del postulante.'
        ]
    },
    {
        num: '17',
        title: 'Gestión de Mis Ofertas Publicadas',
        file: '17_empleador_mis_ofertas.png',
        route: '/empleador/ofertas',
        role: 'Empleador',
        rf: 'RF05',
        desc: 'Catálogo de todas las vacantes creadas por la empresa con control de ciclo de vida.',
        items: [
            'Listado con título del puesto, distrito, remuneración y fecha de vencimiento.',
            'Indicador de estado: Pendiente (en moderación), Aprobado (en vivo) o Rechazado.',
            'Acciones operativas para editar la información, revisar postulantes o dar de baja el aviso.'
        ]
    },
    {
        num: '18',
        title: 'Publicar Nueva Oferta Laboral',
        file: '18_empleador_crear_oferta.png',
        route: '/empleador/ofertas/crear',
        role: 'Empleador verificado',
        rf: 'RF04',
        desc: 'Formulario integral para crear una convocatoria de trabajo en la plataforma.',
        items: [
            'Título del empleo, descripción detallada y requisitos de formación o experiencia.',
            'Distrito de trabajo en Parinacochas, tipo de jornada y salario estimado.',
            'Definición de fecha límite de postulación para cierre automático de vacantes.'
        ]
    },
    {
        num: '19',
        title: 'Formulario de Edición de Oferta Laboral',
        file: '19_empleador_editar_oferta.png',
        route: '/empleador/ofertas/{id}/editar',
        role: 'Empleador (Propietario)',
        rf: 'RF05',
        desc: 'Ajuste de condiciones contractuales, salario o fechas de una oferta ya publicada.',
        items: [
            'Precarga de todos los campos vigentes para modificaciones puntuales.',
            'Validación de permisos asegurando que solo el creador pueda alterar su oferta.',
            'Guardado inmediato y actualización en tiempo real en el portal.'
        ]
    },
    {
        num: '20',
        title: 'Gestión y Evaluación de Postulantes',
        file: '20_empleador_postulantes.png',
        route: '/empleador/postulantes',
        role: 'Empleador',
        rf: 'RF12',
        desc: 'Módulo de selección de personal y evaluación de hojas de vida de candidatos.',
        items: [
            'Listado de postulantes inscritos con profesión técnica, teléfono y correo.',
            'Mensaje de motivación redactado por el candidato al postular.',
            'Selector interactivo para actualizar estado: Pendiente, En revisión, Aceptado o Rechazado.',
            'Notificación automática al panel del postulante tras guardar el veredicto.'
        ]
    },
    {
        num: '21',
        title: 'Gestión de Publicidades de la Empresa',
        file: '21_empleador_publicidades.png',
        route: '/empleador/publicidades',
        role: 'Empleador',
        rf: 'RF16',
        desc: 'Supervisión de banners y promociones comerciales solicitadas por la empresa.',
        items: [
            'Visualización de campañas publicitarias contratadas con fecha de inicio y fin.',
            'Estado de aprobación por parte de la administración (Pendiente, Aprobado, Finalizado).'
        ]
    },
    {
        num: '22',
        title: 'Solicitud de Nueva Campaña Publicitaria',
        file: '22_empleador_crear_publicidad.png',
        route: '/empleador/publicidades/crear',
        role: 'Empleador verificado',
        rf: 'RF16',
        desc: 'Carga de banners gráficos para promocionar productos y servicios locales.',
        items: [
            'Título de la campaña, texto descriptivo y enlace promocional.',
            'Carga de archivo de imagen (PNG, JPG, WEBP) almacenado localmente.',
            'Selección de rango de vigencia para visualización en el carrusel de inicio.'
        ]
    },
    {
        num: '23',
        title: 'Perfil Corporativo de la Empresa',
        file: '23_empleador_perfil.png',
        route: '/perfil',
        role: 'Empleador',
        rf: 'RF03',
        desc: 'Identidad corporativa que genera confianza en los postulantes locales.',
        items: [
            'Razón social, nombre del representante legal y teléfono institucional.',
            'Distrito de sede central y descripción de actividades productivas o comerciales.'
        ]
    },

    // ADMINISTRADOR
    {
        num: '24',
        title: 'Panel de Control (Dashboard) del Administrador',
        file: '24_admin_dashboard.png',
        route: '/dashboard',
        role: 'Administrador General',
        rf: 'RF19',
        desc: 'Panel maestro con indicadores de rendimiento y gobierno de toda la plataforma.',
        items: [
            'Contadores globales: Total de usuarios, ofertas vigentes, postulaciones, oportunidades y anuncios.',
            'Distribución de usuarios por rol (Administradores, Empleadores, Postulantes).',
            'Desglose de ofertas por estado de aprobación y anuncios en curso.',
            'Bandeja de moderación rápida para atender avisos pendientes.'
        ]
    },
    {
        num: '25',
        title: 'Padrón General de Usuarios Registrados',
        file: '25_admin_usuarios.png',
        route: '/admin/usuarios',
        role: 'Administrador General',
        rf: 'RF13',
        desc: 'Auditoría, control y búsqueda de todas las cuentas creadas en el sistema.',
        items: [
            'Filtros por rol y barra de búsqueda por nombre o correo electrónico.',
            'Tabla con nombre, correo, rol, distrito y estado de verificación de correo.',
            'Acciones para editar credenciales o dar de baja cuentas infractoras.'
        ]
    },
    {
        num: '26',
        title: 'Edición Administrativa de Usuario y Roles',
        file: '26_admin_editar_usuario.png',
        route: '/admin/usuarios/{id}/editar',
        role: 'Administrador General',
        rf: 'RF13',
        desc: 'Gestión granular de datos de usuario y asignación de privilegios en el sistema.',
        items: [
            'Modificación de nombres, apellidos, teléfono y distrito del usuario.',
            'Selector de rol para promover o reasignar permisos (Admin, Empleador, Postulante).'
        ]
    },
    {
        num: '27',
        title: 'Moderación y Aprobación de Ofertas Laborales',
        file: '27_admin_moderacion_ofertas.png',
        route: '/admin/ofertas',
        role: 'Administrador General',
        rf: 'RF06',
        desc: 'Filtro de calidad y control previo antes de hacer públicas las ofertas laborales.',
        items: [
            'Bandeja con todas las vacantes enviadas por empleadores pendientes de revisión.',
            'Botón "Aprobar" para publicar inmediatamente la oferta en el catálogo público.',
            'Botón "Rechazar" para vetar ofertas que incumplan normativas laborales o contengan datos falsos.',
            'Botón "Editar" para corregir erratas antes de la aprobación.'
        ]
    },
    {
        num: '28',
        title: 'Edición Administrativa de Oferta Laboral',
        file: '28_admin_editar_oferta.png',
        route: '/admin/ofertas/{id}/editar',
        role: 'Administrador General',
        rf: 'RF06',
        desc: 'Corrección y homologación de datos de cualquier vacante por el Administrador.',
        items: [
            'Ajuste de salarios, ortografía, distrito o fechas de vigencia.',
            'Garantiza que la información visible para la comunidad sea clara y veraz.'
        ]
    },
    {
        num: '29',
        title: 'Gestión General de Oportunidades y Cursos',
        file: '29_admin_oportunidades.png',
        route: '/admin/oportunidades',
        role: 'Administrador General',
        rf: 'RF14',
        desc: 'Control y publicación de becas de estudio, talleres formativos y programas sociales.',
        items: [
            'Tabla con título, entidad organizadora, tipo de evento, fecha y estado (Publicado/Borrador).',
            'Acceso a creación rápida con el botón "+ Nueva oportunidad".'
        ]
    },
    {
        num: '30',
        title: 'Formulario para Registrar Nueva Oportunidad',
        file: '30_admin_crear_oportunidad.png',
        route: '/admin/oportunidades/create',
        role: 'Administrador General',
        rf: 'RF14',
        desc: 'Alta de programas de formación y becas impulsadas por instituciones públicas o privadas.',
        items: [
            'Definición de título, tipo (Beca, Curso, Taller, Feria), descripción y requisitos.',
            'Institución aliada responsable, lugar o modalidad, fecha límite y enlace oficial.',
            'Carga de afiche gráfico promocional almacenado localmente.'
        ]
    },
    {
        num: '31',
        title: 'Edición de Oportunidad Formativa',
        file: '31_admin_editar_oportunidad.png',
        route: '/admin/oportunidades/{id}/edit',
        role: 'Administrador General',
        rf: 'RF14',
        desc: 'Actualización de fechas, términos o enlaces externos de convocatorias activas.',
        items: [
            'Edición completa de datos de la oportunidad.',
            'Previsualización de la imagen actual y opción para sustituirla con un nuevo archivo.'
        ]
    },
    {
        num: '32',
        title: 'Moderación y Aprobación de Banners Publicitarios',
        file: '32_admin_publicidades.png',
        route: '/admin/publicidades',
        role: 'Administrador General',
        rf: 'RF17',
        desc: 'Supervisión y control ético de los anuncios comerciales emitidos por negocios locales.',
        items: [
            'Previsualización del banner publicitario, texto y empresa solicitante.',
            'Botones directos para Aprobar, Rechazar o dar por Finalizada una campaña publicitaria.'
        ]
    }
];

async function generateDocx() {
    console.log('Construyendo documento DOCX estructurado...');

    const children = [];

    // --- PORTADA ---
    children.push(
        new Paragraph({
            text: "SISTEMA WEB REGIONAL · PARINACOCHAS",
            heading: HeadingLevel.HEADING_3,
            spacing: { before: 200, after: 100 },
        }),
        new Paragraph({
            text: "EMPLEA LOCAL",
            heading: HeadingLevel.TITLE,
            spacing: { before: 200, after: 200 },
        }),
        new Paragraph({
            children: [
                new TextRun({
                    text: "Manual de Usuario y Catálogo Completo de Pantallas (32 Vistas)",
                    size: 28,
                    bold: true,
                    color: BRAND_DARK,
                })
            ],
            spacing: { after: 400 }
        }),
        new Paragraph({
            children: [
                new TextRun({
                    text: "Documento técnico y operativo que describe la navegación, interacción y reglas de negocio para los roles Visitante, Postulante, Empleador y Administrador General.",
                    italics: true,
                    color: BRAND_GRAY,
                    size: 22,
                })
            ],
            spacing: { after: 600 }
        })
    );

    // Tabla de metadatos de portada
    const metaTable = new Table({
        width: { size: 100, type: WidthType.PERCENTAGE },
        rows: [
            new TableRow({
                children: [
                    new TableCell({ children: [new Paragraph({ text: "Ámbito Geográfico:", bold: true })], width: { size: 30, type: WidthType.PERCENTAGE } }),
                    new TableCell({ children: [new Paragraph("Provincia de Parinacochas (Coracora, Chumpi, Puquio, etc.)")], width: { size: 70, type: WidthType.PERCENTAGE } }),
                ]
            }),
            new TableRow({
                children: [
                    new TableCell({ children: [new Paragraph({ text: "Tecnología Base:", bold: true })] }),
                    new TableCell({ children: [new Paragraph("Laravel 12 · SQLite Local · WebSockets Reverb · Bootstrap 5.3")] }),
                ]
            }),
            new TableRow({
                children: [
                    new TableCell({ children: [new Paragraph({ text: "Total de Vistas:", bold: true })] }),
                    new TableCell({ children: [new Paragraph("32 Vistas Funcionales con capturas de pantalla integradas")] }),
                ]
            }),
            new TableRow({
                children: [
                    new TableCell({ children: [new Paragraph({ text: "Requerimientos:", bold: true })] }),
                    new TableCell({ children: [new Paragraph("RF01 al RF20 (Mapeo completo)")] }),
                ]
            }),
            new TableRow({
                children: [
                    new TableCell({ children: [new Paragraph({ text: "Fecha de Emisión:", bold: true })] }),
                    new TableCell({ children: [new Paragraph("Noviembre de 2026 · Versión 1.0")] }),
                ]
            })
        ]
    });
    children.push(metaTable);

    // Salto de página tras portada
    children.push(new Paragraph({ children: [new TextRun("")], pageBreakBefore: true }));

    // --- SECCIÓN 1: INTRODUCCIÓN Y CUENTAS DEMO ---
    children.push(
        new Paragraph({
            text: "1. Introducción y Arquitectura del Sistema",
            heading: HeadingLevel.HEADING_1,
            spacing: { before: 200, after: 150 }
        }),
        new Paragraph({
            text: "Emplea Local es una plataforma diseñada para conectar la oferta laboral y las necesidades de talento en la provincia de Parinacochas, impulsando el trabajo formal y el acceso a becas y capacitaciones técnicas en la región.",
            spacing: { after: 200 }
        }),
        new Paragraph({
            text: "1.1. Cuentas de Acceso de Demostración",
            heading: HeadingLevel.HEADING_2,
            spacing: { before: 150, after: 100 }
        })
    );

    const accountsTable = new Table({
        width: { size: 100, type: WidthType.PERCENTAGE },
        rows: [
            new TableRow({
                children: [
                    new TableCell({ children: [new Paragraph({ text: "Rol", bold: true })], shading: { fill: BRAND_LIGHT, type: ShadingType.CLEAR } }),
                    new TableCell({ children: [new Paragraph({ text: "Correo Electrónico", bold: true })], shading: { fill: BRAND_LIGHT, type: ShadingType.CLEAR } }),
                    new TableCell({ children: [new Paragraph({ text: "Contraseña", bold: true })], shading: { fill: BRAND_LIGHT, type: ShadingType.CLEAR } }),
                    new TableCell({ children: [new Paragraph({ text: "Nombre Representativo", bold: true })], shading: { fill: BRAND_LIGHT, type: ShadingType.CLEAR } }),
                ]
            }),
            new TableRow({
                children: [
                    new TableCell({ children: [new Paragraph("Administrador")] }),
                    new TableCell({ children: [new Paragraph("admin@emplealocal.test")] }),
                    new TableCell({ children: [new Paragraph("password123")] }),
                    new TableCell({ children: [new Paragraph("Administrador General")] }),
                ]
            }),
            new TableRow({
                children: [
                    new TableCell({ children: [new Paragraph("Empleador")] }),
                    new TableCell({ children: [new Paragraph("empleador@emplealocal.test")] }),
                    new TableCell({ children: [new Paragraph("password123")] }),
                    new TableCell({ children: [new Paragraph("Constructora Parinacochas S.A.C.")] }),
                ]
            }),
            new TableRow({
                children: [
                    new TableCell({ children: [new Paragraph("Postulante")] }),
                    new TableCell({ children: [new Paragraph("postulante@emplealocal.test")] }),
                    new TableCell({ children: [new Paragraph("password123")] }),
                    new TableCell({ children: [new Paragraph("María Huamán Campos")] }),
                ]
            }),
        ]
    });
    children.push(accountsTable);

    // Salto de página antes de las vistas
    children.push(new Paragraph({ children: [new TextRun("")], pageBreakBefore: true }));

    // --- SECCIÓN 2: CATÁLOGO DE VISTAS CON CAPTURAS ---
    children.push(
        new Paragraph({
            text: "2. Catálogo Oficial de Pantallas del Sistema (32 Vistas)",
            heading: HeadingLevel.HEADING_1,
            spacing: { before: 200, after: 200 }
        })
    );

    let lastModule = '';

    for (const v of views) {
        let currentModule = '';
        const n = parseInt(v.num, 10);
        if (n <= 9) currentModule = 'Módulo 1: Portal Público y Visitantes';
        else if (n <= 15) currentModule = 'Módulo 2: Panel y Gestión del Postulante';
        else if (n <= 23) currentModule = 'Módulo 3: Panel y Gestión del Empleador';
        else currentModule = 'Módulo 4: Administración General y Gobernanza';

        if (currentModule !== lastModule) {
            lastModule = currentModule;
            children.push(
                new Paragraph({
                    text: currentModule.toUpperCase(),
                    heading: HeadingLevel.HEADING_2,
                    spacing: { before: 400, after: 200 },
                    pageBreakBefore: n > 1
                })
            );
        }

        // Título de la vista
        children.push(
            new Paragraph({
                children: [
                    new TextRun({
                        text: `Vista ${v.num}: ${v.title}`,
                        bold: true,
                        size: 26,
                        color: BRAND_TEAL
                    })
                ],
                spacing: { before: 250, after: 80 }
            }),
            new Paragraph({
                children: [
                    new TextRun({ text: "Ruta: ", bold: true }),
                    new TextRun({ text: `${v.route}   |   ` }),
                    new TextRun({ text: "Rol: ", bold: true }),
                    new TextRun({ text: `${v.role}   |   ` }),
                    new TextRun({ text: "Req: ", bold: true, color: BRAND_AMBER }),
                    new TextRun({ text: `${v.rf}`, bold: true, color: BRAND_AMBER })
                ],
                spacing: { after: 120 }
            }),
            new Paragraph({
                text: v.desc,
                spacing: { after: 180 }
            })
        );

        // Imagen incrustada
        const imgPath = path.join(SCREENSHOTS_DIR, v.file);
        if (fs.existsSync(imgPath)) {
            const imgData = fs.readFileSync(imgPath);
            children.push(
                new Paragraph({
                    alignment: AlignmentType.CENTER,
                    children: [
                        new ImageRun({
                            data: imgData,
                            transformation: {
                                width: 550,
                                height: 350,
                            }
                        })
                    ],
                    spacing: { after: 150 }
                })
            );
        }

        // Lista de items
        for (const item of v.items) {
            children.push(
                new Paragraph({
                    children: [
                        new TextRun({ text: "•  ", bold: true, color: BRAND_TEAL }),
                        new TextRun(item)
                    ],
                    spacing: { after: 60 }
                })
            );
        }

        children.push(new Paragraph({ text: "", spacing: { after: 200 } }));
    }

    // --- SECCIÓN 3: MATRIZ DE REQUERIMIENTOS ---
    children.push(
        new Paragraph({
            text: "3. Matriz Integral de Requerimientos Funcionales (RF01 - RF20)",
            heading: HeadingLevel.HEADING_1,
            spacing: { before: 300, after: 200 },
            pageBreakBefore: true
        })
    );

    const rfData = [
        ["RF01", "Registro de Usuarios", "Registro ciudadano como Postulante o Empleador con correo único.", "Vista 07"],
        ["RF02", "Autenticación y Recuperación", "Inicio de sesión seguro y recuperación de clave mediante enlace temporal.", "Vistas 06, 08, 09"],
        ["RF03", "Gestión de Perfil", "Actualización de experiencia, teléfono, distrito y cambio de contraseña.", "Vistas 12, 13, 14, 23"],
        ["RF04", "Publicación de Ofertas", "Publicación de vacantes con puesto, salario, distrito y fecha límite.", "Vista 18"],
        ["RF05", "Gestión de Ofertas Propias", "Monitoreo, edición y baja de avisos laborales por el empleador.", "Vistas 17, 19"],
        ["RF06", "Moderación de Ofertas", "Aprobación y filtro de calidad obligatorio por el Administrador.", "Vistas 27, 28"],
        ["RF07", "Búsqueda y Filtrado", "Buscador multicriterio por palabra clave, distrito y jornada laboral.", "Vistas 01, 02"],
        ["RF08", "Ficha Técnica de Vacante", "Visualización de funciones, salario y datos de la empresa.", "Vista 03"],
        ["RF09", "Postulación Directa", "Postulación a ofertas con mensaje personalizado (correo verificado).", "Vista 15"],
        ["RF10", "Seguimiento de Solicitudes", "Historial de postulaciones y consulta de estado.", "Vistas 10, 11"],
        ["RF11", "Actualización de Estado", "Notificación y visualización de cambios (Pendiente, Aceptado, etc.).", "Vistas 11, 20"],
        ["RF12", "Gestión de Candidatos", "Evaluación de currículum y calificación por el empleador.", "Vista 20"],
        ["RF13", "Gestión de Usuarios", "Auditoría de cuentas y reasignación de privilegios.", "Vistas 25, 26"],
        ["RF14", "Gestión de Oportunidades", "Publicación de becas, cursos y ferias por el Administrador.", "Vistas 29, 30, 31"],
        ["RF15", "Directorio de Becas", "Exposición pública de oportunidades formativas regionales.", "Vistas 04, 05"],
        ["RF16", "Solicitud de Anuncios", "Contratación de publicidad con banner por empresas.", "Vistas 21, 22"],
        ["RF17", "Moderación de Publicidad", "Control y aprobación de anuncios antes de su visualización.", "Vista 32"],
        ["RF18", "Notificaciones Reactivas", "Eventos y alertas en tiempo real con WebSockets (Reverb).", "Paneles y alertas"],
        ["RF19", "Tableros de Métricas (KPIs)", "Paneles de control y estadísticas por rol de usuario.", "Vistas 10, 16, 24"],
        ["RF20", "Control de Acceso (RBAC)", "Protección de rutas según rol del usuario autenticado.", "Rutas protegidas"]
    ];

    const rfRows = [
        new TableRow({
            children: [
                new TableCell({ children: [new Paragraph({ text: "Código", bold: true })], shading: { fill: BRAND_LIGHT, type: ShadingType.CLEAR }, width: { size: 15, type: WidthType.PERCENTAGE } }),
                new TableCell({ children: [new Paragraph({ text: "Requerimiento", bold: true })], shading: { fill: BRAND_LIGHT, type: ShadingType.CLEAR }, width: { size: 25, type: WidthType.PERCENTAGE } }),
                new TableCell({ children: [new Paragraph({ text: "Descripción / Regla", bold: true })], shading: { fill: BRAND_LIGHT, type: ShadingType.CLEAR }, width: { size: 40, type: WidthType.PERCENTAGE } }),
                new TableCell({ children: [new Paragraph({ text: "Vistas", bold: true })], shading: { fill: BRAND_LIGHT, type: ShadingType.CLEAR }, width: { size: 20, type: WidthType.PERCENTAGE } }),
            ]
        })
    ];

    for (const r of rfData) {
        rfRows.push(
            new TableRow({
                children: [
                    new TableCell({ children: [new Paragraph({ text: r[0], bold: true })] }),
                    new TableCell({ children: [new Paragraph(r[1])] }),
                    new TableCell({ children: [new Paragraph(r[2])] }),
                    new TableCell({ children: [new Paragraph(r[3])] }),
                ]
            })
        );
    }

    children.push(new Table({ width: { size: 100, type: WidthType.PERCENTAGE }, rows: rfRows }));

    // Crear Documento
    const doc = new Document({
        creator: "Emplea Local",
        title: "Manual de Usuario — Emplea Local",
        description: "Manual de Usuario con catálogo de 32 pantallas para la plataforma Emplea Local (Parinacochas)",
        sections: [{
            properties: {
                page: {
                    margin: { top: 1000, right: 1000, bottom: 1000, left: 1000 }
                }
            },
            children: children
        }]
    });

    console.log('Comprimiendo y guardando archivo DOCX...');
    const buffer = await Packer.toBuffer(doc);
    fs.writeFileSync(OUTPUT_DOCX, buffer);
    console.log(`¡DOCX generado exitosamente en: ${OUTPUT_DOCX}! Tamaño: ${buffer.length} bytes`);
}

generateDocx().catch(err => {
    console.error('Error generando DOCX:', err);
    process.exit(1);
});
