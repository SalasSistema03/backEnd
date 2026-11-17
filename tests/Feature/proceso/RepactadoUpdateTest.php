<?php

use App\Models\At_cl\Propiedad;
use App\Models\proceso\Estado_repactado;
use App\Models\proceso\Historial_estado_repactado;
use App\Models\proceso\Proceso_propiedad;
use App\Models\usuarios_y_permisos\Usuario;
use App\Services\proceso\RepactadoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::connection('mysql10')->beginTransaction();
});

afterEach(function () {
    DB::connection('mysql10')->rollBack();
});

it('crea una nueva versión del historial y mueve el proceso de propiedad al nuevo id', function () {
    $usuario = Usuario::query()->firstOrFail();
    $estado = Estado_repactado::query()->firstOrFail();
    $propiedadesEnProceso = Proceso_propiedad::query()->pluck('id_propiedad');
    $propiedad = Propiedad::query()
        ->whereNotIn('id', $propiedadesEnProceso)
        ->firstOrFail();

    $historial = Historial_estado_repactado::create([
        'estado' => $estado->id,
        'cuenta_corriente' => 'Mala',
        'quien_guardo' => $usuario->getKey(),
        'propiedad_id' => $propiedad->getKey(),
        'periodo_cargado' => '2099-12',
        'monto_alquiler_viejo' => 1000,
        'monto_documento' => 200,
        'reconocimiento' => 50,
        'inicio_contrato' => '2099-01-01',
        'vencimiento_contrato' => '2099-12-31',
    ]);

    $proceso = Proceso_propiedad::create([
        'asesor' => $usuario->getKey(),
        'id_propiedad' => $propiedad->getKey(),
        'tipo_reserva' => 'EFECTIVO',
        'moneda' => '$',
        'monto_reserva' => 0,
        'monto_aceptado' => 0,
        'id_historial_estado_repactado' => $historial->id,
    ]);

    $resultado = (new RepactadoService())->updateRepactado(Request::create('/updateRepactado', 'PUT', [
        'id' => $historial->id,
        'estado' => $estado->id,
        'cuenta_corriente' => 'Buena',
        'monto_documento' => 200,
        'reconocimiento' => 50,
    ]));

    $nuevoHistorial = $resultado['data'];

    expect($nuevoHistorial->id)->not->toBe($historial->id)
        ->and($nuevoHistorial->cuenta_corriente)->toBe('Buena')
        ->and((float) $nuevoHistorial->total)->toBe(1150.0)
        ->and(Historial_estado_repactado::findOrFail($historial->id)->cuenta_corriente)->toBe('Mala')
        ->and(Proceso_propiedad::findOrFail($proceso->id)->id_historial_estado_repactado)->toBe($nuevoHistorial->id);
});
