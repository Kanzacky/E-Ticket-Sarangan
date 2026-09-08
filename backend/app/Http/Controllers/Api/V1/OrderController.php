<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TicketType;
use App\Support\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;

class OrderController extends Controller
{
    /**
     * Display a listing of orders for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.ticketType'])
            ->latest()
            ->get();

        return ApiResponse::success('Riwayat booking berhasil diambil', $orders);
    }

    /**
     * Store a newly created order.
     *
     * FIX: Xendit invoice creation is moved OUTSIDE of DB::transaction.
     * Previously the external HTTP call to Xendit blocked the DB connection
     * for 5-15s, causing frontend Axios (15s timeout) to give up and show
     * "Terjadi kesalahan server" — even though the order was already saved.
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $user      = $request->user();
        $visitDate = $request->validated('visit_date');
        $rawItems  = $request->validated('items');

        // Consolidate duplicate ticket_type_ids in payload
        $consolidatedItems = [];
        foreach ($rawItems as $item) {
            $typeId = (int) $item['ticket_type_id'];
            $qty    = (int) $item['quantity'];
            if ($qty > 0) {
                $consolidatedItems[$typeId] = ($consolidatedItems[$typeId] ?? 0) + $qty;
            }
        }

        if (empty($consolidatedItems)) {
            return ApiResponse::error('Terdapat data booking yang tidak valid.', 422);
        }

        // ── STEP 1: Create order & items in DB (fast, no external calls) ──────
        try {
            $order = DB::transaction(function () use ($user, $visitDate, $request, $consolidatedItems) {
                $totalQuantity = 0;
                $totalAmount   = 0;
                $orderItemsData = [];

                foreach ($consolidatedItems as $ticketTypeId => $quantity) {
                    /** @var TicketType|null $ticketType */
                    $ticketType = TicketType::where('id', $ticketTypeId)->lockForUpdate()->first();

                    if (! $ticketType) {
                        throw new Exception('Jenis tiket tidak ditemukan.', 422);
                    }

                    if ($ticketType->status !== 'ACTIVE') {
                        throw new Exception("Jenis tiket '{$ticketType->name}' sedang tidak aktif.", 422);
                    }

                    $bookedQuantity = OrderItem::where('ticket_type_id', $ticketType->id)
                        ->whereHas('order', function ($query) use ($visitDate) {
                            $query->where('visit_date', $visitDate)
                                ->whereIn('status', ['PENDING', 'PAID']);
                        })
                        ->sum('quantity');

                    $remainingQuota = $ticketType->quota - $bookedQuantity;

                    if ($quantity > $remainingQuota) {
                        $available = max(0, $remainingQuota);
                        throw new Exception("Kuota tiket '{$ticketType->name}' tidak mencukupi untuk tanggal {$visitDate} (tersisa: {$available}).", 409);
                    }

                    $unitPrice = (float) $ticketType->price;
                    $subtotal  = $unitPrice * $quantity;

                    $totalQuantity += $quantity;
                    $totalAmount   += $subtotal;

