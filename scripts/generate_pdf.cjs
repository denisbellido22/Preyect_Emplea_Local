const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');

const SCREENSHOTS_DIR = path.join(__dirname, '..', 'docs', 'manual', 'screenshots');
const OUTPUT_HTML = path.join(__dirname, '..', 'docs', 'manual_print.html');
const OUTPUT_PDF = path.join(__dirname, '..', 'docs', 'MANUAL_DE_USUARIO_EMPLEA_LOCAL.pdf');

function getBase64Image(filename) {
    const filePath = path.join(SCREENSHOTS_DIR, filename);
    if (!fs.existsSync(filePath)) {
        console.warn(`Archivo no encontrado: ${filePath}`);
        return '';
    }
    const data = fs.readFileSync(filePath);
    return `data:image/png;base64,${data.toString('base64')}`;
}

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

function generateHtml() {
    let html = `<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Manual de Usuario — Emplea Local</title>
<style>
  @page {
    size: A4 portrait;
    margin: 1.5cm 1.4cm 1.8cm 1.4cm;
    @bottom-center {
      content: "Página " counter(page);
      font-size: 8pt;
      color: #64748b;
    }
  }

  * { box-sizing: border-box; }
  body {
    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
    color: #1e293b;
    background: #fff;
    line-height: 1.45;
    font-size: 10pt;
    margin: 0;
    padding: 0;
  }

  /* PORTADA */
  .cover-page {
    height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 3cm 1.5cm;
    background: linear-gradient(145deg, #0f766e 0%, #115e59 60%, #042f2e 100%);
    color: #ffffff;
    page-break-after: always;
    border-radius: 8px;
    box-sizing: border-box;
  }
  .cover-header {
    border-bottom: 2px solid rgba(255,255,255,0.25);
    padding-bottom: 20px;
  }
  .cover-logo-badge {
    display: inline-block;
    background: rgba(255,255,255,0.15);
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 11pt;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 12px;
  }
  .cover-title {
    font-size: 32pt;
    font-weight: 800;
    line-height: 1.15;
    margin: 10px 0;
  }
  .cover-subtitle {
    font-size: 16pt;
    font-weight: 300;
    color: #99f6e4;
    margin-bottom: 20px;
  }
  .cover-body {
    margin: 40px 0;
  }
  .cover-desc {
    font-size: 12pt;
    color: #e2e8f0;
    max-width: 600px;
    line-height: 1.6;
  }
  .cover-meta {
    background: rgba(0, 0, 0, 0.25);
    padding: 20px 24px;
    border-radius: 8px;
    border-left: 4px solid #f59e0b;
    font-size: 10pt;
  }
  .cover-meta table {
    width: 100%;
    color: #fff;
    border-collapse: collapse;
  }
  .cover-meta td {
    padding: 4px 8px;
  }
  .cover-meta td.label {
    font-weight: 600;
    color: #cbd5e1;
    width: 140px;
  }

  /* INDICE Y SECCIONES */
  .page-break { page-break-before: always; }
  .avoid-break { page-break-inside: avoid; }

  h1.section-header {
    font-size: 18pt;
    font-weight: 700;
    color: #0f766e;
    border-bottom: 2px solid #0f766e;
    padding-bottom: 6px;
    margin-top: 24px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
  }
  h2.section-header {
    font-size: 14pt;
    font-weight: 700;
    color: #115e59;
    margin-top: 18px;
    margin-bottom: 10px;
  }

  p { margin: 6px 0 10px 0; }

  /* CARDS DE CADA VISTA */
  .view-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 20px;
    background: #ffffff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    page-break-inside: avoid;
  }
  .view-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 8px;
    margin-bottom: 10px;
  }
  .view-title {
    font-size: 12.5pt;
    font-weight: 700;
    color: #0f766e;
    margin: 0;
  }
  .view-meta-badges {
    display: flex;
    gap: 6px;
  }
  .badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 8pt;
    font-weight: 600;
  }
  .badge-role { background: #e0f2fe; color: #0369a1; }
  .badge-route { background: #f1f5f9; color: #334155; font-family: monospace; }
  .badge-rf { background: #fef3c7; color: #92400e; }

  .view-desc {
    font-size: 9.5pt;
    color: #475569;
    margin-bottom: 10px;
  }

  .view-image-container {
    text-align: center;
    margin: 10px 0 12px 0;
    background: #f8fafc;
    padding: 6px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
  }
  .view-image {
    max-width: 100%;
    height: auto;
    max-height: 480px;
    border-radius: 4px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    display: inline-block;
  }

  .view-items-list {
    margin: 8px 0 0 0;
    padding-left: 20px;
    font-size: 9pt;
    color: #334155;
  }
  .view-items-list li {
    margin-bottom: 4px;
  }

  /* TABLAS GENERALES */
  table.data-table {
    width: 100%;
    border-collapse: collapse;
    margin: 12px 0 20px 0;
    font-size: 9pt;
  }
  table.data-table th, table.data-table td {
    border: 1px solid #cbd5e1;
    padding: 6px 10px;
    text-align: left;
  }
  table.data-table th {
    background: #f1f5f9;
    color: #0f766e;
    font-weight: 700;
  }
  table.data-table tr:nth-child(even) td {
    background: #f8fafc;
  }

  .alert-box {
    background: #f0fdfa;
    border-left: 4px solid #0f766e;
    padding: 10px 14px;
    margin: 12px 0;
    border-radius: 4px;
    font-size: 9.5pt;
    color: #115e59;
  }
</style>
</head>
<body>

<!-- 1. PORTADA -->
<div class="cover-page">
  <div class="cover-header">
    <div class="cover-logo-badge">Sistema Web Regional · Laravel 12</div>
    <div class="cover-title">EMPLEA LOCAL</div>
    <div class="cover-subtitle">Manual Oficial de Usuario y Catálogo de Pantallas</div>
  </div>

  <div class="cover-body">
    <div class="cover-desc">
      Guía integral de uso y referencia visual de todas las vistas del sistema web para la intermediación laboral, fomento del empleo formal y difusión de oportunidades de desarrollo en la provincia de <strong>Parinacochas (Ayacucho)</strong>.
    </div>
  </div>

  <div class="cover-meta">
    <table>
      <tr>
        <td class="label">Ámbito Regional:</td>
        <td>Provincia de Parinacochas — Coracora, Chumpi, Puquio y distritos aledaños</td>
      </tr>
      <tr>
        <td class="label">Versión de Sistema:</td>
        <td>1.0.0 (Laravel 12 / SQLite Local / WebSockets Reverb / Bootstrap 5.3)</td>
      </tr>
      <tr>
        <td class="label">Total de Vistas:</td>
        <td>32 Vistas Funcionales con capturas de pantalla de alta resolución</td>
      </tr>
      <tr>
        <td class="label">Requerimientos:</td>
        <td>RF01 al RF20 (Mapeo integral de requerimientos funcionales)</td>
      </tr>
      <tr>
        <td class="label">Fecha de Emisión:</td>
        <td>Noviembre de 2026</td>
      </tr>
    </table>
  </div>
</div>

<!-- 2. INTRODUCCIÓN Y ARQUITECTURA -->
<div class="page-break"></div>
<h1 class="section-header">1. Introducción y Arquitectura del Sistema</h1>

<p>
  <strong>Emplea Local</strong> es una solución tecnológica desarrollada con el propósito de centralizar y dinamizar las oportunidades laborales y de formación técnica en la provincia de Parinacochas. Toda la información —incluyendo base de datos SQLite y archivos multimedia— opera de forma local y autónoma, asegurando alta disponibilidad e independencia de infraestructura externa compleja.
</p>

<div class="alert-box">
  <strong>Pilares del Sistema:</strong>
  <ul>
    <li><strong>Transparencia:</strong> Cada oferta laboral publicada por una empresa es auditada y aprobada por un Administrador antes de exponerse al público.</li>
    <li><strong>Inclusividad:</strong> Acceso público y sin barreras para la consulta de ofertas y programas de becas.</li>
    <li><strong>Seguridad:</strong> Control de acceso por roles (RBAC), verificación obligatoria de correo electrónico y protección de datos personales.</li>
  </ul>
</div>

<h2 class="section-header">1.1. Cuentas de Acceso Preconfiguradas (Demostración)</h2>
<p>Para evaluación técnica y académica, la plataforma provee las siguientes cuentas de prueba:</p>

<table class="data-table">
  <thead>
    <tr>
      <th>Rol del Usuario</th>
      <th>Correo Electrónico</th>
      <th>Contraseña</th>
      <th>Nombre / Representante</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Administrador</strong></td>
      <td><code>admin@emplealocal.test</code></td>
      <td><code>password123</code></td>
      <td>Administrador General del Sistema</td>
    </tr>
    <tr>
      <td><strong>Empleador</strong></td>
      <td><code>empleador@emplealocal.test</code></td>
      <td><code>password123</code></td>
      <td>Constructora Parinacochas S.A.C.</td>
    </tr>
    <tr>
      <td><strong>Postulante</strong></td>
      <td><code>postulante@emplealocal.test</code></td>
      <td><code>password123</code></td>
      <td>María Huamán Campos (Téc. en Administración)</td>
    </tr>
  </tbody>
</table>

<!-- 3. INDICE GENERAL DE VISTAS -->
<h1 class="section-header">2. Índice General de Vistas (32 Pantallas)</h1>
<table class="data-table">
  <thead>
    <tr>
      <th style="width: 45px;">N°</th>
      <th>Nombre de la Vista</th>
      <th>Ruta (URL)</th>
      <th>Rol Autorizado</th>
      <th style="width: 80px;">Req. Func.</th>
    </tr>
  </thead>
  <tbody>
`;

views.forEach(v => {
    html += `
    <tr>
      <td><strong>${v.num}</strong></td>
      <td>${v.title}</td>
      <td><code>${v.route}</code></td>
      <td>${v.role}</td>
      <td>${v.rf}</td>
    </tr>`;
});

html += `
  </tbody>
</table>

<!-- 4. CATÁLOGO COMPLETO DE VISTAS CON CAPTURAS -->
<div class="page-break"></div>
<h1 class="section-header">3. Catálogo Detallado de Vistas con Capturas</h1>
`;

let currentSection = '';

views.forEach(v => {
    let sectionTitle = '';
    const numInt = parseInt(v.num, 10);
    if (numInt === 1) sectionTitle = 'MÓDULO 1: PORTAL PÚBLICO Y VISITANTES';
    else if (numInt === 10) sectionTitle = 'MÓDULO 2: PANEL Y GESTIÓN DEL POSTULANTE';
    else if (numInt === 16) sectionTitle = 'MÓDULO 3: PANEL Y GESTIÓN DEL EMPLEADOR';
    else if (numInt === 24) sectionTitle = 'MÓDULO 4: ADMINISTRACIÓN GENERAL Y GOBERNANZA';

    if (sectionTitle && sectionTitle !== currentSection) {
        currentSection = sectionTitle;
        html += `
<div class="page-break"></div>
<h1 class="section-header" style="background:#f0fdfa; padding: 10px 14px; border-radius: 6px; border: 1px solid #ccfbf1;">
  ${currentSection}
</h1>`;
    }

    const base64Img = getBase64Image(v.file);

    html += `
<div class="view-card">
  <div class="view-header">
    <h3 class="view-title">Vista ${v.num}: ${v.title}</h3>
    <div class="view-meta-badges">
      <span class="badge badge-role">${v.role}</span>
      <span class="badge badge-route">${v.route}</span>
      <span class="badge badge-rf">${v.rf}</span>
    </div>
  </div>

  <div class="view-desc">${v.desc}</div>

  <div class="view-image-container">
    <img src="${base64Img}" alt="Captura ${v.title}" class="view-image" />
  </div>

  <ul class="view-items-list">
    ${v.items.map(it => `<li>${it}</li>`).join('')}
  </ul>
</div>`;
});

// MATRIZ DE REQUERIMIENTOS FUNCIONALES
html += `
<div class="page-break"></div>
<h1 class="section-header">4. Matriz Integral de Requerimientos Funcionales (RF01 - RF20)</h1>

<table class="data-table">
  <thead>
    <tr>
      <th style="width: 60px;">Código</th>
      <th>Requerimiento Funcional</th>
      <th>Descripción y Regla de Negocio</th>
      <th>Vistas Asociadas</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>RF01</strong></td>
      <td>Registro de Usuarios</td>
      <td>Permite el registro ciudadano como Postulante o Empleador con correo único y validación de contraseña.</td>
      <td>Vista 07 (<code>/register</code>)</td>
    </tr>
    <tr>
      <td><strong>RF02</strong></td>
      <td>Autenticación y Recuperación</td>
      <td>Inicio de sesión seguro, recordatorio en cookies y restablecimiento de contraseña mediante token por correo.</td>
      <td>Vistas 06, 08 y 09</td>
    </tr>
    <tr>
      <td><strong>RF03</strong></td>
      <td>Gestión de Perfil</td>
      <td>Actualización de datos personales, experiencia laboral, datos corporativos y cambio seguro de contraseña.</td>
      <td>Vistas 12, 13, 14 y 23</td>
    </tr>
    <tr>
      <td><strong>RF04</strong></td>
      <td>Publicación de Ofertas</td>
      <td>Registro de vacantes por empleadores con puesto, requisitos, salario, distrito y fecha de vencimiento.</td>
      <td>Vista 18 (<code>/empleador/ofertas/crear</code>)</td>
    </tr>
    <tr>
      <td><strong>RF05</strong></td>
      <td>Gestión de Ofertas Propias</td>
      <td>Monitoreo, edición y baja de ofertas laborales creadas por la empresa.</td>
      <td>Vistas 17 y 19</td>
    </tr>
    <tr>
      <td><strong>RF06</strong></td>
      <td>Moderación de Ofertas</td>
      <td>Revisión y aprobación obligatoria de ofertas por el Administrador antes de publicarse en el catálogo.</td>
      <td>Vistas 27 y 28</td>
    </tr>
    <tr>
      <td><strong>RF07</strong></td>
      <td>Búsqueda y Filtrado</td>
      <td>Buscador multicriterio por palabra clave, distrito, jornada laboral y rango salarial.</td>
      <td>Vistas 01 y 02</td>
    </tr>
    <tr>
      <td><strong>RF08</strong></td>
      <td>Ficha Técnica de Vacante</td>
      <td>Visualización de responsabilidades, condiciones salariales y datos de la empresa contratante.</td>
      <td>Vista 03 (<code>/ofertas/{id}</code>)</td>
    </tr>
    <tr>
      <td><strong>RF09</strong></td>
      <td>Postulación Directa</td>
      <td>Postulación a vacantes aprobadas con mensaje personalizado del candidato (exige correo verificado).</td>
      <td>Vista 15 (<code>/ofertas/{id}</code>)</td>
    </tr>
    <tr>
      <td><strong>RF10</strong></td>
      <td>Seguimiento de Postulaciones</td>
      <td>Historial cronológico de postulaciones enviadas con consulta del estado de selección.</td>
      <td>Vistas 10 y 11</td>
    </tr>
    <tr>
      <td><strong>RF11</strong></td>
      <td>Actualización de Estado</td>
      <td>Notificación y visualización de cambios de estado (Pendiente, En revisión, Aceptado, Rechazado).</td>
      <td>Vistas 11 y 20</td>
    </tr>
    <tr>
      <td><strong>RF12</strong></td>
      <td>Gestión de Candidatos</td>
      <td>Bandeja de postulantes recibidos con calificación y actualización de estado por el empleador.</td>
      <td>Vista 20 (<code>/empleador/postulantes</code>)</td>
    </tr>
    <tr>
      <td><strong>RF13</strong></td>
      <td>Auditoría y Gestión de Usuarios</td>
      <td>Listado maestro de usuarios, filtrado por rol y capacidad de reasignar privilegios.</td>
      <td>Vistas 25 y 26</td>
    </tr>
    <tr>
      <td><strong>RF14</strong></td>
      <td>Gestión de Oportunidades</td>
      <td>Alta, edición y baja de programas formativos, becas técnicas y ferias laborales por el Administrador.</td>
      <td>Vistas 29, 30 y 31</td>
    </tr>
    <tr>
      <td><strong>RF15</strong></td>
      <td>Directorio de Oportunidades</td>
      <td>Exposición pública y gratuita de convocatorias de formación técnica para la juventud de la zona.</td>
      <td>Vistas 04 y 05</td>
    </tr>
    <tr>
      <td><strong>RF16</strong></td>
      <td>Solicitud de Anuncios</td>
      <td>Registro de publicidad con banner gráfico y periodo de vigencia por empresas locales.</td>
      <td>Vistas 21 y 22</td>
    </tr>
    <tr>
      <td><strong>RF17</strong></td>
      <td>Moderación de Publicidad</td>
      <td>Control y aprobación de anuncios comerciales antes de su exhibición en la portada pública.</td>
      <td>Vista 32 (<code>/admin/publicidades</code>)</td>
    </tr>
    <tr>
      <td><strong>RF18</strong></td>
      <td>Notificaciones Reactivas</td>
      <td>Actualización de eventos mediante WebSockets locales (Laravel Reverb).</td>
      <td>Paneles de control y alertas</td>
    </tr>
    <tr>
      <td><strong>RF19</strong></td>
      <td>Tableros de Métricas (KPIs)</td>
      <td>Paneles visuales con estadísticas de rendimiento adaptadas a cada rol de usuario.</td>
      <td>Vistas 10, 16 y 24</td>
    </tr>
    <tr>
      <td><strong>RF20</strong></td>
      <td>Control de Acceso (RBAC)</td>
      <td>Protección de rutas mediante middleware según el rol del usuario y verificación de correo.</td>
      <td>Rutas autenticadas del sistema</td>
    </tr>
  </tbody>
</table>

<div class="page-break"></div>
<h1 class="section-header">5. Preguntas Frecuentes y Soporte Operativo</h1>

<div class="view-card">
  <h3 style="color:#0f766e; margin-top:0;">1. ¿Por qué una oferta creada por el empleador no aparece de inmediato en el catálogo público?</h3>
  <p>De acuerdo con la regla de negocio <strong>RF06</strong>, toda vacante laboral entra en estado <code>Pendiente</code> para proteger a los postulantes de ofertas fraudulentas o condiciones irregulares. Un Administrador General debe validarla y pulsar "Aprobar" desde su bandeja de moderación para que se visualice públicamente.</p>
</div>

<div class="view-card">
  <h3 style="color:#0f766e; margin-top:0;">2. ¿Qué requisitos se exigen para postularse a una oferta laboral?</h3>
  <p>El postulante debe haber creado una cuenta con rol <strong>Postulante</strong> y tener su correo electrónico debidamente verificado (o emplear una cuenta de prueba autorizada). Esto garantiza que el empleador reciba datos fidedignos y medios de contacto directos (teléfono y correo).</p>
</div>

<div class="view-card">
  <h3 style="color:#0f766e; margin-top:0;">3. ¿Cómo evalúa el empleador a los candidatos postulados?</h3>
  <p>El empleador accede a la sección <em>"Postulantes"</em> (<code>/empleador/postulantes</code>). Allí puede revisar el currículum técnico, teléfono y mensaje del candidato. Mediante un menú desplegable puede cambiar la fase de selección (<em>En revisión</em>, <em>Aceptado</em> o <em>Rechazado</em>) y pulsar "Guardar", lo que actualiza de inmediato el panel del postulante.</p>
</div>

<p style="text-align: center; color: #64748b; font-size: 8.5pt; margin-top: 30px;">
  Documento generado automáticamente para la plataforma <strong>Emplea Local</strong> (Parinacochas, Ayacucho) · Versión 1.0 (2026).
</p>

</body>
</html>`;

    return html;
}

