#!/bin/sh
set -e

cd /var/www/html

# Directorios de escritura que Laravel necesita en tiempo de ejecución.
mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    public/uploads/ofertas \
    public/uploads/oportunidades \
    public/uploads/publicidades

# ---------------------------------------------------------------------------
# Clave de aplicación: si el .env montado aún no tiene APP_KEY se genera una
# sola vez. Nunca se hornea una clave dentro de la imagen.
# ---------------------------------------------------------------------------
if [ ! -f .env ]; then
    echo "[entrypoint] ERROR: falta el archivo .env. Ejecuta: cp .env.example .env" >&2
    exit 1
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    echo "[entrypoint] APP_KEY vacía: generando una clave para .env"
    php artisan key:generate --force
fi

# ---------------------------------------------------------------------------
# Migraciones seguras (nunca migrate:fresh). Sólo el servicio app las ejecuta;
# reverb y queue arrancan con RUN_MIGRATIONS=false y esperan a que app esté
# saludable.
# ---------------------------------------------------------------------------
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    BASE_NUEVA="false"

    if [ ! -s database/database.sqlite ]; then
        echo "[entrypoint] Creando base de datos SQLite vacía"
        touch database/database.sqlite
        BASE_NUEVA="true"
    fi

    chown -R www-data:www-data database storage bootstrap/cache public/uploads 2>/dev/null || true
    chmod -R ug+rwX database storage bootstrap/cache public/uploads

    echo "[entrypoint] Ejecutando migraciones (sin borrar datos)"
    su-exec www-data php artisan migrate --force

    # Sólo en una base recién creada: datos de demostración idempotentes
    # (usuarios, ofertas, oportunidades y publicidades de ejemplo).
    if [ "$BASE_NUEVA" = "true" ] && [ "${SEED_ON_FIRST_RUN:-true}" = "true" ]; then
        echo "[entrypoint] Sembrando datos de demostración"
        su-exec www-data php artisan db:seed --force
    fi
fi

exec "$@"
