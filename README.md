# EMPLEA LOCAL

**Conectando oportunidades laborales en Parinacochas**

Sistema web desarrollado en **Laravel 12** con **SQLite** y **PHP 8.2+**. Incluye
interfaz web completa, **API REST con autenticación por tokens (Laravel Sanctum)**,
WebSockets en tiempo real con **Laravel Reverb + Laravel Echo** y ejecución
completa mediante **Docker**.

---

## Requisitos

### Ejecución local

- PHP >= 8.2 (extensiones `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`)
- Composer 2
- Node.js 20.19+ (probado con Node 22) y npm

### Ejecución con Docker (recomendada para la demostración)

- Docker Desktop 24+ con Docker Compose v2
- No hace falta instalar PHP, Composer, Node ni SQLite en el equipo

---

## Opción 1 · Ejecución con Docker

### 1. Preparar el entorno

```bash
cp .env.example .env
```

El `APP_KEY` puede dejarse vacío: el contenedor genera uno en el `.env` local la
primera vez. No subas `.env` al repositorio.

### 2. Construir y arrancar

```bash
docker compose up -d --build
```

La primera vez, el entrypoint crea `database/database.sqlite`, ejecuta las
migraciones (nunca `migrate:fresh`) y siembra los datos de demostración si la
base es nueva.

Abrir:

- Aplicación: **http://localhost:8000**
- WebSocket (Reverb): **ws://localhost:8080**

### 3. Servicios

| Servicio | Función | Puerto |
|----------|---------|--------|
| `app` | PHP-FPM 8.3 + Laravel 12; ejecuta migraciones seguras | interno `9000` |
| `nginx` | Servidor web, archivos estáticos y FastCGI a `app:9000` | `8000` |
| `reverb` | Servidor Laravel Reverb (WebSockets) | `8080` |
| `queue` | Worker de colas (`queue:work`, driver `database`) | — |

No hay contenedor de base de datos porque el proyecto usa **SQLite**: el archivo
vive en `./database/database.sqlite` y se conserva con un bind mount. Tampoco se
usa Redis: caché, sesiones y colas utilizan el driver `database` del proyecto.

### 4. Comandos útiles

```bash
# Construir la imagen
docker compose build

# Iniciar / detener
docker compose up -d
docker compose down

# Ver estado y registros
docker compose ps
docker compose logs -f --tail=100
docker compose logs -f app
docker compose logs -f reverb

# Migraciones seguras (sin borrar datos)
docker compose exec app php artisan migrate --force

# Datos de demostración (los seeders son idempotentes)
docker compose exec app php artisan db:seed --force

# Pruebas automatizadas dentro del contenedor
docker compose exec app php artisan test

# Reconstruir después de cambiar código, Dockerfile o assets
docker compose up -d --build

# Abrir una shell
docker compose exec app sh
```

### 5. Variables y WebSockets dentro de Docker

`docker-compose.yml` fija la comunicación **interna** entre contenedores:

```dotenv
REVERB_HOST=reverb          # app -> reverb por la red interna
REVERB_PORT=8080
REVERB_SERVER_HOST=0.0.0.0  # Reverb escucha en su contenedor
REVERB_SERVER_PORT=8080
BROADCAST_CONNECTION=reverb
```

El navegador usa una dirección **pública distinta**, incrustada al compilar el
frontend con Vite:

```dotenv
DOCKER_REVERB_HOST=localhost
DOCKER_REVERB_PORT=8080
DOCKER_REVERB_SCHEME=http
VITE_REVERB_APP_KEY=${REVERB_APP_KEY}
```

Si cambias `REVERB_APP_KEY`, el host público o el puerto, reconstruye la imagen
(`docker compose up -d --build`) porque los valores `VITE_*` forman parte del
bundle.

### 6. Persistencia

- `./database/database.sqlite` está en un **bind mount**: sobrevive a
  `docker compose down` y a la recreación de contenedores.
- `emplea-storage` y `emplea-uploads` son volúmenes nombrados para logs,
  caché de vistas e imágenes subidas.
- No se ejecuta `migrate:fresh` en ningún arranque.

---

## Opción 2 · Ejecución local (sin Docker)

```bash
composer install
cp .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
npm install
npm run build
```

Arrancar todos los procesos (servidor, colas, Reverb, Vite):

```bash
composer run dev
```

O en terminales separadas:

```bash
php artisan serve
php artisan reverb:start
npm run dev
```

Abrir `http://127.0.0.1:8000`.

---

## API REST

Documentación completa: **[docs/API_REST.md](docs/API_REST.md)**

- Base: `http://localhost:8000/api`
- Token Sanctum: `Authorization: Bearer <token>`
- Formato uniforme: `{ success, message, data, meta? }`
- Códigos: 200, 201, 400, 401, 403, 404, 422, 500
- Colección Postman: `docs/postman/Emplea_Local_API.postman_collection.json`

