<?php

namespace Database\Factories\At_cl;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\At_cl\HistorialFechas;
use App\Models\At_cl\Propiedad;

class HistorialFechasFactory extends Factory
{
    protected $model = HistorialFechas::class;

    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        $fechaAlta = $this->faker->dateTimeBetween('-2 years', 'now');
        $fechaBaja = $this->faker->dateTimeBetween($fechaAlta, 'now');

        return [
            'propiedad_id' => Propiedad::inRandomOrder()->first()->id ?? Propiedad::factory()->create()->id,
            'fecha_de_alta' => $fechaAlta,
            'fecha_de_baja' => $fechaBaja,
        ];
    }
}
