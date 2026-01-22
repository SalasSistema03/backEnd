<?php

namespace Database\Factories\At_cl;
use Illuminate\Database\Eloquent\Factories\Factory;

class AtclVistaPropiedadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\At_cl\AtclVistaPropiedad::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            /* 'btn_propietario' => $this->faker->boolean(),
            'btn_documentacion' => $this->faker->boolean(), */
            /* 'btn_novedades_venta_guardar' => $this->faker->boolean(),
            'btn_novedades_alquiler_guardar' => $this->faker->boolean(),
            'btn_modificar' => $this->faker->boolean(), */
        ];
    }
}
