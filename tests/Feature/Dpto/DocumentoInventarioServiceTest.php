<?php

use App\Models\proceso\Documento_inventario;
use App\Models\proceso\Proceso_propiedad;
use App\Services\Dpto_Tecnico\DocumentoInventarioService;
use App\Services\Dpto_Tecnico\InventarioRutas;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

// Carpeta temporal en lugar de \\10.10.10.183, y transacción en mysql10 revertida al final.
beforeEach(function () {
    $this->rutaTemporal = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'inv_doc_test_' . uniqid();
    File::makeDirectory($this->rutaTemporal, 0777, true);
    config(['inventario.fotos_ruta_fisica' => $this->rutaTemporal]);

    DB::connection('mysql10')->beginTransaction();
});

afterEach(function () {
    DB::connection('mysql10')->rollBack();
    File::deleteDirectory($this->rutaTemporal);
});

function procesoDocPrueba(int $id, int $propiedad, string $fechaReserva): Proceso_propiedad
{
    $proceso = new Proceso_propiedad(['id_propiedad' => $propiedad, 'fecha_reserva' => $fechaReserva]);
    $proceso->id = $id;

    return $proceso;
}

// Genera un .docx mínimo válido (zip con las partes que identifican a Word).
function docxReal(string $ruta): string
{
    $zip = new ZipArchive();
    $zip->open($ruta, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"/>');
    $zip->addFromString('word/document.xml', '<?xml version="1.0"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body/></w:document>');
    $zip->close();

    return $ruta;
}

// Usa el mismo cálculo que producción (InventarioRutas), con la base temporal configurada en beforeEach.
function rutaFisicaDoc(string $base, Documento_inventario $doc): string
{
    return InventarioRutas::rutaFisicaDesdeUrl($doc->url);
}

it('guarda el documento en propiedad/año_reserva/proceso con nombre original y usuario', function () {
    $service = new DocumentoInventarioService();
    $archivo = UploadedFile::fake()->create('Informe final.docx', 10, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    $doc = $service->guardar(procesoDocPrueba(57, 1680, '2026-03-15 10:00:00'), $archivo, 9);

    expect($doc->url)->toBe('/inventario/1680/2026/Informe%20final.docx')
        ->and(basename(rawurldecode($doc->url)))->toBe('Informe final.docx')
        ->and($doc->nombre_original)->toBe('Informe final.docx')
        ->and($doc->quien_cargo)->toBe(9)
        ->and(file_exists(rutaFisicaDoc($this->rutaTemporal, $doc)))->toBeTrue();
});

it('mantiene un solo documento por proceso: el nuevo reemplaza al anterior', function () {
    $service = new DocumentoInventarioService();
    $proceso = procesoDocPrueba(57, 1680, '2026-03-15');

    $primero = $service->guardar($proceso, UploadedFile::fake()->create('a.doc', 1, 'application/msword'), 1);
    $segundo = $service->guardar($proceso, UploadedFile::fake()->create('b.docx', 1, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'), 1);

    $lista = $service->listar(57);

    expect($lista->pluck('id')->all())->toBe([$segundo->id])
        ->and(Documento_inventario::find($primero->id))->toBeNull()
        ->and(file_exists(rutaFisicaDoc($this->rutaTemporal, $primero)))->toBeFalse()
        ->and(file_exists(rutaFisicaDoc($this->rutaTemporal, $segundo)))->toBeTrue();
});

it('al subir el mismo nombre reemplaza el anterior y conserva el nombre exacto', function () {
    $service = new DocumentoInventarioService();
    $proceso = procesoDocPrueba(57, 1680, '2026-03-15');

    $primero = $service->guardar($proceso, UploadedFile::fake()->create('Acta.docx', 1, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'), 1);
    $segundo = $service->guardar($proceso, UploadedFile::fake()->create('Acta.docx', 2, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'), 1);

    $carpeta = $this->rutaTemporal . DIRECTORY_SEPARATOR . '1680' . DIRECTORY_SEPARATOR . '2026';

    expect($service->listar(57)->pluck('id')->all())->toBe([$segundo->id])
        ->and($segundo->url)->toBe('/inventario/1680/2026/Acta.docx')
        ->and(Documento_inventario::find($primero->id))->toBeNull()
        ->and(scandir($carpeta))->toContain('Acta.docx')
        ->and(scandir($carpeta))->not->toContain('Acta (1).docx');
});

it('no reemplaza documentos de otro proceso de la misma propiedad', function () {
    $service = new DocumentoInventarioService();

    $a = $service->guardar(procesoDocPrueba(57, 1680, '2026-03-15'), UploadedFile::fake()->create('a.doc', 1, 'application/msword'), 1);
    $b = $service->guardar(procesoDocPrueba(88, 1680, '2026-08-01'), UploadedFile::fake()->create('b.doc', 1, 'application/msword'), 1);

    expect($service->listar(57)->pluck('id')->all())->toBe([$a->id])
        ->and($service->listar(88)->pluck('id')->all())->toBe([$b->id]);
});

it('eliminar borra el archivo físico y el registro', function () {
    $service = new DocumentoInventarioService();
    $doc = $service->guardar(procesoDocPrueba(57, 1680, '2026-03-15'), UploadedFile::fake()->create('c.doc', 1, 'application/msword'), 1);
    $fisica = rutaFisicaDoc($this->rutaTemporal, $doc);
    expect(file_exists($fisica))->toBeTrue();

    $service->eliminar(Documento_inventario::find($doc->id));

    expect(file_exists($fisica))->toBeFalse()
        ->and(Documento_inventario::find($doc->id))->toBeNull();
});

it('acepta un .docx real y rechaza un PDF disfrazado de Word', function () {
    $reglas = [
        'documento' => [
            'required', 'file', 'max:20480', 'mimes:doc,docx',
            'mimetypes:application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
    ];

    $docx = new UploadedFile(docxReal(sys_get_temp_dir() . '/real_' . uniqid() . '.docx'), 'real.docx', null, null, true);
    expect(Validator::make(['documento' => $docx], $reglas)->passes())->toBeTrue();

    $pdfFalso = tempnam(sys_get_temp_dir(), 'pdf') . '.docx';
    file_put_contents($pdfFalso, "%PDF-1.4\n%fake\n");
    $pdf = new UploadedFile($pdfFalso, 'falso.docx', null, null, true);
    expect(Validator::make(['documento' => $pdf], $reglas)->passes())->toBeFalse();
});
