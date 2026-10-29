<?php

namespace App\Http\Controllers\impuesto\Exportar_PDF_impuesto;

use App\Models\usuarios_y_permisos\Usuario;
use App\Services\impuesto\IMPUESTO\PDF_IMPUESTO\PdfImpuesto;
use App\Services\impuesto\TGI\CargaTgiService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PdfImpuestoController
{
    protected $cargarTgiService;
    protected $usuario, $id_usuario;

    public function __construct(CargaTgiService $cargarTgiService)
    {
        $this->cargarTgiService = $cargarTgiService;
    }

    public function PDF_broche(Request $request)
    {
        $data = (new PdfImpuesto)->obtenerRegistrosPorBroche($request->anio, $request->mes, $request->impuesto);
        //Log::info($data);
        return response()->json($data);
    }


    // Este metodo genera el pdf del borche de SALAS consumiendo el servicio obtenerRegistrosDesdeFolio50000
    public function PDF_BorcheSalas(Request $request)
    {
        $data = (new PdfImpuesto)->obtenerRegistrosDesdeFolio50000($request->anio, $request->mes, $request->impuesto);

        return response()->json($data);
    }

    public function PDF_BrocheSP(Request $request)
    {
        $data = (new PdfImpuesto)->obtenerRegistroPorBrocheSP($request->anio, $request->mes, $request->impuesto);

        return response()->json($data);
    }

    //Exportación de broches especiales (VENCIDA / FUERA_BROCHE)
    public function PDF_BrocheEspecial(Request $request)
    {
        try {
            $data = (new PdfImpuesto)->obtenerRegistrosPorMotivo(
                $request->anio,
                $request->mes,
                $request->impuesto,
                $request->motivo
            );

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function descargaPdf(Request $request)
    {
        // Log::info('entro');
        // Los datos que antes pasabas por props en Vue
        $broches = $request->input('broches');
        $anio = $request->input('anio');
        $mes = $request->input('mes');
        $impuesto = $request->input('impuesto');
        $motivo = $request->input('motivo');
        $usuario_id = auth('api')->id();
        $username = Usuario::where('id', $usuario_id)->first()->username;
        //Log::info('informacion del broche', ['broches' => $broches, 'anio' => $anio, 'mes' => $mes, 'impuesto' => $impuesto, 'request' => $request->all()]);

        $tituloPdf = $motivo ? 'Broches ' . str_replace('_', ' ', $motivo) : 'Broches de Impuestos';
        $nombreArchivo = ($motivo ? strtolower($motivo) . '_' : 'broches_') . "{$anio}_{$mes}.pdf";

        // Generamos el HTML usando una vista de Blade limpia
        $html = view('pdfs.broches', compact('broches', 'anio', 'mes', 'impuesto'))->render();

        return response()->streamDownload(function () use ($html, $username, $tituloPdf) {
            echo \Spatie\Browsershot\Browsershot::html($html)
                ->format('A4')
                ->margins(10, 10, 10, 10)
                ->emulateMedia('screen')
                ->showBackground()
                ->setOption('displayHeaderFooter', true)
                ->setOption('headerTemplate', '<div style="font-size:10px; color:#666; width:100%; display:flex; justify-content:space-between; padding:0 20px;"><span style="text-align:left;">' . $tituloPdf . '</span><span style="text-align:right;" class="date"></span></div>')
                ->setOption('footerTemplate', '<div style="font-size:10px; color:#666; width:100%; display:flex; justify-content:space-between; padding:0 20px;"><span style="text-align:left;">Salas Inmobiliaria</span><span style="text-align:right;">' . $username . '</span></div>')
                ->pdf();
        }, $nombreArchivo);
    }
}
