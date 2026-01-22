<?php

namespace App\Http\Controllers\impuesto;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\At_cl\Usuario;
use App\Services\At_cl\PermitirAccesoPropiedadService;
use App\Services\clientes\Permisos;
use App\Services\impuesto\API\CargaApiService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\impuesto\API\ExtraerCodigoBarras;
use App\Models\impuesto\Api_Padron;


class ApiController extends Controller
{
    protected $cargarApiService;
    protected $usuario;
    protected $usuario_id;
    protected $permisoService;
    protected $extraerCodigoBarras;


    public function __construct(
        CargaApiService $cargarApiService,
        Permisos $permisoService,
        ExtraerCodigoBarras $extraerCodigoBarras
    ) {
        $this->cargarApiService = $cargarApiService;
        $this->permisoService = $permisoService;
        $this->extraerCodigoBarras = $extraerCodigoBarras;
    }


    public function index(Request $request)
    {


        //$this->usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        //$this->usuario = Usuario::find($this->usuario_id);

        //$vistaCargarTgi = 'carga_tgi';
        //$permisoService = new PermitirAccesoPropiedadService($this->usuario->id);

        // Verificar si el usuario tiene acceso a la vista
        //if (!$permisoService->tieneAccesoAVista($vistaCargarTgi)) {
        // Redirigir o mostrar un mensaje de error si no tiene acceso
        //return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        //}

        // Si vienen nuevos filtros, los guardamos en sesión
        if ($request->filled('anio') || $request->filled('mes') || $request->filled('busqueda')) {
            session([
                'filtro_anio' => $request->input('anio'),
                'filtro_mes' => $request->input('mes'),
                'filtro_folio' => $request->input('folio'),
                'filtro_estado' => $request->input('estado'),
                'filtro_bajado' => $request->input('bajado'),
                'filtro_busqueda' => $request->input('busqueda'),
            ]);
        }

        // Recuperamos los filtros desde sesión si no vienen en el request
        $anio = $request->input('anio', session('filtro_anio'));
        $mes = $request->input('mes', session('filtro_mes'));
        $folio = $request->input('folio', session('filtro_folio'));
        $estado = $request->input('estado', session('filtro_estado'));
        $bajado = $request->input('bajado', session('filtro_bajado'));
        $busqueda = $request->input('busqueda', session('filtro_busqueda'));


        $query = $this->cargarApiService->obtenerRegistros();

        if ($anio) {
            $query->where('periodo_anio', $anio);
        }


        if ($mes) {
            $query->where('periodo_mes', $mes);
        }

        if ($folio) {
           
            $query->where(function ($q) use ($folio) {
                // Buscar en padron
                $q->whereHas('padron', function ($sub) use ($folio) {
                    $sub->where('folio', $folio);
                });
                /* dd($q); */

                // Buscar dentro del JSON embebido en 'compartidos'
                $q->orWhere('compartidos', 'like', '%"folio":' . (int)$folio . '%');
            });
        }

        if ($estado) {
            $query->whereHas('padron', function ($sub) use ($estado) {
                $sub->where('estado', $estado);
            });
        }
        if ($bajado) {
            if ($bajado === 'N') {
                $query->where(function ($q) {
                    $q->whereNull('bajado')
                        ->orWhere('bajado', '=', 'N');
                });
            }
        }
        if ($busqueda) {
            /* dd($busqueda); */
            $query->where(function ($q) use ($busqueda) {
                $q->whereHas('padron', function ($sub) use ($busqueda) {
                    $sub->where('partida', 'like', "%{$busqueda}%");
                    /* $sub->where('folio', 'like', "%{$busqueda}%")
                        ->orWhere('partida', 'like', "%{$busqueda}%"); */
                });

                // Si el campo compartidos es texto plano con JSON embebido
                $q->orWhere('compartidos', 'like', '%"partida":' . (int)$busqueda . '%');
            });
        }


        $registros = $query->get();
        // Procesar cada registro para extraer información del código de barras
        $registros->transform(function ($registro) {
            // Asumiendo que el código de barras está en el campo 'codigo_barra'
            if (isset($registro->codigo_barra)) {
                $datosSeparados = $this->extraerCodigoBarras->separarCodigoBarras($registro->codigo_barra);

                // Agregar los datos extraídos al registro
                $registro->partida_extraida = $datosSeparados['partida'];
                $registro->importe_extraido = $datosSeparados['importe'];
                $registro->fecha_vencimiento_extraida = $datosSeparados['fecha_vencimiento'];

                //con partida extraida buscar en la tabla padron
                $padron = Api_Padron::where('partida', $registro->partida_extraida)->first();
                $registro->administra = $padron->administra;
            }
            return $registro;
        });


        return view('impuesto.api.carga_API', compact('registros'));
    }


