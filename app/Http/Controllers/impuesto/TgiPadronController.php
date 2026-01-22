<?php

namespace App\Http\Controllers\impuesto;


use App\Http\Controllers\Controller;
use App\Models\At_cl\Usuario;
use App\Models\impuesto\Tgi_padron;
use App\Services\At_cl\PermitirAccesoPropiedadService;
use App\Services\clientes\Permisos;
use App\Services\impuesto\TGI\PadronTgiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TgiPadronController extends Controller
{
    protected $padronTgiService;
    protected $cargarTgiService;
    protected $usuario;
    protected $usuario_id;
    protected $permisoService;

    public function __construct(
        PadronTgiService $padronTgiService,
        Permisos $permisoService
    ) {
        $this->padronTgiService = $padronTgiService;
        $this->permisoService = $permisoService;
    }

    public function index(Request $request)
    {
        $this->usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        $this->usuario = Usuario::find($this->usuario_id);

        $vistaCargarTgi = 'padron_tgi';
        $permisoService = new PermitirAccesoPropiedadService($this->usuario->id);


        // Verificar si el usuario tiene acceso a la vista
        if (!$permisoService->tieneAccesoAVista($vistaCargarTgi)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }



        $search = $request->input('search_all');
        $search_folio = $request->input('search_folio');
        $filtros = $request->input('filtros', []);

        // Si no hay filtros, por defecto mostrar solo activos
        if (empty($filtros)) {
            $filtros[] = 'ACTIVO';
        }

        // Separar filtros por tipo
        $estados = array_intersect($filtros, ['ACTIVO', 'INACTIVO']);
        $administraciones = array_intersect($filtros, ['L', 'P', 'I']);

        $padron = $this->padronTgiService->obtenerPadronExistente();

        //filtrar por folio, pero el numero exacto, no si solo lo contiene
       if ($search_folio) {
    $padron = $padron->filter(function ($item) use ($search_folio) {
        // Comparación exacta
        return strtolower($item->folio) === strtolower($search_folio);
    });
}


        // Filtrar por búsqueda
        if ($search) {
            $padron = $padron->filter(function ($item) use ($search) {
                return  str_contains(strtolower($item->calle), strtolower($search)) ||
                    str_contains(strtolower($item->partida), strtolower($search)) ||
                    str_contains(strtolower($item->clave), strtolower($search));
            });
        }

        // Aplicar filtros combinados
        $padron = $padron->filter(function ($item) use ($estados, $administraciones) {
            $estadoOk = empty($estados) || in_array(strtoupper($item->estado), $estados);
            $adminOk = empty($administraciones) || in_array(strtoupper($item->administra), $administraciones);
            return $estadoOk && $adminOk;
        });

        return view('impuesto.tgi.padronTGI', compact('padron'));
    }





    public function actualizarPadronTGI()
    {
        $this->padronTgiService->obtenerPadronTGI();
        return redirect()->route('padron_tgi')->with('success', 'Padrón TGI actualizado correctamente.');
    }

    public function obtenerRegistroPadronManual(Request $request)
    {
        $folio = $request->folio;
        $empresa = $request->empresa;
        Log::info("Folio: $folio, Empresa: $empresa");

        $registro = $this->padronTgiService->obtenerRegistroPadronManual($folio, $empresa);

        if (!$registro) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }

        return response()->json($registro);
    }

    // Modificar registro seleccionado
    public function actualizar(Request $request)
    {
        // Verificar si ya existe otro registro con el mismo folio, clave y partida
        $existe = Tgi_padron::where('folio', $request->folio)
            ->where('clave', $request->clave)
            ->where('partida', $request->partida)
            ->where('id', '!=', $request->id) // excluir el actual
            ->exists();

        if ($existe) {
            return redirect()->route('padron_tgi')
                ->with('error', 'Ya existe otro registro con el mismo folio, clave y partida.');
        }

        // Si no existe duplicado, actualizar
        $registro = Tgi_padron::findOrFail($request->id);
        $registro->folio = $request->folio;
        $registro->calle = $request->calle;
        $registro->partida = $request->partida;
        $registro->clave = $request->clave;
        $registro->estado = $request->estado;
        $registro->administra = $request->administra;
        $registro->save();

        return redirect()->route('padron_tgi')->with('success', 'Registro actualizado correctamente.');
    }
}
