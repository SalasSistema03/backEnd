<?php

namespace App\Http\Controllers\impuesto\Exportar_PDF_impuesto;

use App\Models\At_cl\Usuario;
use App\Services\impuesto\Api\CargaApiService;
use Barryvdh\Snappy\Facades\SnappyPdf;

class Pdf_Api
{
    protected $cargarApiService;
    protected $usuario, $id_usuario;

    public function __construct(CargaApiService $cargarApiService)
    {
        $this->cargarApiService = $cargarApiService;
        $this->id_usuario = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        $this->usuario = Usuario::find($this->id_usuario);
    }

    public function PDF_broche_api($anio, $mes)
    {
        $data = $this->cargarApiService->obtenerRegistrosPorBroche($anio, $mes);


        $pdf = SnappyPdf::loadView(
            'impuesto.api.PDF.listado_api_PDF',
            compact('data')
        )
            ->setOption('page-size', 'a4')
            ->setOption('orientation', 'portrait')
            ->setOption('enable-local-file-access', true) // Permite acceso a archivos locales
            ->setOption('zoom', 0.8)
            ->setOption('dpi', 300)
            ->setOption('disable-smart-shrinking', true)
            ->setOption('footer-left', 'Salas Inmobiliaria') // Texto pie izquierdo
            ->setOption('footer-font-size', 7) // Tamaño fuente pie (px)
            ->setOption('footer-center', $this->usuario->name)
            ->setOption('zoom', 0.85);

        return $pdf->stream("listadoEstados.pdf");
    }


    // Este metodo genera el pdf del borche de SALAS consumiendo el servicio obtenerRegistrosDesdeFolio50000
    public function PDF_BorcheSalas_api($anio, $mes)
    {
        $data = $this->cargarApiService->obtenerRegistrosDesdeFolio50000($anio, $mes);


        $pdf = SnappyPdf::loadView(
            'impuesto.api.PDF.listado_api_salas_PDF',
            compact('data')
        )
            ->setOption('page-size', 'a4')
            ->setOption('orientation', 'portrait')
            ->setOption('enable-local-file-access', true) // Permite acceso a archivos locales
            ->setOption('zoom', 0.8)
            ->setOption('dpi', 300)
            ->setOption('disable-smart-shrinking', true)
            ->setOption('footer-left', 'Salas Inmobiliaria') // Texto pie izquierdo
            ->setOption('footer-font-size', 7) // Tamaño fuente pie (px)
            ->setOption('footer-center', $this->usuario->name)
            ->setOption('zoom', 0.85);

        return $pdf->stream("listadoEstados.pdf");
    }
}
