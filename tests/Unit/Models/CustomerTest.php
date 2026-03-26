<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\AccountStatus;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\SavedDesign;
use App\Models\ShoppingCart;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Unit tests for the Customer model.
 *
 * Covers model configuration, hidden field serialisation, authentication
 * configuration, relationship structure and data resolution, and business
 * logic for isActive() and isResetTokenValid().
 *
 * isResetTokenValid() boundary values:
 * - Expiry 1 second ago: false
 * - Expiry exactly now: true
 * - Expiry 1 second from now: true
 */
class CustomerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Model configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** Model uses the customers table. */
    public function model_uses_customers_table(): void
    {
        $customer = new Customer();

        $this->assertSame('customers', $customer->getTable());
    }

    #[Test]
    /** Primary key is customer_id. */
    public function primary_key_is_customer_id(): void
    {
        $customer = new Customer();

        $this->assertSame('customer_id', $customer->getKeyName());
    }

    #[Test]
    /** Primary key type is integer. */
    public function primary_key_type_is_integer(): void
    {
        $customer = new Customer();

        $this->assertSame('int', $customer->getKeyType());
    }

    #[Test]
    /** Primary key is auto-incrementing. */
    public function primary_key_is_auto_incrementing(): void
    {
        $customer = new Customer();

        $this->assertTrue($customer->incrementing);
    }

    #[Test]
    /** Timestamps are enabled. */
    public function timestamps_are_enabled(): void
    {
        $customer = new Customer();

        $this->assertTrue($customer->timestamps);
    }

    #[Test]
    /** Fillable array contains expected fields. */
    public function fillable_contains_expected_fields(): void
    {
        $customer = new Customer();
        $fillable = $customer->getFillable();

        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('full_name', $fillable);
        $this->assertContains('phone_number', $fillable);
        $this->assertContains('account_status', $fillable);
        $this->assertContains('reset_token', $fillable);
        $this->assertContains('reset_token_expiry', $fillable);
    }

    #[Test]
    /** account_status is cast to AccountStatus enum. */
    public function account_status_cast_is_configured(): void
    {
        $customer = new Customer();

        $this->assertSame(AccountStatus::class, $customer->getCasts()['account_status']);
    }

    #[Test]
    /** reset_token_expiry is cast to datetime. */
    public function reset_token_expiry_cast_is_configured(): void
    {
        $customer = new Customer();

        $this->assertSame('datetime', $customer->getCasts()['reset_token_expiry']);
    }

    // -------------------------------------------------------------------------
    // Hidden field serialisation
    // -------------------------------------------------------------------------

    #[Test]
    /** password is hidden from array serialisation. */
    public function password_is_hidden_from_array_serialisation(): void
    {
        $customer = Customer::factory()->create();
        $array = $customer->toArray();

        $this->assertArrayNotHasKey('password', $array);
    }

    #[Test]
    /** reset_token is hidden from array serialisation. */
    public function reset_token_is_hidden_from_array_serialisation(): void
    {
        $customer = Customer::factory()->withPendingReset()->create();
        $array = $customer->toArray();

        $this->assertArrayNotHasKey('reset_token', $array);
    }

    #[Test]
    /** reset_token_expiry is hidden from array serialisation. */
    public function reset_token_expiry_is_hidden_from_array_serialisation(): void
    {
        $customer = Customer::factory()->withPendingReset()->create();
        $array = $customer->toArray();

        $this->assertArrayNotHasKey('reset_token_expiry', $array);
    }

    // -------------------------------------------------------------------------
    // Authentication configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** getAuthIdentifierName returns customer_id. */
    public function get_auth_identifier_name_returns_customer_id(): void
    {
        $customer = new Customer();

        $this->assertSame('customer_id', $customer->getAuthIdentifierName());
    }

    // -------------------------------------------------------------------------
    // Relationship structure – cart()
    // -------------------------------------------------------------------------

    #[Test]
    /** cart() returns a HasOne relation. */
    public function cart_returns_has_one_relation(): void
    {
        $relation = (new Customer())->cart();

        $this->assertInstanceOf(HasOne::class, $relation);
    }

    #[Test]
    /** cart() uses customer_id as foreign key. */
    public function cart_uses_customer_id_as_foreign_key(): void
    {
        $relation = (new Customer())->cart();

        $this->assertSame('customer_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** cart() uses customer_id as local key. */
    public function cart_uses_customer_id_as_local_key(): void
    {
        $relation = (new Customer())->cart();

        $this->assertSame('customer_id', $relation->getLocalKeyName());
    }

    #[Test]
    /** cart() relates to ShoppingCart model. */
    public function cart_relates_to_shopping_cart_model(): void
    {
        $relation = (new Customer())->cart();

        $this->assertInstanceOf(ShoppingCart::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship structure – wishlistItems()
    // -------------------------------------------------------------------------

    #[Test]
    /** wishlistItems() returns a HasMany relation. */
    public function wishlist_items_returns_has_many_relation(): void
    {
        $relation = (new Customer())->wishlistItems();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    #[Test]
    /** wishlistItems() uses customer_id as foreign key. */
    public function wishlist_items_uses_customer_id_as_foreign_key(): void
    {
        $relation = (new Customer())->wishlistItems();

        $this->assertSame('customer_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** wishlistItems() relates to WishlistItem model. */
    public function wishlist_items_relates_to_wishlist_item_model(): void
    {
        $relation = (new Customer())->wishlistItems();

        $this->assertInstanceOf(WishlistItem::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship structure – savedDesigns()
    // -------------------------------------------------------------------------

    #[Test]
    /** savedDesigns() returns a HasMany relation. */
    public function saved_designs_returns_has_many_relation(): void
    {
        $relation = (new Customer())->savedDesigns();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    #[Test]
    /** savedDesigns() uses customer_id as foreign key. */
    public function saved_designs_uses_customer_id_as_foreign_key(): void
    {
        $relation = (new Customer())->savedDesigns();

        $this->assertSame('customer_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** savedDesigns() relates to SavedDesign model. */
    public function saved_designs_relates_to_saved_design_model(): void
    {
        $relation = (new Customer())->savedDesigns();

        $this->assertInstanceOf(SavedDesign::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship structure – orders()
    // -------------------------------------------------------------------------

    #[Test]
    /** orders() returns a HasMany relation. */
    public function orders_returns_has_many_relation(): void
    {
        $relation = (new Customer())->orders();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    #[Test]
    /** orders() uses customer_id as foreign key. */
    public function orders_uses_customer_id_as_foreign_key(): void
    {
        $relation = (new Customer())->orders();

        $this->assertSame('customer_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** orders() relates to CustomerOrder model. */
    public function orders_relates_to_customer_order_model(): void
    {
        $relation = (new Customer())->orders();

        $this->assertInstanceOf(CustomerOrder::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship data resolution
    // -------------------------------------------------------------------------

    #[Test]
    /** cart() resolves to the customer's shopping cart. */
    public function cart_resolves_to_customers_shopping_cart(): void
    {
        $customer = Customer::factory()->create();
        $cart = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);

        $resolved = $customer->cart;

        $this->assertInstanceOf(ShoppingCart::class, $resolved);
        $this->assertSame($cart->cart_id, $resolved->cart_id);
    }

    #[Test]
    /** cart() resolves to null when customer has no cart. */
    public function cart_resolves_to_null_when_no_cart_exists(): void
    {
        $customer = Customer::factory()->create();

        $this->assertNull($customer->cart);
    }

    #[Test]
    /** wishlistItems() resolves to all wishlist items belonging to the customer. */
    public function wishlist_items_resolves_to_customers_wishlist_items(): void
    {
        $customer = Customer::factory()->create();
        WishlistItem::factory()->count(3)->create(['customer_id' => $customer->customer_id]);

        $this->assertCount(3, $customer->wishlistItems);
        $customer->wishlistItems->each(
            fn ($item) => $this->assertSame($customer->customer_id, $item->customer_id)
        );
    }

    #[Test]
    /** wishlistItems() excludes items belonging to other customers. */
    public function wishlist_items_excludes_other_customers_items(): void
    {
        $customer = Customer::factory()->create();
        WishlistItem::factory()->count(2)->create(['customer_id' => $customer->customer_id]);
        WishlistItem::factory()->count(3)->create();

        $this->assertCount(2, $customer->wishlistItems);
    }

    #[Test]
    /** savedDesigns() resolves to all saved designs belonging to the customer. */
    public function saved_designs_resolves_to_customers_saved_designs(): void
    {
        $customer = Customer::factory()->create();
        SavedDesign::factory()->count(2)->create(['customer_id' => $customer->customer_id]);

        $this->assertCount(2, $customer->savedDesigns);
        $customer->savedDesigns->each(
            fn ($design) => $this->assertSame($customer->customer_id, $design->customer_id)
        );
    }

    #[Test]
    /** orders() resolves to all orders placed by the customer. */
    public function orders_resolves_to_customers_orders(): void
    {
        $customer = Customer::factory()->create();
        CustomerOrder::factory()->count(4)->create(['customer_id' => $customer->customer_id]);

        $this->assertCount(4, $customer->orders);
        $customer->orders->each(
            fn ($order) => $this->assertSame($customer->customer_id, $order->customer_id)
        );
    }

    #[Test]
    /** orders() excludes orders placed by other customers. */
    public function orders_excludes_other_customers_orders(): void
    {
        $customer = Customer::factory()->create();
        CustomerOrder::factory()->count(2)->create(['customer_id' => $customer->customer_id]);
        CustomerOrder::factory()->count(3)->create();

        $this->assertCount(2, $customer->orders);
    }

    // -------------------------------------------------------------------------
    // isActive()
    // -------------------------------------------------------------------------

    #[Test]
    /** isActive() returns true when account status is Active. */
    public function is_active_returns_true_for_active_account(): void
    {
        $customer = Customer::factory()->create();

        $this->assertTrue($customer->isActive());
    }

    #[Test]
    /** isActive() returns false when account status is Inactive. */
    public function is_active_returns_false_for_inactive_account(): void
    {
        $customer = Customer::factory()->inactive()->create();

        $this->assertFalse($customer->isActive());
    }

    // -------------------------------------------------------------------------
    // isResetTokenValid()
    // -------------------------------------------------------------------------

    #[Test]
    /** isResetTokenValid() returns false when no reset token exists. */
    public function is_reset_token_valid_returns_false_when_reset_token_is_null(): void
    {
        $customer = Customer::factory()->create();

        $this->assertFalse($customer->isResetTokenValid('any-token'));
    }

    #[Test]
    /** isResetTokenValid() returns false when token does not match. */
    public function is_reset_token_valid_returns_false_when_token_does_not_match(): void
    {
        $customer = Customer::factory()->withPendingReset()->create();

        $this->assertFalse($customer->isResetTokenValid('wrong-token'));
    }

    #[Test]
    /** isResetTokenValid() returns false when expiry is null. */
    public function is_reset_token_valid_returns_false_when_expiry_is_null(): void
    {
        $rawToken = 'reset-token-value';
        $customer = Customer::factory()->create([
            'reset_token' => Hash::make($rawToken),
            'reset_token_expiry' => null,
        ]);

        $this->assertFalse($customer->isResetTokenValid($rawToken));
    }

    #[Test]
    /** isResetTokenValid() returns false when expiry is in the past. */
    public function is_reset_token_valid_returns_false_when_expiry_is_one_second_ago(): void
    {
        $rawToken = 'reset-token-value';
        $customer = Customer::factory()->create([
            'reset_token' => Hash::make($rawToken),
            'reset_token_expiry' => now()->subSecond(),
        ]);

        $this->assertFalse($customer->isResetTokenValid($rawToken));
    }

    #[Test]
    /** isResetTokenValid() returns true when expiry is exactly now. */
    public function is_reset_token_valid_returns_true_when_expiry_is_exactly_now(): void
    {
        $this->travelTo(now()->startOfSecond());

        $rawToken = 'reset-token-value';
        $customer = Customer::factory()->create([
            'reset_token' => Hash::make($rawToken),
            'reset_token_expiry' => now(),
        ]);

        $this->assertTrue($customer->isResetTokenValid($rawToken));
    }

    #[Test]
    /** isResetTokenValid() returns true when expiry is in the future. */
    public function is_reset_token_valid_returns_true_when_expiry_is_one_second_from_now(): void
    {
        $rawToken = 'reset-token-value';
        $customer = Customer::factory()->create([
            'reset_token' => Hash::make($rawToken),
            'reset_token_expiry' => now()->addSecond(),
        ]);

        $this->assertTrue($customer->isResetTokenValid($rawToken));
    }

    #[Test]
    /** isResetTokenValid() returns true for a standard pending reset token. */
    public function is_reset_token_valid_returns_true_for_a_valid_pending_reset(): void
    {
        $rawToken = 'reset-token-value';
        $customer = Customer::factory()->create([
            'reset_token' => Hash::make($rawToken),
            'reset_token_expiry' => now()->addMinutes(60),
        ]);

        $this->assertTrue($customer->isResetTokenValid($rawToken));
    }
}