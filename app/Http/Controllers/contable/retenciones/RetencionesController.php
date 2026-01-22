<?php

namespace App\Http\Controllers\contable\retenciones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contable\retenciones\Padron_retenciones;
use App\Models\Contable\retenciones\Provincia_retencion;
use App\Models\Contable\retenciones\base_porcentual;
use App\Models\Contable\retenciones\Comprobante_retencion;
use Illuminate\Support\Facades\DB;
use App\Services\contable\retenciones\Padron_retencionesService;
use App\Services\contable\retenciones\Base_porcentualService;
use App\Services\contable\retenciones\Comprobante_retencionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class RetencionesController extends Controller
{
    protected $usuario_id;
    public function __construct()
    {
        $this->usuario_id = session('usuario_id');
    }
    public function index()
    {
        $bases_porcentuales = Base_porcentual::all();
        $provincias = Provincia_retencion::all();


        return view('contable.retenciones.retenciones', compact('provincias', 'bases_porcentuales'));
    }

    public function devolverPersonasRetenciones()
    {
        $service = new Padron_retencionesService();
        return $service->devolverPersonasRetenciones();
    }

    public function devolverBasePorcentual()
    {
        $service = new Base_porcentualService();
        return $service->devolverBasePorcentual();
    }

    public function store(Request $request)
    {

        try {
            DB::beginTransaction();
            Padron_retenciones::create([
                'cuit_retencion' => $request->cuit_carga_retenciones,
                'razon_social_retencion' => $request->razon_social_carga_retenciones,
                'domicilio_retencion' => $request->domicilio_carga_retenciones,
                'localidad_retencion' => $request->localidad_carga_retenciones,
                'id_provincia_retencion' => $request->provincia_id,
                'codigo_postal_retencion' => $request->codigo_posta_carga_retenciones,
                'last_modified_by' => $this->usuario_id,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Persona cargada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al cargar persona: ' . $e->getMessage());
        }
    }

    public function exportarPersonas()
    {
        try {
            $personas = Padron_retenciones::all();
            $contenido = "";

            foreach ($personas as $persona) {
                $linea = str_pad(substr($persona->cuit_retencion ?? '', 0, 11), 11) .
                    str_pad(substr($persona->razon_social_retencion ?? '', 0, 20), 20) .
                    str_pad(substr($persona->domicilio_retencion ?? '', 0, 20), 20) .
                    str_pad(substr($persona->localidad_retencion ?? '', 0, 20), 20) .
                    str_pad(substr($persona->id_provincia_retencion ?? '', 0, 2), 2, '0', STR_PAD_LEFT) .
                    str_pad(substr($persona->codigo_postal_retencion ?? '', 0, 8), 8) .
                    "80" . "\r\n";

                $contenido .= $linea;
               /*  dump($persona->id_provincia_retencion);
                dd($linea); */
            }

            $nombreArchivo = 'personas_' . date('Y-m-d_His') . '.txt';

            return response()->streamDownload(function () use ($contenido) {
                echo $contenido;
            }, $nombreArchivo, [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"'
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    public function updateBasePorcentual(Request $request)
    {
        /*  dd($request->all());  */


        try {
            // Update base
            if ($request->porcentual_dato != null && $request->base_dato != null) {
                $porcentual = Base_porcentual::where('id_base_porcentual', $request->porcentual_id)->first();
                $porcentual->dato = $request->porcentual_dato;
                $porcentual->last_modified_by = $this->usuario_id;
                $porcentual->save();

                $base = Base_porcentual::where('id_base_porcentual', $request->base_id)->first();
                $base->dato = $request->base_dato;
                $base->last_modified_by = $this->usuario_id;
                $base->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Base y porcentual actualizados correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }


    public function verificarComprobante(Request $request)
    {
        $request->validate([
            'cuit_retencion' => 'required|string',
            'fecha_comprobante' => 'required|date'
        ]);

        $comprobante = Comprobante_retencion::where('cuit_retencion', $request->cuit_retencion)
            ->whereYear('fecha_comprobante', date('Y', strtotime($request->fecha_comprobante)))
            ->whereMonth('fecha_comprobante', date('m', strtotime($request->fecha_comprobante)))
            ->first();

        return response()->json([
            'exito' => $comprobante !== null,
            'datos' => $comprobante
        ]);
    }


    public function guardarRetencion(Request $request)
    {


        function evaluarExpresionDecimal($expresion)
        {
            // Validamos solo números, +, -, comas, espacios
            if (!preg_match('/^[0-9+\-\,\.\s]+$/', $expresion)) {
                return $expresion;
            }

            // Eliminamos espacios
            $expresion = str_replace(' ', '', $expresion);

            // Convertimos comas a puntos (para decimales)
            $expresion = str_replace(',', '.', $expresion);

            // Normalizamos signos
            $expresion = preg_replace('/--/', '+', $expresion);
            $expresion = preg_replace('/\+\+/', '+', $expresion);
            $expresion = preg_replace('/\+-/', '-', $expresion);
            $expresion = preg_replace('/-\+/', '-', $expresion);

            // Extraemos todos los números con signo
            preg_match_all('/[+\-]?[0-9.]+/', $expresion, $matches);
            $numeros = $matches[0];

            // Sumar con precisión flotante
            $total = 0.0;
            foreach ($numeros as $numero) {
                $total += (float)$numero;
            }

            return $total;
        }


        $importeComprobante = evaluarExpresionDecimal($request->suma_retenciones);
        $devolverBasePorcentual = $this->devolverBasePorcentual();
        $base = $devolverBasePorcentual->getData()[0]->dato;
        $porcentual = $devolverBasePorcentual->getData()[1]->dato;
        if ($request->calcula_base == 'S') {
            $importeRetencion =round(($importeComprobante - $base) * $porcentual,2);
        } elseif ($request->calcula_base == 'N') {
            $importeRetencion =round($importeComprobante * $porcentual,2);
        }
        /*  dd($this->usuario_id);  */






        try {
            DB::beginTransaction();

            $comprobante = Comprobante_retencion::create([
                'calcula_base' => $request->calcula_base,
                'cuit_retencion' => $request->cuit_retenciones,
                'fecha_comprobante' => $request->fecha_retenciones,
                'suma_comprobante' => $request->suma_retenciones,
                'numero_comprobante' => $request->numero_comprobante_retenciones,
                'fecha_retencion' => null,
                'importe_comprobante' => $importeComprobante,
                'importe_retencion' => $importeRetencion,
                'last_modified_by' => $this->usuario_id,



            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Comprobante guardado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al guardar comprobante: ' /* . $e->getMessage() */);
        }
    }

    public function tablaRetenciones()
    {
        $fechaActual = Carbon::now();
        $anioActual = $fechaActual->year;
        $mesActual = $fechaActual->month;

        $comprobantes = Comprobante_retencion::select([
            'comprobante_retencion.calcula_base',
            'comprobante_retencion.cuit_retencion',
            'comprobante_retencion.fecha_comprobante',
            'comprobante_retencion.fecha_retencion',
            'comprobante_retencion.id_comprobante',
            'comprobante_retencion.importe_comprobante',
            'comprobante_retencion.importe_retencion',
            'comprobante_retencion.numero_comprobante',
            'comprobante_retencion.suma_comprobante',
            'padron_retencion.razon_social_retencion'
        ])
            ->leftJoin('padron_retencion', 'comprobante_retencion.cuit_retencion', '=', 'padron_retencion.cuit_retencion')
            ->where(function($query) use ($anioActual, $mesActual) {
                $query->whereYear('comprobante_retencion.fecha_comprobante', $anioActual)
                      ->whereMonth('comprobante_retencion.fecha_comprobante', $mesActual)
                      ->orWhere('comprobante_retencion.fecha_comprobante', '0000-00-00');
            })
            ->orderBy('comprobante_retencion.id_comprobante', 'DESC')
            ->orderBy('padron_retencion.razon_social_retencion', 'ASC')
            ->get();
        return response()->json($comprobantes);
    }

    public function comprobantesPorId($id)
    {
        $comprobante = Comprobante_retencion::find($id);
        return response()->json($comprobante);
    }

    public function modificarRetencion(Request $request)
    {
        /*   dd($request->all()); */
        $comprobante = Comprobante_retencion::find($request->id_comprobante);
        $comprobante->update([
            'cuit_retencion' => $request->cuit_retenciones_mi,
            'fecha_comprobante' => $request->fecha_comprobante_m,
            'calcula_base' => $request->calcula_base_m,
            'numero_comprobante' => $request->numero_comprobante_retenciones_m,
            'importe_comprobante' => $request->importe_retenciones_m,
            'importe_retencion' => $request->importe_retencion_mi,
            'last_modified_by' => $this->usuario_id,
        ]);
        return redirect()->back()->with('success', 'Comprobante modificado correctamente.');
    }


    public function obtenerSumaQuincena(Request $request)
    {
        $request->validate([
            'anio' => 'required|numeric',
            'mes' => 'required|numeric|between:1,12'
            
        ]);

        $fechaInicio = Carbon::create($request->anio, $request->mes, 1)->startOfMonth();
        $fechaFin = Carbon::create($request->anio, $request->mes, 1)->endOfMonth();
        

        $comprobantes = Comprobante_retencion::whereBetween('fecha_comprobante', [$fechaInicio, $fechaFin])
            ->get(['fecha_comprobante', 'importe_retencion']);

        return response()->json($comprobantes);
    }

    public function exportarRetenciones(Request $request)
    {
        $fechaUpdate = Carbon::now()->toDateString();
        $fechaInicio = Carbon::create($request->anio, $request->mes, 1)->startOfMonth();
        $fechaFin = Carbon::create($request->anio, $request->mes, 1)->endOfMonth();

        $comprobantes = Comprobante_retencion::where('fecha_retencion', null)->get();
        $contenido = "";
        foreach ($comprobantes as $comprobante) {
            /* $comprobante->update([
                'fecha_retencion' => $fechaInicio,
            ]); */
            $codigo = str_pad("6", 2, "0", STR_PAD_LEFT);
            // Verificar si la fecha es válida
            if (empty($comprobante->fecha_comprobante) || $comprobante->fecha_comprobante === '0000-00-00') {
                // Usar la fecha actual si la fecha no es válida
                $fechaDia = now()->format('d');
                $fechaMes = now()->format('m');
                $fechaAnio = now()->format('Y');
            } else {
                // Usar la fecha del comprobante si es válida
                $fechaDia = Carbon::parse($comprobante->fecha_comprobante)->format('d');
                $fechaMes = Carbon::parse($comprobante->fecha_comprobante)->format('m');
                $fechaAnio = Carbon::parse($comprobante->fecha_comprobante)->format('Y');
            }
            /*  $fechaDia = Carbon::parse($comprobante->fecha_comprobante)->format('d');
            $fechaMes = Carbon::parse($comprobante->fecha_comprobante)->format('m');
            $fechaAnio = Carbon::parse($comprobante->fecha_comprobante)->format('Y'); */
            /* dd($fechaDia, $fechaMes, $fechaAnio); */
            /*  dd($comprobante->cuit_retencion); */
            $fecha = $fechaDia . "/" . $fechaMes . "/" . $fechaAnio;
            $numeroComprobante = str_pad($comprobante->numero_comprobante, 12, "0", STR_PAD_LEFT);
            $importeComp = str_replace(".", ",", $comprobante->importe_comprobante);
            $importeComprobante = str_pad($importeComp, 20, " ", STR_PAD_LEFT);
            $codigo_impr = str_pad("217", 4, "0", STR_PAD_LEFT);
            $codigo_reg = str_pad("31", 3, "0", STR_PAD_LEFT);
            $codigo_opera = "1";
            $baseCalculo = str_pad($importeComp, 14, " ", STR_PAD_LEFT);
           /*  $fechaEmision = Carbon::parse($comprobante->fecha_comprobante)->format('d/m/Y'); */
           if (empty($comprobante->fecha_comprobante) || $comprobante->fecha_comprobante === '0000-00-00') {
            $fechaEmision = now()->format('d/m/Y'); // Usar fecha actual si no es válida
        } else {
            $fechaEmision = now()->format('d/m/Y'); // Usar fecha actual si no es válida
        }
            $codigoCod = str_pad("1", 2, "0", STR_PAD_LEFT);
            $ret_suj_cond = "0";
            $importeRet = str_replace(".", ",", $comprobante->importe_retencion);
            $importeRetencion = str_pad($importeRet, 14, " ", STR_PAD_LEFT);
            $porcentajeExc = str_pad("0,00", 6, " ", STR_PAD_LEFT);
            /* $fechaPublicacion = Carbon::parse($comprobante->fecha_comprobante)->format('d/m/Y'); */
            $fechaPublicacion = str_pad(" ", 10, " ", STR_PAD_LEFT);
            $tipoDoc = str_pad("80", 2, " ", STR_PAD_LEFT);
            $documentoRetencion = str_pad($comprobante->cuit_retencion, 20, " ", STR_PAD_RIGHT);
            $numeroCero = str_pad("0", 28, "0", STR_PAD_LEFT);

            $linea = $codigo . $fecha . $numeroComprobante . $importeComprobante . $codigo_impr . $codigo_reg . $codigo_opera . $baseCalculo . $fechaEmision . $codigoCod . $ret_suj_cond . $importeRetencion . $porcentajeExc . $fechaPublicacion . $tipoDoc . $documentoRetencion . $numeroCero . "\r\n";
            $contenido .= $linea;
            $comprobante->update([
                'fecha_retencion' => $fechaUpdate,
                'last_modified_by' => $this->usuario_id,
            ]);
        }

        $nombreArchivo = 'retenciones_' . date('Y-m-d_His') . '.txt';

        return response()->streamDownload(function () use ($contenido) {
            echo $contenido;
        }, $nombreArchivo, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"'
        ]);
    }

    public function exportarRetencionesExcel(Request $request)
    {

        $comprobantes = Comprobante_retencion::where('fecha_retencion', null)->get();
        
        // Crear el contenido HTML para el Excel
        $html = "<table border='1'>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>N° Comprobante</th>
                    <th>Documento Retención</th>
                    <th>Razon Social</th>
                    <th>Importe Comprobante</th>
                    <th>Importe Retención</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($comprobantes as $comprobante) {
            $fecha = $comprobante->fecha_comprobante;
            if($comprobante->cuit_retencion == null){
                $razon_social_retencion = "";
            }else{
                $razon_social_retencion = Padron_retenciones::where('cuit_retencion', $comprobante->cuit_retencion)->first()->razon_social_retencion;
            }
            $html .= "<tr>";
            $html .= "<td>" . htmlspecialchars($fecha) . "</td>";
            $html .= "<td>" . htmlspecialchars($comprobante->numero_comprobante) . "</td>";
            $html .= "<td>" . htmlspecialchars($comprobante->cuit_retencion) . "</td>";
            $html .= "<td>" . htmlspecialchars($razon_social_retencion) . "</td>";
            $html .= "<td>" . htmlspecialchars(str_replace(".",",",$comprobante->importe_comprobante)) . "</td>";
            $html .= "<td>" . htmlspecialchars(str_replace(".",",",$comprobante->importe_retencion)) . "</td>";
            $html .= "</tr>";
        }

        $html .= "</tbody></table>";

        $nombreArchivo = 'retenciones_' . date('Y-m-d_His') . '.xls';

        // Configurar las cabeceras para forzar la descarga
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        // Devolver la respuesta con el contenido HTML
        return response()->make($html, 200, $headers);
    }

    public function obtenerRetencionesCuit(Request $request)
    {
        $cuit = $request->cuit;

        $retenciones = Comprobante_retencion::select([
            'comprobante_retencion.calcula_base',
            'comprobante_retencion.cuit_retencion',
            'comprobante_retencion.fecha_comprobante',
            'comprobante_retencion.fecha_retencion',
            'comprobante_retencion.id_comprobante',
            'comprobante_retencion.importe_comprobante',
            'comprobante_retencion.importe_retencion',
            'comprobante_retencion.numero_comprobante',
            'comprobante_retencion.suma_comprobante',
            'padron_retencion.razon_social_retencion'
        ])
            ->leftJoin('padron_retencion', 'comprobante_retencion.cuit_retencion', '=', 'padron_retencion.cuit_retencion')
            ->where('comprobante_retencion.cuit_retencion', $cuit)
            ->orderBy('comprobante_retencion.id_comprobante', 'DESC')
            ->orderBy('padron_retencion.razon_social_retencion', 'ASC')
            ->get();

        return response()->json($retenciones);
    }
}
