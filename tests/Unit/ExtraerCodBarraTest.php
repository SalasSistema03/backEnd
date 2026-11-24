<?php

use App\Services\impuesto\TGI\ExtraerCodBarra;

function invocarComparacionPeriodoTgi(string $fechaVencimiento, string $fechaTgi): bool
{
    $metodo = new ReflectionMethod(ExtraerCodBarra::class, 'fechaVencimientoSuperaPeriodoTgi');

    return $metodo->invoke(new ExtraerCodBarra(), $fechaVencimiento, $fechaTgi);
}

test('no considera desactualizado diciembre de 2026 cuando el padrón llega hasta marzo de 2027', function () {
    expect(invocarComparacionPeriodoTgi('2026-12-10', '2027-03-01'))->toBeFalse();
});

test('considera desactualizado un vencimiento posterior al período del padrón', function () {
    expect(invocarComparacionPeriodoTgi('2027-04-10', '2027-03-01'))->toBeTrue();
});
