<?php

namespace App\Http\Controllers\impuesto\Exportar_PDF_impuesto;

use App\Models\At_cl\Usuario;
use App\Services\impuesto\TGI\CargaTgiService;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\Log;

class Pdf_Tgi
{
    protected $cargarTgiService;
    protected $usuario, $id_usuario;

    public function __construct(CargaTgiService $cargarTgiService)
    {
        $this->cargarTgiService = $cargarTgiService;
        $this->id_usuario = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        $this->usuario = Usuario::find($this->id_usuario);
    }

    public function PDF_broche($anio, $mes)
    {
        $data = $this->cargarTgiService->obtenerRegistrosPorBroche($anio, $mes);
        Log::info('Datos para el PDF de broches: ' . json_encode($data));


        $pdf = SnappyPdf::loadView(
            'impuesto.tgi.PDF.listado_tgi_PDF',
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
            ->setOption('footer-center', $this->usuario->username)
            ->setOption('zoom', 0.85);

        return $pdf->stream("listadoEstados.pdf");
    }


    // Este metodo genera el pdf del borche de SALAS consumiendo el servicio obtenerRegistrosDesdeFolio50000
    public function PDF_BorcheSalas($anio, $mes)
    {
        $data = $this->cargarTgiService->obtenerRegistrosDesdeFolio50000($anio, $mes);


        $pdf = SnappyPdf::loadView(
            'impuesto.tgi.PDF.listado_tgi_salas_PDF',
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
            ->setOption('footer-center', $this->usuario->username)
            ->setOption('zoom', 0.85);

        return $pdf->stream("listadoEstados.pdf");
    }
}
