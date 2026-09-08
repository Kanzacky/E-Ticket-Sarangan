<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpirePendingOrders extends Command
{
    protected $signature = 'orders:expire {--hours=24 : Jam sebelum pending dianggap expired}';
    protected $description = 'Expired order PENDING yang melewati batas waktu (jika webhook miss)';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $threshold = Carbon::now()->subHours($hours);
        $orders = Order::where('status', 'PENDING')->where('created_at', '<', $threshold)->get();
        $count = 0;
        foreach ($orders as $order) {
            $order->status = 'EXPIRED';
            $order->save();
            try {
                \App\Services\NotificationService::sendOrderExpired($order);
            } catch (\Throwable $e) {}
            $count++;
        }
        $this->info("Expired {$count} orders pending > {$hours}h");
        return self::SUCCESS;
    }
}
