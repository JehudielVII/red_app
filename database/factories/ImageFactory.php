<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use app\Models\Image;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'url' => 'https://picsum.photos/id/' . $this->faker->numberBetween(1, 1000) . '/1090/800',
            'imageable_id' => $this->faker->randomDigitNotNull(),
            'imageable_type' => $this->faker->randomElement(['App\Models\product', 'App\Models\User']),
        ];
    }
}
