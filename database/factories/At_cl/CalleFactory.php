<?php

namespace Database\Factories\At_cl;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\At_cl\Calle>
 */
class CalleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Define nombre de calles aleatorias para el campo nombre de la tabla calle
        return [
            /* 'name' => fake()->streetName(), */
            
        ];
    }
}
