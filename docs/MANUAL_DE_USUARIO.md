# MANUAL DE USUARIO — SISTEMA "EMPLEA LOCAL"
### Conectando Oportunidades Laborales y Formativas en Parinacochas

---

> **Archivos generados para descarga:**
> - 📄 **Documento PDF:** [MANUAL_DE_USUARIO_EMPLEA_LOCAL.pdf](MANUAL_DE_USUARIO_EMPLEA_LOCAL.pdf) (4.4 MB)
> - 📝 **Documento Word (DOCX):** [MANUAL_DE_USUARIO_EMPLEA_LOCAL.docx](MANUAL_DE_USUARIO_EMPLEA_LOCAL.docx) (2.2 MB)
> - 🖼️ **Carpeta de Capturas:** [manual/screenshots/](manual/screenshots/)

---

## 1. Introducción y Arquitectura de la Plataforma

**Emplea Local** es una plataforma web desarrollada en **Laravel 12** con persistencia en **SQLite local**, diseñada específicamente para conectar el talento laboral, empresas e instituciones formativas de la provincia de **Parinacochas (Ayacucho)**.

El sistema funciona de manera autónoma, con almacenamiento local de imágenes, base de datos SQLite y actualización de eventos en tiempo real mediante WebSockets (Laravel Reverb).

### 1.1. Roles de Usuario

El sistema contempla tres perfiles de usuario bien definidos:

1. **Visitante / Público General:** Acceso sin autenticación para consultar el catálogo de empleos, revisar oportunidades formativas y acceder al registro e inicio de sesión.
2. **Postulante:** Ciudadanos que buscan trabajo, gestionan su currículum y perfil laboral, se postulan a ofertas aprobadas y monitorean en tiempo real el estado de sus solicitudes.
3. **Empleador:** Empresas o empleadores particulares locales que publican vacantes laborales, evalúan candidatos postulados y gestionan anuncios publicitarios para sus negocios.
4. **Administrador General:** Personal encargado de la gobernanza de la plataforma, auditoría de usuarios, moderación y aprobación de ofertas laborales, publicación de becas/cursos locales y supervisión de anuncios publicitarios.

### 1.2. Cuentas de Acceso de Demostración

Para fines de prueba, auditoría y demostración académica, el sistema cuenta con usuarios preconfigurados:

| Rol | Correo Electrónico | Contraseña | Nombre Representativo |
| :--- | :--- | :--- | :--- |
| **Administrador** | `admin@emplealocal.test` | `password123` | Administrador General |
| **Empleador** | `empleador@emplealocal.test` | `password123` | Constructora Parinacochas |
| **Postulante** | `postulante@emplealocal.test` | `password123` | María Huamán Campos |

---

## 2. Portal Público y Módulo de Visitantes

### 2.1. Vista 01: Portada Principal (Landing Page)
- **Ruta:** `/`
- **Acceso:** Público (Visitantes y usuarios autenticados)

![Portada Principal](screenshots/01_inicio_landing.png)

#### Descripción de la Interfaz:
- **Cabecera (Hero Section):** Mensaje de bienvenida enfocado en Parinacochas, con barra de búsqueda rápida por puesto, empresa o distrito.
- **Acciones Rápidas:** Botones directos para *"Explorar empleos"* y *"Ver becas y oportunidades"*.
- **Indicadores Estadísticos:** Tarjetas numéricas dinámicas que muestran empleos activos, oportunidades vigentes, instituciones aliadas y distritos con cobertura.
- **Carrusel y Secciones Destacadas:** Presentación de ofertas laborales recientes y avisos publicitarios aprobados.

---

### 2.2. Vista 02: Catálogo de Ofertas Laborales
- **Ruta:** `/ofertas`
- **Acceso:** Público

![Catálogo de Ofertas](screenshots/02_catalogo_ofertas.png)

