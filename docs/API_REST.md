# API REST de Emplea Local

Documentación de los endpoints implementados sobre el proyecto Laravel 12 existente.
No se creó una tabla `posts`: las publicaciones se construyen a partir de los modelos
reales del sistema (`Oferta`, `Oportunidad`, `Postulacion`, `User`).

- **URL base local:** `http://localhost:8000/api`
- **URL base con Docker:** `http://localhost:8000/api`
- **Formato:** JSON (`Content-Type: application/json` y `Accept: application/json`)
- **Autenticación:** tokens personales de **Laravel Sanctum**
- **Colección Postman:** `docs/postman/Emplea_Local_API.postman_collection.json`

---

## 1. Formato de respuesta

Todas las respuestas usan el mismo sobre:

```json
{
  "success": true,
  "message": "Empleos obtenidos correctamente.",
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 9,
    "last_page": 1,
    "total": 0,
    "from": null,
    "to": null
  }
}
```

En errores:

```json
{
  "success": false,
  "message": "Los datos enviados no son válidos.",
  "errors": {
    "titulo": ["El campo titulo es obligatorio."]
  }
}
```

### Códigos HTTP utilizados

| Código | Significado |
|--------|-------------|
| 200 | Operación correcta |
| 201 | Recurso creado |
| 400 | Solicitud incorrecta |
| 401 | No autenticado o token revocado |
| 403 | Autenticado sin permisos (rol o propiedad) |
| 404 | Recurso inexistente o publicación no visible |
| 422 | Error de validación o regla de negocio (duplicados, oferta no aprobada) |
| 500 | Error interno del servidor |

---

## 2. Autenticación

### 2.1. Registro

`POST /api/auth/register` — público

| Campo | Tipo | Obligatorio | Descripción |
|-------|------|-------------|-------------|
| name | string (máx. 255) | Sí | Nombre completo |
| email | email único | Sí | Correo de la cuenta |
| password | string (mín. 8) | Sí | Contraseña |
| password_confirmation | string | Sí | Debe coincidir con `password` |
| role | `empleador` \| `postulante` | Sí | El rol `admin` no se puede registrar |
| telefono | string (máx. 20) | No | Teléfono de contacto |
| distrito | string (máx. 150) | No | Distrito |
| profesion | string (máx. 150) | No | Profesión |
| empresa | string (máx. 150) | No | Empresa (empleador) |

Respuesta `201`:

