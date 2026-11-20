<?php

use App\Models\At_cl\Empresas_propiedades;
use App\Models\At_cl\Tasacion;
use App\Models\proceso\Historial_estado_repactado;
use App\Models\proceso\Historial_tasaciones_repactado;
use App\Models\usuarios_y_permisos\Usuario;
use App\Services\proceso\RepactadoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::connection('mysql')->beginTransaction();
    DB::connection('mysql10')->beginTransaction();
});

afterEach(function () {
    if (DB::connection('mysql10')->transactionLevel() > 0) {
        DB::connection('mysql10')->rollBack();
    }

    if (DB::connection('mysql')->transactionLevel() > 0) {
        DB::connection('mysql')->rollBack();
    }
});

it('guarda los importes como nueva tasación y crea el vínculo con el repactado', function () {
    $empresaPropiedad = Empresas_propiedades::query()->firstOrFail();
    $usuario = Usuario::query()->firstOrFail();
    auth('api')->setUser($usuario);

    $historial = Historial_estado_repactado::create([
        'quien_guardo' => $usuario->getKey(),
        'propiedad_id' => $empresaPropiedad->propiedad_id,
        'periodo_cargado' => '2099-12',
        'monto_alquiler_viejo' => 1000,
        'inicio_contrato' => '2099-01-01',
        'vencimiento_contrato' => '2099-12-31',
    ]);

    $resultado = (new RepactadoService())->guardarTasacionRepactado(Request::create(
        '/guardarTasacionRepactado',
        'POST',
        [
            'id_empresa_propiedad' => $empresaPropiedad->id,
            'id_historial_estado_repactado' => $historial->id,
            'tasacion_venta' => 250000,
            'moneda_venta' => '2',
            'tasacion_alquiler' => 85000,
            'fecha_tasacion' => '2099-12-01',
            'comentario_tasacion' => 'Tasación de prueba',
        ]
    ));

    $tasacion = $resultado['data']['tasacion'];
    $asociacion = $resultado['data']['historial'];
    $resultadoActualizacion = (new RepactadoService())->guardarTasacionRepactado(Request::create(
        '/guardarTasacionRepactado',
        'POST',
        [
            'id_empresa_propiedad' => $empresaPropiedad->id,
            'id_historial_estado_repactado' => $historial->id,
            'tasacion_alquiler' => 95000,
            'fecha_tasacion' => '2099-12-02',
            'comentario_tasacion' => 'Actualización de tasación',
        ]
    ));
    $tasacionActualizada = $resultadoActualizacion['data']['tasacion'];

    expect($resultado['success'])->toBeTrue()
        ->and($tasacion)->toBeInstanceOf(Tasacion::class)
        ->and((int) $tasacion->propiedad_id)->toBe((int) $empresaPropiedad->propiedad_id)
        ->and((int) $tasacion->id_usuario)->toBe((int) $usuario->getKey())
        ->and((int) $tasacion->tasacion_dolar_venta)->toBe(250000)
        ->and((int) $tasacion->tasacion_pesos_alquiler)->toBe(85000)
        ->and($tasacion->comentario_tasacion)->toBe('Tasación de prueba')
        ->and($asociacion)->toBeInstanceOf(Historial_tasaciones_repactado::class)
        ->and((int) $asociacion->id_empresa_propiedad)->toBe((int) $empresaPropiedad->id)
        ->and((int) $asociacion->id_historial_estado_repactado)->toBe((int) $historial->id)
        ->and($tasacionActualizada->id)->not->toBe($tasacion->id)
        ->and((int) $tasacionActualizada->id_usuario)->toBe((int) $usuario->getKey())
        ->and((int) $tasacion->refresh()->tasacion_dolar_venta)->toBe(250000)
        ->and((int) $tasacionActualizada->tasacion_dolar_venta)->toBe(250000)
        ->and((int) $tasacionActualizada->tasacion_pesos_alquiler)->toBe(95000)
        ->and($tasacionActualizada->fecha_tasacion)->toBe('2099-12-02')
        ->and(Historial_tasaciones_repactado::where('id_historial_estado_repactado', $historial->id)->count())->toBe(2);
});
