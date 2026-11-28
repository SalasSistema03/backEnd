<?php

use App\Services\impuesto\AGUA\ExtraerCodBarraAgua;

test('extrae las dos cuotas del nuevo código de barras de agua', function () {
    $codigoBarras = '0000686883141226002045128014012700000020451280L';
    $extractor = new ExtraerCodBarraAgua();

    expect(strlen($codigoBarras))->toBe(47);
    expect($extractor->separarFechaVencimiento($codigoBarras, 10))->toBe('2026-12-14');
    expect($extractor->separarImporte($codigoBarras, 18))->toBe('204512.80');
    expect($extractor->separarFechaVencimiento($codigoBarras, 26))->toBe('2027-01-14');
    expect($extractor->separarImporte($codigoBarras, 38))->toBe('204512.80');
});
