<?php

use App\Services\impuesto\IMPUESTO\CargaImpuestoService;

function invocarCargaImpuesto(string $metodo, ...$argumentos)
{
    $reflection = new ReflectionMethod(CargaImpuestoService::class, $metodo);

    return $reflection->invoke(new CargaImpuestoService(), ...$argumentos);
}

test('deja una sola entrada por folio y conserva el contrato de mayor comienza', function () {
    $registros = collect([
        (object) [
            'folio' => 189,
            'estado' => 'INACTIVO',
            'empresa' => 1,
            'comienzo' => '2024-01-15',
            'rescicion' => '2025-01-15',
        ],
        (object) [
            'folio' => 189,
            'estado' => 'ACTIVO',
            'empresa' => 1,
            'comienzo' => '2026-12-01',
            'rescicion' => '2027-12-01',
        ],
    ]);

    $seleccionados = (new CargaImpuestoService())->seleccionarFoliosCompartidos($registros);

    expect($seleccionados)->toHaveCount(1);
    expect($seleccionados->first()->estado)->toBe('ACTIVO');
    expect($seleccionados->first()->comienzo)->toBe('2026-12-01');
});

test('prioriza el folio ACTIVO cuando comparte fecha de inicio con uno MODIFICADO', function () {
    $registros = collect([
        (object) [
            'folio' => 5,
            'estado' => 'MODIFICADO',
            'empresa' => 1,
            'comienza' => '2026-09-01 00:00:00',
            'rescicion' => '2028-02-29',
        ],
        (object) [
            'folio' => 5,
            'estado' => 'ACTIVO',
            'empresa' => 1,
            'comienza' => '2026-09-01 00:00:00',
            'rescicion' => '2028-02-29',
        ],
    ]);

    $seleccionados = (new CargaImpuestoService())->seleccionarFoliosCompartidos($registros);

    expect($seleccionados)->toHaveCount(1);
    expect($seleccionados->first()->estado)->toBe('ACTIVO');
});

test('excluye contratos rescindidos antes del mes y considera vigentes los del mes actual', function () {
    $registros = collect([
        (object) [
            'folio' => 5,
            'estado' => 'ACTIVO',
            'empresa' => 1,
            'comienza' => '2026-09-01',
            'rescicion' => '2026-10-31',
        ],
        (object) [
            'folio' => 6,
            'estado' => 'ACTIVO',
            'empresa' => 1,
            'comienza' => '2024-01-01',
            'rescicion' => '2026-11-28',
        ],
    ]);

    $seleccionados = (new CargaImpuestoService())->seleccionarFoliosCompartidos(
        $registros,
        \Carbon\Carbon::parse('2026-11-01')
    );

    expect($seleccionados)->toHaveCount(1);
    expect($seleccionados->first()->folio)->toBe(6);
});

test('muestra dos folios distintos que comparten partida/clave', function () {
    $registros = collect([
        (object) [
            'folio' => 189,
            'estado' => 'ACTIVO',
            'empresa' => 1,
            'comienzo' => '2026-12-01',
            'rescicion' => '2027-12-01',
        ],
        (object) [
            'folio' => 190,
            'estado' => 'ACTIVO',
            'empresa' => 1,
            'comienzo' => '2026-11-20',
            'rescicion' => '2027-11-20',
        ],
    ]);

    $seleccionados = (new CargaImpuestoService())->seleccionarFoliosCompartidos($registros);

    expect($seleccionados)->toHaveCount(2);
    expect($seleccionados->pluck('folio')->all())->toBe([189, 190]);
});

test('selecciona el padrón ACTIVO más reciente y, sin ACTIVO, el más reciente', function () {
    $conActivo = collect([
        (object) [
            'id' => 10,
            'estado' => 'ACTIVO',
            'comienzo' => '2025-05-16',
        ],
        (object) [
            'id' => 11,
            'estado' => 'PENDIENTE',
            'comienzo' => '2026-11-16',
        ],
        (object) [
            'id' => 12,
            'estado' => 'ACTIVO',
            'comienzo' => '2026-01-10',
        ],
    ]);

    expect(CargaImpuestoService::seleccionarRegistroPadron($conActivo)->id)->toBe(12);

    $sinActivo = collect([
        (object) [
            'id' => 20,
            'estado' => 'INACTIVO',
            'comienzo' => '2025-03-01',
        ],
        (object) [
            'id' => 21,
            'estado' => 'PENDIENTE',
            'comienzo' => '2026-09-01',
        ],
    ]);

    expect(CargaImpuestoService::seleccionarRegistroPadron($sinActivo)->id)->toBe(21);
});

test('reconoce una carga API existente por código de barras aunque el id de padrón haya cambiado', function () {
    $servicio = new CargaImpuestoService();
    $cargas = collect([
        (object) [
            'id_apiPadron' => 3152,
            'codigo_barra' => 'API-BARRA-001',
            'periodo_mes' => 12,
            'periodo_anio' => 2026,
        ],
    ]);

    // Id de padrón distinto, mismo código de barras y mismo período: es un duplicado.
    expect($servicio->yaFueCargado($cargas, 3156, 12, 2026, 'api', 'API-BARRA-001'))->toBeTrue();

    // Id distinto y código de barras distinto: no es duplicado.
    expect($servicio->yaFueCargado($cargas, 3156, 12, 2026, 'api', 'API-BARRA-999'))->toBeFalse();

    // Mismo código de barras en otro período: no es duplicado.
    expect($servicio->yaFueCargado($cargas, 3156, 11, 2026, 'api', 'API-BARRA-001'))->toBeFalse();
});

test('reconoce una carga de agua por código de barras aunque cambie el id', function () {
    $servicio = new CargaImpuestoService();
    $cargas = collect([
        (object) [
            'id_aguaPadron' => 400,
            'codigo_barra' => 'AGUA-BARRA-001',
            'periodo_mes' => 10,
            'periodo_anio' => 2026,
        ],
    ]);

    expect($servicio->yaFueCargado($cargas, 401, 10, 2026, 'agua', 'AGUA-BARRA-001'))->toBeTrue();
    expect($servicio->yaFueCargado($cargas, 401, 10, 2026, 'agua', 'AGUA-BARRA-999'))->toBeFalse();
    expect($servicio->yaFueCargado($cargas, 401, 9, 2026, 'agua', 'AGUA-BARRA-001'))->toBeFalse();
});

test('reconoce una carga de gas por código de barras aunque cambie el id', function () {
    $servicio = new CargaImpuestoService();
    $cargas = collect([
        (object) [
            'id_gasPadron' => 500,
            'codigo_barra' => 'GAS-BARRA-001',
            'periodo_mes' => 8,
            'periodo_anio' => 2026,
        ],
    ]);

    expect($servicio->yaFueCargado($cargas, 501, 8, 2026, 'gas', 'GAS-BARRA-001'))->toBeTrue();
    expect($servicio->yaFueCargado($cargas, 501, 8, 2026, 'gas', 'GAS-BARRA-999'))->toBeFalse();
    expect($servicio->yaFueCargado($cargas, 501, 7, 2026, 'gas', 'GAS-BARRA-001'))->toBeFalse();
});
