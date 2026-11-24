<?php

use App\Services\impuesto\IMPUESTO\PadronImpuestoService;

function invocarMetodoIdentidadPadron(string $nombre, ...$argumentos)
{
    $metodo = new ReflectionMethod(PadronImpuestoService::class, $nombre);

    return $metodo->invoke(new PadronImpuestoService(), ...$argumentos);
}

test('distingue contratos cabecera distintos aunque compartan folio y partida', function () {
    $contratoAnterior = (object) [
        'folio' => '438',
        'empresa' => 1,
        'partida' => '0036422',
        'clave' => '8964045',
        'id_contrato_cabecera' => 100,
        'comienza' => '2025-02-20',
    ];

    $contratoNuevo = (object) [
        'folio' => '438',
        'empresa' => 1,
        'partida' => '00036422',
        'clave' => '8964045',
        'id_contrato_cabecera' => 200,
        'comienza' => '2026-11-17',
    ];

    expect(invocarMetodoIdentidadPadron('esMismaCabeceraContrato', $contratoAnterior, $contratoNuevo))->toBeFalse();
    expect(invocarMetodoIdentidadPadron('claveRegistroPadron', $contratoAnterior))
        ->not->toBe(invocarMetodoIdentidadPadron('claveRegistroPadron', $contratoNuevo));
});

test('conserva la identidad del contrato cuando cambia solo la fecha de rescisión', function () {
    $rescisionAnterior = (object) [
        'folio' => '504',
        'empresa' => 1,
        'partida' => '00013962',
        'clave' => '92755155',
        'id_contrato_cabecera' => 300,
        'comienza' => '2025-07-15',
        'rescicion' => '2026-04-06',
    ];

    $rescisionActualizada = (object) [
        'folio' => '504',
        'empresa' => 1,
        'partida' => '0013962',
        'clave' => '92755155',
        'id_contrato_cabecera' => 300,
        'comienza' => '2025-07-15',
        'rescicion' => '2027-01-14',
    ];

    expect(invocarMetodoIdentidadPadron('esMismaCabeceraContrato', $rescisionAnterior, $rescisionActualizada))->toBeTrue();
    expect(invocarMetodoIdentidadPadron('claveRegistroPadron', $rescisionAnterior))
        ->toBe(invocarMetodoIdentidadPadron('claveRegistroPadron', $rescisionActualizada));
});

test('marca pendiente la TGI ausente después de una rescisión dentro del mes actual', function () {
    $estado = invocarMetodoIdentidadPadron(
        'calcularEstadoTgiAusente',
        '2026-11-11',
        \Carbon\Carbon::parse('2026-11-20')
    );

    expect($estado)->toBe('PENDIENTE');
});

test('mantiene los umbrales existentes para la inactivación de TGI', function () {
    $fechaActual = \Carbon\Carbon::parse('2026-11-20');

    expect(invocarMetodoIdentidadPadron(
        'calcularEstadoTgiAusente',
        '2026-10-11',
        $fechaActual
    ))->toBe('PENDIENTE');

    expect(invocarMetodoIdentidadPadron(
        'calcularEstadoTgiAusente',
        '2026-08-19',
        $fechaActual
    ))->toBe('INACTIVO');
});
