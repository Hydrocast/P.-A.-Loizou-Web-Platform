<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\ShoppingCart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShoppingCart>
 *
 * Creates shopping carts with valid default values.
 * Each cart is associated with a newly created customer.
 *
 * The unique constraint on customer_id enforces one cart per customer.
 * For tests needing a customer with a populated cart, use CartItemFactory
 * with an explicit cart_id instead of creating multiple carts.
 */
class ShoppingCartFactory extends Factory
{
    protected $model = ShoppingCart::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id'  => Customer::factory(),
            'last_updated' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}