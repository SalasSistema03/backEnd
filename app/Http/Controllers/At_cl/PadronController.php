<?php

namespace App\Http\Controllers\At_cl;


use App\Models\At_cl\Padron; // Importa el modelo Padron, que representa la tabla en la base de datos
use App\Models\At_cl\Padron_telefonos;
use App\Models\At_cl\Propiedad; // Import the Propiedad model
use App\Services\At_cl\PermitirAccesoPropiedadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\At_cl\PadronService;
use App\Http\Requests\StorePadronRequest;
use App\Models\sistema_usuarios_permisos\Usuario;
use Illuminate\Support\Facades\Log;

/**
 * Class PadronController
 *
 * Este controlador gestiona todas las operaciones CRUD (Crear, Leer, Actualizar, Eliminar)
 * relacionadas con el modelo Padron.
 */
class PadronController
{
    protected $usuario;
    protected $usuario_id;
    protected $accessService;
    protected $padronService;


    public function __construct(
        PadronService $padronService
    ) {
        $this->usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        //$this->usuario = Usuario::find($this->usuario_id);
        $this->accessService = new PermitirAccesoPropiedadService($this->usuario_id);
        $this->padronService = $padronService;
    }





    public function CargarPadron(Request $request)
    {
        if ($request->has('id')) {
            $padron = $this->padronService->editaPadron($request);
        } else {
            //Log::info('CargarPadron', $request->all());
            $padron = $this->padronService->CargarPadron($request);
        }

        return response()->json($padron);
    }

    public function padronActivos()
    {
        $padron = (new PadronService())->padronActivoAlquiler();
        return response()->json($padron);
    }



}
