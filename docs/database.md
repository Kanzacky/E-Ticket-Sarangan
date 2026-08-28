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
> **Lokal/CI**: `DB_SSLMODE=prefer` (PostgreSQL lokal tanpa SSL)
> **Produksi Vercel**: `DB_SSLMODE=require` (Supabase wajib SSL)

## Schema Terkini

Semua tabel dibuat melalui **migration Laravel**. Nama tabel: jamak `snake_case`; kolom FK: `snake_case_id`.

### Tabel Inti (Fase 1 — Fondasi)

| Tabel | Keterangan |
|---|---|
| `users` | User dengan kolom `role` (`wisatawan`/`petugas`/`admin`), `phone` |
| `cache` / `cache_locks` | Cache driver database |
| `jobs` / `job_batches` / `failed_jobs` | Queue database |
| `sessions` | Session database |
| `password_reset_tokens` | Reset password |
| `personal_access_tokens` | Token Sanctum (SPA token) |

### Tabel Fitur (Fase 2 — Sudah dibuat)

| Tabel | Relasi Utama | Keterangan |
|---|---|---|
| `ticket_categories` | — | Kategori tiket dynamic (harga, umur min/max, status aktif) |
| `ticket_types` | `ticket_categories` | Jenis tiket per kategori (kuota, harga, status ACTIVE/INACTIVE) |
| `orders` | `users` (FK `user_id`) | Order tiket: `order_code` unik, `visit_date`, data pemesan, `total_amount`, `status` (PENDING/PAID/COMPLETED/CANCELLED/EXPIRED), `payment_id`, `payment_url`, `qr_expires_at`, `scanned_at`, `scanned_by` |
| `order_items` | `orders` + `ticket_types` | Item per order: `quantity`, `price`, `subtotal` |
| `accommodations` | — | Katalog penginapan: nama, alamat, harga/malam, `total_rooms`, `available_rooms`, `rating`, `facilities` (JSON), `is_active`, **Google Places fields**: `google_place_id` (unique), `latitude`, `longitude`, `source` (manual/google) |
| `accommodation_bookings` | `users` + `accommodations` | Booking penginapan: `booking_code` (ACC-XXXXXXXX), `check_in`/`check_out`, `rooms`, `guests`, `total_price`, `guest_name/phone`, `status` (pending/confirmed/cancelled/completed/paid/expired), `payment_id`, `payment_url`, `payment_expires_at`, `notes` |
| `notifications` | `users` | Notifikasi user: `title`, `message`, `type` (order_pending/order_paid/accommodation_booked/scan_success/scan_failed/accommodation_expired), `data` (JSON), `read_at` |
| `audit_logs` | `users` (nullable) | Audit trail: `action`, `model_type`, `model_id`, `old_values`/`new_values` (JSON), `ip_address`, `user_agent` |
| `scan_logs` | `users` (nullable) | Log scan petugas: `order_code`, `is_valid` (bool), `reason` |
| `settings` | — | Key-value settings: `key` (unique), `value` (text) |

---

## Detail Kolom Penting

### `orders`
- `order_code`: `ETK-YYYYMMDD-XXXXXX` (unik, index)
- `visit_date`: tanggal kunjungan (index)
- `status`: `PENDING` → `PAID` → `COMPLETED` (scan), atau `EXPIRED` (cron 24h), atau `CANCELLED`
- `payment_id`: Xendit invoice ID
- `payment_url`: Xendit invoice URL (redirect user)
- `qr_expires_at`: `visit_date 23:59:59` (validasi QR)
- `scanned_at` / `scanned_by`: waktu & petugas scan

### `order_items`
- `quantity` × `price` = `subtotal`
- FK ke `ticket_types` (quota check via `lockForUpdate`)

### `accommodations`
- `available_rooms` dikurangi saat booking dibuat, dikembalikan saat expired/cancelled
- `rating` decimal(2,1), `price_per_night` integer (rupiah)
- `facilities`: JSON array string
- **Google Places sync**: `google_place_id` (unik), `latitude/longitude` (decimal 10,7), `source` enum `manual`/`google`

### `accommodation_bookings`
- `booking_code`: `ACC-XXXXXXXX`
- `status`: `pending` → `confirmed` (PAID webhook) / `cancelled` (expired webhook), `completed` (manual admin)
- `payment_expires_at`: 24 jam dari created_at

### `notifications`
- `type`: enum untuk routing UI
- `data`: JSON payload untuk deep-link (mis. `order_code`, `booking_code`)
- `read_at` null = belum dibaca

### `audit_logs`
- Polymorphic: `model_type` + `model_id`
- `old_values`/`new_values` JSON untuk diff

### `scan_logs`
- Satu baris per percobaan scan
- `is_valid` boolean, `reason` string jika invalid