    public function cargarNuevoApi(Request $request)
    {
        
        try {
            $codigoBarras = $request->input('codigo_barras');
            if (!$codigoBarras) {
                return redirect()->back()->with('error', 'El campo código de barras es obligatorio.');
            }

            if (empty($codigoBarras) || strlen($codigoBarras) !== 50) {
                return redirect()->back()->with('error', 'Debés ingresar un código de barras válido de 50 caracteres.');
            }

            $this->cargarApiService->cargarNuevoApiService($codigoBarras);

            // Recuperamos los filtros desde sesión para mantenerlos
            return redirect()->route('carga_api', [
                'anio' => session('filtro_anio'),
                'mes' => session('filtro_mes'),
                'busqueda' => session('filtro_busqueda'),
            ])->with('success', 'El código de barras se ha procesado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function cargarNuevoApiControllerManual(Request $request)
    {

        try {
            $this->cargarApiService->cargarNuevoApiServiceManual($request);
            return redirect()->route(
                'carga_api',
                [
                    'anio' => $request->input('anio', session('filtro_anio')),
                    'mes' => $request->input('mes', session('filtro_mes')),
                    'busqueda' => $request->input('busqueda', session('filtro_busqueda')),
                ]
            )->with('success', 'Registro cargado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function eliminarRegistro(Request $request)
    {
        $id = $request->id;
        $this->cargarApiService->eliminarRegistro($id);


        return redirect()->route('carga_api', [
            'anio' => $request->input('anio', session('anio')),
            'mes' => $request->input('mes', session('mes')),
            'busqueda' => $request->input('busqueda', session('busqueda')),
        ])->with('success', 'El registro se ha eliminado correctamente.');
    }


    public function sumarMontos($anio, $mes)
    {
        $total = $this->cargarApiService->sumarMontosApiService($anio, $mes);
        $totalSalas = $this->cargarApiService->sumarMontosApiSalasService($anio, $mes);

        return response()->json([
            'total' => $total,
            'totalSalas' => $totalSalas,
        ]);
    }

    public function exportarApiFaltantes($anio, $mes)
    {
       
        $registros = $this->cargarApiService->exportarApiFaltantesService($anio, $mes);

        $contenido = '';

        foreach ($registros as $r) {
            $fila = [
                $r->folio,
                $r->partida,
                $r->abona,
                $r->administra,
                $r->empresa,
                $r->estado,
                Carbon::parse($r->comienza)->format('Y-m-d'),
                Carbon::parse($r->rescicion)->format('Y-m-d'),
            ];

            $contenido .= implode("\t", $fila) . "\n";
        }

        $nombreArchivo = "api_faltantes_{$anio}_{$mes}.txt";

        return Response::make($contenido, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename={$nombreArchivo}",
        ]);
    }


    //Este metodo sirve para mostrar en formato JSON la info de los broches, y que sea consumia por el front JS
    public function MostrarBroche($anio, $mes, $cantidadBroches)
    {
        $resultado = $this->cargarApiService->generarDistribucionBroches($anio, $mes, $cantidadBroches);

        return response()->json($resultado);
    }

    //Este metodo sirve para guardar en la base la info de los broches, y que sea consumia por el front JS
    public function guardarBroches($anio, $mes, $cantidadBroches)
    {
     
        try {
            $resultado = $this->cargarApiService->generarDistribucionBroches($anio, $mes, $cantidadBroches);
            log::info('Registros filtrados para guardar broches: ', ['registros' => $resultado['registrosFiltrados']]);

            $this->cargarApiService->guardarDistribucionBroches($resultado['registrosFiltrados']);

            return response()->json([
                'status' => 'success',
                'message' => 'Los broches se guardaron correctamente.'
            ]);
        } catch (\Exception $e) {
            Log::error("Error al guardar broches: " . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Hubo un problema al guardar los broches: ' . $e->getMessage()
            ], 500);
        }
    }


    //Este metodo sirve para guardar en la base la info de los broches, y que sea consumia por el front JS
    //  -- IMPORTANTE  --  SOLO ARMA BROCHE PARA REGISTROS QUE TENGAN FOLIOS 50000 EN ADELANTE
    public function guardarBrocheSALAS($anio, $mes)
    {
        try {
            $this->cargarApiService->guardarDistribucionBrocheSALAS($anio, $mes);

            return response()->json([
                'status' => 'success',
                'message' => 'Los broches se guardaron correctamente.'
            ]);
        } catch (\Exception $e) {
            Log::error("Error al guardar broches: " . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Hubo un problema al guardar los broches: ' . $e->getMessage()
            ], 500);
        }
    }

    
    /* Modificar estado de un registro de tgi_carga */
    public function modificarEstadoController(Request $request, $id)
    {
        try {
            $estado = $request->input('estado');
            $this->cargarApiService->modificarEstado($id, $estado);

            return redirect()->route(
                'carga_api',
                [
                    'anio' => $request->input('anio', session('filtro_anio')),
                    'mes' => $request->input('mes', session('filtro_mes')),
                    'busqueda' => $request->input('busqueda', session('filtro_busqueda')),
                ]
            )->with('success', 'Estado modificado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al modificar el estado: ' . $e->getMessage());
        }
    }

    //Este controlador modifica el bajado de los registros de tgi_carga que tengan num_broche. Rorna un mensaje correcto
    public function modificarBajadoController($anio, $mes)
    {
        try {
            $this->cargarApiService->modificarBajadoService($anio, $mes);

            return redirect()->route(
                'carga_api',
                [
                    'anio' => $anio,
                    'mes' => $mes,
                ]
            )->with('success', 'Registro cargado correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al modificar bajado: " . $e->getMessage());

            return redirect()->back()->with('error', 'Hubo un problema al modificar el bajado: ' . $e->getMessage());
        }
    }

    // Exportar PDF
    /* public function exportarBroches($anio, $mes)
    {
        $resultado = $this->cargarTgiService->obtenerRegistrosPorBroche($anio, $mes);
        return response()->json($resultado);
    } */
}

