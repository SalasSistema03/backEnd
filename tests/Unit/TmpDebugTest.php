<?php

use App\Services\impuesto\IMPUESTO\CargaImpuestoService;

test('tmp debug selector', function () {
    $registros = collect([
        (object) ['folio' => 189, 'estado' => 'INACTIVO', 'comienzo' => '2024-01-15'],
        (object) ['folio' => 189, 'estado' => 'ACTIVO', 'comienzo' => '2026-12-01'],
    ]);

    $manual = $registros->groupBy(fn($i) => (string) $i->folio)
        ->map(fn($f) => $f->sortByDesc('comienzo')->first())
        ->values();

    fwrite(STDERR, "\nMANUAL: " . $manual->first()->estado . ' / ' . $manual->first()->comienzo);

    $service = (new CargaImpuestoService())->seleccionarFoliosCompartidos($registros);
    fwrite(STDERR, "\nSERVICE: " . $service->first()->estado . ' / ' . $service->first()->comienzo . "\n");

    expect(true)->toBeTrue();
});
