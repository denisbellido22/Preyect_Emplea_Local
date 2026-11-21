# EMPLEA LOCAL

**Conectando oportunidades laborales en Parinacochas**

Sistema web desarrollado en **Laravel 12** con **SQLite local** (sin MySQL, MariaDB, XAMPP ni
bases de datos externas). Toda la información —incluida la base de datos y las imágenes— vive
dentro de la carpeta del proyecto.

---

## Requisitos

- PHP >= 8.2 (con extensión `pdo_sqlite` y `sqlite3`)
- Composer 2
- Node.js 20+ y npm (assets y WebSocket del frontend con Vite)

## Instalación

### Opción rápida (script incluido)

```bash
composer run setup
```

Esto instala dependencias, crea `.env`, genera la clave, crea `database/database.sqlite`
si no existe y ejecuta migraciones + seeders. Después instala y compila los assets:

```bash
npm install
npm run build
```

### Opción manual

```bash
composer install
cp .env.example .env          # en Windows: copy .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
npm install
npm run build
```

## Ejecución

La forma más rápida, que arranca servidor, WebSockets, colas, logs y Vite a la vez:

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

> Si la portada muestra **“Reconectando”**, verifica que `php artisan reverb:start` esté en
ejecución y que las variables `REVERB_*` de `.env` coincidan con `VITE_REVERB_*`.

## WebSockets (Laravel Reverb)

La portada escucha el canal público `publicaciones` mediante Laravel Echo. Reverb usa por
defecto `127.0.0.1:8080` y requiere en `.env`:

```dotenv
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=emplea-local
REVERB_APP_KEY=cambia-esta-clave
REVERB_APP_SECRET=cambia-este-secreto
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Sin Reverb la web sigue funcionando, pero muestra el aviso de conexión en tiempo real.

## Credenciales de prueba (creadas por los seeders)

| Rol          | Correo                          | Contraseña    |
|--------------|---------------------------------|---------------|
| Administrador | `admin@emplealocal.test`        | `password123` |
| Empleador     | `empleador@emplealocal.test`    | `password123` |
| Postulante    | `postulante@emplealocal.test`   | `password123` |

> El administrador **no** puede registrarse desde el formulario público; se crea por seeder.

## Pruebas

```bash
php artisan test
```

Cubren registro, login/logout, perfil, ofertas (crear/editar/eliminar/moderar), búsqueda,
detalle, postulación, bloqueo de duplicados, postulaciones propias y de empleador, gestión de
usuarios, oportunidades, publicidad, carga de imágenes, dashboard, autorización por roles y
eventos en tiempo real de la portada.

## Estructura principal

- `app/Models`: `User`, `Oferta`, `Postulacion`, `Oportunidad`, `Publicidad`
- `app/Http/Controllers`: `AuthController`, `ProfileController`, `OfertaController`,
  `PostulacionController`, `PublicidadController`, `OportunidadController`, `DashboardController`,
  `Admin/*`
- `app/Http/Middleware/RoleMiddleware.php`: autorización por rol
- `app/Events`, `app/Observers`, `app/Support/Realtime.php`: eventos WebSocket de publicaciones
- `app/Support/ImageUploader.php`: guardado real de imágenes en `public/uploads/`
- `resources/js/realtime.js`: cliente Laravel Echo de la portada
- `database/migrations`, `database/seeders`
- `routes/web.php`
- `resources/views`: interfaz completa
- `public/uploads/{ofertas,oportunidades,publicidades}`: imágenes

## Base de datos

Tablas: `users`, `ofertas`, `postulaciones`, `oportunidades`, `publicidades`
(+ `sessions`, `cache`, `jobs` propias del framework).
Relaciones con claves foráneas y restricción `UNIQUE(oferta_id, user_id)` en `postulaciones`
para impedir postulaciones duplicadas.
