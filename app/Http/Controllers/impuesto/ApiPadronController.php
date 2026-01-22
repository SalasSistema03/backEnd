<?php

namespace App\Http\Controllers\impuesto;

use App\Models\impuesto\Api_padron;


use App\Http\Controllers\Controller;
use App\Models\At_cl\Usuario;
use App\Models\impuesto\Tgi_padron;
use App\Services\At_cl\PermitirAccesoPropiedadService;
use App\Services\clientes\Permisos;
use App\Services\impuesto\API\PadronApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiPadronController extends Controller
{
    protected $padronApiService;
    //protected $cargarTgiService;
    protected $usuario;
    protected $usuario_id;
    protected $permisoService;

    public function __construct(
        PadronApiService $padronApiService,
        Permisos $permisoService
    ) {
        $this->padronApiService = $padronApiService;
        $this->permisoService = $permisoService;
    }

    public function index(Request $request)
    {
        //$this->usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        //$this->usuario = Usuario::find($this->usuario_id);

        //$vistaCargarTgi = 'padron_tgi';
        //$permisoService = new PermitirAccesoPropiedadService($this->usuario->id);

        // Verificar si el usuario tiene acceso a la vista
        //if (!$permisoService->tieneAccesoAVista($vistaCargarTgi)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            //return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        //}


        //Resive los datos del input buscador
        $search = $request->input('search');
        //Resive los datos del boton filtro 
        $filtros = $request->input('filtros', []);

        // Si no hay filtros, por defecto mostrar solo activos
        if (empty($filtros)) {
            $filtros[] = 'ACTIVO';
        }

        // Separar filtros por tipo
        $estados = array_intersect($filtros, ['ACTIVO', 'INACTIVO']);
        $administraciones = array_intersect($filtros, ['L', 'P', 'I']);

        $padron = $this->padronApiService->obtenerPadronExistente();

        // Filtrar por búsqueda
        if ($search) {
            $padron = $padron->filter(function ($item) use ($search) {
                return str_contains(strtolower($item->folio), strtolower($search)) ||
                    str_contains(strtolower($item->calle), strtolower($search)) ||
                    str_contains(strtolower($item->partida), strtolower($search));
            });
        }

        // Aplicar filtros combinados
        $padron = $padron->filter(function ($item) use ($estados, $administraciones) {
            $estadoOk = empty($estados) || in_array(strtoupper($item->estado), $estados);
            $adminOk = empty($administraciones) || in_array(strtoupper($item->administra), $administraciones);
            return $estadoOk && $adminOk;
        });

        return view('impuesto.api.padronApi', compact('padron'));
    }





    public function actualizarPadronAPI()
    {
        $this->padronApiService->obtenerPadronAPI();
        return redirect()->route('padron_api')->with('success', 'Padrón API actualizado correctamente.');
    }

    public function obtenerRegistroPadronManual(Request $request)
    {
        $folio = $request->folio;
        $empresa = $request->empresa;
        Log::info("Folio: $folio, Empresa: $empresa");

        $registro = $this->padronApiService->obtenerRegistroPadronManual($folio, $empresa);

        if (!$registro) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }

        return response()->json($registro);
    }

    // Modificar registro seleccionado
    public function actualizar(Request $request)
    {
        // Verificar si ya existe otro registro con el mismo folio, clave y partida
        $existe = Api_padron::where('folio', $request->folio)
            ->where('partida', $request->partida)
            ->where('id', '!=', $request->id) // excluir el actual
            ->exists();

        if ($existe) {
            return redirect()->route('padron_api')
                ->with('error', 'Ya existe otro registro con el mismo folio y partida.');
        }

        // Si no existe duplicado, actualizar
        $registro = Api_padron::findOrFail($request->id);
        $registro->folio = $request->folio;
        $registro->calle = $request->calle;
        $registro->partida = $request->partida;
        $registro->estado = $request->estado;
        $registro->administra = $request->administra;
        $registro->save();

        return redirect()->route('padron_api')->with('success', 'Registro actualizado correctamente.');
    }
}
