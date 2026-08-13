#!/bin/sh
set -e

# ── 1. Storage skeleton (container filesystem is fresh per deployment) ──
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         storage/app/public \
         bootstrap/cache

# ── 2. Public storage symlink (public/storage -> storage/app/public) ──
ln -sfn /app/storage/app/public /app/public/storage

# ── 3. APP_KEY guard ────────────────────────────────────────────────────
# APP_KEY harus di-set di Vercel Environment Variables (lihat docs).
# Fallback generate per-instance hanya agar container tidak 500; untuk
# enkripsi yang stabil tetap set APP_KEY di dashboard Vercel.
if [ -z "${APP_KEY:-}" ]; then
    echo "[entrypoint] WARN: APP_KEY tidak diset. Generate key sementara." >&2
    echo "[entrypoint] WAJIB: set APP_KEY di Vercel env untuk enkripsi stabil." >&2
    php artisan key:generate --force >/dev/null
fi

# ── 4. Migrations (production migration strategy) ───────────────────────
# Idempotent: hanya menerapkan migration yang belum jalan. Bukan reset.
# --isolated memakai cache lock (database) agar aman saat banyak instance.
if [ "${AUTO_MIGRATE:-true}" = "true" ]; then
    php artisan migrate --force --isolated
fi

# ── 5. Production optimizations (config/route/view/event cache) ─────────
php artisan optimize || echo "[entrypoint] WARN: optimize gagal, lanjut tanpa cache" >&2

# ── 6. Run FrankenPHP on $PORT ──────────────────────────────────────────
exec "$@"
