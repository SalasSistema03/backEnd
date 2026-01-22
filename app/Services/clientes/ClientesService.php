<?php

namespace App\Services\clientes;

use App\Models\At_cl\Usuario;
use App\Models\cliente\clientes as ModelsClientes;
use App\Models\cliente\CriterioBusquedaVenta;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Log;

class ClientesService
{
    protected $usuarioSectorService;

    public function __construct()
    {
        $this->usuarioSectorService = new UsuarioSectorService();
    }

    public function guardarcliente(array $data)
    {
        // Sin try/catch aquí
        return ModelsClientes::create($data);
    }

    public function clientePorTelefonoService($telefono)
    {
        $session = session();
        $usuario_id = $session->get('usuario_id');

        return ModelsClientes::with([
            'consulta_prop_venta.propiedad',
            'criterio_busqueda_venta.tipoInmueble',
            'criterio_busqueda_venta.zona',
            'asesor',
            'asesor.usuario',    

        ])->where('telefono', $telefono)->first()  ;
        
    }

    public function actualizarCliente(array $request, $id)
    {
        $cliente = ModelsClientes::findOrFail($id);

        $cliente->nombre = $request['nombre'];
        $cliente->telefono = $request['telefono'];
        $cliente->observaciones = $request['observaciones'];
        $cliente->ingreso = $request['ingreso_por'];
        $cliente->pertenece_a_inmobiliaria = $request['pertenece_a_inmobiliaria'];
        $cliente->nombre_de_inmobiliaria = $request['nombre_de_inmobiliaria'];
        $cliente->id_asesor_venta = $request['id_asesor_venta'];

        $cliente->save();

        return $cliente;
    }

    


}
