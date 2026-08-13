# API — e-Ticket Sarangan

Base URL: `/api/v1`

Prefix versi dikonfigurasi di `backend/bootstrap/app.php`:

```php
->withRouting(
    api: __DIR__.'/../routes/api.php',
    apiPrefix: 'api/v1',
    ...
)
```

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

## Endpoint Fase 1

| Method | Endpoint | Deskripsi | Auth |
|---|---|---|---|
| GET | `/api/v1/health` | Health check (app + database) | Publik |

### Health Check

`GET /api/v1/health`

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
POST   /api/v1/auth/login
POST   /api/v1/auth/register
POST   /api/v1/auth/logout
GET    /api/v1/auth/me

GET    /api/v1/ticket-categories

POST   /api/v1/bookings
GET    /api/v1/bookings
GET    /api/v1/bookings/{id}

GET    /api/v1/tickets
GET    /api/v1/tickets/{id}

POST   /api/v1/payments
POST   /api/v1/payments/webhook

POST   /api/v1/checkins
GET    /api/v1/checkins

POST   /api/v1/tickets/{id}/upgrade

GET    /api/v1/admin/dashboard
GET    /api/v1/admin/analytics
```