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
        $perPage = min((int) $request->input('per_page', 15), 50);

        $query = Order::where('user_id', $request->user()->id)
            ->with(['items.ticketType'])
            ->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'ilike', "%{$search}%")
                  ->orWhere('customer_name', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $paginated = $query->paginate($perPage);

        return ApiResponse::success(
            'Riwayat booking berhasil diambil',
            $paginated->items(),
            [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ]
        );
    }

    /**
     * Store a newly created order.
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $user = $request->user();
        $visitDate = $request->validated('visit_date');
        $rawItems = $request->validated('items');

        // Consolidate duplicate ticket_type_ids in payload
        $consolidatedItems = [];
        foreach ($rawItems as $item) {
            $typeId = (int) $item['ticket_type_id'];
            $qty = (int) $item['quantity'];
            if ($qty > 0) {
                $consolidatedItems[$typeId] = ($consolidatedItems[$typeId] ?? 0) + $qty;
            }
        }

        if (empty($consolidatedItems)) {
            return ApiResponse::error('Terdapat data booking yang tidak valid.', 422);
        }

        try {
            $order = DB::transaction(function () use ($user, $visitDate, $request, $consolidatedItems) {
                $totalQuantity = 0;
                $totalAmount = 0;
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

                    // Calculate booked quota for this visit_date (PENDING + PAID)
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
                    $subtotal = $unitPrice * $quantity;

                    $totalQuantity += $quantity;
                    $totalAmount += $subtotal;

                    $orderItemsData[] = [
                        'ticket_type_id' => $ticketType->id,
                        'quantity' => $quantity,
                        'price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ];
                }

                // Generate unique order code (e.g. ETK-20260830-ABC123)
                $datePrefix = date('Ymd', strtotime($visitDate));
                do {
                    $orderCode = 'ETK-' . $datePrefix . '-' . strtoupper(Str::random(6));
                } while (Order::where('order_code', $orderCode)->exists());

                // Create Order
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_code' => $orderCode,
                    'visit_date' => $visitDate,
                    'customer_name' => $request->validated('customer_name'),
                    'customer_email' => $request->validated('customer_email'),
                    'customer_phone' => $request->validated('customer_phone'),
                    'total_quantity' => $totalQuantity,
                    'total_amount' => $totalAmount,
                    'status' => 'PENDING',
                ]);

                // Create Order Items
                foreach ($orderItemsData as $itemData) {
                    $order->items()->create($itemData);
                }

                // Generate Xendit Invoice
                Configuration::setXenditKey(env('XENDIT_SECRET_KEY'));
                $apiInstance = new InvoiceApi();
                
                $createInvoiceRequest = new CreateInvoiceRequest([
                    'external_id' => $orderCode,
                    'amount' => $totalAmount,
                    'payer_email' => $request->validated('customer_email'),
                    'description' => "Pembayaran e-Ticket Sarangan - " . $orderCode,
                    'success_redirect_url' => env('FRONTEND_URL') . "/booking/success/{$orderCode}",
                    'failure_redirect_url' => env('FRONTEND_URL') . "/booking/success/{$orderCode}",
                ]);

                try {
                    $result = $apiInstance->createInvoice($createInvoiceRequest);
                    
                    $order->payment_id = $result['id'];
                    $order->payment_url = $result['invoice_url'];
                    $order->save();
                } catch (\Exception $e) {
                    // Log Xendit error, but keep order created
                    \Illuminate\Support\Facades\Log::error('Xendit Invoice Creation Failed: ' . $e->getMessage());
                }

                return $order->load('items.ticketType');
            });

            // Notifikasi: order pending
            try { \App\Services\NotificationService::sendOrderPending($order); } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::warning('Notif pending failed: '.$e->getMessage()); }

            return ApiResponse::success('Booking berhasil dibuat', $order, [], 201);
        } catch (Exception $e) {
            $statusCode = in_array($e->getCode(), [409, 422]) ? $e->getCode() : 500;
            $message = $statusCode === 500 ? 'Terjadi kesalahan server. Silakan coba lagi.' : $e->getMessage();

            return ApiResponse::error($message, $statusCode);
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
