<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitOrderRequest;
use App\Models\CustomerOrder;
use App\Services\CheckoutService;
use App\Support\DesignDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(private CheckoutService $checkoutService) {}

    public function review(): Response|RedirectResponse
    {
        $customerId = Auth::guard('customer')->id();

        try {
            $checkout = $this->checkoutService->reviewCheckout($customerId);
        } catch (ValidationException $e) {
            return redirect()->route('cart.index')
                ->with('error', collect($e->errors())->flatten()->first() ?? 'Unable to start checkout.');
        }

        return Inertia::render('Customer/Checkout/CheckoutReview', [
            'checkout' => [
                'items' => collect($checkout['items'])->map(function ($item) {
                    return [
                        'cart_item_id' => $item->cart_item_id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'resolved_unit_price' => $item->resolved_unit_price,
                        'resolved_line_subtotal' => $item->resolved_line_subtotal,
                        'shirt_color_label' => DesignDocument::extractShirtColorLabel($item->design_snapshot),
                        'print_sides_label' => DesignDocument::extractPrintSidesLabel($item->design_snapshot),
                        'product' => $item->product ? [
                            'product_name' => $item->product->product_name,
                        ] : null,
                    ];
                })->values(),
                'cart_total' => $checkout['cart_total'],
                'vat_rate' => $checkout['vat_rate'],
                'vat_amount' => $checkout['vat_amount'],
                'net_amount' => $checkout['net_amount'],
            ],
            'auth' => [
                'customer' => Auth::guard('customer')->user(),
            ],
        ]);
    }

    public function submit(SubmitOrderRequest $request): RedirectResponse
    {
        $customerId = Auth::guard('customer')->id();
        $data = $request->validated();

        $order = $this->checkoutService->submitOrder(
            $customerId,
            $data['customer_name'],
            $data['customer_email'],
            $data['customer_phone'],
        );

        return redirect()->route('checkout.confirmation', $order->order_id);
    }

    public function confirmation(CustomerOrder $order): Response
    {
        $this->authorize('viewOwn', $order);

        $order->load('items');

        return Inertia::render('Customer/Checkout/CheckoutConfirmation', [
            'order' => [
                'order_id' => $order->order_id,
                'order_creation_timestamp' => $order->order_creation_timestamp,
                'order_status' => $order->order_status,
                'total_amount' => $order->total_amount,
                'vat_amount' => $order->vat_amount,
                'net_amount' => $order->net_amount,
                'vat_rate' => $order->vat_rate,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'items' => $order->items->map(function ($item) {
                    return [
                        'order_item_id' => $item->order_item_id,
                        'product_name' => $item->product_name,
                        'unit_price' => $item->unit_price,
                        'quantity' => $item->quantity,
                        'line_subtotal' => $item->line_subtotal,
                        'shirt_color_label' => DesignDocument::extractShirtColorLabel($item->design_snapshot),
                        'print_sides_label' => DesignDocument::extractPrintSidesLabel($item->design_snapshot),
                    ];
                })->values(),
            ],
        ]);
    }
}