                    $orderItemsData[] = [
                        'ticket_type_id' => $ticketType->id,
                        'quantity'       => $quantity,
                        'price'          => $unitPrice,
                        'subtotal'       => $subtotal,
                    ];
                }

                $datePrefix = date('Ymd', strtotime($visitDate));
                do {
                    $orderCode = 'ETK-' . $datePrefix . '-' . strtoupper(Str::random(6));
                } while (Order::where('order_code', $orderCode)->exists());

                $order = Order::create([
                    'user_id'        => $user->id,
                    'order_code'     => $orderCode,
                    'visit_date'     => $visitDate,
                    'customer_name'  => $request->validated('customer_name'),
                    'customer_email' => $request->validated('customer_email'),
                    'customer_phone' => $request->validated('customer_phone'),
                    'total_quantity' => $totalQuantity,
                    'total_amount'   => $totalAmount,
                    'status'         => 'PENDING',
                ]);

                foreach ($orderItemsData as $itemData) {
                    $order->items()->create($itemData);
                }

                return $order;
            });
        } catch (Exception $e) {
            $statusCode = in_array($e->getCode(), [409, 422]) ? $e->getCode() : 500;
            $message    = $statusCode === 500 ? 'Terjadi kesalahan server saat membuat pesanan.' : $e->getMessage();
            return ApiResponse::error($message, $statusCode);
        }

        Log::info('[BOOKING] Order created: ' . $order->order_code);

        // ── STEP 2: Create Xendit invoice OUTSIDE transaction ─────────────────
        // This is the key fix — external HTTP call no longer blocks DB connection.
        $paymentUrl = null;
        try {
            Configuration::setXenditKey(env('XENDIT_SECRET_KEY'));
            $apiInstance = new InvoiceApi();

            $invoiceReq = new CreateInvoiceRequest([
                'external_id'          => $order->order_code,
                'amount'               => $order->total_amount,
                'payer_email'          => $order->customer_email,
                'description'          => "Pembayaran e-Ticket Sarangan - {$order->order_code}",
                'success_redirect_url' => env('FRONTEND_URL') . "/booking/success/{$order->order_code}",
                'failure_redirect_url' => env('FRONTEND_URL') . "/booking/success/{$order->order_code}",
            ]);

            Log::info('[PAYMENT] Creating Xendit invoice for: ' . $order->order_code);
            $result = $apiInstance->createInvoice($invoiceReq);

            $order->payment_id  = $result['id'];
            $order->payment_url = $result['invoice_url'];
            $order->save();

            $paymentUrl = $result['invoice_url'];
            Log::info('[PAYMENT] Xendit invoice created for: ' . $order->order_code);
        } catch (\Exception $e) {
            Log::error('[PAYMENT ERROR] booking=' . $order->order_code . ' msg=' . $e->getMessage());
            // Order remains saved — user can pay via "Pesanan Saya → Bayar"
        }

        // Notification
        try {
            \App\Services\NotificationService::sendOrderPending($order);
        } catch (\Throwable $e) {
            Log::warning('Notif pending failed: ' . $e->getMessage());
        }

        $order->load('items.ticketType');

        // Always return payment_url so frontend can redirect immediately if available
        $responseData = $order->toArray();
        $responseData['payment_url'] = $paymentUrl;

        return ApiResponse::success('Booking berhasil dibuat', $responseData, [], 201);
    }

    /**
     * Create / retrieve a Xendit payment URL for an existing PENDING order.
     * Used by the "Bayar" button in MyBookings and as retry if invoice
     * creation failed during the initial booking request.
     */
    public function pay(Request $request, string $order_code): JsonResponse
    {
        $order = Order::where('order_code', $order_code)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $order) {
            return ApiResponse::error('Pesanan tidak ditemukan.', 404);
        }

        if ($order->status !== 'PENDING') {
            return ApiResponse::error('Pesanan ini tidak dapat dibayar (status: ' . $order->status . ').', 400);
        }

        // Reuse existing payment URL if available
        if (! empty($order->payment_url)) {
            return ApiResponse::success('URL pembayaran tersedia.', ['payment_url' => $order->payment_url]);
        }

        // Generate new Xendit invoice
        try {
            Configuration::setXenditKey(env('XENDIT_SECRET_KEY'));
            $apiInstance = new InvoiceApi();

            $invoiceReq = new CreateInvoiceRequest([
                'external_id'          => $order->order_code . '-' . time(), // suffix to avoid Xendit duplicate
                'amount'               => $order->total_amount,
                'payer_email'          => $order->customer_email,
                'description'          => "Pembayaran e-Ticket Sarangan - {$order->order_code}",
                'success_redirect_url' => env('FRONTEND_URL') . "/booking/success/{$order->order_code}",
                'failure_redirect_url' => env('FRONTEND_URL') . "/booking/success/{$order->order_code}",
            ]);

            $result = $apiInstance->createInvoice($invoiceReq);

            $order->payment_id  = $result['id'];
            $order->payment_url = $result['invoice_url'];
            $order->save();

            return ApiResponse::success('URL pembayaran berhasil dibuat.', ['payment_url' => $result['invoice_url']]);
        } catch (\Exception $e) {
            Log::error('[PAYMENT RETRY] ' . $order_code . ': ' . $e->getMessage());
            return ApiResponse::error('Gagal membuat link pembayaran. Silakan coba lagi.', 503);
        }
    }

    /**
     * Display the specified order by order_code for the authenticated user.
     */
    public function show(Request $request, string $order_code): JsonResponse
    {
        $order = Order::where('order_code', $order_code)
            ->where('user_id', $request->user()->id)
            ->with(['items.ticketType'])
            ->first();

        if (! $order) {
            return ApiResponse::error('Data booking tidak ditemukan', 404);
        }

        return ApiResponse::success('Detail booking berhasil diambil', $order);
    }
}