#### Funcionalidades:
- **Barra de Filtros:** Filtrado dinámico por texto (título/descripción), distrito (Coracora, Chumpi, Puquio, etc.), tipo de jornada (tiempo completo, medio tiempo, por horas) y rango salarial.
- **Tarjetas de Empleo:** Muestran el título de la vacante, empresa contratante, distrito, salario estimado y etiquetas de modalidad.
- **Paginación:** Navegación fluida entre páginas de resultados.

---

### 2.3. Vista 03: Detalle Público de Oferta Laboral
- **Ruta:** `/ofertas/{id}`
- **Acceso:** Público

![Detalle de Oferta](screenshots/03_detalle_oferta.png)

#### Funcionalidades:
- **Ficha Técnica del Puesto:** Descripción exhaustiva de las tareas a desempeñar, requisitos mínimos y competencias deseadas.
- **Condiciones Contractuales:** Ubicación geográfica, jornada laboral, remuneración ofrecida y fecha límite de postulación.
- **Información del Empleador:** Razón social y perfil de la empresa contratante.
- **Llamado a la Acción:** Si el visitante no está autenticado, la plataforma le invita a iniciar sesión o crear una cuenta de postulante para aplicar.

---

### 2.4. Vista 04: Directorio de Becas y Oportunidades
- **Ruta:** `/oportunidades`
- **Acceso:** Público

![Directorio de Oportunidades](screenshots/04_directorio_oportunidades.png)

#### Funcionalidades:
- **Mapeo de Oportunidades:** Convocatorias a becas estudiantiles, capacitaciones técnicas, talleres agropecuarios y programas de inserción laboral en la región.
- **Filtros por Tipo:** Segmentación entre cursos, becas, talleres y eventos de orientación laboral.

---

### 2.5. Vista 05: Detalle de Oportunidad Formativa
- **Ruta:** `/oportunidades/{id}`
- **Acceso:** Público

![Detalle de Oportunidad](screenshots/05_detalle_oportunidad.png)

#### Funcionalidades:
- **Información del Programa:** Objetivos del curso o beca, público beneficiario y requisitos de admisión.
- **Entidad Promotora:** Nombre de la institución educativa o gubernamental responsable.
- **Enlace de Inscripción Oficial:** Botón directo hacia el formulario externo o portal oficial de la convocatoria.

---

### 2.6. Vista 06: Formulario de Inicio de Sesión
- **Ruta:** `/login`
- **Acceso:** Visitantes (`guest`)

![Iniciar Sesión](screenshots/06_iniciar_sesion.png)

#### Funcionalidades:
- **Campos de Acceso:** Ingreso de correo electrónico y contraseña.
- **Recordar Credenciales:** Casilla *"Recordarme"* para persistencia de sesión segura en cookies.
- **Acceso Rápido a Cuentas Demo:** Panel interactivo en entorno local que permite autocompletar credenciales con un solo clic para los roles Administrador, Empleador y Postulante.
- **Enlaces de Recuperación y Registro:** Accesos directos a "¿Olvidaste tu contraseña?" y formulario de registro.

---

### 2.7. Vista 07: Formulario de Registro de Usuario
- **Ruta:** `/register`
- **Acceso:** Visitantes (`guest`)

![Registro de Usuario](screenshots/07_registro_usuario.png)

#### Funcionalidades:
- **Selección de Rol:** Permite registrarse como **Postulante** o **Empleador** (el rol Administrador no está disponible en el registro público por motivos de seguridad).
- **Datos Requeridos:** Nombre completo o razón social, correo electrónico único, número telefónico y confirmación de contraseña (mínimo 8 caracteres).

---

### 2.8. Vista 08: Solicitud de Recuperación de Contraseña
- **Ruta:** `/forgot-password`
- **Acceso:** Visitantes (`guest`)

![Recuperar Contraseña](screenshots/08_recuperar_clave.png)

#### Funcionalidades:
- **Ingreso de Correo:** Validación de correo registrado en el sistema.
- **Generación de Token Seguro:** Envío de correo con enlace de un solo uso y caducidad temporal para restablecer la contraseña sin comprometer la cuenta.

