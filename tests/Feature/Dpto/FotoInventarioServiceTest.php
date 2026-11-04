<?php

use App\Models\proceso\Foto_inventario;
use App\Models\proceso\Proceso_propiedad;
use App\Services\Dpto_Tecnico\FotoInventarioService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

// Carpeta temporal en lugar del recurso 10.10.10.183, y transacción en mysql10 revertida al final.
beforeEach(function () {
    $this->rutaTemporal = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'inv_test_' . uniqid();
    File::makeDirectory($this->rutaTemporal, 0777, true);
    config(['inventario.fotos_ruta_fisica' => $this->rutaTemporal]);

    DB::connection('mysql10')->beginTransaction();
});

afterEach(function () {
    DB::connection('mysql10')->rollBack();
    File::deleteDirectory($this->rutaTemporal);
});

function procesoDePrueba(int $id, int $propiedad, string $fechaReserva): Proceso_propiedad
{
    $proceso = new Proceso_propiedad(['id_propiedad' => $propiedad, 'fecha_reserva' => $fechaReserva]);
    $proceso->id = $id;

    return $proceso;
}

// Carpeta física de la propiedad y año (sin carpeta de proceso: el proceso está en la tabla).
function carpetaFotos(string $base, int $propiedad, int $anio): string
{
    return $base . DIRECTORY_SEPARATOR . $propiedad . DIRECTORY_SEPARATOR . $anio;
}

it('guarda la foto como /inventario/{propiedad}/{año}/propiedad_{id}_{n}.ext y registra comentario y usuario', function () {
    $service = new FotoInventarioService();
    $proceso = procesoDePrueba(57, 1680, '2026-03-15 10:00:00');

    $archivo = UploadedFile::fake()->create('frente.jpg', 10, 'image/jpeg');
    $foto = $service->guardar($proceso, [$archivo], ['frente de la casa'], 9)[0];

    expect($foto->url)->toBe('/inventario/1680/2026/propiedad_1680_0.jpg')
        ->and($foto->notes)->toBe('frente de la casa')
        ->and($foto->quien_cargo)->toBe(9)
        ->and($foto->propiedad_id)->toBe(1680)
        ->and($foto->id_proceso_propiedad)->toBe(57);

    expect(file_exists(carpetaFotos($this->rutaTemporal, 1680, 2026) . DIRECTORY_SEPARATOR . 'propiedad_1680_0.jpg'))->toBeTrue();
});

it('numera las fotos del mismo proceso en forma correlativa', function () {
    $service = new FotoInventarioService();
    $proceso = procesoDePrueba(57, 1680, '2026-03-15 10:00:00');

    $a = $service->guardar($proceso, [UploadedFile::fake()->create('x.jpg', 1, 'image/jpeg')], [], 1)[0];
    $b = $service->guardar($proceso, [UploadedFile::fake()->create('y.jpg', 1, 'image/jpeg')], [], 1)[0];

    expect($a->url)->toBe('/inventario/1680/2026/propiedad_1680_0.jpg')
        ->and($b->url)->toBe('/inventario/1680/2026/propiedad_1680_1.jpg');
});

it('no pisa un archivo existente de otra reserva de la misma propiedad y año', function () {
    $service = new FotoInventarioService();

    $a = $service->guardar(procesoDePrueba(57, 1680, '2026-03-15'), [UploadedFile::fake()->create('a.jpg', 1, 'image/jpeg')], [], 1)[0];
    $b = $service->guardar(procesoDePrueba(88, 1680, '2026-08-01'), [UploadedFile::fake()->create('b.jpg', 1, 'image/jpeg')], [], 1)[0];

    expect($a->url)->toBe('/inventario/1680/2026/propiedad_1680_0.jpg')
        ->and($b->url)->toBe('/inventario/1680/2026/propiedad_1680_0%20%281%29.jpg');

    $carpeta = carpetaFotos($this->rutaTemporal, 1680, 2026);
    expect(file_exists($carpeta . DIRECTORY_SEPARATOR . 'propiedad_1680_0.jpg'))->toBeTrue()
        ->and(file_exists($carpeta . DIRECTORY_SEPARATOR . 'propiedad_1680_0 (1).jpg'))->toBeTrue();
});

it('separa inventarios de reservas distintas de la misma propiedad y año', function () {
    $service = new FotoInventarioService();

    $a = $service->guardar(procesoDePrueba(57, 1680, '2026-03-15'), [UploadedFile::fake()->create('a.jpg', 1, 'image/jpeg')], [], 1);
    $b = $service->guardar(procesoDePrueba(88, 1680, '2026-08-01'), [UploadedFile::fake()->create('b.jpg', 1, 'image/jpeg')], [], 1);

    expect($service->listar(57)->pluck('id')->all())->toBe([$a[0]->id])
        ->and($service->listar(88)->pluck('id')->all())->toBe([$b[0]->id]);
});

it('usa el año de carga del inventario cuando la reserva no tiene fecha', function () {
    $service = new FotoInventarioService();
    $proceso = procesoDePrueba(70, 1680, '2026-01-01');
    $proceso->fecha_reserva = null;

    $proceso->setRelation('historialEstadoDpto', new \App\Models\proceso\Historial_estado_dpto(['fecha_carga' => '2025-06-10']));

    $foto = $service->guardar($proceso, [UploadedFile::fake()->create('c.jpg', 1, 'image/jpeg')], [], 1)[0];

    expect($foto->url)->toBe('/inventario/1680/2025/propiedad_1680_0.jpg');
});

it('eliminar borra el archivo físico y el registro', function () {
    $service = new FotoInventarioService();
    $foto = $service->guardar(procesoDePrueba(57, 1680, '2026-03-15'), [UploadedFile::fake()->create('d.jpg', 1, 'image/jpeg')], [], 1)[0];

    $fisica = carpetaFotos($this->rutaTemporal, 1680, 2026) . DIRECTORY_SEPARATOR . 'propiedad_1680_0.jpg';
    expect(file_exists($fisica))->toBeTrue();

    $service->eliminar(Foto_inventario::find($foto->id));

    expect(file_exists($fisica))->toBeFalse()
        ->and(Foto_inventario::find($foto->id))->toBeNull();
});
