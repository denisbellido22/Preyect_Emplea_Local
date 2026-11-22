# INFORME FINAL — PROYECTO EMPLEA LOCAL

**Entrega:** API REST, Docker y publicación en GitHub
**Repositorio:** https://github.com/denisbellido22/Preyect_Emplea_Local.git
**Rama:** `main`
**Commit de la entrega:** `1ca9a9b35d6a4ebf198cd2e1e950fa8fc1e6df6c`
**Fecha:** 22 de noviembre de 2026

---

## 1. Resumen

Se completó el proyecto **Emplea Local** existente, sin crear otro proyecto ni
reemplazar la base de datos:

1. **API REST funcional** con Laravel Sanctum, CRUD de ofertas y oportunidades,
   postulaciones, permisos por rol y propiedad, validaciones, respuestas JSON
   uniformes, documentación y colección Postman.
2. **Dockerización completa** con Nginx, PHP-FPM, Laravel Reverb y worker de
   colas; SQLite persistente y compilación real de assets con Vite.
3. **Publicación en GitHub** del código verificado, sin `.env`, sin base de datos
   con datos de usuarios y sin dependencias locales.

La interfaz web, el diseño y las funcionalidades existentes se conservaron.
La API se adaptó a los modelos/tablas reales (`ofertas`, `oportunidades`,
`postulaciones`, `users`); **no se creó ninguna tabla `posts`**.

---

## 2. Entorno y versiones

| Componente | Versión |
|------------|---------|
| Laravel | 12.69.3 |
| PHP local | 8.5.12 |
| PHP en Docker | 8.3 (compatible con `composer.json`: `^8.2`) |
| Node.js (build Docker) | 22 Alpine |
| Base de datos | SQLite (`database/database.sqlite`) |
| Laravel Sanctum | 4.3 |
| Laravel Reverb | 1.14 |
| Laravel Echo / Pusher JS | 2.6 / 8.6 |
| Docker Compose | v5.7 (Docker 29.9) |

---

## 3. Fase 1 — API REST

### 3.1. Qué se implementó

| Archivo | Función |
|---------|---------|
| `app/Http/Controllers/Api/AuthController.php` | Registro, login, perfil y logout con tokens |
| `app/Http/Controllers/Api/OfertaController.php` | API de empleos, filtros, CRUD y moderación |
| `app/Http/Controllers/Api/PostulacionController.php` | Postulaciones, duplicados y evaluación |
| `app/Http/Controllers/Api/OportunidadController.php` | Becas, convocatorias, exámenes, etc. |
| `app/Http/Controllers/PostController.php` | Feed unificado de publicaciones (antes vacío) |
| `app/Http/Requests/Api/*` | Validaciones con Form Requests |
| `app/Http/Resources/*` | Serialización JSON sin datos sensibles |
| `app/Support/ApiResponse.php` | Formato uniforme de respuesta |
| `app/Http/Middleware/ApiRoleMiddleware.php` | Roles en API con 401/403 JSON |
| `bootstrap/app.php` | Renderizado JSON de errores en `/api/*` |
| `routes/api.php` | 34 rutas organizadas por recurso |

No se duplicaron modelos ni controladores web. Los endpoints reutilizan
`Oferta`, `Oportunidad`, `Postulacion` y `User`, sus relaciones y sus reglas de
negocio (aprobación de ofertas, verificación de correo, propiedad del recurso).

### 3.2. Endpoints

**Públicos (sin token)**

| Método | URL | Descripción |
|--------|-----|-------------|
| GET | `/api/ofertas` | Empleos aprobados; filtros `q`, `titulo`, `ubicacion`, `categoria`, `recientes`, `orden`, `page`, `per_page` |
| GET | `/api/ofertas/recientes` | Últimos empleos aprobados (`limite`) |
| GET | `/api/ofertas/{id}` | Detalle; pendientes sólo dueño/admin |
| GET | `/api/empleos` | Alias de `/api/ofertas` |
| GET | `/api/oportunidades` | Oportunidades publicadas; filtros `tipo`/`categoria`, `q` |
| GET | `/api/oportunidades/recientes` | Últimas publicadas |
| GET | `/api/oportunidades/{id}` | Detalle; borradores sólo admin |
| GET | `/api/publicaciones` | Feed combinado con el mismo payload del WebSocket |
| GET | `/api/publicaciones/{tipo}/{id}` | Detalle (`oferta` \| `oportunidad`) |
| POST | `/api/auth/register` | Registro de empleador/postulante + token |
| POST | `/api/auth/login` | Login + token |

