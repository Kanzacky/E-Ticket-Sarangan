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

Endpoint publik (`/`, `/api/health`, `/api/health/database`) didaftarkan di
`backend/routes/health.php` (di luar grup middleware web/api).

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

## Endpoint

| Method | Endpoint | Deskripsi | Auth |
|---|---|---|---|
| GET | `/` | Root JSON (status + database) | Publik |
| GET | `/api/health` | Health check (app + database) | Publik |
| GET | `/api/health/database` | Cek koneksi database saja | Publik |
| POST | `/api/auth/register` | Registrasi user (`wisatawan`) | Publik |
| POST | `/api/auth/login` | Login → `access_token` (Sanctum) | Publik |
| POST | `/api/auth/logout` | Revoke token | Sanctum |
| GET | `/api/auth/me` | Data user saat ini | Sanctum |

### Health Check

`GET /api/health`

```json
{
  "success": true,
  "message": "E-Ticket Sarangan API is running",
  "data": {
    "status": "ok",
    "app": "e-Ticket Sarangan",
    "version": "v1",
    "database": "connected"
  }
}
```

## Roadmap Endpoint (fase berikutnya)

```
GET    /api/ticket-categories

POST   /api/bookings
GET    /api/bookings
GET    /api/bookings/{id}

GET    /api/tickets
GET    /api/tickets/{id}

POST   /api/payments
POST   /api/payments/webhook

POST   /api/checkins
GET    /api/checkins

POST   /api/tickets/{id}/upgrade

GET    /api/admin/dashboard
GET    /api/admin/analytics
```