---

### 2.9. Vista 09: Restablecimiento de Contraseña
- **Ruta:** `/reset-password/{token}`
- **Acceso:** Visitantes con token válido

![Restablecer Contraseña](screenshots/09_restablecer_clave.png)

#### Funcionalidades:
- **Verificación Criptográfica:** Validación del token recibido por correo electrónico.
- **Actualización de Clave:** Ingreso y confirmación de la nueva contraseña con validación de fortaleza.

---

## 3. Módulo del Postulante

### 3.1. Vista 10: Panel de Control (Dashboard) del Postulante
- **Ruta:** `/dashboard`
- **Acceso:** Autenticado como `postulante`

![Dashboard del Postulante](screenshots/10_postulante_dashboard.png)

#### Componentes Principales:
- **Tarjetas Métricas:** Resumen de postulaciones totales, postulaciones pendientes de revisión, postulaciones aceptadas y ofertas vigentes disponibles.
- **Accesos Directos:** Navegación rápida hacia *"Buscar ofertas"*, *"Mis postulaciones"* y *"Oportunidades"*.
- **Tabla de Actividad Reciente:** Resumen de las últimas postulaciones realizadas y sus estados.
- **Recomendaciones Laborales:** Listado de empleos recientes adaptados a la zona.

---

### 3.2. Vista 11: Mis Postulaciones
- **Ruta:** `/mis-postulaciones`
- **Acceso:** Autenticado como `postulante`

![Mis Postulaciones](screenshots/11_postulante_mis_postulaciones.png)

#### Componentes Principales:
- **Historial Completo:** Listado cronológico de todas las solicitudes enviadas a empleadores.
- **Estados Visuales (Badges):**
  - <span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:4px;font-weight:bold;">Pendiente</span>: Solicitud enviada, aún en espera de revisión.
  - <span style="background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:4px;font-weight:bold;">En revisión</span>: El empleador está evaluando el CV del candidato.
  - <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:4px;font-weight:bold;">Aceptado</span>: El postulante ha sido seleccionado o precalificado.
  - <span style="background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:4px;font-weight:bold;">Rechazado</span>: El empleador concluyó el proceso sin seleccionar la candidatura.
- **Acción:** Botón *"Ver oferta"* para revisar los términos del aviso original.

---

### 3.3. Vista 12: Mi Perfil Laboral (Postulante)
- **Ruta:** `/perfil`
- **Acceso:** Autenticado como `postulante`

![Perfil del Postulante](screenshots/12_postulante_perfil.png)

#### Componentes Principales:
- **Datos de Identificación:** Nombre completo, DNI, correo electrónico y teléfono de contacto.
- **Ubicación:** Distrito de residencia dentro de Parinacochas.
- **Ficha Profesional:** Profesión u ocupación técnica, resumen de experiencia laboral y descripción personal.

---

### 3.4. Vista 13: Edición de Perfil Laboral
- **Ruta:** `/perfil/editar`
- **Acceso:** Autenticado como `postulante`

![Editar Perfil](screenshots/13_postulante_perfil_editar.png)

#### Funcionalidades:
- Formulario para actualizar información personal, número telefónico, ocupación técnica y experiencia previa.
- Los cambios se reflejan inmediatamente en las futuras postulaciones que reciba el empleador.

---

### 3.5. Vista 14: Cambio de Contraseña
- **Ruta:** `/perfil/password`
- **Acceso:** Autenticado (Cualquier rol)

![Cambiar Contraseña](screenshots/14_postulante_cambiar_password.png)

#### Funcionalidades:
- Exige ingresar la **contraseña actual** para comprobar identidad.
- Permite definir una nueva contraseña segura con confirmación.
- Mecanismo de seguridad contra ataques de suplantación.

---

