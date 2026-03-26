<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddSavedDesignToCartRequest;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartQuantityRequest;
use App\Models\CartItem;
use App\Services\CartService;
use App\Support\DesignDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function index(): Response
    {
        $customerId = Auth::guard('customer')->id();

        $cartItems = $this->cartService->getCartContents($customerId);

        return Inertia::render('Customer/Cart/Cart', [
            'cartItems' => $cartItems->map(fn ($item) => [
                'cart_item_id' => $item->cart_item_id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'design_snapshot' => $item->design_snapshot,
                'preview_image_reference' => $item->preview_image_reference,
                'shirt_color_label' => DesignDocument::extractShirtColorLabel($item->design_snapshot),
                'print_sides_label' => DesignDocument::extractPrintSidesLabel($item->design_snapshot),
                'product' => $item->product ? [
                    'product_name' => $item->product->product_name,
                ] : null,
            ]),
        ]);
    }

    public function store(AddToCartRequest $request): RedirectResponse
    {
        $customerId = Auth::guard('customer')->id();
        $data = $request->validated();

        $storedDesignDocument = DesignDocument::encode(
            $data['design_data'],
            $data['customization_options'] ?? [],
        );

        $this->cartService->addToCart(
            $customerId,
            $data['product_id'],
            $data['quantity'],
            $storedDesignDocument,
            $data['preview_image_reference'] ?? null,
        );

        return redirect()->route('cart.index')
            ->with('success', 'Item added to cart.');
    }

    public function addFromDesign(AddSavedDesignToCartRequest $request): RedirectResponse
    {
        $customerId = Auth::guard('customer')->id();
        $data = $request->validated();

        $this->cartService->addSavedDesignToCart(
            $customerId,
            $data['design_id']
        );

        return redirect()->route('cart.index')
            ->with('success', 'Design added to cart.');
    }

    public function update(UpdateCartQuantityRequest $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorize('update', $cartItem);

        $customerId = Auth::guard('customer')->id();
        $data = $request->validated();

        $this->cartService->updateQuantity(
            $customerId,
            $cartItem->cart_item_id,
            $data['quantity']
        );

        return redirect()->route('cart.index')
            ->with('success', 'Cart updated.');
    }

    public function destroy(CartItem $cartItem): RedirectResponse
    {
        $this->authorize('delete', $cartItem);

        $customerId = Auth::guard('customer')->id();

        $this->cartService->removeFromCart(
            $customerId,
            $cartItem->cart_item_id
        );

        return redirect()->route('cart.index')
            ->with('success', 'Item removed from cart.');
    }
}