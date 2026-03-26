<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\ProductType;
use App\Models\Customer;
use App\Models\CustomizablePrintProduct;
use App\Models\StandardProduct;
use App\Models\WishlistItem;
use App\Services\WishlistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Unit tests for WishlistService.
 *
 * Covers retrieving a wishlist, adding items (standard and customizable),
 * duplicate prevention, product existence checks, removing items,
 * and checking whether a product is in the wishlist.
 */
class WishlistServiceTest extends TestCase
{
    use RefreshDatabase;

    private WishlistService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WishlistService();
    }

    // -------------------------------------------------------------------------
    // getWishlist()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns all wishlist items belonging to the customer. */
    public function get_wishlist_returns_all_items_for_customer(): void
    {
        $customer = Customer::factory()->create();
        WishlistItem::factory()->count(3)->create(['customer_id' => $customer->customer_id]);

        $items = $this->service->getWishlist($customer->customer_id);

        $this->assertCount(3, $items);
    }

    #[Test]
    /** Returns an empty collection when the customer has no wishlist items. */
    public function get_wishlist_returns_empty_when_customer_has_no_items(): void
    {
        $customer = Customer::factory()->create();
        $items    = $this->service->getWishlist($customer->customer_id);
        $this->assertCount(0, $items);
    }

    #[Test]
    /** Excludes items belonging to other customers. */
    public function get_wishlist_returns_only_items_belonging_to_customer(): void
    {
        $customer = Customer::factory()->create();
        $other    = Customer::factory()->create();

        WishlistItem::factory()->create(['customer_id' => $customer->customer_id]);
        WishlistItem::factory()->create(['customer_id' => $other->customer_id]);

        $items = $this->service->getWishlist($customer->customer_id);
        $this->assertCount(1, $items);
    }

    #[Test]
    /** Items referencing inactive products are deleted from the database and excluded from the result. */
    public function get_wishlist_prunes_and_excludes_items_for_inactive_products(): void
    {
        $customer       = Customer::factory()->create();
        $activeProduct  = StandardProduct::factory()->create();
        $inactiveProduct = StandardProduct::factory()->inactive()->create();

        WishlistItem::factory()->create([
            'customer_id'  => $customer->customer_id,
            'product_id'   => $activeProduct->product_id,
            'product_type' => ProductType::Standard,
        ]);
        $staleItem = WishlistItem::factory()->create([
            'customer_id'  => $customer->customer_id,
            'product_id'   => $inactiveProduct->product_id,
            'product_type' => ProductType::Standard,
        ]);

        $items = $this->service->getWishlist($customer->customer_id);

        $this->assertCount(1, $items);
        $this->assertDatabaseMissing('wishlist_items', ['wishlist_item_id' => $staleItem->wishlist_item_id]);
    }

    // -------------------------------------------------------------------------
    // addToWishlist()
    // -------------------------------------------------------------------------

    #[Test]
    /** Adds a standard product to the wishlist. */
    public function add_to_wishlist_adds_standard_product(): void
    {
        $customer = Customer::factory()->create();
        $product  = StandardProduct::factory()->create();

        $item = $this->service->addToWishlist(
            $customer->customer_id,
            $product->product_id,
            ProductType::Standard,
        );

        $this->assertInstanceOf(WishlistItem::class, $item);
        $this->assertDatabaseHas('wishlist_items', [
            'customer_id'  => $customer->customer_id,
            'product_id'   => $product->product_id,
            'product_type' => ProductType::Standard->value,
        ]);
    }

    #[Test]
    /** Adds a customizable product to the wishlist. */
    public function add_to_wishlist_adds_customizable_product(): void
    {
        $customer = Customer::factory()->create();
        $product  = CustomizablePrintProduct::factory()->create();

        $item = $this->service->addToWishlist(
            $customer->customer_id,
            $product->product_id,
            ProductType::Customizable,
        );

        $this->assertEquals(ProductType::Customizable, $item->product_type);
    }

    #[Test]
    /** Throws ValidationException when the product is already in the wishlist. */
    public function add_to_wishlist_throws_when_product_already_wishlisted(): void
    {
        $customer = Customer::factory()->create();
        $product  = StandardProduct::factory()->create();

        WishlistItem::factory()->create([
            'customer_id'  => $customer->customer_id,
            'product_id'   => $product->product_id,
            'product_type' => ProductType::Standard,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->addToWishlist($customer->customer_id, $product->product_id, ProductType::Standard);
    }

    #[Test]
    /** Throws ValidationException when the product does not exist. */
    public function add_to_wishlist_throws_when_product_does_not_exist(): void
    {
        $customer = Customer::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->addToWishlist($customer->customer_id, 99999, ProductType::Standard);
    }

    #[Test]
    /** Throws ValidationException when the product is inactive. */
    public function add_to_wishlist_throws_when_product_is_inactive(): void
    {
        $customer = Customer::factory()->create();
        $product  = StandardProduct::factory()->inactive()->create();

        $this->expectException(ValidationException::class);
        $this->service->addToWishlist($customer->customer_id, $product->product_id, ProductType::Standard);
    }

    #[Test]
    /** The same product ID is allowed when the product type differs. */
    public function add_to_wishlist_allows_same_id_with_different_product_type(): void
    {
        $customer = Customer::factory()->create();
        $standard = StandardProduct::factory()->create();
        $custom   = CustomizablePrintProduct::factory()->create();

        $this->service->addToWishlist($customer->customer_id, $standard->product_id, ProductType::Standard);
        WishlistItem::factory()->create([
            'customer_id'  => $customer->customer_id,
            'product_id'   => $custom->product_id,
            'product_type' => ProductType::Customizable,
        ]);

        $items = $this->service->getWishlist($customer->customer_id);
        $this->assertCount(2, $items);
    }

    // -------------------------------------------------------------------------
    // removeFromWishlist()
    // -------------------------------------------------------------------------

    #[Test]
    /** Removes an item from the wishlist. */
    public function remove_from_wishlist_deletes_item(): void
    {
        $customer = Customer::factory()->create();
        $item     = WishlistItem::factory()->create(['customer_id' => $customer->customer_id]);

        $this->service->removeFromWishlist($customer->customer_id, $item->wishlist_item_id);

        $this->assertDatabaseMissing('wishlist_items', ['wishlist_item_id' => $item->wishlist_item_id]);
    }

    #[Test]
    /** Throws ValidationException when the item belongs to a different customer. */
    public function remove_from_wishlist_throws_when_item_belongs_to_different_customer(): void
    {
        $owner    = Customer::factory()->create();
        $intruder = Customer::factory()->create();
        $item     = WishlistItem::factory()->create(['customer_id' => $owner->customer_id]);

        $this->expectException(ValidationException::class);
        $this->service->removeFromWishlist($intruder->customer_id, $item->wishlist_item_id);
    }

    #[Test]
    /** Throws ValidationException when the item does not exist. */
    public function remove_from_wishlist_throws_when_item_does_not_exist(): void
    {
        $customer = Customer::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->removeFromWishlist($customer->customer_id, 99999);
    }

    // -------------------------------------------------------------------------
    // isInWishlist()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns true when the product is in the customer's wishlist. */
    public function is_in_wishlist_returns_true_when_product_is_in_wishlist(): void
    {
        $customer = Customer::factory()->create();
        $product  = StandardProduct::factory()->create();

        WishlistItem::factory()->create([
            'customer_id'  => $customer->customer_id,
            'product_id'   => $product->product_id,
            'product_type' => ProductType::Standard,
        ]);

        $this->assertTrue(
            $this->service->isInWishlist($customer->customer_id, $product->product_id, ProductType::Standard)
        );
    }

    #[Test]
    /** Returns false when the product is not in the customer's wishlist. */
    public function is_in_wishlist_returns_false_when_product_is_not_in_wishlist(): void
    {
        $customer = Customer::factory()->create();
        $product  = StandardProduct::factory()->create();

        $this->assertFalse(
            $this->service->isInWishlist($customer->customer_id, $product->product_id, ProductType::Standard)
        );
    }

    #[Test]
    /** Returns false when the same product ID exists under a different product type. */
    public function is_in_wishlist_returns_false_when_product_type_differs(): void
    {
        $customer = Customer::factory()->create();
        $product  = StandardProduct::factory()->create();

        WishlistItem::factory()->create([
            'customer_id'  => $customer->customer_id,
            'product_id'   => $product->product_id,
            'product_type' => ProductType::Standard,
        ]);

        $this->assertFalse(
            $this->service->isInWishlist($customer->customer_id, $product->product_id, ProductType::Customizable)
        );
    }
}