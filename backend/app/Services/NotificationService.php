<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public static function send(int $userId, string $title, string $message, ?string $type = null, ?array $data = null): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
        ]);
    }

    public static function sendOrderPending($order): void
    {
        self::send(
            $order->user_id,
            'Pesanan dibuat',
            "Pesanan {$order->order_code} berhasil dibuat. Silakan selesaikan pembayaran sebelum kadaluarsa.",
            'order_pending',
            ['order_code' => $order->order_code, 'amount' => $order->total_amount]
        );
    }

    public static function sendOrderPaid($order): void
    {
        self::send(
            $order->user_id,
            'Pembayaran berhasil',
            "Pembayaran {$order->order_code} lunas. QR tiket Anda siap digunakan hingga ". \Carbon\Carbon::parse($order->visit_date)->translatedFormat('d F Y') .".",
            'order_paid',
            ['order_code' => $order->order_code]
        );
    }

    public static function sendOrderExpired($order): void
    {
        self::send(
            $order->user_id,
            'Pesanan kadaluarsa',
            "Pesanan {$order->order_code} kadaluarsa karena pembayaran tidak selesai tepat waktu.",
            'order_expired',
            ['order_code' => $order->order_code]
        );
    }

    public static function sendOrderScanned($order): void
    {
        self::send(
            $order->user_id,
            'Tiket digunakan',
            "Tiket {$order->order_code} telah berhasil dipindai petugas pada ". now()->translatedFormat('d F Y H:i') .". Selamat menikmati wisata!",
            'order_scanned',
            ['order_code' => $order->order_code]
        );
    }

    public static function sendAccommodationBooked($booking): void
    {
        self::send(
            $booking->user_id,
            'Booking penginapan dibuat',
            "Booking {$booking->booking_code} di {$booking->accommodation->name} pada {$booking->check_in->format('d/m/Y')} berhasil dibuat.",
            'accommodation_booked',
            ['booking_code' => $booking->booking_code, 'accommodation_id' => $booking->accommodation_id]
        );
    }
}