**Protegidos (`Authorization: Bearer <token>`)**

| Método | URL | Rol / permiso |
|--------|-----|----------------|
| GET | `/api/auth/me` | Cualquier autenticado |
| POST | `/api/auth/logout` | Revoca el token usado |
| GET | `/api/user` | Ruta original de Sanctum (conservada) |
| POST | `/api/ofertas` | Empleador con correo verificado |
| PUT/PATCH | `/api/ofertas/{id}` | Dueño o admin; al editar vuelve a `pendiente` |
| DELETE | `/api/ofertas/{id}` | Dueño o admin |
| PATCH | `/api/admin/ofertas/{id}/estado` | Admin (`pendiente`/`aprobado`/`rechazado`) |
| POST | `/api/ofertas/{id}/postulaciones` | Postulante verificado |
| GET | `/api/postulaciones` | Postulante: propias; empleador: recibidas; admin: todas |
| GET | `/api/postulaciones/{id}` | Autor, dueño de la oferta o admin |
| GET | `/api/ofertas/{id}/postulantes` | Dueño de la oferta o admin |
| PATCH/PUT | `/api/postulaciones/{id}/estado` | Dueño de la oferta o admin |
| POST | `/api/oportunidades` | Admin |
| PUT/PATCH | `/api/oportunidades/{id}` | Admin |
| DELETE | `/api/oportunidades/{id}` | Admin |
| POST | `/api/publicaciones` | Empleador (oferta) o admin (oportunidad) |
| PUT/PATCH | `/api/publicaciones/{tipo}/{id}` | Dueño/admin de la oferta o admin |
| DELETE | `/api/publicaciones/{tipo}/{id}` | Dueño/admin de la oferta o admin |

### 3.3. Autenticación

- **Laravel Sanctum** ya estaba instalado en `composer.json`; se publicó su
  migración `personal_access_tokens` (aditiva, sin borrar datos) y se agregó
  `HasApiTokens` al modelo `User`.
- Login: `POST /api/auth/login` devuelve `data.token` (`token_type: Bearer`).
- Cada petición protegida envía `Authorization: Bearer <token>`.
- Logout revoca **sólo** el token de la petición (`currentAccessToken()->delete()`).
- El registro sólo permite roles `empleador` y `postulante`; el rol `admin` se
  crea por seeder, igual que en la web.
- Los tokens nunca se guardan en texto plano en la base: Sanctum almacena el
  hash SHA-256.

Ejemplo real:

```bash
curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"email":"empleador@emplealocal.test","password":"password123"}'
```

### 3.4. Protección de rutas y permisos

Se aplican **dos capas**:

1. Middleware `api.role`: devuelve **401** sin token y **403** si el rol no
   corresponde (nunca redirige al login, a diferencia del middleware web).
2. Verificación de propiedad dentro del controlador (`user_id` de la oferta o
   de la postulación). Un empleador no puede modificar ofertas ajenas ni evaluar
   postulaciones de otro empleador. Un postulante sólo ve sus postulaciones.

Reglas adicionales conservadas de la web:

- Publicar ofertas y postularse exige **correo verificado**.
- Sólo se postula a ofertas **aprobadas**.
- Toda oferta nueva o editada por un empleador vuelve a **pendiente**.
- Los endpoints públicos sólo muestran ofertas `aprobado` y oportunidades
  `publicado`.

### 3.5. Respuestas y validaciones

Formato uniforme:

```json
{ "success": true, "message": "...", "data": {}, "meta": {} }
{ "success": false, "message": "...", "errors": { "campo": ["..."] } }
```