### 3.6. Vista 15: Postulación Directa a Oferta Laboral
- **Ruta:** `/ofertas/{id}`
- **Acceso:** Autenticado como `postulante`

![Postular a Oferta](screenshots/15_postulante_oferta_postulacion.png)

#### Flujo de Postulación:
- Al estar autenticado como postulante con correo verificado, la ficha técnica del empleo habilita el botón interactivo de postulación.
- El postulante puede incluir un mensaje personalizado de presentación dirigido al empleador.
- La postulación se registra instantáneamente y notifica al empleador correspondiente.

---

## 4. Módulo del Empleador

### 4.1. Vista 16: Panel de Control (Dashboard) del Empleador
- **Ruta:** `/dashboard`
- **Acceso:** Autenticado como `empleador`

![Dashboard del Empleador](screenshots/16_empleador_dashboard.png)

#### Componentes Principales:
- **Métricas Operativas:** Cantidad de ofertas propias publicadas, ofertas en estado pendiente de moderación, ofertas aprobadas activas y total de postulaciones recibidas.
- **Botonera de Acción Rápida:** Botones para `+ Nueva oferta`, `Mis ofertas`, `Postulantes` y `Solicitar publicidad`.
- **Bandeja de Postulaciones Recientes:** Tabla con los últimos candidatos inscritos en sus ofertas.

---

### 4.2. Vista 17: Mis Ofertas Laborales Publicadas
- **Ruta:** `/empleador/ofertas`
- **Acceso:** Autenticado como `empleador`

![Mis Ofertas](screenshots/17_empleador_mis_ofertas.png)

#### Componentes Principales:
- **Gestión de Vacantes:** Listado de todas las ofertas creadas por la empresa.
- **Estados de Publicación:**
  - `Pendiente`: En cola de revisión por el Administrador General (RF06).
  - `Aprobado`: Publicada en el portal y visible para la comunidad.
  - `Rechazado`: No cumple las pautas de moderación (con motivo detallado).
- **Acciones Disponibles:** Botón de edición de contenido, visualización y eliminación de oferta.

---

### 4.3. Vista 18: Formulario para Crear Nueva Oferta
- **Ruta:** `/empleador/ofertas/crear`
- **Acceso:** Autenticado como `empleador` (Requiere correo verificado)

![Crear Oferta Laboral](screenshots/18_empleador_crear_oferta.png)

#### Campos del Formulario:
- **Título de la Vacante:** Puesto requerido (ej. Albañil, Administrador, Conductor).
- **Descripción del Trabajo:** Funciones detalladas del puesto.
- **Requisitos:** Años de experiencia, formación académica o habilidades técnicas.
- **Lugar / Distrito:** Selección del distrito en Parinacochas donde se desarrollará la labor.
- **Tipo de Trabajo:** Jornada completa, medio tiempo, temporal o por horas.
- **Salario Ofrecido:** Monto en Soles (S/.) o indicación de convenir.
- **Fecha Límite:** Fecha de cierre de la convocatoria.

---

### 4.4. Vista 19: Formulario para Editar Oferta Laboral
- **Ruta:** `/empleador/ofertas/{id}/editar`
- **Acceso:** Autenticado como `empleador` (Propietario de la oferta)

![Editar Oferta Laboral](screenshots/19_empleador_editar_oferta.png)

#### Funcionalidades:
- Carga los datos actuales de la vacante para su modificación.
- Permite corregir salarios, plazos de vigencia o términos del puesto.

---

### 4.5. Vista 20: Gestión de Postulantes a mis Ofertas
- **Ruta:** `/empleador/postulantes`
- **Acceso:** Autenticado como `empleador`

![Gestión de Postulantes](screenshots/20_empleador_postulantes.png)

#### Funcionalidades:
- **Ficha del Postulante:** Muestra nombre, carrera/profesión, correo electrónico y teléfono de contacto.
- **Oferta de Referencia:** Identifica a qué puesto postuló el candidato.
- **Gestión de Estado Dinámico:** Menú desplegable para calificar al postulante:
  - `Pendiente`
  - `En revisión`
  - `Aceptado / Seleccionado`
  - `Rechazado`
