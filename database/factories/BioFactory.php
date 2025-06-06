<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'gender'    => 'male',
            'age'       => $this->faker->numberBetween(18, 100),
            'job'       => $this->faker->jobTitle(),
            'company'   => $this->faker->company(),
            'education' => 'BSc',
            'about'     => $this->faker->sentences(3, true),
        ];
    }
}
