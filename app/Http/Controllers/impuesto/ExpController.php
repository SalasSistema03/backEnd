<?php
// app/Http/Controllers/impuesto/ExpController.php
namespace App\Http\Controllers\impuesto;

use App\Models\impuesto\Exp_administrador_consorcio;
use Illuminate\Http\Request;
use App\Models\impuesto\Exp_edificio;
use App\Models\impuesto\Exp_Unidades;
use App\Models\impuesto\Exp_broche;
use App\Models\impuesto\Exp_unidades_sys;
use App\Services\impuesto\EXP\ProveedoresServices;
use App\Services\impuesto\EXP\EdificioServices;
use App\Services\impuesto\EXP\UnidadesServices;
use Illuminate\Support\Facades\DB;
use App\Models\At_cl\Calle;
use Illuminate\Support\Facades\Log;
use Barryvdh\Snappy\Facades\SnappyPdf;
use App\Services\At_cl\PermitirAccesoPropiedadService;
use App\Models\At_cl\Usuario;


class ExpController
{

    protected $usuario_id, $usuario, $proveedoresServices;
    public function __construct()
    {
        $this->usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        $this->usuario = Usuario::find($this->usuario_id);
        $this->proveedoresServices = new ProveedoresServices();
    }
    //Funcion que no muestra el padron de administradores de expensas
    public function create()
    {
        $vistaNombre = 'exp-administrador-consorcio';

        // Crear una instancia del servicio de permisos
        $permisoService = new PermitirAccesoPropiedadService($this->usuario->id);

        // Verificar si el usuario tiene acceso a la vista
        if (!$permisoService->tieneAccesoAVista($vistaNombre)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }

        $proveedoresServices = new ProveedoresServices();
        $proveedores = $proveedoresServices->actualizarPadronProveedores();
        return view('impuesto.expensas.Carga_Administrador', compact('proveedores'));
    }

    //Funcion que actualiza el padron de administradores de expensas y esta relacionado con el boton actualizar padron
    public function CargarAdministradores()
    {

        $proveedores = $this->proveedoresServices->actualizarPadronProveedores();
        return view('impuesto.expensas.Carga_Administrador', compact('proveedores'));
    }

    public function filtro(Request $request)
    {
        $search = $request->input('search', '');
        $proveedores = $this->proveedoresServices->filtrarAdministradores($search);

        return view('impuesto.expensas.Carga_Administrador', compact('proveedores'));
    }



    /* Unidades */
    public function PadronUnidades()
    {
        $vistaNombre = 'exp-unidades';

        // Crear una instancia del servicio de permisos
        $permisoService = new PermitirAccesoPropiedadService($this->usuario->id);

        // Verificar si el usuario tiene acceso a la vista
        if (!$permisoService->tieneAccesoAVista($vistaNombre)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }

        $unidades = Exp_unidades_sys::all();
        $edificios = Exp_edificio::all()->sortBy('nombre_consorcio');
        $unidadesPadron = Exp_Unidades::all();
        $administradores = Exp_administrador_consorcio::all();
        $unidadesPadronByCasa = $unidadesPadron->groupBy('id_casa');

        return view('impuesto.expensas.Carga_Unidad', compact('unidades', 'administradores', 'unidadesPadronByCasa', 'edificios'));
    }

    public function actualizarPadronUnidades()
    {
        $unidadesServices = new UnidadesServices();
        $unidadesServices->PadronUnidadesSyS();
        return redirect()->route('exp_unidades');
    }