Códigos usados y verificados en las pruebas: **200, 201, 401, 403, 404 y 422**.
Los códigos **400** (solicitud malformada) y **500** (error interno) los produce
el framework y también se serializan como JSON. Todos los errores de `/api/*` se
renderizan siempre como JSON, aunque el cliente no envíe
`Accept: application/json`.

Los `Resource` no exponen `password`, `remember_token`, tokens, DNI ni datos
internos. Al empleador sólo se publica `id`, nombre visible y empresa.

### 3.6. Documentación y Postman

- `docs/API_REST.md`: parámetros, cuerpos, códigos, permisos, ejemplos `curl` y
  pruebas.
- `docs/postman/Emplea_Local_API.postman_collection.json`: 5 carpetas y 34
  peticiones. El login guarda automáticamente el token en `{{token}}`.

### 3.7. Pruebas de la API

48 pruebas específicas en `tests/Feature/Api/`:

- Autenticación: login, token real, revocación en logout, 401 sin token,
  registro, rol admin prohibido, correo duplicado.
- Empleos: listado sólo aprobados, búsqueda por título/ubicación/categoría,
  recientes, creación, edición, eliminación, moderación, 403 por rol/propiedad,
  404 de pendientes, validaciones 422 y ausencia de datos personales.
- Postulaciones: alta, duplicados 422 + restricción UNIQUE, oferta no aprobada,
  permisos, listado propio, postulantes del empleador, cambio de estado.
- Oportunidades: publicadas vs. borradores, filtros, CRUD admin, 403 y 422.
- Publicaciones: feed combinado, filtros, detalle, CRUD unificado y permisos.

Resultado: **48/48 pruebas de API correctas** (incluidas en la suite total).

---

## 4. Fase 2 — Docker

### 4.1. Arquitectura

```
                        Navegador
                    ┌───────┴────────┐
              :8000 │                │ :8080
                    ▼                ▼
                 nginx           reverb (WebSocket)
                    │ fastcgi         ▲
                    ▼                 │ HTTP interno :8080
                   app ───────────────┘
                    │
       ./database/database.sqlite (bind mount persistente)
                    ▲
                 queue (worker de colas)
```

| Servicio | Imagen / comando | Función |
|----------|------------------|---------|
| `app` | PHP 8.3-FPM + Laravel | Ejecuta migraciones seguras y atiende PHP |
| `nginx` | misma imagen, `nginx -g 'daemon off;'` | Sirve la app y los assets en `:8000` |
| `reverb` | `php artisan reverb:start --host=0.0.0.0 --port=8080` | WebSockets en `:8080` |
| `queue` | `php artisan queue:work` | Procesa la cola `database` |

**No hay contenedor de base de datos**: el proyecto usa **SQLite**, por lo que
la base es un archivo persistente (`./database:/var/www/html/database`). Esto
evita migrar de motor y conserva los datos. **No hay Redis**: caché, sesiones y
colas usan el driver `database` del proyecto; agregarlo sería un servicio
innecesario.

### 4.2. Comunicación entre contenedores

- Nginx resuelve `app:9000` por la red interna `emplea` (FastCGI).
- Laravel publica eventos a `http://reverb:8080` (nombre interno).
- El navegador se conecta a `ws://localhost:8080` (dirección pública).
- Reverb escucha en `0.0.0.0:8080` dentro de su contenedor.
- `queue` y `reverb` esperan a que `app` esté `healthy`.

### 4.3. Laravel Reverb dentro de Docker

Variables internas (`docker-compose.yml`):

```dotenv
BROADCAST_CONNECTION=reverb
REVERB_HOST=reverb
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
```

Variables públicas compiladas en el bundle por Vite (build args):