```json
{
  "success": true,
  "message": "Cuenta creada correctamente. Revisa tu correo para verificarla.",
  "data": {
    "user": { "id": 7, "name": "Ana Torres", "email": "ana@example.com", "role": "postulante", "email_verified": false },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

### 2.2. Login

`POST /api/auth/login` — público

```json
{ "email": "empleador@emplealocal.test", "password": "password123", "device_name": "postman" }
```

Respuesta `200`: igual a la del registro. Guarda `data.token`.

Las cuentas creadas por los seeders ya están verificadas. Un usuario registrado
por la API debe verificar su correo desde el enlace enviado antes de publicar
ofertas o postularse (igual que en la interfaz web).

### 2.3. Uso del token

```
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Accept: application/json
```

### 2.4. Perfil y cierre de sesión

| Método | URL | Auth | Descripción |
|--------|-----|------|-------------|
| GET | `/api/auth/me` | Token | Perfil del usuario autenticado |
| GET | `/api/user` | Token | Ruta conservada de Sanctum (mismo perfil) |
| POST | `/api/auth/logout` | Token | Revoca el token usado en la petición |

`logout` responde `200` y a partir de ese momento el token devuelve `401`.

---

## 3. Empleos (ofertas)

Los listados públicos **sólo devuelven ofertas con estado `aprobado`**. Las
ofertas `pendiente` o `rechazado` nunca aparecen en los endpoints públicos.

| Método | URL | Auth | Descripción |
|--------|-----|------|-------------|
| GET | `/api/ofertas` | Público | Listar empleos aprobados (paginado) |
| GET | `/api/ofertas/recientes` | Público | Últimos empleos aprobados (`limite`, por defecto 10) |
| GET | `/api/ofertas/{oferta}` | Público | Detalle por ID (dueño/admin ven cualquier estado) |
| GET | `/api/empleos` | Público | Alias de `/api/ofertas` |
| GET | `/api/empleos/recientes` | Público | Alias de `/api/ofertas/recientes` |
| GET | `/api/empleos/{oferta}` | Público | Alias de `/api/ofertas/{oferta}` |
| POST | `/api/ofertas` | Empleador verificado | Crear oferta (queda `pendiente`) |
| PUT/PATCH | `/api/ofertas/{oferta}` | Dueño o admin | Actualizar (el empleador vuelve a `pendiente`) |
| DELETE | `/api/ofertas/{oferta}` | Dueño o admin | Eliminar oferta |
| PATCH | `/api/admin/ofertas/{oferta}/estado` | Admin | Aprobar / rechazar / devolver a pendiente |

### Parámetros de consulta de `GET /api/ofertas`

| Parámetro | Ejemplo | Descripción |
|-----------|---------|-------------|
| `q` | `programador` | Busca en título, lugar y tipo de trabajo |
| `titulo` | `desarrollador` | Búsqueda por título |
| `ubicacion` | `Coracora` | Filtro por lugar |
| `categoria` | `Tiempo completo` | Filtro por categoría/modalidad (`tipo_trabajo`) |
| `tipo_trabajo` | `Medio tiempo` | Equivalente a `categoria` |
| `recientes` | `1` | Creadas en los últimos 30 días |
| `orden` | `recientes` \| `antiguos` \| `salario` \| `salario_asc` | Orden |
| `page` | `1` | Página |
| `per_page` | `9` | Tamaño de página (1–50) |

### Cuerpo para crear / actualizar una oferta

```json
{
  "titulo": "Desarrollador web",
  "descripcion": "Desarrollo de aplicaciones con Laravel.",
  "requisitos": "Experiencia de un año en PHP.",
  "lugar": "Coracora",
  "tipo_trabajo": "Tiempo completo",
  "salario": 2500,
  "fecha_limite": "2026-12-31"
}
```

### Moderación (administrador)

`PATCH /api/admin/ofertas/1/estado`

```json
{ "estado": "aprobado" }
```

Estados admitidos: `pendiente`, `aprobado`, `rechazado`.

---

## 4. Postulaciones

| Método | URL | Auth | Descripción |
|--------|-----|------|-------------|
| POST | `/api/ofertas/{oferta}/postulaciones` | Postulante verificado | Postularse a una oferta aprobada |
| GET | `/api/postulaciones` | Cualquier rol | Postulante: las propias. Empleador: recibidas en sus ofertas. Admin: todas |
| GET | `/api/postulaciones/{postulacion}` | Autor, empleador dueño o admin | Detalle |
| GET | `/api/ofertas/{oferta}/postulantes` | Empleador dueño o admin | Postulantes de una oferta |
| PATCH/PUT | `/api/postulaciones/{postulacion}/estado` | Empleador dueño o admin | Cambiar estado |

Cuerpo de la postulación:

```json
{ "mensaje": "Tengo experiencia en el puesto." }
```

- Estado inicial: `pendiente`.
- Sólo se puede postular a ofertas `aprobado`.
- Una segunda postulación a la misma oferta responde `422` con
  `errors.oferta_id`; además existe el índice `UNIQUE(oferta_id, user_id)` en la
  base de datos como segunda barrera.
- Estados al evaluar: `pendiente`, `revisada`, `aceptada`, `rechazada`.

---

## 5. Oportunidades (becas, convocatorias, exámenes, prácticas, cursos, eventos)

| Método | URL | Auth | Descripción |
|--------|-----|------|-------------|
| GET | `/api/oportunidades` | Público | Listar publicadas |
| GET | `/api/oportunidades/recientes` | Público | Últimas publicadas (`limite`) |
| GET | `/api/oportunidades/{oportunidad}` | Público | Detalle (borradores sólo admin) |
| POST | `/api/oportunidades` | Admin | Crear |
| PUT/PATCH | `/api/oportunidades/{oportunidad}` | Admin | Actualizar |
| DELETE | `/api/oportunidades/{oportunidad}` | Admin | Eliminar |

Filtros: `q`, `tipo` (o `categoria`), `recientes`, `orden`
(`recientes` \| `antiguos` \| `fecha` \| `fecha_desc`), `page`, `per_page`.

Tipos válidos: `beca`, `charla`, `capacitacion`, `curso`, `evento`, `otra`,
`convocatoria`, `examen`, `practica`.

```json
{
  "titulo": "Beca de estudios técnicos",
  "tipo": "beca",
  "descripcion": "Beca completa para estudiar en Coracora.",
  "institucion": "Instituto Parinacochas",
  "fecha": "2026-12-15",
  "lugar": "Coracora",
  "enlace": "https://ejemplo.test/convocatoria",
  "estado": "publicado"
}
```

`imagen` se envía como `multipart/form-data` (JPG, PNG o WebP, máximo 2 MB).

---

## 6. Publicaciones (feed unificado)

Es la API de "publicaciones" adaptada a los modelos reales. No hay tabla
`posts`: combina ofertas aprobadas y oportunidades publicadas con el mismo
formato que se transmite por Laravel Reverb, sin datos personales.

| Método | URL | Auth | Descripción |
|--------|-----|------|-------------|
| GET | `/api/publicaciones` | Público | Feed combinado (paginado) |
| GET | `/api/publicaciones/{tipo}/{id}` | Público | Detalle: `tipo` = `oferta` \| `oportunidad` |
| POST | `/api/publicaciones` | Empleador (oferta) o admin (oportunidad) | Crear |
| PUT/PATCH | `/api/publicaciones/{tipo}/{id}` | Dueño/admin (oferta) o admin (oportunidad) | Actualizar |
| DELETE | `/api/publicaciones/{tipo}/{id}` | Dueño/admin (oferta) o admin (oportunidad) | Eliminar |

Filtros de `GET /api/publicaciones`: `tipo_publicacion` (`oferta` \|
`oportunidad`), `categoria` (`empleo`, `beca`, `convocatoria`, ...), `q`,
`page`, `per_page`.

Para crear una oferta:

```json
{
  "tipo_publicacion": "oferta",
  "titulo": "Desarrollador web",
  "descripcion": "Desarrollo de aplicaciones.",
  "requisitos": "Experiencia en PHP.",
  "lugar": "Coracora",
  "tipo_trabajo": "Tiempo completo",
  "salario": 2500,
  "fecha_limite": "2026-12-31"
}
```

Para crear una oportunidad (sólo admin), incluye además de `tipo_publicacion:
"oportunidad"` los campos `tipo`, `descripcion`, `institucion`, `fecha`, etc.

---

## 7. Permisos por rol

| Rol | Puede |
|-----|-------|
| Visitante | Consultar ofertas aprobadas, oportunidades publicadas y el feed |
| Postulante | Consultar sus postulaciones, postularse a ofertas aprobadas, ver/editar su perfil |
| Empleador | Crear/editar/eliminar sus propias ofertas, ver y evaluar postulantes de sus ofertas |
| Administrador | Moderar ofertas, gestionar oportunidades, consultar todas las postulaciones |

La autorización se comprueba en dos niveles: middleware `api.role` para el rol y
verificación de propiedad dentro de cada controlador (`user_id`). Nunca se
confía en un rol o identificador enviado por el cliente.

---

## 8. Ejemplos con curl

```bash
# Login y obtención del token
curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"empleador@emplealocal.test","password":"password123"}'

