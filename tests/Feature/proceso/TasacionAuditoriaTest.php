<?php

use App\Models\At_cl\Tasacion;
use App\Models\usuarios_y_permisos\Usuario;
use App\Services\At_cl\TasacionService;
use Illuminate\Support\Facades\DB;

it('registra al usuario autenticado al crear tasaciones por ambos métodos', function () {
    $usuario = new Usuario();
    $usuario->setAttribute('id', 708);
    auth('api')->setUser($usuario);

    $service = new TasacionService();
    $tasacionDesdeRequest = null;
    $tasacionDirecta = null;

    $queries = DB::connection('mysql')->pretend(function () use (
        $service,
        &$tasacionDesdeRequest,
        &$tasacionDirecta
    ) {
        $tasacionDesdeRequest = $service->crearDesdeRequest(
            [
                'tasacion_venta' => 120000,
                'moneda_venta' => '1',
                'fecha_tasacion_venta' => '2026-11-17',
            ],
            55,
            ['tasacion_alquiler' => 45000]
        );
        $tasacionDirecta = $service->crearTasacion([
            'propiedad_id' => 55,
            'tasacion_pesos_venta' => 120000,
        ]);
    });

    expect($tasacionDesdeRequest->id_usuario)->toBe(708)
        ->and($tasacionDirecta->id_usuario)->toBe(708)
        ->and($queries)->toHaveCount(2)
        ->and($queries[0]['query'])->toContain('id_usuario')
        ->and($queries[1]['query'])->toContain('id_usuario');
});
