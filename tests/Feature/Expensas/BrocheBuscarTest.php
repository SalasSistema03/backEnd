<?php

use App\Http\Controllers\impuesto\Expensas\ExpensasController;
use App\Services\impuesto\EXP\ExpensasService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

it('devuelve el contrato estable de broche-buscar y reenvía todos los filtros', function () {
    $fila = [
        'id_unidades_sys' => 901,
        'folio' => 4917,
        'id_unidad' => 301,
        'unidad' => '3 B',
        'piso' => '3',
        'depto' => 'B',
        'id_edificio' => 71,
        'direccion_edificio' => 'Calle Controlada',
        'altura_edificio' => '1234',
        'nombre_consorcio' => 'Consorcio de prueba',
        'id_administrador' => 44,
        'nombre' => 'Administración de prueba',
    ];

    $filtros = [
        'folio' => 4917,
        'empresa' => 12,
        'edificio' => 71,
        'administrador' => 44,
    ];

    $servicio = Mockery::mock(ExpensasService::class);
    $servicio->shouldReceive('buscarUnidadesParaBroche')
        ->once()
        ->with($filtros)
        ->andReturn(new Collection([$fila]));

    $controlador = new ExpensasController();
    $propiedad = (new ReflectionClass($controlador))->getProperty('ExpensasService');
    $propiedad->setValue($controlador, $servicio);

    $respuesta = $controlador->brocheExpensasBuscar(Request::create(
        '/api/v1/expensas/broche-buscar',
        'GET',
        $filtros,
    ));

    expect($respuesta->getStatusCode())->toBe(200)
        ->and($respuesta->getData(true))->toBe([
            'status' => 'success',
            'data' => [$fila],
        ]);

    expect(array_keys($fila))->toBe([
        'id_unidades_sys',
        'folio',
        'id_unidad',
        'unidad',
        'piso',
        'depto',
        'id_edificio',
        'direccion_edificio',
        'altura_edificio',
        'nombre_consorcio',
        'id_administrador',
        'nombre',
    ])
        ->and($fila['id_edificio'])->toBe(71)
        ->and($fila['id_administrador'])->toBe(44);
});

it('mantiene una proyección explícita para la búsqueda de broches', function () {
    $fuente = file_get_contents(base_path('app/Services/impuesto/EXP/ExpensasService.php'));

    expect($fuente)->toContain("'exp_unidades_sys.id as id_unidades_sys'")
        ->and($fuente)->toContain("'exp_administrador_consorcio.id as id_administrador'")
        ->and($fuente)->not->toContain("select('*')")
        ->and($fuente)->not->toContain("select(\"*\")");
});
