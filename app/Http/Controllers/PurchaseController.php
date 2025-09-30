<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Products;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
        $randomNumber = rand(0, 999);
        $orderId = 'ORD-' . str_pad($randomNumber, 3, '0', STR_PAD_LEFT);
        $total = 0;
        $orderItemsData = [];

        foreach ($request->items as $item) {
            $product = Products::find($item['product_id']);
            $quantity = $item['quantity'];
            $price = $product->price;
            $subtotal = $price * $quantity;

            $availableStock = $product->in_stock - $product->total_sold;
            if ($quantity > $availableStock) {
                return response()->json([
                    'message' => "Not enough stock for product: {$product->items}",
                    'available_stock' => $availableStock
                ], 400);
            }

            $product->total_sold += $quantity;
            $product->save();
            
            $orderItemsData[] = [
                'order_id' => $orderId,
                'product_id' => $product->id,
                'product_name' => $product->items,
                'item_price' => $price,
                'quantity' => $quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $total += $subtotal;
        }

        Order::create([
            'order_id' => $orderId,
            'total_amount' => $total
        ]);

        OrderItem::insert($orderItemsData);

        $responseItems = collect($orderItemsData)->map(function ($item) {
            return [
                'product_name' => $item['product_name'],
                'quantity' => $item['quantity'],
                'item_price' => $item['item_price']
            ];
        });

        return response()->json([
            'message' => 'Order placed successfully',
            'order_id' => $orderId,
            'items' => $responseItems,
            'total' => $total
        ]);
    }

    public function allOrders(Request $request)
    {
        $orders = Order::orderBy('created_at', 'desc')->paginate(10);

        $data = $orders->map(function ($order) {
            $items = OrderItem::where('order_id', $order->order_id)->get();

            return [
                'order_id' => $order->order_id,
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at->toDateTimeString(),
                'items' => $items->map(function ($item) {
                    return [
                        'product_name' => $item->product_name,
                        'item_price' => $item->item_price,
                        'quantity' => $item->quantity,
                    ];
                }),
            ];
        });

        return response()->json([
            'message' => 'All orders fetched successfully',
            'orders' => $data,
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ]);
    }

    public function todaysOrders()
    {
        $today = \Carbon\Carbon::today();

        $orders = Order::whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalProductCount = 0;
        $totalAmountMade = 0;

        $data = $orders->map(function ($order) use (&$totalProductCount, &$totalAmountMade) {
            $items = OrderItem::where('order_id', $order->order_id)->get();

            $formattedItems = $items->map(function ($item) use (&$totalProductCount) {
                $totalProductCount += $item->quantity;

                return [
                    'product_name' => $item->product_name,
                    'item_price' => $item->item_price,
                    'quantity' => $item->quantity,
                ];
            });
            $totalAmountMade += $order->total_amount;
            return [
                'order_id' => $order->order_id,
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at->toDateTimeString(),
                'items' => $formattedItems,
            ];
        });

        return response()->json([
            'message' => "Today's orders fetched successfully",
            'total_order' => $totalProductCount,
            'total_amount_made_today'    => $totalAmountMade,
            'orders' => $data,
        ]);
    }

    public function ordersByMonth(Request $request, $month, $year = null)
{
    // Ensure month is valid (1–12)
    if ($month < 1 || $month > 12) {
        return response()->json([
            'message' => 'Invalid month. Please provide a value between 1 and 12.'
        ], 400);
    }

    // If no year is passed, use current year
    $year = $year ?? Carbon::now()->year;

    // Fetch all orders in given month + year
    $orders = Order::whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->orderBy('created_at', 'desc')
        ->get();

    $totalProductCount = 0;
    $totalAmountMade   = 0;

    $data = $orders->map(function ($order) use (&$totalProductCount, &$totalAmountMade) {
        $items = OrderItem::where('order_id', $order->order_id)->get();

        $formattedItems = $items->map(function ($item) use (&$totalProductCount) {
            $totalProductCount += $item->quantity;

            return [
                'product_name' => $item->product_name,
                'item_price'   => $item->item_price,
                'quantity'     => $item->quantity,
            ];
        });

        $totalAmountMade += $order->total_amount;

        return [
            'order_id'     => $order->order_id,
            'total_amount' => $order->total_amount,
            'created_at'   => $order->created_at->toDateTimeString(),
            'items'        => $formattedItems,
        ];
    });

    return response()->json([
        'message'      => 'Orders fetched successfully',
        'month'        => Carbon::create()->month($month)->format('F'), // e.g. April
        'year'         => $year,
        'total_order'  => $totalProductCount,
        'total_amount' => $totalAmountMade,
        'orders'       => $data,
    ]);
}


}



