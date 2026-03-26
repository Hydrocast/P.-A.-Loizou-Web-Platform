<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Support\DesignDocument;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OrderHistoryController extends Controller
{
    public function index(): Response
    {
        $customerId = Auth::guard('customer')->id();

        $orders = CustomerOrder::with([
                'items' => function ($query) {
                    $query->orderBy('order_item_id');
                },
            ])
            ->where('customer_id', $customerId)
            ->orderByDesc('order_creation_timestamp')
            ->get();

        return Inertia::render('Customer/Account/OrderHistory', [
            'orders' => $orders->map(function ($order) {
                return [
                    'order_id' => $order->order_id,
                    'order_creation_timestamp' => $order->order_creation_timestamp,
                    'order_status' => $order->order_status,
                    'net_amount' => $order->net_amount,
                    'vat_amount' => $order->vat_amount,
                    'total_amount' => $order->total_amount,
                    'vat_rate' => $order->vat_rate,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'order_item_id' => $item->order_item_id,
                            'product_id' => $item->product_id,
                            'product_name' => $item->product_name,
                            'unit_price' => $item->unit_price,
                            'quantity' => $item->quantity,
                            'line_subtotal' => $item->line_subtotal,
                            'design_snapshot' => $item->design_snapshot,
                            'preview_image_reference' => $item->preview_image_reference,
                            'shirt_color_label' => DesignDocument::extractShirtColorLabel($item->design_snapshot),
                            'print_sides_label' => DesignDocument::extractPrintSidesLabel($item->design_snapshot),
                        ];
                    })->values(),
                ];
            })->values(),
        ]);
    }
}