- **Botón Guardar:** Actualiza el estado de la candidatura y lo notifica al postulante en su panel.

---

### 4.6. Vista 21: Gestión de Publicidades y Anuncios
- **Ruta:** `/empleador/publicidades`
- **Acceso:** Autenticado como `empleador`

![Publicidades del Empleador](screenshots/21_empleador_publicidades.png)

#### Funcionalidades:
- Monitoreo de banners publicitarios y promociones contratadas por la empresa para promocionar sus productos o servicios locales.
- Visualización de fechas de inicio, fechas de vencimiento y estado de aprobación del Administrador.

---

### 4.7. Vista 22: Solicitud de Nueva Publicidad
- **Ruta:** `/empleador/publicidades/crear`
- **Acceso:** Autenticado como `empleador`

![Crear Solicitud de Publicidad](screenshots/22_empleador_crear_publicidad.png)

#### Campos del Formulario:
- **Título de la Campaña:** Nombre de la promoción o aviso comercial.
- **Descripción:** Texto explicativo del anuncio.
- **Carga de Imagen (Banner):** Archivo PNG, JPG o WEBP con almacenamiento en el sistema local.
- **Vigencia:** Selección de fecha inicial y fecha final de la pauta publicitaria.

---

### 4.8. Vista 23: Perfil Corporativo de la Empresa
- **Ruta:** `/perfil`
- **Acceso:** Autenticado como `empleador`

![Perfil Corporativo](screenshots/23_empleador_perfil.png)

#### Funcionalidades:
- Visualización de la razón social, representante legal, teléfono, distrito y reseña descriptiva de la empresa.

---

## 5. Módulo del Administrador General

### 5.1. Vista 24: Panel de Control (Dashboard) del Administrador
- **Ruta:** `/dashboard`
- **Acceso:** Autenticado como `admin`

![Dashboard del Administrador](screenshots/24_admin_dashboard.png)

#### Indicadores Clave del Sistema:
- **Métricas Globales (KPIs):** Cantidad total de usuarios registrados, total de ofertas laborales en el sistema, postulaciones globales, oportunidades publicadas, anuncios publicitarios y ofertas pendientes de moderación.
- **Distribución de Usuarios por Rol:** Conteo en tiempo real de administradores, empleadores y postulantes.
- **Resumen de Estados:** Desglose de ofertas pendientes, aprobadas y rechazadas.
- **Bandeja de Moderación Rápida:** Listado de últimas solicitudes con botones de acceso directo.

---

### 5.2. Vista 25: Gestión General de Usuarios
- **Ruta:** `/admin/usuarios`
- **Acceso:** Autenticado como `admin`

![Gestión de Usuarios](screenshots/25_admin_usuarios.png)

#### Funcionalidades:
- **Padrón de Usuarios:** Listado con nombre, correo, rol asignado, distrito de procedencia y estado de verificación de correo.
- **Filtrado y Búsqueda:** Filtros por rol (Administrador, Empleador, Postulante) y búsqueda por nombre o correo.
- **Acciones:** Botones para editar datos del usuario o eliminar cuentas no deseadas.

---

### 5.3. Vista 26: Edición Administrativa de Usuario y Roles
- **Ruta:** `/admin/usuarios/{id}/editar`
- **Acceso:** Autenticado como `admin`

![Editar Usuario](screenshots/26_admin_editar_usuario.png)

#### Funcionalidades:
- Modificación de nombres, apellidos, teléfono y distrito del usuario.
- **Reasignación de Roles:** Capacidad de promover o cambiar el rol de una cuenta entre Administrador, Empleador y Postulante según los permisos requeridos.

---

### 5.4. Vista 27: Moderación y Aprobación de Ofertas Laborales
- **Ruta:** `/admin/ofertas`
- **Acceso:** Autenticado como `admin`