```dotenv
VITE_REVERB_APP_KEY=${REVERB_APP_KEY}
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

Se mantuvieron los eventos y observadores existentes:

- `PublicacionPublicada`, `PublicacionActualizada`, `PublicacionRetirada`.
- `OfertaObserver` y `OportunidadObserver`.
- Canal público `publicaciones`.
- Payload filtrado por `App\Support\PublicacionPublica` (sin correos, DNI ni
  postulaciones).

Si Reverb no está disponible, `App\Support\Realtime` registra un aviso y la web
sigue funcionando.

### 4.4. Frontend y Vite

- Etapa `frontend` del Dockerfile: `npm ci` + `npm run build`.
- El `public/build` generado se copia a la imagen final; no se depende de
  `npm run dev` ni de `public/hot`.
- Nginx sirve `/build/assets/...` con caché de 7 días.
- Verificado: `GET /` → 200; bundle JS → 200 (`application/javascript`);
  Bootstrap CSS e iconos (CDN) → 200; CSS inline presente en la página.

### 4.5. Base de datos y persistencia

- Motor conservado: **SQLite**.
- `docker/entrypoint.sh` crea el archivo si no existe, ejecuta
  `php artisan migrate --force` (nunca `migrate:fresh`) y siembra datos demo
  **sólo** si la base es nueva (`SEED_ON_FIRST_RUN=true`).
- El archivo está en un bind mount, por lo que sobrevive a `docker compose down`
  y a la recreación de contenedores.
- Los seeders son idempotentes (`updateOrCreate`), por lo que `db:seed` puede
  ejecutarse sin duplicar.

### 4.6. Configuración y secretos

- `.env` se monta en tiempo de ejecución y **no** se hornea en la imagen
  (`.dockerignore` lo excluye).
- `.env.example` se actualizó con la sección Docker y
  `DOCKER_SEED_ON_FIRST_RUN`.
- Ninguna contraseña, `APP_KEY` ni `REVERB_APP_SECRET` está en `Dockerfile` o
  `docker-compose.yml`.
- Si `.env` no existe, el entrypoint falla con un mensaje claro. Si `APP_KEY`
  está vacía, se genera una en el `.env` local.

### 4.7. Comandos de ejecución

```bash
cp .env.example .env
docker compose up -d --build      # construir e iniciar
docker compose ps                 # estado
docker compose logs -f app        # registros
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan test
docker compose down               # detener (los datos quedan)
docker compose up -d --build      # reconstruir tras cambios
```

### 4.8. Verificaciones reales de Docker

| Verificación | Resultado |
|--------------|-----------|
| Los 4 contenedores inician | `app`, `nginx`, `reverb` healthy; `queue` up |
| Laravel responde | `/up` 200; portada 200 |
| Conexión a la base | Migraciones OK; login y consultas funcionan |
| Inicio de sesión web | `POST /login` → 302 a `/dashboard`; dashboard 200 |
| Inicio de sesión API | `POST /api/auth/login` → 200 con token |
| API responde | `/api/ofertas` 200; crear 201; aprobar 200; duplicado 422; pendiente 404 |
| CSS y JavaScript | Bundle JS 200; Bootstrap CSS/iconos 200; CSS inline |
| Reverb inicia | Healthcheck healthy; “Starting server on 0.0.0.0:8080” |
| Echo se conecta | Chrome headless: estado `connected`, indicador “En vivo” |
| Tarjetas en tiempo real | 8 → 9 tarjetas con una oferta nueva, **sin recargar** |
| Persistencia | Registro visible tras `docker compose down` + `up`; migraciones “Nothing to migrate” |
| Clon nuevo / base nueva | Sin `database.sqlite`: se crea, migra y siembra; login demo 200 |
| Pruebas en Docker | **177 passed (867 assertions)** |
| La suite no toca la BD real | Conteos antes/después idénticos (5/4/0/4/3) |
| Reconstrucción tras cambios | `docker compose up -d --build` OK; assets y servicios healthy |

Prueba de tiempo real en navegador real (Chrome headless + DevTools Protocol):

```json
{
  "echo_estado": "connected",
  "indicador_tiempo_real": "En vivo",
  "tarjetas_antes": 8,
  "tarjetas_despues": 9,
  "tarjeta_insertada": { "titulo": "TARJETA EN TIEMPO REAL ECHO", "visible": true },
  "navegaciones_en_la_pagina": 1
}
```

---

## 5. Fase 3 — Publicación en GitHub

### 5.1. Revisión previa

```bash
git status
git branch        # main
git remote -v     # https://github.com/denisbellido22/Preyect_Emplea_Local.git
git diff
```

### 5.2. Seguridad antes del push

- `.env` **no** se subió (está en `.gitignore` y en `.dockerignore`).
- `vendor`, `node_modules`, `public/build`, logs, cachés y `.backups` no se
  versionan.
- **`database/database.sqlite` se destrackeó** (`git rm --cached`): el archivo
  local se conservó, pero no se publica porque contenía cuentas de correo
  personales. Se actualizó `database/.gitignore` y el proyecto ahora crea la base
  con `php artisan migrate --seed` (o automáticamente con Docker).
- `temp_edge_login_test/` quedó excluido por `.gitignore`.
- `.env.example` actualizado; ninguna credencial real se subió.
- Revisión de `git diff` y de los archivos staged antes del commit.

### 5.3. Commit y push

```bash
git commit -m "feat: completar API REST y dockerizar Emplea Local"
git push origin main
```

Resultado real del push:

```
6c1771a..1ca9a9b  main -> main
```

- Commit: **`1ca9a9b35d6a4ebf198cd2e1e950fa8fc1e6df6c`**
- `git status`: limpio.
- `git ls-remote` / `git rev-parse origin/main`: coincide con `1ca9a9b`.
- No se usó `--force` ni se eliminaron commits anteriores.

> El presente informe se versiona en un commit de documentación posterior sobre
> la misma rama `main`.

---

## 6. Incidente detectado y corregido (transparencia)

Durante la **primera** ejecución de la suite dentro del contenedor, las
variables de entorno del contenedor (`APP_ENV`, `DB_DATABASE`) prevalecieron
sobre `phpunit.xml`. Las pruebas usaron la base real y `RefreshDatabase` la
vaciaron. Se detectó de inmediato al consultar un registro (404) y se actuó así:

1. Se restauró `database/database.sqlite` desde la última versión versionada
   (`git show HEAD:database/database.sqlite`) y se ejecutó `migrate` + `db:seed`.
2. Se agregó `tests/bootstrap.php`, que fija `DB_DATABASE=:memory:` y el resto
   del entorno de pruebas **antes** de cargar Composer y Laravel.
3. Se cambiaron `phpunit.xml` y el bootstrap para que la suite nunca pueda tocar
   la base real, incluso dentro de Docker.
4. Se reconstruyó la imagen y se repitieron las 177 pruebas: **todas pasan** y
   los conteos de la base real quedaron idénticos antes y después.

**Datos no recuperables:** la copia de trabajo previa al incidente tenía 1
usuario, 1 oferta, 2 postulaciones y 1 oportunidad más que la última versión
versionada. Eran datos de demostración/prueba, no cuentas del sistema
productivo. La base actual conserva 5 usuarios (3 cuentas demo del seeder y 2
cuentas demo con correo Gmail), 4 ofertas, 4 oportunidades y 3 publicidades.
Por privacidad, esa base ya no se versiona.

No se ejecutó `migrate:fresh` en ningún arranque de Docker.

---

## 7. Cómo ejecutar el proyecto desde cero

### Docker (recomendado)

```bash
git clone https://github.com/denisbellido22/Preyect_Emplea_Local.git
cd Preyect_Emplea_Local
cp .env.example .env
docker compose up -d --build
```

- App: http://localhost:8000
- WebSocket: ws://localhost:8080
- La primera vez se crea y siembra la base automáticamente.
- Si el `.env` no tiene `APP_KEY`, el contenedor la genera.

### Local

```bash
composer install
cp .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
npm install && npm run build
composer run dev
```

### Credenciales de demostración

| Rol | Correo | Contraseña |
|-----|--------|------------|
| Administrador | `admin@emplealocal.test` | `password123` |
| Empleador | `empleador@emplealocal.test` | `password123` |
| Postulante | `postulante@emplealocal.test` | `password123` |

---

## 8. Cómo probar la API con Postman

1. Importar `docs/postman/Emplea_Local_API.postman_collection.json`.
2. Verificar `base_url = http://localhost:8000`.
3. Ejecutar **Autenticación → Login empleador** (el script guarda `{{token}}`).
4. Probar:
   - `Empleos → Crear oferta` (201, estado `pendiente`).
   - `Empleos → Aprobar oferta (admin)` tras iniciar sesión como admin.
   - `Empleos → Listar empleos` como visitante (la oferta aparece recién
     aprobada).
   - `Postulaciones → Postularse` con el token del postulante y repetir para
     comprobar el **422 por duplicado**.
   - `Publicaciones → Feed unificado`.

