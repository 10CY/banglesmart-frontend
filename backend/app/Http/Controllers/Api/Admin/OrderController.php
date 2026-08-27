<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\InventoryMovement;
use App\Services\OrderEmailService;
class OrderController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Order List
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Order::query()
            ->with([
                'user:id,name,email',
            ])
            ->withCount('items');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where(
                    'order_number',
                    'like',
                    "%{$search}%"
                );

                $q->orWhereHas(
                    'user',
                    function ($userQuery) use ($search) {

                        $userQuery
                            ->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'email',
                                'like',
                                "%{$search}%"
                            );
                    }
                );

            });
        }

        /*
        |--------------------------------------------------------------------------
        | Order Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Payment Method
        |--------------------------------------------------------------------------
        */

        if ($request->filled('payment_method')) {
            $query->where(
                'payment_method',
                $request->payment_method
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Payment Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('payment_status')) {
            $query->where(
                'payment_status',
                $request->payment_status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */

        $orders = $query
            ->latest()
            ->paginate(20);

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total' =>
                Order::count(),

            'pending' =>
                Order::where(
                    'status',
                    'pending'
                )->count(),

            'processing' =>
                Order::where(
                    'status',
                    'processing'
                )->count(),

            'shipped' =>
                Order::where(
                    'status',
                    'shipped'
                )->count(),

            'delivered' =>
                Order::where(
                    'status',
                    'delivered'
                )->count(),

            'cancelled' =>
                Order::where(
                    'status',
                    'cancelled'
                )->count(),
        ];

        return response()->json([
            'success' => true,

            'data' => $orders,

            'summary' => $summary,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Order Details
    |--------------------------------------------------------------------------
    */

    public function show(Order $order)
    {
        $order->load([
            'user:id,name,email,status',
            'items',
        ]);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Order Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        Request $request,
        Order $order
    ) {
        $validated = $request->validate([
            'status' => [
                'required',

                Rule::in([
                    'pending',
                    'processing',
                    'shipped',
                    'delivered',
                    'cancelled',
                ]),
            ],
        ]);

        $newStatus =
            $validated['status'];

        try {

            $updatedOrder =
                DB::transaction(
                    function () use (
                        $order,
                        $newStatus,
                        $request
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Lock Order
                        |--------------------------------------------------------------------------
                        */

                        $lockedOrder =
                            Order::where(
                                'id',
                                $order->id
                            )
                                ->lockForUpdate()
                                ->firstOrFail();

                        $lockedOrder->load(
                            'items'
                        );

                        $currentStatus =
                            $lockedOrder->status;

                        if (
                            $currentStatus ===
                            $newStatus
                        ) {
                            return $lockedOrder;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Final Orders Cannot Change
                        |--------------------------------------------------------------------------
                        */

                        if (
                            in_array(
                                $currentStatus,
                                [
                                    'delivered',
                                    'cancelled',
                                ],
                                true
                            )
                        ) {
                            throw new \RuntimeException(
                                'This order is already completed and its status cannot be changed.'
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Allowed Transitions
                        |--------------------------------------------------------------------------
                        */

                        $allowedTransitions = [

                            'pending' => [
                                'processing',
                                'cancelled',
                            ],

                            'processing' => [
                                'shipped',
                                'cancelled',
                            ],

                            'shipped' => [
                                'delivered',
                            ],
                        ];

                        $allowed =
                            $allowedTransitions[
                                $currentStatus
                            ]
                            ?? [];

                        if (
                            !in_array(
                                $newStatus,
                                $allowed,
                                true
                            )
                        ) {
                            throw new \RuntimeException(
                                "Order cannot be changed from {$currentStatus} to {$newStatus}."
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Cancellation
                        |--------------------------------------------------------------------------
                        */

                        if ($newStatus === 'cancelled') {

                            foreach (
                                $lockedOrder->items as $item
                            ) {

                                if (
                                    !$item->product_variant_id
                                ) {
                                    continue;
                                }

                                $inventory =
                                    Inventory::where(
                                        'product_variant_id',
                                        $item->product_variant_id
                                    )
                                        ->lockForUpdate()
                                        ->first();

                                if (!$inventory) {
                                    continue;
                                }

                                $releaseQuantity =
                                    min(
                                        (int) $item->quantity,
                                        (int) $inventory
                                            ->reserved_quantity
                                    );

                                if (
                                    $releaseQuantity <= 0
                                ) {
                                    continue;
                                }

                                /*
                                |--------------------------------------------------------------------------
                                | Available before release
                                |--------------------------------------------------------------------------
                                */

                                $beforeAvailable =
                                    max(
                                        0,
                                        $inventory->quantity
                                        -
                                        $inventory
                                            ->reserved_quantity
                                    );

                                /*
                                |--------------------------------------------------------------------------
                                | Release reservation
                                |--------------------------------------------------------------------------
                                */

                                $inventory->update([
                                    'reserved_quantity' =>
                                        max(
                                            0,
                                            $inventory
                                                ->reserved_quantity
                                            -
                                            $releaseQuantity
                                        ),
                                ]);

                                /*
                                |--------------------------------------------------------------------------
                                | Available after release
                                |--------------------------------------------------------------------------
                                */

                                $afterAvailable =
                                    max(
                                        0,
                                        $inventory->quantity
                                        -
                                        $inventory
                                            ->reserved_quantity
                                    );

                                /*
                                |--------------------------------------------------------------------------
                                | History
                                |--------------------------------------------------------------------------
                                */

                                InventoryMovement::create([
                                    'product_variant_id' =>
                                        $item->product_variant_id,

                                    'user_id' =>
                                        $request->user()?->id,

                                    'type' =>
                                        'order_cancelled',

                                    'quantity' =>
                                        $releaseQuantity,

                                    'before_quantity' =>
                                        $beforeAvailable,

                                    'after_quantity' =>
                                        $afterAvailable,

                                    'reference_type' =>
                                        'order',

                                    'reference_id' =>
                                        $lockedOrder->id,

                                    'notes' =>
                                        "Reservation released for cancelled order {$lockedOrder->order_number}",
                                ]);
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Delivery
                        |--------------------------------------------------------------------------
                        */

                        if ($newStatus === 'delivered') {

                            foreach (
                                $lockedOrder->items as $item
                            ) {

                                if (
                                    !$item->product_variant_id
                                ) {
                                    continue;
                                }

                                $inventory =
                                    Inventory::where(
                                        'product_variant_id',
                                        $item->product_variant_id
                                    )
                                        ->lockForUpdate()
                                        ->first();

                                if (!$inventory) {
                                    throw new \RuntimeException(
                                        "Inventory is missing for {$item->product_name}."
                                    );
                                }

                                $quantity =
                                    (int) $item->quantity;

                                if (
                                    $inventory
                                        ->reserved_quantity
                                    <
                                    $quantity
                                ) {
                                    throw new \RuntimeException(
                                        "Reserved inventory is inconsistent for {$item->product_name}."
                                    );
                                }

                                if (
                                    $inventory->quantity
                                    <
                                    $quantity
                                ) {
                                    throw new \RuntimeException(
                                        "Physical stock is insufficient for {$item->product_name}."
                                    );
                                }

                                /*
                                |--------------------------------------------------------------------------
                                | Physical quantity before delivery
                                |--------------------------------------------------------------------------
                                */

                                $beforeQuantity =
                                    (int)
                                    $inventory->quantity;

                                /*
                                |--------------------------------------------------------------------------
                                | Deduct physical + reserved
                                |--------------------------------------------------------------------------
                                */

                                $inventory->update([
                                    'quantity' =>
                                        $inventory->quantity
                                        -
                                        $quantity,

                                    'reserved_quantity' =>
                                        $inventory
                                            ->reserved_quantity
                                        -
                                        $quantity,
                                ]);

                                $afterQuantity =
                                    (int)
                                    $inventory->quantity;

                                /*
                                |--------------------------------------------------------------------------
                                | Stock Movement
                                |--------------------------------------------------------------------------
                                */

                                InventoryMovement::create([
                                    'product_variant_id' =>
                                        $item->product_variant_id,

                                    'user_id' =>
                                        $request->user()?->id,

                                    'type' =>
                                        'order_delivered',

                                    'quantity' =>
                                        -$quantity,

                                    'before_quantity' =>
                                        $beforeQuantity,

                                    'after_quantity' =>
                                        $afterQuantity,

                                    'reference_type' =>
                                        'order',

                                    'reference_id' =>
                                        $lockedOrder->id,

                                    'notes' =>
                                        "Stock deducted for delivered order {$lockedOrder->order_number}",
                                ]);
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Update Order
                        |--------------------------------------------------------------------------
                        */

                        $updates = [
                            'status' => $newStatus,
                        ];

                        if ($newStatus === 'shipped') {
                            $updates['shipped_at'] = now();
                        }

                        if ($newStatus === 'delivered') {
                            $updates['delivered_at'] = now();
                        }

                        if ($newStatus === 'cancelled') {
                            $updates['cancelled_at'] = now();
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | COD becomes paid when delivered
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $newStatus ===
                                'delivered'
                            &&
                            $lockedOrder
                                ->payment_method ===
                                'cod'
                        ) {
                            $updates[
                                'payment_status'
                            ] = 'paid';
                        }

                        $lockedOrder->update(
                            $updates
                        );

                        return $lockedOrder;

                    },
                    3
                );

        } catch (\Throwable $exception) {

            return response()->json([
                'success' => false,

                'message' =>
                    $exception->getMessage(),
            ], 422);
        }

        $updatedOrder->load([
            'user:id,name,email,status',
            'items',
        ]);

        if ($updatedOrder->user?->email) {
            $emailService = app(OrderEmailService::class);
            app()->terminating(function () use ($emailService, $updatedOrder) {
                try {
                    $emailService->sendStatus($updatedOrder);
                } catch (\Throwable $mailException) {
                    logger()->warning('Order status email could not be sent with PHP mail().', [
                        'order_id' => $updatedOrder->id,
                        'error' => $mailException->getMessage(),
                    ]);
                }
            });
        }

        return response()->json([
            'success' => true,

            'message' =>
                'Order status updated successfully.',

            'data' =>
                $updatedOrder,
        ]);
    }
    public function updateShipping(Request $request, Order $order)
    {
        $validated = $request->validate([
            'courier_name' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
        ]);

        $order->update($validated);

        $order->load(['user:id,name,email,status', 'items']);

        if ($order->user?->email) {
            $emailService = app(OrderEmailService::class);
            app()->terminating(function () use ($emailService, $order) {
                try {
                    $emailService->sendStatus($order);
                } catch (\Throwable $mailException) {
                    logger()->warning('Shipping email could not be sent with PHP mail().', [
                        'order_id' => $order->id,
                        'error' => $mailException->getMessage(),
                    ]);
                }
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Shipping details updated successfully.',
            'data' => $order,
        ]);
    }

}
