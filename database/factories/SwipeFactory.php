<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SwipeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'direction' => $this->faker->randomElement(['left', 'right']),
        ];
    }
}