### Ejemplo rápido

```bash
# 1. Login (guarda el token de data.token)
curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"empleador@emplealocal.test","password":"password123"}'

# 2. Consulta pública de empleos
curl -s "http://localhost:8000/api/ofertas?ubicacion=Coracora" \
  -H "Accept: application/json"

# 3. Publicar una oferta (token de empleador)
curl -s -X POST http://localhost:8000/api/ofertas \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"titulo":"Desarrollador web","descripcion":"Laravel","requisitos":"PHP","lugar":"Coracora","tipo_trabajo":"Tiempo completo","salario":2500,"fecha_limite":"2026-12-31"}'
```

### Recursos principales

| Recurso | Endpoints |
|---------|-----------|
| Autenticación | `POST /api/auth/register`, `POST /api/auth/login`, `GET /api/auth/me`, `POST /api/auth/logout` |
| Empleos | `GET /api/ofertas` (+ `q`, `titulo`, `ubicacion`, `categoria`, `recientes`), `GET /api/ofertas/{id}`, `POST/PUT/DELETE /api/ofertas/{id}` |
| Postulaciones | `POST /api/ofertas/{id}/postulaciones`, `GET /api/postulaciones`, `GET /api/ofertas/{id}/postulantes`, `PATCH /api/postulaciones/{id}/estado` |
| Oportunidades | `GET /api/oportunidades`, `GET /api/oportunidades/recientes`, CRUD con rol admin |
| Publicaciones | `GET /api/publicaciones`, `GET /api/publicaciones/{tipo}/{id}` y CRUD unificado |

Las ofertas pendientes nunca aparecen en los endpoints públicos. Los permisos se
validan por rol **y** por propiedad del recurso.

---

## WebSockets (Laravel Reverb)

La portada escucha el canal público `publicaciones` con Laravel Echo. Los
observadores de `Oferta` y `Oportunidad` emiten eventos cuando una publicación se
aprueba, se edita o se retira. El payload usa `App\Support\PublicacionPublica` y
no contiene datos personales.

Sin Reverb la web sigue funcionando; sólo se muestra el aviso “Reconectando”.

En local, `php artisan reverb:start` usa `127.0.0.1:8080`. En Docker, el
contenedor `reverb` escucha en `0.0.0.0:8080` y se publica en el puerto `8080`.

---

## Credenciales de prueba (creadas por los seeders)

| Rol | Correo | Contraseña |
|-----|--------|------------|
| Administrador | `admin@emplealocal.test` | `password123` |
| Empleador | `empleador@emplealocal.test` | `password123` |
| Postulante | `postulante@emplealocal.test` | `password123` |

> El administrador **no** puede registrarse desde el formulario público ni desde
> `POST /api/auth/register`; se crea por seeder.

---

## Pruebas

```bash
# En local
php artisan test

# Dentro de Docker
docker compose exec app php artisan test
```

Cubren registro, login/logout, perfil, ofertas (crear/editar/eliminar/moderar),
búsqueda, detalle, postulación, duplicados, postulaciones propias y de empleador,
gestión de usuarios, oportunidades, publicidad, imágenes, dashboard, autorización
por roles, eventos en tiempo real y **toda la API REST** (48 pruebas específicas).

`tests/bootstrap.php` fuerza SQLite en memoria: las pruebas nunca tocan
`database/database.sqlite`, ni siquiera dentro de Docker.

---

## Base de datos

El archivo `database/database.sqlite` **no se versiona** para no publicar datos de
usuarios. Se crea con:

```bash
php artisan migrate --seed
# o, con Docker, automáticamente al arrancar con una base nueva
```

Tablas: `users`, `ofertas`, `postulaciones`, `oportunidades`, `publicidades`,
`personal_access_tokens` (+ `sessions`, `cache`, `jobs` del framework).
La restricción `UNIQUE(oferta_id, user_id)` impide postulaciones duplicadas.

---

## Estructura principal

- `app/Models`: `User`, `Oferta`, `Postulacion`, `Oportunidad`, `Publicidad`
- `app/Http/Controllers`: interfaz web y `Api/*` para la API REST
- `app/Http/Requests/Api`: validaciones de la API
- `app/Http/Resources`: respuestas JSON sin datos sensibles
- `app/Http/Middleware/ApiRoleMiddleware.php`: roles en la API (401/403 JSON)
- `app/Support/ApiResponse.php`: formato uniforme de respuestas
- `app/Events`, `app/Observers`, `app/Support/Realtime.php`: WebSockets
- `routes/api.php`, `routes/web.php`
- `Dockerfile`, `docker-compose.yml`, `docker/` (Nginx, PHP, entrypoint)
- `tests/Feature/Api`: pruebas de la API
- `docs/API_REST.md`, `docs/postman/`, `docs/INFORME_FINAL.md`
