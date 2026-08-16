# e-Ticket Sarangan

Sistem digital ticketing dan manajemen pengunjung untuk wisata Sarangan — **INOTEK Award 2026**.

## Architecture

```
Vue 3 (frontend / SPA)
   │
   │ Axios
   ▼
Laravel 12 REST API (/api)
   │
   │ Eloquent
   ▼
PostgreSQL (Supabase-ready)
```

Frontend dan backend dipisah dalam satu monorepo:

```
e-ticket-sarangan/
├── backend/     # Laravel 12 REST API
├── frontend/    # Vue 3 + TypeScript SPA
├── docs/        # Dokumentasi teknis
└── README.md
```

## Database

Database: **Supabase PostgreSQL** (main database).

Architecture:

```
Vue
 ↓
Laravel REST API
 ↓
Eloquent
 ↓
Supabase PostgreSQL
```

Environment setup (backend) — isi dengan nilai Supabase Anda, jangan commit nilai asli:

```env
DB_CONNECTION=pgsql
DB_HOST=<SUPABASE_HOST>
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=<SUPABASE_USERNAME>
DB_PASSWORD=<SUPABASE_PASSWORD>
DB_SSLMODE=require
```

> Gunakan **Supabase pooler** (session `5432` / transaction `6543`) untuk akses
> IPv4 yang stabil. Format username pooler: `postgres.<project_reference>`.
> Detail lengkap: `docs/supabase.md`.

## Tech Stack

### Frontend — `frontend/`

- Vue 3 (Composition API, `<script setup lang="ts">`)
- TypeScript (strict)
- Vite
- Vue Router
- Pinia
- Axios
- Tailwind CSS
- Vue I18n (default `id`, fallback `en`)
- Lucide Vue Next

### Backend — `backend/`

- Laravel 12
- PHP 8.3
- Laravel Sanctum
- Eloquent ORM
- PostgreSQL (Supabase-ready)

## Prerequisites

- PHP >= 8.3 (dengan ekstensi `pdo_pgsql`, `pgsql`)
- Composer 2.x
- Node.js >= 20.19 (Node 22 disarankan)
- npm 10+
- PostgreSQL (lokal atau Supabase)

## Installation

### 1. Clone / masuk direktori project

```bash
cd D:\Project\e-ticket-sarangan
```

### 2. Backend

```bash
cd backend
composer install

# siapkan environment
copy .env.example .env    # Windows
# cp .env.example .env    # Linux/Mac

php artisan key:generate

# default menggunakan PostgreSQL lokal (lihat docs/database.md)
# contoh: buat database e_ticket_sarangan lalu sesuaikan .env

php artisan migrate
php artisan serve          # http://localhost:8000
```

### 3. Frontend

```bash
cd frontend
npm install

# siapkan environment
copy .env.example .env    # Windows
# cp .env.example .env    # Linux/Mac

npm run dev                # http://localhost:5173
```

## Verifikasi Fase 1

1. Buka `http://localhost:5173` → halaman beranda menampilkan status frontend.
2. Kartu status mengecek `GET /api/health` → tampil **API: Terhubung**.
3. Endpoint health langsung: `http://localhost:8000/api/health`

## Testing

### Backend

```bash
cd backend
php artisan test
# ./vendor/bin/pint --test   # code style (Laravel Pint)
```

### Frontend

```bash
cd frontend
npm run lint        # ESLint
npm run type-check  # Vue + TypeScript strict
npm run build       # production build
```

## API Overview

Base URL: `/api`

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/health` | Health check aplikasi + database |
| POST | `/api/auth/login` | Login user |
| POST | `/api/auth/register` | Registrasi user |

Response format konsisten: `{ success, message, data, meta }`.
Lihat `docs/api.md` untuk detail dan roadmap endpoint.

## Role & Permission

Fase 1 belum mengimplementasikan autentikasi penuh. Rencana tiga role:

| Role | Deskripsi |
|---|---|
| `wisatawan` | Melakukan booking, melihat tiket & status pembayaran |
| `petugas` | Scan QR, validasi & check-in tiket |
| `admin` | Manajemen sistem, laporan, analitik |

Infrastruktur tersedia: Sanctum terpasang, `app/Policies` dan
`app/Http/Middleware` disiapkan di `backend/`.

## Localization

- Bahasa default: **Indonesia (`id`)**.
- Bahasa kedua: **English (`en`)**.
- File: `frontend/src/locales/{id,en}.json`.
- Semua teks UI memakai translation key (mis. `{{ t('common.save') }}`).
- Format tanggal `DD MMMM YYYY`, waktu 24-jam, mata uang `IDR` (Intl API) — lihat `frontend/src/utils/formatters.ts`.

## Environment

Salin `.env.example` → `.env` di masing-masing direktori dan sesuaikan.

`backend/.env.example` (ringkas):

```env
APP_NAME="e-Ticket Sarangan"
APP_ENV=local
APP_LOCALE=id
APP_FALLBACK_LOCALE=en

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=e_ticket_sarangan
DB_USERNAME=postgres
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173
FRONTEND_URL=http://localhost:5173

MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
```

`frontend/.env.example`:

```env
VITE_APP_NAME="e-Ticket Sarangan"
VITE_API_URL=http://localhost:8000/api
VITE_DEFAULT_LOCALE=id
```

> Jangan pernah meng-commit `.env`. Kredensial asli (Supabase, Midtrans) hanya
> disimpan di `.env` lokal. Lihat `docs/supabase.md` untuk panduan alih ke Supabase.

## Documentation

- `docs/architecture.md` — arsitektur & tanggung jawab layer
- `docs/database.md` — schema, konvensi, koneksi
- `docs/api.md` — format response & daftar endpoint
- `docs/supabase.md` — setup & alih koneksi ke Supabase

## Deployment

(Disiapkan pada fase berikutnya — struktur, env, dan build sudah siap untuk
deploy backend ke host PHP/Laravel dan frontend ke static hosting/edge.)