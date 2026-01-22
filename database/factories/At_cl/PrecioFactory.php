<?php

namespace Database\Factories\At_cl;

use App\Models\At_cl\Precio;
use App\Models\At_cl\Propiedad;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrecioFactory extends Factory
{
    protected $model = Precio::class;

    public function definition(): array
    {
        return [
            /* 'propiedad_id' => Propiedad::inRandomOrder()->value('id') ?? Propiedad::factory(), // Asegurar que tenga un ID válido
            'moneda_alquiler_dolar' => $this->faker->randomFloat(2, 500, 5000),
            'moneda_alquiler_pesos' => $this->faker->randomFloat(2, 500, 5000),
            'moneda_venta_pesos' => $this->faker->randomFloat(2, 500, 5000),
            'moneda_venta_dolar' => $this->faker->randomFloat(2, 500, 5000),
            'venta_fecha_alta' => $this->faker->date('Y-m-d'),
            'venta_fecha_baja' => $this->faker->date('Y-m-d'),
            'alquiler_fecha_alta' => $this->faker->date('Y-m-d'),
            'alquiler_fecha_baja' => $this->faker->date('Y-m-d'), */
        ];
    }
}



