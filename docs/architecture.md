# Arsitektur — e-Ticket Sarangan

## Arsitektur Umum

```
┌─────────────────────────────┐
│          FRONTEND           │
│                             │
│ Vue 3 + TypeScript          │
│ Vue Router                  │
│ Pinia                       │
│ Tailwind CSS                │
│ Vue I18n                    │
│ Axios                       │
└──────────────┬──────────────┘
               │
             Axios
               │
               ▼
┌─────────────────────────────┐
│           BACKEND           │
│                             │
│ Laravel 12 REST API         │
│ Sanctum                     │
│ Controllers / Requests      │
│ Resources / Services        │
│ Policies                    │
│ Eloquent                    │
└──────────────┬──────────────┘
               │
          PostgreSQL
               │
               ▼
┌─────────────────────────────┐
│       SUPABASE / DEV PG     │
│       PostgreSQL DB         │
└─────────────────────────────┘
```

## Tanggung Jawab Layer

### Frontend (Vue 3)
- Rendering UI dan interaksi pengguna.
- State management klien (Pinia) dan routing role-based (Vue Router).
- Lokalisasi UI dengan vue-i18n (default `id`).
- Komunikasi ke backend **hanya** melalui Axios → Laravel API.
- **TIDAK** boleh mengakses database Supabase/PostgreSQL secara langsung.

### Backend (Laravel)
- Satu-satunya pintu masuk data (API gateway), prefix `/api/v1`.
- Autentikasi (Sanctum) dan otorisasi (middleware + policy).
- Business logic di service layer (dibuat pada fase berikutnya).
- Validasi input via Form Request.
- Perhitungan harga, usia, kategori tiket, total: **selalu di backend**.

### Database (Supabase PostgreSQL)
- Penyimpanan data.
- Constraint foreign key dan integrity.
- Seluruh akses dilakukan via Eloquent (Laravel).

## Struktur Folder

```
e-ticket-sarangan/
├── backend/                 # Laravel 12 (REST API)
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/V1/   # Controller versi v1
│   │   │   ├── Middleware/            # middleware kustom
│   │   │   ├── Requests/              # Form Request (validasi)
│   │   │   └── Resources/             # API Resource (response shape)
│   │   ├── Models/                    # Eloquent models
│   │   ├── Services/                  # business logic layer
│   │   ├── Policies/                  # authorization policies
│   │   └── Support/                   # helper: ApiResponse
│   ├── database/
│   │   ├── factories/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   │   ├── api.php                    # /api/v1/*
│   │   └── web.php
│   ├── tests/
│   │   ├── Feature/
│   │   └── Unit/
│   ├── .env / .env.example
│   └── artisan
│
├── frontend/                # Vue 3 + TS (SPA)
│   ├── src/
│   │   ├── assets/css/                # Tailwind + global style
│   │   ├── components/ui/             # reusable UI components
│   │   ├── composables/               # reusable composition functions
│   │   ├── layouts/                   # layout dashboard
│   │   ├── locales/                   # id.json / en.json
│   │   ├── router/                    # role-based routing + guard
│   │   ├── services/                  # axios instance + api modules
│   │   ├── stores/                    # Pinia stores
│   │   ├── types/                     # TypeScript types
│   │   ├── utils/                     # formatters, helpers
│   │   └── views/                     # auth/ wisatawan/ petugas/ admin/
│   ├── .env / .env.example
│   └── vite.config.ts
│
├── docs/                    # dokumentasi teknis
├── README.md
└── .gitignore
```