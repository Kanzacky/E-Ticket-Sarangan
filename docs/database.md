# Database — e-Ticket Sarangan

## Koneksi

Database utama: **Supabase PostgreSQL** (terintegrasi).

```
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-southeast-2.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.<project-reference>
DB_PASSWORD=<password>
DB_SSLMODE=require
```

> Lokasi database adalah **Supabase**, bukan PostgreSQL lokal.
> PostgreSQL lokal (Laragon) hanya dipakai untuk environment testing terisolasi.

## Schema

### Fase 1 (fondasi — sudah dibuat)

| Tabel | Keterangan |
|---|---|
| `users` | Tabel user default Laravel (belum ada kolom role) |
| `cache` / `cache_locks` | Cache driver database |
| `jobs` / `job_batches` / `failed_jobs` | Queue database |
| `sessions` | Session database |
| `password_reset_tokens` | Reset password |
| `personal_access_tokens` | Token Sanctum |

### Fase berikutnya (belum dibuat — rencana)

| Tabel | Relasi utama |
|---|---|
| `ticket_categories` | kategori tiket dynamic (harga, umur min/max) |
| `bookings` | milik `users`, berisi banyak visitor |
| `booking_visitors` | per orang; FK ke `bookings` dan `ticket_categories` |
| `tickets` | per visitor; FK ke `booking_visitors` |
| `payments` | milik `bookings` |
| `checkins` | milik `tickets` |
| `ticket_upgrades` | milik `tickets` |
| `notifications` | notifikasi user |
| `audit_logs` | audit trail |

## Konvensi

- Semua tabel dibuat melalui **migration Laravel** (bukan SQL manual).
- Nama tabel: jamak `snake_case`; kolom FK: `snake_case_id`.
- Menggunakan constraint foreign key (`restrict` / `cascade` sesuai kebutuhan).
- Tidak ada akses DB langsung dari frontend.

## Environment Test

Untuk testing digunakan database terpisah agar data Supabase tidak terganggu.
Dikonfigurasi pada `backend/phpunit.xml` (PostgreSQL lokal `e_ticket_sarangan_test`).