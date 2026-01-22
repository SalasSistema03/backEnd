<?php

namespace Database\Factories\At_cl;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\At_cl\Localidad>
 */
class LocalidadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
             // Define nombre de ciudades aleatorias para el campo nombre de la tabla Localidad
            'name' => fake()->city(),
            
        ];
    }
}
