<?php

namespace Database\Factories\At_cl;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * 
 */
class TasacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            /* 'tasacion_pesos_venta' => $this->faker->randomFloat(2, 500, 5000),
            'tasacion_dolar_venta' => $this->faker->randomFloat(2, 500, 5000),
            /* 'tasacion_dolar_alquiler' => $this->faker->randomFloat(2, 500, 5000),
            'tasacion_pesos_alquiler' => $this->faker->randomFloat(2, 500, 5000), */
           /*  'fecha_tasacion' => $this->faker->dateTimeBetween('now', 'now')->format('Y-m-d'), */
            
            
          /*   'moneda' => array_rand(['$', 'u$s'], 1), */
           
        ];
    }
}
