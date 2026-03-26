<?php

namespace Database\Factories;

use App\Models\Clipart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Clipart>
 *
 * Creates clipart items for the design workspace.
 * Generates unique names from a predefined list with a random suffix,
 * and creates a corresponding image reference path.
 */
class ClipartFactory extends Factory
{
    protected $model = Clipart::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $names = [
            'Star', 'Heart', 'Arrow', 'Flower', 'Sun', 'Moon', 'Crown',
            'Trophy', 'Balloon', 'Gift', 'Cake', 'Leaf', 'Tree', 'Banner',
        ];

        return [
            'clipart_name'    => $this->faker->unique()->randomElement($names)
                . '_' . $this->faker->lexify('???'),
            'image_reference' => 'clipart/' . $this->faker->lexify('????????') . '.png',
        ];
    }
}