También se puede demostrar el tiempo real: con la portada abierta, aprobar una
oferta desde Postman y ver la tarjeta aparecer sin recargar.

---

## 9. Archivos creados / modificados

**Nuevos**

- `Dockerfile`, `docker-compose.yml`, `.dockerignore`
- `docker/entrypoint.sh`, `docker/nginx/default.conf`, `docker/php/php.ini`
- `app/Http/Controllers/Api/*` (4 controladores)
- `app/Http/Requests/Api/*` (9 Form Requests)
- `app/Http/Resources/*` (4 Resources)
- `app/Http/Middleware/ApiRoleMiddleware.php`
- `app/Support/ApiResponse.php`
- `database/migrations/2026_11_22_144242_create_personal_access_tokens_table.php`
- `tests/Feature/Api/*` (5 pruebas + base)
- `tests/bootstrap.php`
- `docs/API_REST.md`, `docs/INFORME_FINAL.md`,
  `docs/postman/Emplea_Local_API.postman_collection.json`

**Modificados**

- `routes/api.php`, `bootstrap/app.php`, `app/Models/User.php`
- `app/Http/Controllers/PostController.php`
- `phpunit.xml` (entorno de pruebas forzado)
- `.env.example`, `.gitignore`, `database/.gitignore`, `README.md`
- `package.json` / `package-lock.json` (dependencias de Echo/manual ya presentes
  en la copia de trabajo)
