<?php

namespace App\Http\Controllers\Customer;

use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddToWishlistRequest;
use App\Models\WishlistItem;
use App\Services\WishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    public function __construct(private WishlistService $wishlistService) {}

    public function index(): Response
    {
        $customerId = Auth::guard('customer')->id();

        $wishlist = $this->wishlistService
            ->getWishlist($customerId)
            ->map(function (WishlistItem $item) {
                $product = $item->product();

                return [
                    'wishlist_item_id' => $item->wishlist_item_id,
                    'product_id' => $item->product_id,
                    'product_type' => $item->product_type->value,
                    'date_added' => optional($item->date_added)?->format('Y-m-d H:i:s'),
                    'product' => $product ? [
                        'product_id' => $product->product_id,
                        'product_name' => $product->product_name,
                        'description' => $product->description,
                        'image_reference' => $product->image_reference,
                        'display_price' => property_exists($product, 'display_price') ? $product->display_price : null,
                    ] : null,
                ];
            })
            ->values();

        return Inertia::render('Customer/Account/Wishlist', [
            'wishlist' => $wishlist,
        ]);
    }

    public function store(AddToWishlistRequest $request): RedirectResponse
    {
        $customerId = Auth::guard('customer')->id();
        $data = $request->validated();

        $this->wishlistService->addToWishlist(
            $customerId,
            (int) $data['product_id'],
            ProductType::from($data['product_type'])
        );

        return back()->with('success', 'Item added to wishlist.');
    }

    public function destroy(WishlistItem $wishlistItem): RedirectResponse
    {
        $customerId = Auth::guard('customer')->id();

        $this->wishlistService->removeFromWishlist(
            $customerId,
            $wishlistItem->wishlist_item_id
        );

        return back()->with('success', 'Item removed from wishlist.');
    }
}