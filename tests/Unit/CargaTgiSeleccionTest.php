<?php

use App\Services\impuesto\IMPUESTO\CargaImpuestoService;
use App\Services\impuesto\TGI\PadronTgiService;

function invocarSelectorTgi(string $clase, string $metodo, ...$argumentos)
{
    $reflection = new ReflectionMethod($clase, $metodo);

    return $reflection->invoke(new $clase(), ...$argumentos);
}

test('elige el padrón activo entre contratos del mismo folio y partida', function () {
    $contratos = collect([
        (object) [
            'id' => 5664,
            'folio' => 469,
            'partida' => '00155991',
            'estado' => 'PENDIENTE',
            'comienza' => '2025-05-16',
        ],
        (object) [
            'id' => 5871,
            'folio' => 469,
            'partida' => '00155991',
            'estado' => 'ACTIVO',
            'comienza' => '2026-11-16',
        ],
    ]);

    $seleccionado = invocarSelectorTgi(
        PadronTgiService::class,
        'seleccionarRegistroParaCarga',
        $contratos
    );

    expect($seleccionado->id)->toBe(5871);
});

test('muestra una sola fila por folio TGI y elige el contrato más reciente sin importar su estado', function () {
    $registros = collect([
        (object) ['folio' => 469, 'empresa' => 1, 'estado' => 'PENDIENTE', 'comienza' => '2025-05-16'],
        (object) ['folio' => 469, 'empresa' => 1, 'estado' => 'ACTIVO', 'comienza' => '2026-11-16'],
        (object) ['folio' => 469, 'empresa' => 2, 'estado' => 'INACTIVO', 'comienza' => '2026-11-17'],
        (object) ['folio' => 468, 'empresa' => 1, 'estado' => 'ACTIVO', 'comienza' => '2026-10-01'],
    ]);

    $seleccionados = invocarSelectorTgi(
        CargaImpuestoService::class,
        'seleccionarFoliosTgi',
        $registros
    );

    expect($seleccionados)->toHaveCount(2);
    expect($seleccionados->firstWhere('folio', 469)->estado)->toBe('INACTIVO');
    expect($seleccionados->firstWhere('folio', 469)->empresa)->toBe(2);
    expect($seleccionados->firstWhere('folio', 468)->estado)->toBe('ACTIVO');
});

test('reconoce una carga existente por su código aunque apunte al padrón histórico', function () {
    $codigoBarra = '0046900155991000000086355010122026L';
    $cargas = collect([
        (object) [
            'id_tgiPadron' => 5664,
            'codigo_barra' => $codigoBarra,
            'periodo_mes' => 12,
            'periodo_anio' => 2026,
        ],
    ]);

    expect((new CargaImpuestoService())->yaFueCargado(
        $cargas,
        5871,
        12,
        2026,
        'tgi',
        $codigoBarra
    ))->toBeTrue();
});