![Moderación de Ofertas](screenshots/27_admin_moderacion_ofertas.png)

#### Funcionalidades (RF06):
- **Cola de Aprobación:** Visualización de todas las vacantes remitidas por empleadores.
- **Acciones Inmediatas:**
  - Botón **Aprobar**: La oferta pasa a estado `Aprobado` y se publica inmediatamente en el portal público.
  - Botón **Rechazar**: La oferta es marcada como `Rechazada` impidiendo su publicación.
  - Botón **Editar**: Permite al administrador corregir textos o erratas antes de aprobar.

---

### 5.5. Vista 28: Edición Administrativa de Oferta Laboral
- **Ruta:** `/admin/ofertas/{id}/editar`
- **Acceso:** Autenticado como `admin`

![Edición de Oferta por Admin](screenshots/28_admin_editar_oferta.png)

#### Funcionalidades:
- Permite a la administración ajustar salarios, corregir ortografía, precisar el distrito o modificar la fecha de vigencia de cualquier oferta de empleo.

---

### 5.6. Vista 29: Gestión de Oportunidades y Cursos Locales
- **Ruta:** `/admin/oportunidades`
- **Acceso:** Autenticado como `admin`

![Gestión de Oportunidades](screenshots/29_admin_oportunidades.png)

#### Funcionalidades (RF14):
- Catálogo administrativo de programas de formación, becas de estudio y ferias laborales.
- Tabla con título, entidad responsable, tipo de programa, fecha programada y estado (`Publicado` / `Borrador`).
- Botón para `+ Nueva oportunidad`.

---

### 5.7. Vista 30: Formulario para Crear Oportunidad Formativa
- **Ruta:** `/admin/oportunidades/create`
- **Acceso:** Autenticado como `admin`

![Crear Oportunidad](screenshots/30_admin_crear_oportunidad.png)

#### Campos del Formulario:
- **Título:** Nombre de la beca, curso o taller.
- **Tipo de Oportunidad:** Beca, Curso, Taller, Convocatoria o Feria de Empleo.
- **Descripción:** Objetivos del programa y requisitos.
- **Institución Promotora:** Nombre del organismo organizador.
- **Lugar:** Distrito o sede donde se dictará.
- **Fecha:** Fecha del evento o límite de aplicación.
- **Enlace Externo:** URL oficial para inscripciones.
- **Imagen Representativa:** Carga de afiche o imagen temática.

---

### 5.8. Vista 31: Edición de Oportunidad Formativa
- **Ruta:** `/admin/oportunidades/{id}/edit`
- **Acceso:** Autenticado como `admin`

![Editar Oportunidad](screenshots/31_admin_editar_oportunidad.png)

#### Funcionalidades:
- Permite actualizar los datos, cambiar la fecha o reemplazar la imagen de una oportunidad ya existente.

---

### 5.9. Vista 32: Moderación de Banners Publicitarios
- **Ruta:** `/admin/publicidades`
- **Acceso:** Autenticado como `admin`

![Moderación de Publicidad](screenshots/32_admin_publicidades.png)

#### Funcionalidades (RF17):
- **Supervisión de Anuncios:** Control de la publicidad contratada por empresas locales.
- **Previsualización del Banner:** Muestra la imagen cargada y el texto promocional.
- **Decisión Administrativa:** Botones para **Aprobar** (inicia la visualización en la portada pública), **Rechazar** o **Finalizar** la pauta publicitaria.

---

## 6. Matriz de Requerimientos Funcionales (RF) vs Vistas

