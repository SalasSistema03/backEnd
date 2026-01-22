<?php

namespace Database\Factories\At_cl;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para la creación de instancias del modelo Padron.
 *
 * Esta clase se encarga de definir cómo se generan los datos de prueba para el modelo `Padron`. 
 * Es utilizada principalmente para crear datos ficticios durante las pruebas, lo que facilita 
 * el llenado de la base de datos con información aleatoria para escenarios de prueba y migración.
 * 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\At_cl\Padron>
 */
class PadronFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo `Padron`.
     *
     * Este método es invocado cuando se utiliza la fábrica para crear nuevas instancias del modelo `Padron`. 
     * Las instancias generadas tendrán estos valores como predeterminados, pero pueden ser sobrescritos si es necesario.
     *
     * @return array<string, mixed> Un arreglo asociativo que define los atributos y valores para una instancia del modelo.
     * 
     * @note Este método hace uso de `faker` para generar datos aleatorios, lo que permite automatizar la 
     *       creación de datos de prueba durante el desarrollo y las pruebas.
     */
    public function definition(): array
    {
        return [
            // Genera un nombre de persona aleatorio
            'nombre' => fake()->firstName(), // Utiliza faker para generar un primer nombre aleatorio.

            // Genera un apellido aleatorio
            'apellido' => fake()->lastName(),  // Utiliza faker para generar un apellido aleatorio.
            'documento'=> fake()->numberBetween(41930849, 49999999),
            // Genera una fecha de nacimiento aleatoria (año, mes y día en formato Y-m-d) con un límite máximo de nacimiento (2005-01-01).
     'fecha_nacimiento' => fake()->date('Y-m-d', '2005-01-01'), // Faker genera una fecha aleatoria para el campo de fecha de nacimiento.

            // Genera un nombre de calle aleatorio.
            'calle' => fake()->streetName(),  // Faker genera un nombre de calle aleatorio.

            // Genera un número de calle aleatorio entre 1 y 9999.
            'numero_calle' => fake()->numberBetween(1, 9999), // Faker genera un número aleatorio para el número de la calle.

            // Genera una combinación aleatoria entre número y letra para el piso o apartamento, o un valor nulo.
            'piso_departamento' => fake()->randomElement(['1A', '2B', '3C', null]),  // Faker elige aleatoriamente un valor de la lista o null.

            // Genera un nombre de ciudad aleatorio.
            'ciudad' => fake()->city(), // Faker genera el nombre de una ciudad aleatoria.

            // Genera el nombre de una provincia o estado aleatorio.
            'provincia' => fake()->state(),  // Faker genera el nombre de un estado aleatorio.

            // Genera una oración aleatoria como nota opcional, puede ser null si no se genera.
            'notes' => fake()->optional()->sentence(), // Faker genera una frase aleatoria o null si no se genera.

        ];
    }
}
