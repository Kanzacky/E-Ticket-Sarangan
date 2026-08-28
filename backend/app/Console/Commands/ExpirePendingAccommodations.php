<?php

namespace App\Console\Commands;

use App\Models\AccommodationBooking;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpirePendingAccommodations extends Command
{
    protected $signature = 'accommodations:expire {--hours=24 : Jam sebelum pending dianggap expired}';
    protected $description = 'Expired accommodation pending yang melewati batas waktu & release kamar';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $threshold = Carbon::now()->subHours($hours);
        $bookings = AccommodationBooking::where('status', 'pending')
            ->where('created_at', '<', $threshold)
            ->where(function ($q) use ($threshold) {
                $q->whereNull('payment_expires_at')->orWhere('payment_expires_at', '<', Carbon::now());
            })->get();
        // also catch those where payment_expires_at explicitly passed regardless of hours
        $expiredByPayment = AccommodationBooking::where('status', 'pending')->whereNotNull('payment_expires_at')->where('payment_expires_at', '<', Carbon::now())->whereNotIn('id', $bookings->pluck('id'))->get();
        $all = $bookings->merge($expiredByPayment);

        $count = 0;
        foreach ($all as $booking) {
            $booking->status = 'cancelled';
            $booking->save();
            try {
                $acc = $booking->accommodation;
                if ($acc) { $acc->available_rooms += $booking->rooms; $acc->save(); }
            } catch (\Throwable $e) {}
            try {
                \App\Services\NotificationService::send(
                    $booking->user_id,
                    'Booking penginapan kadaluarsa',
                    "Booking {$booking->booking_code} dibatalkan otomatis (expired > {$hours}h). Kamar dilepas.",
                    'accommodation_expired',
                    ['booking_code' => $booking->booking_code]
                );
            } catch (\Throwable $e) {}
            $count++;
        }
        $this->info("Expired {$count} accommodation bookings pending");
        return self::SUCCESS;
    }
}