    public function filtroUnidadesCompleto(Request $request)
    {
        $edificios = Exp_edificio::all();
        $administradores = Exp_administrador_consorcio::all();
        $adminsById = $administradores->keyBy('id');
        $unidadesPadron = Exp_unidades::all();
        // Agrupar todas las unidades por id_casa (puede haber múltiples por casa)
        $unidadesPadronByCasa = $unidadesPadron->groupBy('id_casa');
        $search = trim($request->input('search', ''));
        $filtros = $request->input('filtros', []);

        $unidadesServices = new UnidadesServices();
        $unidadesServices->PadronUnidadesSyS();

        $query = Exp_unidades_sys::query();

        // Estado filters (values in table are 'Activo'/'Inactivo')
        $activos = in_array('ACTIVO', $filtros, true);
        $inactivos = in_array('INACTIVO', $filtros, true);

        if ($activos && !$inactivos) {
            $query->where('estado', '=', 'Activo');
        } elseif (!$activos && $inactivos) {
            $query->where('estado', '=', 'Inactivo');
        }

        $adminFilters = array_values(array_intersect($filtros, ['L', 'P', 'I']));
        if (!empty($adminFilters)) {
            $query->whereIn('administra', $adminFilters);
        }


        if ($search !== '') {
            $needle = "%{$search}%";
            $query->where(function ($q) use ($needle) {
                $q->where('folio', 'like', $needle)
                    ->orWhere('casa', 'like', $needle)
                    ->orWhere('ubicacion', 'like', $needle)
                    ->orWhere('comision', 'like', $needle)
                    ->orWhere('administra', 'like', $needle)
                    ->orWhere('estado', 'like', $needle);
            });
        }

        $unidades = $query->orderBy('folio')->get();
        return view('impuesto.expensas.Carga_Unidad', compact('unidades', 'administradores', 'adminsById', 'unidadesPadronByCasa', 'edificios'));
    }



