<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Revenue
        |--------------------------------------------------------------------------
        |
        | Revenue only counts orders whose payment status is paid.
        | COD becomes paid after delivery in your current workflow.
        |
        */

        $totalRevenue =
            Order::where(
                'payment_status',
                'paid'
            )
                ->where(
                    'status',
                    '!=',
                    'cancelled'
                )
                ->sum(
                    'total_amount'
                );

        /*
        |--------------------------------------------------------------------------
        | Order Counts
        |--------------------------------------------------------------------------
        */

        $totalOrders =
            Order::count();

        $pendingOrders =
            Order::where(
                'status',
                'pending'
            )->count();

        $processingOrders =
            Order::where(
                'status',
                'processing'
            )->count();

        $shippedOrders =
            Order::where(
                'status',
                'shipped'
            )->count();

        $deliveredOrders =
            Order::where(
                'status',
                'delivered'
            )->count();

        $cancelledOrders =
            Order::where(
                'status',
                'cancelled'
            )->count();

        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        $totalCustomers =
            User::where(
                'role',
                'customer'
            )->count();

        $activeCustomers =
            User::where(
                'role',
                'customer'
            )
                ->where(
                    'status',
                    'active'
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | Low Stock
        |--------------------------------------------------------------------------
        |
        | Available stock:
        |
        | quantity - reserved_quantity
        |
        */

        $lowStockQuery =
            Inventory::query()
                ->whereRaw(
                    'GREATEST(quantity - reserved_quantity, 0) <= low_stock_limit'
                );

        $lowStockCount =
            (clone $lowStockQuery)
                ->count();

        $lowStockItems =
            $lowStockQuery
                ->with([
                    'variant.product.primaryImage',
                    'variant.size',
                    'variant.color',
                ])
                ->orderByRaw(
                    'GREATEST(quantity - reserved_quantity, 0) ASC'
                )
                ->limit(8)
                ->get()
                ->map(
                    function ($inventory) {

                        $variant =
                            $inventory->variant;

                        $available =
                            max(
                                0,
                                (int)
                                $inventory->quantity
                                -
                                (int)
                                $inventory
                                    ->reserved_quantity
                            );

                        return [
                            'inventory_id' =>
                                $inventory->id,

                            'variant_id' =>
                                $variant?->id,

                            'product_name' =>
                                $variant
                                    ?->product
                                    ?->name,

                            'product_slug' =>
                                $variant
                                    ?->product
                                    ?->slug,

                            'image' =>
                                $variant
                                    ?->product
                                    ?->primaryImage
                                    ?->image,

                            'sku' =>
                                $variant?->sku,

                            'size' =>
                                $variant
                                    ?->size
                                    ? (
                                        $variant
                                            ->size
                                            ->display_name
                                        ??
                                        $variant
                                            ->size
                                            ->name
                                    )
                                    : null,

                            'color' =>
                                $variant
                                    ?->color
                                    ? (
                                        $variant
                                            ->color
                                            ->display_name
                                        ??
                                        $variant
                                            ->color
                                            ->name
                                    )
                                    : null,

                            'quantity' =>
                                (int)
                                $inventory->quantity,

                            'reserved_quantity' =>
                                (int)
                                $inventory
                                    ->reserved_quantity,

                            'available_quantity' =>
                                $available,

                            'low_stock_limit' =>
                                (int)
                                $inventory
                                    ->low_stock_limit,
                        ];
                    }
                );

        /*
        |--------------------------------------------------------------------------
        | Recent Orders
        |--------------------------------------------------------------------------
        */

        $recentOrders =
            Order::with([
                'user:id,name,email',
            ])
                ->withCount(
                    'items'
                )
                ->latest()
                ->limit(8)
                ->get([
                    'id',
                    'user_id',
                    'order_number',
                    'status',
                    'payment_method',
                    'payment_status',
                    'total_amount',
                    'created_at',
                ]);

        /*
        |--------------------------------------------------------------------------
        | Best Selling Products
        |--------------------------------------------------------------------------
        |
        | Only delivered orders count as completed product sales.
        |
        */

        $bestSelling =
            OrderItem::query()
                ->join(
                    'orders',
                    'orders.id',
                    '=',
                    'order_items.order_id'
                )
                ->where(
                    'orders.status',
                    'delivered'
                )
                ->select([
                    'order_items.product_id',
                    'order_items.product_name',
                    'order_items.image',

                    DB::raw(
                        'SUM(order_items.quantity) as total_quantity'
                    ),

                    DB::raw(
                        'SUM(order_items.line_total) as total_sales'
                    ),
                ])
                ->groupBy(
                    'order_items.product_id',
                    'order_items.product_name',
                    'order_items.image'
                )
                ->orderByDesc(
                    'total_quantity'
                )
                ->limit(5)
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Last 7 Days Sales
        |--------------------------------------------------------------------------
        */

        $startDate =
            now()
                ->startOfDay()
                ->subDays(6);

        $salesRows =
            Order::query()
                ->where(
                    'payment_status',
                    'paid'
                )
                ->where(
                    'status',
                    '!=',
                    'cancelled'
                )
                ->where(
                    'created_at',
                    '>=',
                    $startDate
                )
                ->selectRaw(
                    'DATE(created_at) as sale_date'
                )
                ->selectRaw(
                    'SUM(total_amount) as revenue'
                )
                ->selectRaw(
                    'COUNT(*) as orders_count'
                )
                ->groupBy(
                    'sale_date'
                )
                ->orderBy(
                    'sale_date'
                )
                ->get()
                ->keyBy(
                    'sale_date'
                );

        $salesChart = [];

        for (
            $i = 0;
            $i < 7;
            $i++
        ) {
            $date =
                Carbon::parse(
                    $startDate
                )
                    ->addDays(
                        $i
                    );

            $dateKey =
                $date->format(
                    'Y-m-d'
                );

            $row =
                $salesRows->get(
                    $dateKey
                );

            $salesChart[] = [
                'date' =>
                    $dateKey,

                'label' =>
                    $date->format(
                        'D'
                    ),

                'revenue' =>
                    $row
                        ? (float)
                            $row->revenue
                        : 0,

                'orders' =>
                    $row
                        ? (int)
                            $row
                                ->orders_count
                        : 0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Today's Stats
        |--------------------------------------------------------------------------
        */

        $todayOrders =
            Order::whereDate(
                'created_at',
                today()
            )->count();

        $todayRevenue =
            Order::whereDate(
                'created_at',
                today()
            )
                ->where(
                    'payment_status',
                    'paid'
                )
                ->where(
                    'status',
                    '!=',
                    'cancelled'
                )
                ->sum(
                    'total_amount'
                );

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'data' => [
                'summary' => [
                    'total_revenue' =>
                        (float)
                        $totalRevenue,

                    'total_orders' =>
                        $totalOrders,

                    'pending_orders' =>
                        $pendingOrders,

                    'processing_orders' =>
                        $processingOrders,

                    'shipped_orders' =>
                        $shippedOrders,

                    'delivered_orders' =>
                        $deliveredOrders,

                    'cancelled_orders' =>
                        $cancelledOrders,

                    'total_customers' =>
                        $totalCustomers,

                    'active_customers' =>
                        $activeCustomers,

                    'low_stock_count' =>
                        $lowStockCount,

                    'today_orders' =>
                        $todayOrders,

                    'today_revenue' =>
                        (float)
                        $todayRevenue,
                ],

                'sales_chart' =>
                    $salesChart,

                'recent_orders' =>
                    $recentOrders,

                'low_stock_items' =>
                    $lowStockItems,

                'best_selling_products' =>
                    $bestSelling,
            ],
        ]);
    }
}