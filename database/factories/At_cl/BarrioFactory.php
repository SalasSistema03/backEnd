<?php

namespace Database\Factories\At_cl;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\At_cl\Barrio>
 */
class BarrioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Define ciudades aleatorias para el campo nombre de la tabla Barrio
        return [
          /*   'name' => fake()->city(), */
        ];
    }
}