### `settings`
- Seeded defaults: `site_name`, `site_description`, `contact_email`, `contact_phone`, `address`, `operational_hours`, `payment_gateway`, `tax_rate`
- Admin CRUD via `GET/PATCH /api/admin/settings`

---

## Migrasi Terurut (Timeline)

| File | Tabel | Tipe |
|---|---|---|
| `0001_01_01_000000_create_users_table.php` | `users` | create |
| `0001_01_01_000001_create_cache_table.php` | `cache`, `cache_locks` | create |
| `0001_01_01_000002_create_jobs_table.php` | `jobs`, `job_batches`, `failed_jobs` | create |
| `2026_08_13_085846_create_personal_access_tokens_table.php` | `personal_access_tokens` | create |
| `2026_08_13_133214_add_role_and_phone_to_users_table.php` | `users` | alter (role, phone) |
| `2026_08_13_140731_create_ticket_categories_table.php` | `ticket_categories` | create |
| `2026_08_13_140738_create_notifications_table.php` | `notifications` | create (v1: title, message, read_at) |
| `2026_08_13_140739_create_audit_logs_table.php` | `audit_logs` | create |
| `2026_08_26_120000_create_ticket_types_and_orders_tables.php` | `ticket_types`, `orders`, `order_items` | create |
| `2026_08_26_150000_create_accommodations_table.php` | `accommodations`, `accommodation_bookings` | create |
| `2026_08_26_150537_add_payment_and_qr_to_orders_table.php` | `orders` | alter (payment_id, payment_url, qr_expires_at) |
| `2026_08_26_160000_add_scan_fields_to_orders_table.php` | `orders` | alter (scanned_at, scanned_by) |
| `2026_08_26_164259_create_scan_logs_table.php` | `scan_logs` | create |
| `2026_08_31_000001_add_type_data_to_notifications_table.php` | `notifications` | alter (type, data) |
| `2026_08_31_000002_add_google_fields_to_accommodations_table.php` | `accommodations` | alter (google_place_id, latitude, longitude, source) |
| `2026_08_31_000003_create_settings_table.php` | `settings` | create + seed defaults |
| `2026_08_31_000004_add_payment_to_accommodation_bookings.php` | `accommodation_bookings` | alter (payment_id, payment_url, payment_expires_at) |

---

## Foreign Key Constraints

| Child Table | Child Column | Parent Table | Parent Column | On Delete |
|---|---|---|---|---|
| `orders` | `user_id` | `users` | `id` | CASCADE |
| `order_items` | `order_id` | `orders` | `id` | CASCADE |
| `order_items` | `ticket_type_id` | `ticket_types` | `id` | RESTRICT |
| `ticket_types` | `ticket_category_id` | `ticket_categories` | `id` | RESTRICT |
| `accommodation_bookings` | `user_id` | `users` | `id` | CASCADE |
| `accommodation_bookings` | `accommodation_id` | `accommodations` | `id` | RESTRICT |
| `notifications` | `user_id` | `users` | `id` | CASCADE |
| `audit_logs` | `user_id` | `users` | `id` | SET NULL |
| `scan_logs` | `scanned_by` | `users` | `id` | SET NULL |
| `orders` | `scanned_by` | `users` | `id` | SET NULL |

---

## Indexes Utama

| Tabel | Index |
|---|---|
| `orders` | `order_code` (unique), `user_id`, `visit_date`, `status` |
| `order_items` | `order_id`, `ticket_type_id` |
| `accommodations` | `is_active`, `rating` |
| `accommodation_bookings` | `booking_code` (unique), `user_id`, `accommodation_id`, `status` |
| `notifications` | `user_id`, `read_at` |
| `audit_logs` | `user_id`, `model_type` + `model_id` |
| `scan_logs` | `order_code`, `scanned_by` |
| `personal_access_tokens` | `tokenable_type` + `tokenable_id`, `token` (unique) |

---

## Environment Test

Untuk testing digunakan database terpisah agar data Supabase tidak terganggu.
Dikonfigurasi pada `backend/phpunit.xml` (PostgreSQL lokal `e_ticket_sarangan_test` atau `sqlite :memory:`).
- CI: `sqlite :memory:` (default `phpunit.xml`)
- Lokal developer: bisa pakai PostgreSQL lokal dengan `DB_SSLMODE=prefer`

---

## Konvensi

- Semua tabel dibuat melalui **migration Laravel** (bukan SQL manual).
- Nama tabel: jamak `snake_case`; kolom FK: `snake_case_id`.
- Menggunakan constraint foreign key (`restrict` / `cascade` sesuai kebutuhan).
- Tidak ada akses DB langsung dari frontend (semua lewat REST API).
- Soft delete **tidak** dipakai (semua hard delete / status column).
- `timestamps()` (`created_at`, `updated_at`) di semua tabel fitur.