- `docs/` y `scripts/` (manual de usuario y generadores, previos en la copia de
  trabajo)

---

## 10. Pruebas ejecutadas y resultados

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter=Api` | 48/48 API correctas |
| `php artisan test` (local) | **177 passed**, 867 assertions |
| `docker compose exec app php artisan test` | **177 passed**, 867 assertions |
| Smoke test API real (curl) | login 200, crear 201, pendiente 404, aprobar 200, duplicado 422, eliminar 200 |
| Login web real (curl + CSRF) | 302 → `/dashboard` 200 |
| WebSocket Reverb | evento `publicacion.publicada` recibido con payload |
| Chrome headless + Echo | conectado, “En vivo”, tarjeta nueva sin recargar |
| Persistencia | registro intacto tras `down` + `up` |
| Base nueva (clon limpio) | creada, migrada y sembrada; login 200 |
| Conteos de BD antes/después de tests | idénticos |

Limitaciones conocidas:

- `APP_ENV=local` y `APP_DEBUG=true` por defecto en Docker para la demostración;
  para producción deben cambiarse a `production`/`false`.
- El puerto público de Reverb y su clave se compilan en el frontend: si cambian,
  hay que reconstruir la imagen.
- No se configuró HTTPS (entorno académico local).
- No se agregó Redis ni MySQL porque la arquitectura existente no los requiere.
- Las dependencias de desarrollo se incluyen en la imagen para poder ejecutar
  `php artisan test` dentro de Docker; una imagen de producción podría usar
  `--no-dev`.

---

## 11. Guion breve de demostración

1. `docker compose up -d --build` y esperar `docker compose ps` (3 healthy).
2. Abrir http://localhost:8000: portada con ofertas, oportunidades, indicador
   “En vivo”.
3. Iniciar sesión como empleador y crear una oferta (queda pendiente).
4. Iniciar sesión como admin y aprobarla.
5. Mostrar la portada: la tarjeta aparece **sin recargar** (Reverb + Echo).
6. En Postman: login, listar empleos, postularse, repetir (422), moderar.
7. `docker compose exec app php artisan test` → 177 pruebas correctas.