# Listado público de empleos
curl -s "http://localhost:8000/api/ofertas?titulo=desarrollador&ubicacion=Coracora" \
  -H "Accept: application/json"

# Crear una oferta (token de empleador)
curl -s -X POST http://localhost:8000/api/ofertas \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"titulo":"Desarrollador web","descripcion":"Desarrollo con Laravel.","requisitos":"PHP","lugar":"Coracora","tipo_trabajo":"Tiempo completo","salario":2500,"fecha_limite":"2026-12-31"}'

# Postularse (token de postulante)
curl -s -X POST http://localhost:8000/api/ofertas/1/postulaciones \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"mensaje":"Me interesa el puesto."}'
```

---

## 9. Pruebas automatizadas

```bash
php artisan test --filter=Api
```

Archivos:

- `tests/Feature/Api/ApiAuthenticationTest.php`
- `tests/Feature/Api/ApiOfertaTest.php`
- `tests/Feature/Api/ApiPostulacionTest.php`
- `tests/Feature/Api/ApiOportunidadTest.php`
- `tests/Feature/Api/ApiPublicacionTest.php`
- `tests/Feature/Api/ApiTestCase.php` (utilidades comunes)

Cubren autenticación con tokens reales, revocación, roles, propiedad de los
recursos, validaciones, moderación, postulaciones duplicadas y la exclusión de
publicaciones no aprobadas.

---

## 10. Probar con Postman

1. Importa `docs/postman/Emplea_Local_API.postman_collection.json`.
2. Verifica la variable `base_url` (`http://localhost:8000`).
3. Ejecuta **Autenticación → Login empleador** (o postulante/admin). El script de
   la petición guarda automáticamente `data.token` en la variable `token`.
4. Ejecuta cualquier petición protegida: la colección envía
   `Authorization: Bearer {{token}}`.
5. Para el flujo completo de moderación: crea una oferta con el empleador,
   aprueba con el admin (`PATCH /api/admin/ofertas/{id}/estado`) y vuelve a
   consultar `/api/ofertas` como visitante.
