<?php

use App\Services\At_cl\FiltrosPdfService;

uses(Tests\TestCase::class);

test('it filters properties that have a key', function () {
    $query = (new FiltrosPdfService())->aplicarFiltrosUnificados([
        'sector' => 'Venta',
        'llave' => 's',
    ]);

    expect($query->toSql())->toContain('`llave` > ?')
        ->and($query->getBindings())->toContain(0);
});

test('it filters properties without a key including null key values', function () {
    $query = (new FiltrosPdfService())->aplicarFiltrosUnificados([
        'sector' => 'Alquiler',
        'llave' => 'no',
    ]);

    expect($query->toSql())->toContain('`llave` is null or `llave` <= ?')
        ->and($query->getBindings())->toContain(0);
});

test('it does not filter properties by key when all are requested', function () {
    $query = (new FiltrosPdfService())->aplicarFiltrosUnificados([
        'sector' => 'Venta',
        'llave' => '',
    ]);

    expect($query->toSql())->not->toContain('`llave`');
});
