<?php

use App\Services\proceso\RepactadoService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

it('descarta contratos reemplazados por otro contrato con mayor ID para la misma casa', function () {
    $service = new RepactadoService();
    $method = new \ReflectionMethod($service, 'esContratoMasRecientePorCasa');
    $method->setAccessible(true);

    expect($method->invoke($service, 42, 105, [42 => 109]))->toBeFalse()
        ->and($method->invoke($service, 42, 109, [42 => 109]))->toBeTrue()
        ->and($method->invoke($service, 43, 105, [42 => 109]))->toBeFalse()
        ->and($method->invoke($service, 42, 109, ['1|42' => 109], 1))->toBeTrue()
        ->and($method->invoke($service, 42, 109, ['2|42' => 109], 1))->toBeFalse();
});

it('requiere al menos un filtro para listar repactados', function () {
    expect(fn() => (new RepactadoService())->getRepactadoFiltrado(
        Request::create('/getRepactadoFiltrado', 'GET')
    ))->toThrow(ValidationException::class);
});