async function run() {
    console.log('Generando archivo HTML para impresión...');
    const htmlContent = generateHtml();
    fs.writeFileSync(OUTPUT_HTML, htmlContent, 'utf8');
    console.log(`HTML generado en: ${OUTPUT_HTML}`);

    console.log('Invocando Microsoft Edge para compilar PDF...');
    const edgePath = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
    const tempProfile = path.join(__dirname, '..', 'temp_pdf_profile');
    if (!fs.existsSync(tempProfile)) fs.mkdirSync(tempProfile, { recursive: true });

    const proc = spawn(edgePath, [
        '--headless=new',
        '--disable-gpu',
        '--no-pdf-header-footer',
        `--user-data-dir=${tempProfile}`,
        `--print-to-pdf=${OUTPUT_PDF}`,
        OUTPUT_HTML
    ]);

    await new Promise((resolve, reject) => {
        proc.on('close', code => {
            if (code === 0) resolve();
            else reject(new Error(`Edge salió con código ${code}`));
        });
        proc.on('error', reject);
    });

    try { fs.rmSync(tempProfile, { recursive: true, force: true }); } catch {}

    if (fs.existsSync(OUTPUT_PDF)) {
        const stats = fs.statSync(OUTPUT_PDF);
        console.log(`¡PDF generado con éxito! Tamaño: ${stats.size} bytes`);
        console.log(`Ruta final: ${OUTPUT_PDF}`);
    } else {
        throw new Error('El archivo PDF no fue creado.');
    }
}

run().catch(err => {
    console.error('Error generando PDF:', err);
    process.exit(1);
});
