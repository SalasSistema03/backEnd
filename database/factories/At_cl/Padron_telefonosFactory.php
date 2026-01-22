<?php

namespace Database\Factories\At_cl;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para la creación de instancias del modelo Padron_telefonos.
 *
 * Esta clase define cómo se deben generar los datos de prueba para el modelo `Padron_telefonos` durante la 
 * ejecución de pruebas o al realizar migraciones de datos. Utiliza el paquete `faker` para generar datos aleatorios
 * y facilitar la inserción de registros de prueba en la base de datos.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\At_cl\Padron_telefonos>
 */
class Padron_telefonosFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     *
     * Este método se utiliza para definir los valores por defecto de los atributos del modelo `Padron_telefonos` cuando
     * se crea una nueva instancia de este modelo usando la fábrica. Estos valores pueden ser sobrescritos al generar datos
     * de prueba si es necesario.
     *
     * @return array<string, mixed> Un arreglo asociativo con los atributos del modelo y sus valores generados aleatoriamente.
     * 
     * @note Los valores generados pueden ser utilizados para la creación masiva de datos en pruebas unitarias, 
     *       pruebas de integración o durante la generación de datos ficticios en la base de datos.
     */
    public function definition(): array
    {
        return [
            // Genera un número de teléfono de 8 dígitos con un formato arbitrario
            'phone_number' => fake()->numerify('########'), // Faker genera un número de teléfono aleatorio de 8 dígitos

            // Genera una frase aleatoria que simula observaciones
            'notes' => fake()->sentence(), // Faker genera una oración aleatoria como nota

            // Crea una relación con el modelo `Padron` usando su fábrica para generar un `padron_id` válido
            'padron_id' => \App\Models\At_cl\Padron::factory(), // Relaciona cada teléfono generado con un registro de `Padron`
        ];
    }
}
