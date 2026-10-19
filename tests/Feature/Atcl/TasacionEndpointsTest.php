<?php

use App\Models\At_cl\Tasaciones;
use App\Services\At_cl\TasacionService;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Integración front <-> back para ModalTasacionesPropiedad.vue.
 *
 * El front (src/components/Atcl/Tasaciones/ModalTasacionesPropiedad.vue) envía el
 * payload camelCase definido en tests/mocks/modalTasaciones.js (TASACION_PAYLOAD_KEYS).
 * Estos tests verifican que la API mapea correctamente esos campos a la tabla `tasaciones`.
 *
 * Se usa DatabaseTransactions sobre la conexión por defecto (`mysql` = sistema_atcl),
 * que es la conexión del modelo App\Models\At_cl\Tasaciones, así que cada test
 * hace rollback y no deja datos.
 */
uses(DatabaseTransactions::class);

/**
 * Payload EXACTO que arma `guardarTasacion()` en el componente Vue.
 * Mantener sincronizado con TASACION_PAYLOAD_KEYS del test de frontend.
 */
function payloadDesdeFront(): array
{
    return [
        'inmuebleId' => 1,
        'calle' => 'Av. Santa Fe (texto, el back usa id_calle)',
        'id_calle' => 2,
        'numeroCalle' => '1234',
        'piso' => '5',
        'departamento' => 'A',
        'localidadId' => 3,
        'folio' => 12345,
        'propietarioId' => 7,
        'propietario' => 'Perez, Juan',
        'ingreso' => 'Presencial',
        'tipoTasacion' => 'Venta',
        'tasacionVenta' => 150000,
        'tasacionAlquiler' => null,
        'costoTasacion' => 15000,
        'tasadorId' => 9,
        'tasacionInformada' => 'Pasado, Ingreso',
        'observaciones' => 'Observación de prueba',
        'fecha' => '2024-03-15',
    ];
}

beforeEach(function () {
    // Saltea sólo el guard JWT; el resto del stack de la API sigue activo.
    $this->withoutMiddleware(Authenticate::class);
});

describe('POST /api/v1/propiedad/tasacion (crear)', function () {
    it('crea la tasación mapeando el payload del frontend', function () {
        $response = $this->postJson('/api/v1/propiedad/tasacion', payloadDesdeFront());

        $response->assertCreated();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.tipo_inmueble', 1);
        $response->assertJsonPath('data.calle', 2);
        $response->assertJsonPath('data.tipo_tasacion', 'Venta');

        $this->assertNotNull($response->json('data.id'));

        $this->assertDatabaseHas('tasaciones', [
            'id' => $response->json('data.id'),
            'tipo_inmueble' => 1,
            'calle' => 2,
            'numero_calle' => '1234',
            'piso' => '5',
            'departamento' => 'A',
            'id_localidad' => 3,
            'folio' => 12345,
            'propietario' => 7,
            'origen' => 'Presencial',
            'tipo_tasacion' => 'Venta',
            'tasacion_venta' => 150000,
            'tasador' => 9,
            'tasacion_informada_prop' => 'Pasado, Ingreso',
            'observaciones' => 'Observación de prueba',
        ]);
    });

    it('ignora el texto libre `calle` y prioriza `id_calle`', function () {
        $payload = payloadDesdeFront();
        $payload['id_calle'] = 5;

        $response = $this->postJson('/api/v1/propiedad/tasacion', $payload);
        $response->assertCreated();

        $this->assertDatabaseHas('tasaciones', [
            'id' => $response->json('data.id'),
            'calle' => 5,
        ]);
    });

    it('permite guardar una tasación de alquiler', function () {
        $payload = payloadDesdeFront();
        $payload['tipoTasacion'] = 'Alquiler';
        $payload['tasacionVenta'] = null;
        $payload['tasacionAlquiler'] = 80000;

        $response = $this->postJson('/api/v1/propiedad/tasacion', $payload);
        $response->assertCreated();

        $this->assertDatabaseHas('tasaciones', [
            'id' => $response->json('data.id'),
            'tipo_tasacion' => 'Alquiler',
            'tasacion_alquiler' => 80000,
        ]);
    });

    it('guarda con montos nulos (carga incompleta)', function () {
        $payload = payloadDesdeFront();
        $payload['tasacionVenta'] = null;
        $payload['costoTasacion'] = null;

        $response = $this->postJson('/api/v1/propiedad/tasacion', $payload);
        $response->assertCreated();

        $this->assertDatabaseHas('tasaciones', [
            'id' => $response->json('data.id'),
            'tasacion_venta' => null,
            'costo_tasacion' => null,
        ]);
    });
});

describe('POST /api/v1/propiedad/tasacion/{id} (actualizar)', function () {
    it('actualiza la fila existente', function () {
        $id = $this->postJson('/api/v1/propiedad/tasacion', payloadDesdeFront())->json('data.id');

        $payload = payloadDesdeFront();
        $payload['numeroCalle'] = '9999';
        $payload['tipoTasacion'] = 'Alquiler y Venta';
        $payload['tasacionInformada'] = 'Cancelado';

        $response = $this->postJson("/api/v1/propiedad/tasacion/{$id}", $payload);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.id', $id);

        $this->assertDatabaseHas('tasaciones', [
            'id' => $id,
            'numero_calle' => '9999',
            'tipo_tasacion' => 'Alquiler y Venta',
            'tasacion_informada_prop' => 'Cancelado',
        ]);
    });

    it('devuelve 500 si la tasación no existe (findOrFail atrapado por el controller)', function () {
        $response = $this->postJson('/api/v1/propiedad/tasacion/999999999', payloadDesdeFront());

        $response->assertStatus(500);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'No se pudo actualizar la tasación.');
    });
});

describe('DELETE /api/v1/propiedad/tasacion/{id}', function () {
    it('elimina la tasación existente', function () {
        $id = $this->postJson('/api/v1/propiedad/tasacion', payloadDesdeFront())->json('data.id');

        $response = $this->deleteJson("/api/v1/propiedad/tasacion/{$id}");

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertDatabaseMissing('tasaciones', ['id' => $id]);
    });

    it('devuelve 404 si la tasación no existe', function () {
        $response = $this->deleteJson('/api/v1/propiedad/tasacion/999999999');

        $response->assertNotFound();
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'La tasación no existe o no se pudo eliminar.');
    });
});

describe('GET /api/v1/tasaciones/activas', function () {
    it('devuelve sólo las tasaciones con asignada = 0', function () {
        $activaId = $this->postJson('/api/v1/propiedad/tasacion', payloadDesdeFront())->json('data.id');

        $inactiva = Tasaciones::create([
            'tipo_inmueble' => 1,
            'calle' => 1,
            'numero_calle' => '0000',
            'propietario' => 1,
            'tipo_tasacion' => 'Venta',
            'asignada' => 1,
        ]);

        $response = $this->getJson('/api/v1/tasaciones/activas');

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $ids = collect($response->json('data'))->pluck('id');

        expect($ids)->toContain($activaId);
        expect($ids)->not->toContain($inactiva->id);
    });
});

describe('TasacionService (unidad de integración)', function () {
    it('eliminarTasacion devuelve true al borrar y false si no existe', function () {
        $tasacion = Tasaciones::create([
            'tipo_inmueble' => 1,
            'calle' => 1,
            'numero_calle' => '1',
            'propietario' => 1,
            'tipo_tasacion' => 'Venta',
        ]);

        $service = new TasacionService();

        expect($service->eliminarTasacion($tasacion->id))->toBeTrue();
        expect($service->eliminarTasacion($tasacion->id))->toBeFalse();
    });
});