| Requerimiento | Descripción | Vistas Asociadas |
| :--- | :--- | :--- |
| **RF01** | Registro de nuevos usuarios con rol Empleador o Postulante | Vista 07 (`/register`) |
| **RF02** | Autenticación y recuperación segura de contraseña | Vista 06 (`/login`), Vista 08 (`/forgot-password`), Vista 09 (`/reset-password`) |
| **RF03** | Gestión de perfil de usuario y cambio de contraseña | Vista 12/13/14 (`/perfil`, `/perfil/editar`, `/perfil/password`), Vista 23 |
| **RF04** | Creación y publicación de ofertas laborales por empleadores | Vista 18 (`/empleador/ofertas/crear`) |
| **RF05** | Edición y gestión de ofertas propias por el empleador | Vista 17 (`/empleador/ofertas`), Vista 19 (`/empleador/ofertas/{id}/editar`) |
| **RF06** | Moderación y aprobación obligatoria de ofertas por el Administrador | Vista 27 (`/admin/ofertas`), Vista 28 (`/admin/ofertas/{id}/editar`) |
| **RF07** | Búsqueda y filtrado multicriterio de ofertas de empleo | Vista 01 (`/`), Vista 02 (`/ofertas`) |
| **RF08** | Visualización detallada de la ficha técnica de la oferta laboral | Vista 03 (`/ofertas/{id}`) |
| **RF09** | Postulación directa de usuarios a ofertas vigentes con mensaje | Vista 15 (`/ofertas/{id}`) |
| **RF10** | Seguimiento y consulta de historial de postulaciones del usuario | Vista 10 (`/dashboard`), Vista 11 (`/mis-postulaciones`) |
| **RF11** | Notificación y actualización de estado de la postulación | Vista 11 (`/mis-postulaciones`), Vista 20 (`/empleador/postulantes`) |
| **RF12** | Gestión y calificación de postulantes recibidos por el empleador | Vista 20 (`/empleador/postulantes`) |
| **RF13** | Panel de administración y auditoría de usuarios registrados | Vista 25 (`/admin/usuarios`), Vista 26 (`/admin/usuarios/{id}/editar`) |
| **RF14** | Gestión de oportunidades de desarrollo (becas y capacitaciones) | Vista 29/30/31 (`/admin/oportunidades`) |
| **RF15** | Directorio público de becas y capacitaciones | Vista 04 (`/oportunidades`), Vista 05 (`/oportunidades/{id}`) |
| **RF16** | Solicitud y registro de anuncios publicitarios por empresas | Vista 21 (`/empleador/publicidades`), Vista 22 (`/empleador/publicidades/crear`) |
| **RF17** | Moderación y control de pauta publicitaria por el Administrador | Vista 32 (`/admin/publicidades`) |
| **RF18** | Actualización reactiva mediante WebSockets | Integrado en los paneles de Empleador, Postulante y Admin |
| **RF19** | Tablero de control y métricas del sistema | Vista 10 (Postulante), Vista 16 (Empleador), Vista 24 (Admin) |
| **RF20** | Control de acceso basado en roles (RBAC) y verificación de correo | Aplicado en todas las rutas autenticadas y middleware del sistema |

---

## 7. Preguntas Frecuentes y Solución de Problemas

### 7.1. ¿Por qué mi oferta publicada no aparece inmediatamente en el catálogo?
De acuerdo con la regla de negocio **RF06**, toda oferta creada por un empleador entra en estado `Pendiente`. Un Administrador General debe revisarla y aprobarla desde el panel de moderación (`/admin/ofertas`) antes de que sea visible al público.

### 7.2. ¿Cómo postulo a una vacante si soy visitante?
Para postularte debes registrarte como **Postulante** o iniciar sesión con una cuenta activa. El sistema exige correo verificado antes de remitir tus datos personales y teléfono al empleador (RF09).

### 7.3. ¿Cómo evalúa el empleador a los candidatos?
El empleador ingresa a su sección *"Postulantes"* (`/empleador/postulantes`), donde visualiza la lista completa de aspirantes con su profesión y datos de contacto. Mediante el selector interactivo puede cambiar el estado a *"En revisión"*, *"Aceptado"* o *"Rechazado"*.

---
*Manual generado automáticamente para el proyecto **Emplea Local** (Parinacochas).*
