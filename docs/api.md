# API — e-Ticket Sarangan

Base URL: `/api`

Prefix dikonfigurasi di `backend/bootstrap/app.php`:

```php
->withRouting(
    api: __DIR__.'/../routes/api.php',
    apiPrefix: 'api',
    ...
)
```

Endpoint publik (`/`, `/api/health`, `/api/health/database`) didaftarkan di `backend/routes/health.php` (di luar grup middleware web/api).

## Format Response

### Success

```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": {},
  "meta": {}
}
```

### Error

```json
{
  "success": false,
  "message": "Data tidak valid",
  "errors": {}
}
```

Helper: `App\Support\ApiResponse` (`ApiResponse::success()` / `ApiResponse::error()`).

## Endpoint Lengkap

| Method | Endpoint | Deskripsi | Auth | Rate Limit |
|---|---|---|---|---|
| GET | `/` | Root JSON (status + database) | Publik | - |
| GET | `/api/health` | Health check (app + database) | Publik | - |
| GET | `/api/health/database` | Cek koneksi database saja | Publik | - |
| POST | `/api/auth/register` | Registrasi user (`wisatawan`) | Publik | 10 req/min |
| POST | `/api/auth/login` | Login → `access_token` (Sanctum) | Publik | 5 req/min |
| POST | `/api/auth/logout` | Revoke token | Sanctum | - |
| GET | `/api/auth/me` | Data user saat ini | Sanctum | - |
| PATCH | `/api/auth/me` | Update profil user | Sanctum | - |
| POST | `/api/auth/forgot-password` | Request reset password email | Publik | 5 req/min |
| POST | `/api/auth/reset-password` | Reset password via token | Publik | 5 req/min |

### Tiket (Publik + Sanctum)

| Method | Endpoint | Deskripsi | Auth |
|---|---|---|---|
| GET | `/api/ticket-types` | Daftar kategori tiket aktif | Publik |
| GET | `/api/orders` | Riwayat order user (paginated, search, status) | Sanctum |
| POST | `/api/orders` | Buat order tiket baru (quota lock, Xendit invoice) | Sanctum |
| GET | `/api/orders/{order_code}` | Detail order by code | Sanctum |

### Akomodasi (Publik + Sanctum)

| Method | Endpoint | Deskripsi | Auth |
|---|---|---|---|
| GET | `/api/accommodations` | Daftar penginapan aktif (paginated, search) | Publik |
| GET | `/api/accommodations/{id}` | Detail penginapan | Publik |
| GET | `/api/accommodation-bookings` | Riwayat booking penginapan user (paginated, search, status) | Sanctum |
| POST | `/api/accommodation-bookings` | Buat booking penginapan (Xendit invoice, decrement rooms) | Sanctum |

### Notifikasi (Sanctum)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/notifications` | Daftar notifikasi user (paginated) |
| GET | `/api/notifications/unread-count` | Jumlah notifikasi belum dibaca |
| PATCH | `/api/notifications/read-all` | Tandai semua notifikasi dibaca |
| PATCH | `/api/notifications/{id}/read` | Tandai satu notifikasi dibaca |
| DELETE | `/api/notifications/{id}` | Hapus notifikasi |

### Scanner (Petugas)

| Method | Endpoint | Deskripsi |
|---|---|---|
| POST | `/api/scan` | Verifikasi QR code tiket (PAID, not expired, not used) |
| GET | `/api/scan/history` | Riwayat scan petugas |

### Webhook

| Method | Endpoint | Deskripsi |
|---|---|---|
| POST | `/api/payments/xendit/webhook` | Xendit callback (verify `XENDIT_CALLBACK_TOKEN`) |
| POST | `/api/webhook` | Fallback alias untuk webhook |

### Admin (role:admin)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/admin/users` | CRUD users (paginated, search) |
| GET | `/api/admin/ticket-types` | CRUD ticket types (paginated, search) |
| GET | `/api/admin/ticket-categories` | CRUD ticket categories (paginated, search) |
| GET | `/api/admin/orders` | CRUD orders (paginated, search, status) |
| PATCH | `/api/admin/orders/{order_code}/status` | Update status order |
| GET | `/api/admin/payments` | Daftar payment (paginated, search) |
| PATCH | `/api/admin/payments/{id}/status` | Update status payment |
| GET | `/api/admin/accommodations` | CRUD akomodasi (paginated, search, file upload) |
| GET | `/api/admin/reports/summary` | Ringkasan laporan (revenue, tickets, bookings) |
| GET | `/api/admin/analytics` | Data analytics (kunjungan, pendapatan, dll) |
| GET | `/api/admin/audit-logs` | Audit log (paginated, search) |
| GET | `/api/admin/checkins` | Check-in logs (paginated, search) |
| GET | `/api/admin/upgrades` | Upgrade log (paginated, search) |
| GET | `/api/admin/settings` | Ambil settings (key-value) |
| PATCH | `/api/admin/settings` | Update settings |
| GET | `/api/admin/dashboard` | Dashboard ringkas admin |

### Petugas (role:petugas)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/petugas/dashboard` | Dashboard petugas |
| GET | `/api/petugas/visits` | Data kunjungan |
| GET | `/api/petugas/bookings` | Data booking |
| GET | `/api/petugas/users` | Data user |

### Cron (Vercel)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/scheduled/commands` | Menjalankan `schedule:run` (hourly: expire orders/accommodations, daily: sync Google Places) |

---

## Response Pagination (Meta)

Semua endpoint yang support pagination mengembalikan `meta`:

```json
{
  "success": true,
  "message": "...",
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 67
  }
}
```

Query params: `?page=1&per_page=15&search=foo&status=PENDING`

---

## Xendit Webhook Flow

1. **Order tiket**: `external_id = order_code` (format `ETK-YYYYMMDD-XXXXXX`)
2. **Booking penginapan**: `external_id = booking_code` (format `ACC-XXXXXXXX`)
3. Webhook dipanggil Xendit saat status invoice berubah (`PAID`, `EXPIRED`, `CANCELLED`)
4. `XENDIT_CALLBACK_TOKEN` diverifikasi jika di-set di `.env`
5. Handler: `XenditWebhookController@handleWebhook`

---

## Error Codes

| Code | Keterangan |
|---|---|
| 400 | Bad Request (validasi gagal) |
| 401 | Unauthorized (token invalid/expired) |
| 403 | Forbidden (role tidak sesuai) |
| 404 | Not Found |
| 409 | Conflict (kuota habis, double booking) |
| 422 | Unprocessable Entity (validasi server) |
| 429 | Too Many Requests (rate limit) |
| 500 | Internal Server Error |