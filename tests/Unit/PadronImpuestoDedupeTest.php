<?php

use App\Services\impuesto\IMPUESTO\CargaImpuestoService;
use App\Services\impuesto\IMPUESTO\PadronImpuestoService;

function invocarDedupePadron(string $metodo, ...$argumentos)
{
    $reflection = new ReflectionMethod(PadronImpuestoService::class, $metodo);

    return $reflection->invoke(new PadronImpuestoService(), ...$argumentos);
}

function invocarCompartidosCarga(string $metodo, ...$argumentos)
{
    $reflection = new ReflectionMethod(CargaImpuestoService::class, $metodo);

    return $reflection->invoke(new CargaImpuestoService(), ...$argumentos);
}

test('deduplica api por folio, empresa y partida conservando el contrato más reciente', function () {
    $padron = collect([
        (object) [
            'folio' => '189',
            'empresa' => 1,
            'partida' => '10110612762500235',
            'clave' => '87854418',
            'comienza' => '2024-04-01',
            'estado' => 'INACTIVO',
        ],
        (object) [
            'folio' => '189',
            'empresa' => 1,
            'partida' => '10110612762500235',
            'clave' => '87854418',
            'comienza' => '2026-12-01',
            'estado' => 'ACTIVO',
        ],
        (object) [
            'folio' => '189',
            'empresa' => 1,
            'partida' => '10110612762500244',
            'clave' => '87854418',
            'comienza' => '2026-10-01',
            'estado' => 'ACTIVO',
        ],
    ]);

    $resultado = invocarDedupePadron('deduplicarPadronFiltrado', $padron, 'api');

    expect($resultado)->toHaveCount(2);
    expect($resultado->first()->partida)->toBe('10110612762500235');
    expect($resultado->first()->comienzo)->toBe('2026-12-01');
    expect($resultado->last()->partida)->toBe('10110612762500244');
});

test('no mezcla claves distintas en tgi/agua/gas', function () {
    foreach (['tgi', 'agua', 'gas'] as $impuesto) {
        $padron = collect([
            (object) [
                'folio' => '469',
                'empresa' => 1,
                'partida' => '00155991',
                'clave' => '8964045',
                'comienza' => '2025-05-16',
            ],
            (object) [
                'folio' => '469',
                'empresa' => 1,
                'partida' => '00155991',
                'clave' => '8964046',
                'comienza' => '2026-11-16',
            ],
        ]);

        $resultado = invocarDedupePadron('deduplicarPadronFiltrado', $padron, $impuesto);

        expect($resultado)->toHaveCount(2);
        expect($resultado->pluck('clave')->all())->toBe(['8964045', '8964046']);
    }
});

test('mantiene el orden de primera aparición al deduplicar', function () {
    $padron = collect([
        (object) [
            'folio' => '100',
            'empresa' => 1,
            'partida' => '00000001',
            'clave' => '111',
            'comienza' => '2025-01-01',
        ],
        (object) [
            'folio' => '200',
            'empresa' => 1,
            'partida' => '00000002',
            'clave' => '222',
            'comienza' => '2025-02-01',
        ],
        (object) [
            'folio' => '100',
            'empresa' => 1,
            'partida' => '00000001',
            'clave' => '111',
            'comienza' => '2026-12-01',
        ],
    ]);

    $resultado = invocarDedupePadron('deduplicarPadronFiltrado', $padron, 'tgi');

    expect($resultado)->toHaveCount(2);
    expect($resultado->pluck('folio')->all())->toBe(['100', '200']);
    expect($resultado->first()->comienzo)->toBe('2026-12-01');
});

test('decodifica compartidos desde JSON, array e inválido', function () {
    $json = '[{"folio":189,"estado":"ACTIVO","empresa":1}]';
    $array = [
        ['folio' => 190, 'estado' => 'INACTIVO', 'empresa' => 1],
    ];

    expect(invocarCompartidosCarga('decodificarCompartidos', $json))
        ->toBe([['folio' => 189, 'estado' => 'ACTIVO', 'empresa' => 1]]);

    expect(invocarCompartidosCarga('decodificarCompartidos', $array))
        ->toBe($array);

    expect(invocarCompartidosCarga('decodificarCompartidos', 'no es json'))
        ->toBe([]);

    expect(invocarCompartidosCarga('decodificarCompartidos', null))
        ->toBe([]);
});
