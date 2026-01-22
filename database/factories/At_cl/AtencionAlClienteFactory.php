<?php

namespace Database\Factories\At_cl;

use App\Models\At_cl\AtclVistaPropiedad;
use App\Models\At_cl\AtclVistaPropiedadBusqueda;
use App\Models\At_cl\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class AtencionAlClienteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
 

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
           /*  'id_usuario' => Usuario::inRandomOrder()->first()->id, 
            'atcl_vista_propiedad' => AtclVistaPropiedad::factory(), // Crea una vista de propiedad aleatoria
            'atcl_vista_propiedad_busqueda' => AtclVistaPropiedadBusqueda::factory(), // Crea una vista de propiedad busqueda aleatoria */
        ];
    }
}
