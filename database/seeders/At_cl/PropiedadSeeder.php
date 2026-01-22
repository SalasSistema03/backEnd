<?php

namespace Database\Seeders\At_cl;

use Illuminate\Database\Seeder;
use App\Models\At_cl\Propiedad;
use App\Models\At_cl\Barrio;
use App\Models\At_cl\Calle;
use App\Models\At_cl\Estado_general;
use Illuminate\Support\Facades\DB;
use App\Models\At_cl\Tipo_inmueble;
use App\Models\At_cl\Localidad;
use App\Models\At_cl\Provincia;
use App\Models\At_cl\Zona;
use App\Models\At_cl\Estado_alquiler;
use App\Models\At_cl\Estado_venta;
use App\Models\At_cl\Precio;
use App\Models\At_cl\Tasacion;

class PropiedadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Este método se encarga de crear registros de propiedad en la base de datos.
     * Para cada propiedad, se asignan valores aleatorios a las diferentes relaciones
     * y campos específicos, garantizando que cada propiedad generada tenga datos
     * de muestra consistentes.
     *
     * @return void
     */
    public function run(): void
    {
        // Crear 10 propiedades con relaciones
        /*  for ($i = 0; $i < 10; $i++) {
            Propiedad::create([
                // Asignar barrio aleatorio o crear uno nuevo si no existe
                'id_barrio' => Barrio::inRandomOrder()->first()->id ?? Barrio::factory()->create()->id,
                // Asignar calle aleatoria o crear una nueva si no existe
                'id_calle' => Calle::inRandomOrder()->first()->id ?? Calle::factory()->create()->id,
                // Asignar estado general aleatorio o crear uno nuevo si no existe
                'id_estado_general' => Estado_general::inRandomOrder()->first()->id ?? Estado_general::factory()->create()->id,
                // Asignar estado de oferta aleatorio o crear uno nuevo si no existe
               
                // Asignar tipo de inmueble aleatorio o crear uno nuevo si no existe
                'id_inmueble' => Tipo_Inmueble::inRandomOrder()->first()->id ?? Tipo_Inmueble::factory()->create()->id,
                // Asignar localidad aleatoria o crear una nueva si no existe
                'id_localidad' => Localidad::inRandomOrder()->first()->id ?? Localidad::factory()->create()->id,
                // Asignar provincia aleatoria o crear una nueva si no existe
                'id_provincia' => Provincia::inRandomOrder()->first()->id ?? Provincia::factory()->create()->id,
                // Asignar zona aleatoria o crear una nueva si no existe
                'id_zona' => Zona::inRandomOrder()->first()->id ?? Zona::factory()->create()->id,
                // Asignar precio aleatorio o crear uno nuevo si no existe
                'id_precio' => Precio::inRandomOrder()->first()->id ?? Precio::factory()->create()->id,
                
                'id_tasacion' => Tasacion::inRandomOrder()->first()->id ?? Precio::factory()->create()->id,
                // Genera un código de alquiler entre 1000 y 5000
                'cod_alquiler' => rand(1000, 2000),
                // Genera un código de venta entre 5000 y 10000
                'cod_venta' => rand(5000, 10000),
                // Generar un valor aleatorio entre 1 y 3 para la empresa
                'empresa' => rand(1, 3),
                // Genera un número aleatorio entre 1000 y 9999 para el folio
                'folio' => rand(1000, 2000),
                // Generar una cantidad aleatoria de dormitorios entre 1 y 99
                'cantidad_dormitorios' => rand(1, 99),
                // Asignar estado de alquiler aleatorio
                'id_estado_alquiler' => Estado_alquiler::inRandomOrder()->first()->id,
                // Asignar estado de venta aleatorio
                'id_estado_venta' => Estado_venta::inRandomOrder()->first()->id,
                // Generar un número aleatorio entre 1 y 9999 para el número de la calle
                'numero_calle' => rand(1, 9999),
                // Generar un número aleatorio entre 1 y 9999 para el piso
                'piso' => rand(1, 100),
                // Generar un valor aleatorio entre 1 y 10 para el departamento
                'departamento' => rand(1, 10),
                // Generar un valor aleatorio entre 1 y 99 para la llave
                'llave' => rand(1, 99),
                // Comentario asociado a la llave
                'comentario_llave' => "Departamento generado:",
                // Generar un valor aleatorio entre 0 y 1 para indicar si hay cartel
                'cartel' => rand(0, 1) == 1 ? 'Sí' : 'No', // Asigna "Sí" o "No"
                // Comentario asociado al cartel
                'comentario_cartel' => "comentario_cartel",
                'descipcion_propiedad' => "comentario_detalle_propiedad",
                'cochera' => rand(0, 1) == 1 ? 'Sí' : 'No', // Asigna "Sí" o "No"
                'numero_cochera' => rand(1, 99),
                'mLote' => rand(1, 99),
                'mCubiertos' => rand(1, 99),
                'banios' => rand(1, 5),
                'moneda' => rand(0, 2) , // Asigna "Sí" o "No"
                'comparte_venta' => rand(1, 2),
                'autorizacion_venta' => rand(1, 2) ,
                'fecha_autorizacion_venta' => rand(0, 1) == 1 ? now() : null,
                'exclusividad_venta' => rand(1, 2) ,
                'condicionado_venta' => rand(1, 2) ,
                'autorizacion_alquiler' => rand(1, 2),
                'exclusividad_alquiler' => rand(1, 2) ,
                'clausula_de_venta' => rand(1, 2) ,
                'fecha_autorizacion_alquiler' => rand(0, 1) == 1 ? now() : null,
                'asfalto' => rand(1, 2),
                'gas' => rand(1, 2),
                'cloaca' => rand(1, 2),
            ]);
        }  */





        
    }
}
