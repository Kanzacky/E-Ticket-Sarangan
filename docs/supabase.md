# Supabase PostgreSQL — Setup & Koneksi

## Status

**Supabase PostgreSQL telah terintegrasi sebagai database utama.**

- Project Supabase: `e-ticket-sarangan`
- Region pooler: `aws-0-ap-southeast-2.pooler.supabase.com`
- Koneksi Laravel menggunakan **Session pooler** (port `5432`), driver `pgsql`.
- Konfigurasi awal menggunakan kredensial di `backend/.env` (bukan hard-code).

> Direkt connection (`db.<ref>.supabase.co`) di lingkungan ini hanya
> ber-resolusi IPv6 sehingga tidak dapat dijangkau dari jaringan lokal.
> Supabase **pooler** menyediakan akses IPv4 yang stabil dan menjadi
> cara resmi yang digunakan untuk konfigurasi ini.

## Konfigurasi Aktif (`backend/.env`)

```env
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-southeast-2.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.<project-reference>
DB_PASSWORD=<password>
DB_SSLMODE=require
```

Format username pooler: `postgres.<project_reference>`.
Contoh project reference: `ewmdpkhpqktiiunwmwdo`.

## Verifikasi Koneksi

```bash
cd backend
php artisan config:clear
php artisan migrate:status      # terbaca => koneksi OK
php artisan tinker --execute="echo DB::connection()->getDriverName();"
```

Cek health endpoint:

```bash
curl http://localhost:8000/api/v1/health
```

`data.database` harus bernilai `connected`.

## Migration

Schema framework (framework default) sudah dimigrasi ke Supabase:

| Tabel |
|---|
| `users`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens`, `personal_access_tokens`, `migrations` |

Schema **bisnis** (ticket, booking, dst.) akan dibuat pada fase berikutnya
selalu melalui **Laravel Migration**, bukan Table Editor.

## Menuju Production / DB lain

1. Buat project Supabase baru → salin kredensial di **Project Settings → Database**.
2. Gunakan **pooler** (session `5432` atau transaction `6543`).
3. Update `backend/.env` dengan nilai baru.
4. `php artisan config:clear && php artisan migrate --force`.

## Catatan Keamanan

- Kredensial hanya di `backend/.env` (ter-ignore Git).
- Jangan commit password/database URL.
- Vue frontend TIDAK pernah mendapat akses database Supabase.
- Jangan gunakan anon/service-role key untuk business operation.