    public function completarCargaUnidades(Request $request)
    {
        $repetir = $request->input('repetir');
        if (empty($repetir)) {
            return redirect()->route('exp_unidades');
        }

        DB::beginTransaction();
        try {
            foreach ($repetir as $rep) {
                if (isset($rep['id']) && $rep['id']) {
                    Exp_unidades::where('id', $rep['id'])->update([
                        'id_casa' => $request->id,
                        'id_edificio' => $request->edificio,
                        'piso' => $rep['piso'],
                        'depto' => $rep['depto'],
                        'unidad' => $rep['unidad'],
                        'tipo' => $rep['tipo'],
                        'observaciones' => $rep['comentario'],
                        'estado' => $rep['estado']
                    ]);
                } else {
                    Exp_unidades::create([
                        'id_casa' => $request->id,
                        'id_edificio' => $request->edificio,
                        'piso' => $rep['piso'],
                        'depto' => $rep['depto'],
                        'unidad' => $rep['unidad'],
                        'tipo' => $rep['tipo'],
                        'observaciones' => $rep['comentario'],
                        'estado' => $rep['estado']
                    ]);
                }

                // actualizar estado en unidades_sys
                Exp_unidades_sys::where('casa', $request->id)->update([
                    'estado' => $request->estado
                ]);
            }

            DB::commit();
            return redirect()->route('exp_unidades')->with('success', 'Unidades cargadas correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('exp_unidades')->withErrors('Error al guardar: ' . $e->getMessage());
        }
    }


    public function eliminarUnidad($id)
    {
        Log::info('ID de la unidad a eliminar: ' . $id);
        try {
            Exp_unidades::where('id', $id)->delete();
            return redirect()->route('exp_unidades');
        } catch (\Exception $e) {
            Log::error('Error al eliminar la unidad: ' . $e->getMessage());
            return redirect()->route('exp_unidades')->with('error', 'Error al eliminar la unidad');
        }
    }







    public function PadronEdificios()
    {
        $vistaNombre = 'exp-edificios';

        // Crear una instancia del servicio de permisos
        $permisoService = new PermitirAccesoPropiedadService($this->usuario->id);

        // Verificar si el usuario tiene acceso a la vista
        if (!$permisoService->tieneAccesoAVista($vistaNombre)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }

        //QUIERO OBTENER EL NOMBRE NOMAS
        $administradores = Exp_administrador_consorcio::orderBy('nombre', 'asc')->get();
        $calle = Calle::all();
        $edificios = Exp_edificio::all();

        //de cada edificio obtener el nombre del administrador por id_administrador_consorcio
        foreach ($edificios as $edificio) {
            $edificio->administrador = Exp_administrador_consorcio::find($edificio->id_administrador_consorcio);
        }
        /* dd($edificios); */
        return view('impuesto.expensas.Cargar_edificio', compact('administradores', 'calle', 'edificios'));
    }

    public function PadronEdificiosCargar(Request $request)
    {
        try {
            $id_administrador = $request->administrador;
            $nombre = $request->nombre;
            $id_calle = $request->calle;
            $altura = $request->altura;

            $direccion = Calle::find($id_calle)->name;

            Exp_edificio::create([
                'direccion' => $direccion,
                'nombre_consorcio' => $nombre,
                'id_administrador_consorcio' => $id_administrador,
                'altura' => $altura,
            ]);

            return redirect()->route('exp_edificios')->with('success', 'Edificio creado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al crear edificio: ' . $e->getMessage());
            return redirect()->route('exp_edificios')->with('error', 'No se pudo crear el edificio');
        }
    }

    public function filtroConsorcio(Request $request)
    {

        $search = trim($request->input('search', ''));
        $adm_consorcio = Exp_administrador_consorcio::where('nombre', '=', $search)->get('id');
        /*  dd($search); */

        $edificios = Exp_edificio::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre_consorcio', 'like', "%{$search}%")
                        ->orWhere('direccion', 'like', "%{$search}%")
                        ->orWhere('altura', 'like', "%{$search}%")
                    ;
                });
            })
            ->get();


        foreach ($edificios as $edificio) {
            $edificio->administrador = Exp_administrador_consorcio::find($edificio->id_administrador_consorcio);
        }
        /* dd($edificios); */

        $administradores = Exp_administrador_consorcio::orderBy('nombre', 'asc')->get();
        $calle = Calle::all();

        return view('impuesto.expensas.Cargar_edificio', compact('edificios', 'calle', 'administradores'));
    }

    public function actualizarConsorcio(Request $request)
    {

        $id = $request->id;
        $nombre = $request->nombre;
        $id_calle = $request->calle;
        $altura = $request->altura;
        $administrador = $request->administra;

        if ($id_calle != null) {

            $direccion = Calle::find($id_calle)->name;
            $administrador = Exp_administrador_consorcio::find($administrador);
            $administrador = $administrador->nombre;

            Exp_edificio::where('id', $id)->update([
                'direccion' => $direccion,
                'nombre_consorcio' => $nombre,
                'id_administrador_consorcio' => $administrador,
                'altura' => $altura,
            ]);
        } else {
            Exp_edificio::where('id', $id)->update([

                'nombre_consorcio' => $nombre,
                'id_administrador_consorcio' => $administrador,
                'altura' => $altura,
            ]);
        }




        return redirect()->route('exp_edificios');
    }







    public function brocheExpensas()
    {
        $vistaNombre = 'exp-broche';

        // Crear una instancia del servicio de permisos
        $permisoService = new PermitirAccesoPropiedadService($this->usuario->id);

        // Verificar si el usuario tiene acceso a la vista
        if (!$permisoService->tieneAccesoAVista($vistaNombre)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }


        $edificios = Exp_edificio::all();
        $empresas = Exp_administrador_consorcio::all();

        $broches = DB::connection('mysql9')
            ->table('exp_broche')
            ->leftJoin('exp_unidades', 'exp_broche.unidad', '=', 'exp_unidades.id')
            ->leftJoin('exp_administrador_consorcio', 'exp_broche.administra', '=', 'exp_administrador_consorcio.id')
            ->leftJoin('exp_edificios', 'exp_unidades.id_edificio', '=', 'exp_edificios.id')
            ->leftJoin('exp_unidades_sys', 'exp_unidades.id_casa', '=', 'exp_unidades_sys.casa')
            ->select(
                'exp_broche.*',
                'exp_broche.id as id_broche',
                'exp_unidades.*',
                'exp_unidades.id as id_unidad',
                'exp_administrador_consorcio.*',
                'exp_administrador_consorcio.id as id_administra',
                'exp_edificios.*',
                'exp_edificios.id as id_edificio',
                'exp_unidades_sys.*',
                'exp_unidades_sys.id as id_unidades_sys',
            )
            ->whereMonth('exp_Broche.vencimiento', now()->month)
            ->whereYear('exp_Broche.vencimiento', now()->year)
            ->get();



        return view('impuesto.expensas.Carga_Broche_Expensas', compact('edificios', 'empresas', 'broches'));
    }

    public function brocheExpensasBuscar($folio, $empresa, $edificio, $administrador)
    {
        try {

            $query = Exp_unidades_sys::join('exp_unidades', 'exp_unidades_sys.casa', '=', 'exp_unidades.id_casa')
                ->join('exp_edificios', 'exp_unidades.id_edificio', '=', 'exp_edificios.id')
                ->join('exp_administrador_consorcio', 'exp_edificios.id_administrador_consorcio', '=', 'exp_administrador_consorcio.id')
                ->select(
                    'exp_unidades_sys.*',
                    'exp_unidades.*',
                    'exp_unidades.id as id_unidad',
                    'exp_edificios.*',
                    'exp_edificios.direccion as direccion_edificio',
                    'exp_edificios.altura as altura_edificio',
                    'exp_administrador_consorcio.*',
                    'exp_administrador_consorcio.id as id_administrador'
                );

            //log de la query
            log::info('Query de búsqueda de brocheExpensasBuscar: ' . $query->toSql());


            //Verifica si filtra empresa
            if ($empresa != "-") {
                $query->where('exp_unidades_sys.id_empresa', $empresa);
            }


            //Verifica si filtra por folio
            if ($folio != "-") {
                $query->where('exp_unidades_sys.folio', $folio);
            }

            //Verifica si filtra por edificio
            if ($edificio != "-") {
                $query->where('exp_edificios.id', $edificio);
            }

            //Verifica si filtra por administrador
            if ($administrador != "-") {
                $query->where('exp_administrador_consorcio.id', $administrador);
            }

            $resultados = $query->get();

            return response()->json([
                'success' => true,
                'data' => $resultados
            ]);
        } catch (\Exception $e) {
            Log::error('Error en brocheExpensasBuscar: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al realizar la búsqueda'
            ], 500);
        }
    }

    //Funcion para guardar un broche de expensas
    public function brocheExpensasGuardar(Request $request)
    {
        //Log::info($request);
        $brochesTotales = Exp_broche::all();
        //Separo la fecha de vencimiento
        $fecha_vencimiento = explode("-", $request->vencimiento);
        $anio_nuevo = $fecha_vencimiento[0];
        $mes_nuevo = $fecha_vencimiento[1];

        foreach ($brochesTotales as $broche) {
            if ( $broche->unidad == $request->id_unidad ) {
                //separa el vencimiento del registro broche
                $fecha_vencimiento_broche = explode("-", $broche->vencimiento);
                $anio = $fecha_vencimiento_broche[0];
                $mes = $fecha_vencimiento_broche[1];
                
                //verifica el mes y anio del registro actual con el enviado
                if ( $mes == $mes_nuevo && $anio == $anio_nuevo ) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Esta unidad ya se encuentra cargada',
                    ], 404);
                }
            }
        }

        $data = Exp_broche::create([
            'vencimiento' => $request->vencimiento,
            'extraordinaria' => $request->importe_extraordinaria,
            'ordinaria' => $request->importe_ordinaria,
            'total' => $request->total,
            'periodo' => $request->periodo,
            'anio' => $request->anio,
            'unidad' => $request->id_unidad,
            'administra' => $request->id_administrador,

        ]);

        //Log::info($data);
        return response()->json([
            'success' => true,
            'message' => 'Se guardaron los datos',
            'data' => $data
        ], 200);
    }

    //Funcion para eliminar un broche
    public function eliminarBroche($id)
    {
        try {
            // Buscar el broche
            $broche = Exp_broche::find($id);

            // Si no existe, devolver error
            if (!$broche) {
                return response()->json([
                    'success' => false,
                    'message' => 'El broche no existe.',
                ], 404);
            }

            // Eliminar
            $broche->delete();

            // Respuesta OK
            return response()->json([
                'success' => true,
                'message' => 'Broche eliminado correctamente.',
            ], 200);
        } catch (\Exception $e) {

            // Manejo de error inesperado
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el broche.',
                'error'   => $e->getMessage(), // opcional
            ], 500);
        }
    }


    public function filtroBrocheExpensas(Request $request)
    {
        $edificios = Exp_edificio::orderBy('nombre_consorcio', 'asc')->get();

        //lo ordeno por nombre_consorcio alfabeticamente
        $empresas = Exp_administrador_consorcio::orderBy('nombre', 'asc')->get();

        $query = DB::connection('mysql9')
            ->table('exp_broche')
            ->leftJoin('exp_unidades', 'exp_broche.unidad', '=', 'exp_unidades.id')
            ->leftJoin('exp_administrador_consorcio', 'exp_broche.administra', '=', 'exp_administrador_consorcio.id')
            ->leftJoin('exp_edificios', 'exp_unidades.id_edificio', '=', 'exp_edificios.id')
            ->leftJoin('exp_unidades_sys', 'exp_unidades.id_casa', '=', 'exp_unidades_sys.casa')
            ->select(
                'exp_broche.*',
                'exp_broche.id as id_broche',
                'exp_broche.vencimiento as vencimientobroche',
                'exp_unidades.*',
                'exp_unidades.id as id_unidad',
                'exp_administrador_consorcio.*',
                'exp_administrador_consorcio.id as id_administra',
                'exp_edificios.*',
                'exp_edificios.id as id_edificio',
                'exp_unidades_sys.*',
                'exp_unidades_sys.id as id_unidades_sys',
            );

        // Aplicar filtros si existen
        if ($request->filled('mes')) {
            $query->whereMonth('exp_broche.vencimiento', $request->mes);
        }

        if ($request->filled('anio')) {
            $query->whereYear('exp_broche.vencimiento', $request->anio);
        }

        if ($request->filled('busqueda')) {
            $searchTerm = '%' . $request->busqueda . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('exp_unidades_sys.folio', 'LIKE', $searchTerm)
                    ->orWhere('exp_unidades.tipo', 'LIKE', $searchTerm)
                    /* ->orWhere('exp_unidades_sys.clave', 'LIKE', $searchTerm) */;
            });
        }

        $broches = $query->get();

        return view('impuesto.expensas.Carga_Broche_Expensas', compact('edificios', 'empresas', 'broches'));
    }


    public function descargarBrocheExpensas(Request $request)
    {
        /* dd($request);  */
        $resultado = DB::connection('mysql9')->table('exp_broche')
            ->leftJoin('exp_unidades', 'exp_broche.unidad', '=', 'exp_unidades.id')
            ->leftJoin('exp_administrador_consorcio', 'exp_broche.administra', '=', 'exp_administrador_consorcio.id')
            ->leftJoin('exp_edificios', 'exp_unidades.id_edificio', '=', 'exp_edificios.id')
            ->leftJoin('exp_unidades_sys', 'exp_unidades.id_casa', '=', 'exp_unidades_sys.casa')
            ->select(
                'exp_unidades_sys.folio',
                'exp_broche.*',
                'exp_administrador_consorcio.*',
                'exp_edificios.*',
                'exp_edificios.id as id_edificio',
                'exp_edificios.direccion as direccion_edificio',
                'exp_edificios.altura as altura_edificio',
                'exp_administrador_consorcio.direccion as direccion_administra',
                'exp_administrador_consorcio.altura as altura_administra',
                'exp_unidades.piso',
                'exp_unidades.depto',
                'exp_unidades.observaciones',

            )
            ->whereMonth('exp_broche.vencimiento', $request->mes)
            ->whereYear('exp_broche.vencimiento', $request->anio)
            ->when($request->filled('administrador'), function ($query) use ($request) {
                return $query->where('exp_administrador_consorcio.id', $request->administrador);
            })
            ->orderBy('exp_administrador_consorcio.id')
            ->orderBy('exp_edificios.id')
            ->orderBy('exp_unidades_sys.folio')
            ->get();

        /* dd($resultado); */
        //Ahora quiero generrar un pdf con snappy
        $pdf = SnappyPdf::loadView(
            'impuesto.expensas.Carga_Broche_Expensas_Pdf',
            compact('resultado')
        )
            ->setOption('page-size', 'legal') // Tamaño de página (A4, Letter, etc.)
            ->setOption('orientation', 'portrait') // 'portrait' (vertical) o 'landscape' (horizontal)
            ->setOption('margin-top', 20) // Margen superior (mm)
            ->setOption('margin-bottom', 15) // Margen inferior (mm)
            ->setOption('margin-left', 15) // Margen izquierdo (mm)
            ->setOption('margin-right', 15) // Margen derecho (mm)
            ->setOption('disable-smart-shrinking', true) // Evita reducción automática
            ->setOption('footer-left', 'Salas Inmobiliaria') // Texto pie izquierdo
            ->setOption('footer-font-size', 8) // Tamaño fuente pie (px)
            ->setOption('footer-center', 'Page [page] of [toPage]') // pagina x de y
            ->setOption('footer-right', session('usuario')->username) // Texto pie derecho
            ->setOption('zoom', 0.85);


        return $pdf->stream("listadoEstados.pdf");
    }
}
