<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Events\OrderPlaced;
use App\Models\CartItem;
use App\Models\CustomerOrder;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Orchestrates checkout review and order submission.
 *
 * This is the only place where pricing is calculated. The process has two steps:
 *   1. reviewCheckout() – reads cart, resolves tier prices, calculates VAT,
 *      returns a read-only summary.
 *   2. submitOrder() – validates contact data, creates order with frozen pricing,
 *      transfers cart items to order items, clears cart, and fires OrderPlaced.
 *
 * The database operations are atomic. After the transaction commits, the
 * OrderPlaced event triggers email dispatch via listeners.
 *
 * VAT calculation: VAT amount = total × (rate / (100 + rate))
 * All stored prices are VAT-inclusive.
 */
class CheckoutService
{
    public function __construct(
        private readonly PricingConfigurationService $pricingService,
        private readonly CartService $cartService,
    ) {}

    /**
     * Calculate pricing for the current cart and return a review summary.
     *
     * For each item, resolves the applicable tier, computes line subtotals,
     * then calculates cart total, VAT, and net amount.
     *
     * Returns array with keys: items, cart_total, vat_rate, vat_amount, net_amount.
     *
     * @throws ValidationException if cart empty or any item lacks a pricing tier
     */
    public function reviewCheckout(int $customerId): array
    {
        $cartItems = $this->cartService->getCartContents($customerId);

        if ($cartItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        $vatRate = (float) config('business.vat_rate');

        $resolvedItems = $cartItems->map(function (CartItem $item) {
            try {
                $tier = $this->pricingService->findTierForQuantity(
                    $item->product_id,
                    $item->quantity,
                );
            } catch (ValidationException) {
                $productName = $item->product?->product_name ?? "Product #{$item->product_id}";

                throw ValidationException::withMessages([
                    'cart' => "No pricing tier was found for \"{$productName}\" at quantity {$item->quantity}.",
                ]);
            }

            $item->resolved_unit_price = (float) $tier->unit_price;
            $item->resolved_line_subtotal = round($item->resolved_unit_price * $item->quantity, 2);

            return $item;
        });

        $cartTotal = round($resolvedItems->sum('resolved_line_subtotal'), 2);
        $totals = $this->calculateOrderTotals($cartTotal, $vatRate);

        return [
            'items' => $resolvedItems,
            'cart_total' => $totals['cart_total'],
            'vat_rate' => $totals['vat_rate'],
            'vat_amount' => $totals['vat_amount'],
            'net_amount' => $totals['net_amount'],
        ];
    }

    /**
     * Submit a confirmed order from the authenticated customer.
     *
     * Recalculates pricing for consistency, creates order with frozen values,
     * transfers cart items to order items, clears cart, and fires OrderPlaced.
     * All database changes are atomic.
     *
     * Newly submitted orders start in Pending status, meaning the order has
     * been placed successfully and is awaiting staff review.
     *
     * The event is dispatched after the transaction commits, ensuring listeners
     * operate on a fully persisted order.
     *
     * @throws ValidationException on validation or pricing errors
     */
    public function submitOrder(
        int $customerId,
        string $customerName,
        string $customerEmail,
        string $customerPhone,
    ): CustomerOrder {
        $this->validateCustomerData($customerName, $customerEmail, $customerPhone);

        $review = $this->reviewCheckout($customerId);

        $order = DB::transaction(function () use ($customerId, $customerName, $customerEmail, $customerPhone, $review) {
            $order = CustomerOrder::create([
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
                'order_creation_timestamp' => now(),
                'order_status' => OrderStatus::Pending,
                'total_amount' => $review['cart_total'],
                'vat_amount' => $review['vat_amount'],
                'net_amount' => $review['net_amount'],
                'vat_rate' => $review['vat_rate'],
            ]);

            foreach ($review['items'] as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->order_id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product->product_name,
                    'unit_price' => $cartItem->resolved_unit_price,
                    'quantity' => $cartItem->quantity,
                    'line_subtotal' => $cartItem->resolved_line_subtotal,
                    'design_snapshot' => $cartItem->design_snapshot,
                    'preview_image_reference' => $cartItem->preview_image_reference,
                    'print_file_reference' => $cartItem->print_file_reference,
                ]);
            }

            $this->cartService->clearCart($customerId);

            return $order;
        });

        OrderPlaced::dispatch($order);

        return $order;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function validateCustomerData(string $name, string $email, string $phone): void
    {
        $errors = [];

        $nameLength = mb_strlen(trim($name));
        if ($nameLength < 2 || $nameLength > 50) {
            $errors['customer_name'] = 'Full name must be between 2 and 50 characters.';
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['customer_email'] = 'Please provide a valid email address.';
        }

        if (! preg_match('/^\d{8}$/', $phone)) {
            $errors['customer_phone'] = 'Phone number must be exactly 8 numeric digits.';
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function calculateOrderTotals(float $cartTotal, float $vatRate): array
    {
        $vatAmount = round($cartTotal * ($vatRate / (100 + $vatRate)), 2);
        $netAmount = round($cartTotal - $vatAmount, 2);

        return [
            'cart_total' => $cartTotal,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'net_amount' => $netAmount,
        ];
    }
}
