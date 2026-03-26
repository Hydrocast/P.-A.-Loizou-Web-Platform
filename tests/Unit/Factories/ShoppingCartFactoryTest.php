<?php

namespace Tests\Unit\Factories;

use PHPUnit\Framework\Attributes\Test;
use App\Models\ShoppingCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for ShoppingCartFactory.
 *
 * Covers the default state only.
 * One cart per customer is enforced by a unique constraint on customer_id.
 */
class ShoppingCartFactoryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Default state
    // -------------------------------------------------------------------------

    #[Test]
    /** Default state creates a ShoppingCart record. */
    public function default_state_creates_shopping_cart_record(): void
    {
        $cart = ShoppingCart::factory()->create();

        $this->assertInstanceOf(ShoppingCart::class, $cart);
        $this->assertDatabaseHas('shopping_carts', ['cart_id' => $cart->cart_id]);
    }

    #[Test]
    /** Default state creates a linked customer. */
    public function default_state_creates_a_linked_customer(): void
    {
        $cart = ShoppingCart::factory()->create();

        $this->assertNotNull($cart->customer_id);
        $this->assertDatabaseHas('customers', ['customer_id' => $cart->customer_id]);
    }

    #[Test]
    /** Default state sets a non-null last_updated timestamp. */
    public function default_state_sets_non_null_last_updated(): void
    {
        $cart = ShoppingCart::factory()->create();

        $this->assertNotNull($cart->last_updated);
    }
}