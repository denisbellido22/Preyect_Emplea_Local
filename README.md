# EMPLEA LOCAL

**Conectando oportunidades laborales en Parinacochas**

Sistema web desarrollado en **Laravel 12** con **SQLite local** (sin MySQL, MariaDB, XAMPP ni
bases de datos externas). Toda la información —incluida la base de datos y las imágenes— vive
dentro de la carpeta del proyecto.

---

## Requisitos

- PHP >= 8.2 (con extensión `pdo_sqlite` y `sqlite3`)
- Composer 2

No se requiere Node ni compilación de assets: la interfaz usa Bootstrap 5 por CDN.

## Instalación

### Opción rápida (script incluido)

```bash
composer run setup
```

Esto instala dependencias, crea `.env`, genera la clave, crea `database/database.sqlite`
si no existe y ejecuta migraciones + seeders.

### Opción manual

```bash
composer install
cp .env.example .env          # en Windows: copy .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
```

## Ejecución

```bash
php artisan serve
```

Abrir `http://127.0.0.1:8000`.

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
usuarios, oportunidades, publicidad, carga de imágenes, dashboard y autorización por roles.

## Estructura principal

- `app/Models`: `User`, `Oferta`, `Postulacion`, `Oportunidad`, `Publicidad`
- `app/Http/Controllers`: `AuthController`, `ProfileController`, `OfertaController`,
  `PostulacionController`, `PublicidadController`, `OportunidadController`, `DashboardController`,
  `Admin/*`
- `app/Http/Middleware/RoleMiddleware.php`: autorización por rol
- `app/Support/ImageUploader.php`: guardado real de imágenes en `public/uploads/`
- `database/migrations`, `database/seeders`
- `routes/web.php`
- `resources/views`: interfaz completa
- `public/uploads/{ofertas,oportunidades,publicidades}`: imágenes

## Base de datos

Tablas: `users`, `ofertas`, `postulaciones`, `oportunidades`, `publicidades`
(+ `sessions`, `cache`, `jobs` propias del framework).
Relaciones con claves foráneas y restricción `UNIQUE(oferta_id, user_id)` en `postulaciones`
para impedir postulaciones duplicadas.
