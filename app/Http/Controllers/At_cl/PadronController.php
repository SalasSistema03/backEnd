<?php

namespace App\Http\Controllers\At_cl;


use App\Models\At_cl\Padron; // Importa el modelo Padron, que representa la tabla en la base de datos
use App\Models\At_cl\Padron_telefonos;
use App\Models\At_cl\Usuario; // Import the Usuario model
use App\Models\At_cl\Propiedad; // Import the Propiedad model
use App\Services\At_cl\PermitirAccesoPropiedadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\At_cl\PadronService;
use App\Http\Requests\StorePadronRequest;

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
        $this->usuario = Usuario::find($this->usuario_id);
        $this->accessService = new PermitirAccesoPropiedadService($this->usuario_id);
        $this->padronService = $padronService;
    }

    /**
     * Muestra el listado de personas del padrón filtradas por apellido o DNI.
     *
     * Antes de ejecutar la búsqueda, valida si el usuario tiene permisos para
     * acceder a la vista correspondiente.
     *
     * @param \Illuminate\Http\Request $request   Datos de búsqueda enviados por el usuario.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $vistaNombre = 'padronBuscar';
        // Crear una instancia del servicio de permisos
        $permisoService = new PermitirAccesoPropiedadService($this->usuario->id);

        // Verificar si el usuario tiene acceso a la vista
        if (!$permisoService->tieneAccesoAVista($vistaNombre)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }

        //Obtenemos los datos del input
        $apellido = $request->input('apellido');
        $dni = $request->input('dni');

        //Buscamos el padron con los datos
        $personas = $this->padronService->BuscarPadron($apellido, $dni);

        return view('atencionAlCliente.padron.index', compact('personas'));
    }



    /**
     * Muestra la información detallada de un registro del padrón
     *
     * Valida los permisos del usuario para acceder a la vista del padrón,
     * determina los permisos para ciertos botones de acción y obtiene el
     * registro específico solicitado para enviarlo a la vista correspondiente.
     *
     * @param int $id identificador único del registro de padrón a mostrar
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     *         retorna la vista con los datos si el usuario tiene acceso;
     *         redirige al inicio con un mensaje de error si no posee permisos
     * @access public
     */
    public function show($id)
    {
        $vistaNombre = 'padron';
        // Crear una instancia del servicio de permisos
        $permisoService = new PermitirAccesoPropiedadService($this->usuario->id);

        // Verificar si el usuario tiene acceso a la vista
        if (!$permisoService->tieneAccesoAVista($vistaNombre)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }
        // Definimos un array con los nombres de los botones
        $btnNombres = [
            'editarPadron'
        ];
        // Inicializamos un array vacío para almacenar los accesos
        $accesos = [];
        // Recorremos cada nombre de botón
        foreach ($btnNombres as $btnNombre) {
            // Verificamos si el usuario tiene acceso a cada botón y almacenamos el resultado en el array de accesos
            $accesos[$btnNombre] = $this->accessService->tieneAccesoPadron($btnNombre);
        }
        // Asignamos el acceso a 'editarPadron'
        $tieneAccesoEditarPadron = $accesos['editarPadron'];
        // Busca el padrón específico por su ID
        $padron = Padron::findOrFail($id);

        // Devuelve la vista 'atencionAlCliente.padron.show' con el recurso específico ($padron)
        return view('atencionAlCliente.padron.show', compact('padron',  'tieneAccesoEditarPadron'));
    }

    /**
     * Muestra el formulario para crear un nuevo registro de padrón
     *
     * Valida los permisos del usuario para acceder a la vista de creación
     * de padrones y obtiene la lista completa de propiedades necesarias
     * para el formulario antes de retornar la vista.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     *         retorna la vista con los datos necesarios o redirige si no hay permisos
     * @access public
     */
    public function create()
    {

        $vistaNombre = 'padronCargar';
        // Crear una instancia del servicio de permisos
        $permisoService = new PermitirAccesoPropiedadService($this->usuario->id);

        // Verificar si el usuario tiene acceso a la vista
        if (!$permisoService->tieneAccesoAVista($vistaNombre)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }

        $propiedades = Propiedad::all(); // Obtén todas las propiedades

        // Devuelve la vista 'padron.create', que contiene el formulario de creación
        return view('atencionAlCliente.padron.create', compact('propiedades'));
    }

    /**
     * Guarda un nuevo registro de padrón junto con sus teléfonos asociados
     *
     * Procesa y valida la información enviada desde el formulario de creación,
     * inicia una transacción para asegurar la integridad de los datos, registra
     * el padrón y sus teléfonos relacionados y redirige según el resultado.
     *
     * @param StorePadronRequest $request los datos validados del formulario de creación del padrón
     *
     * @return \Illuminate\Http\RedirectResponse redirige a la vista de detalle o vuelve atrás con un error
     * @throws \Exception si ocurre un error durante la transacción y el guardado de datos
     * @access public
     */
    public function store(StorePadronRequest $request)
    {
        DB::beginTransaction(); // Iniciar la transacción

        try {
            $data = $request->all();
            $data['last_modified_by'] = session()->get('usuario_id');

            $padron = Padron::create($data);

            if ($request->has('telefonos')) {
                foreach ($request->input('telefonos') as $telefono) {
                    $padron->telefonos()->create([
                        'phone_number' => $telefono['phone_number'],
                        'notes' => $telefono['notes'] ?? null,
                        'last_modified_by' => session()->get('usuario_id'),
                    ]);
                }
            }

            DB::commit(); // Confirmar la transacción
            return redirect()
                ->route('padron.show', ['padron' => $padron->id])
                ->with('success', 'Padrón guardado correctamente junto con los teléfonos.');
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir la transacción en caso de error
            return redirect()
                ->back()
                ->with('error', 'Ocurrió un error al guardar el padrón.');
        }
    }


    public function edit(Padron $padron)
    {

        $vistaNombre = 'padronEditar';
        // Verificar si el usuario tiene acceso a la vista
        $permisoService = new PermitirAccesoPropiedadService($this->usuario->id);

        // Verificar si el usuario tiene acceso a la vista
        if (!$permisoService->tieneAccesoAVista($vistaNombre)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }
        $padron = Padron::findOrFail($padron->id);
        // Devuelve la vista 'padron.edit' con el recurso a editar
        return view('AtencionAlCliente.padron.edit', compact('padron'));
    }


    public function update(Request $request, $id)
    {
        DB::beginTransaction(); // Iniciar la transacción

        try {

            $padron = Padron::findOrFail($id);
            // Actualiza los datos del padrón
            $padron->update($request->only([
                'nombre',
                'apellido',
                'documento',
                'fecha_nacimiento',
                'calle',
                'numero_calle',
                'piso_departamento',
                'ciudad',
                'provincia',
                'notes',
            ]));
            // Process phone numbers
            if ($request->has('telefonos')) {
                foreach ($request->telefonos as $index => $phoneData) {
                    // Skip empty phone numbers
                    if (empty($phoneData['phone_number'])) {
                        continue;
                    }

                    // Check if this is an existing phone (has an ID) or a new one
                    if (isset($phoneData['id']) && !empty($phoneData['id'])) {
                        // Update existing phone
                        $phone = Padron_telefonos::findOrFail($phoneData['id']);
                        $phone->update([
                            'phone_number' => $phoneData['phone_number'],
                            'notes' => $phoneData['notes'] ?? null,
                            'last_modified_by' => session()->get('usuario_id'), // Assuming you track who modified it
                        ]);
                    } else {
                        // Create new phone
                        $phone = new Padron_telefonos([
                            'phone_number' => $phoneData['phone_number'],
                            'notes' => $phoneData['notes'] ?? null,
                            'padron_id' => $padron->id,
                            'last_modified_by' => session()->get('usuario_id'),
                        ]);
                        $phone->save();
                    }
                }
            }

            DB::commit(); // Confirmar la transacción
            return redirect()->route('padron.show', $padron->id)->with('success', 'Padrón y teléfonos actualizados exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir la transacción en caso de error
            return redirect()->back()->with('error', 'Ocurrió un error al guardar el padrón.');
        }
    }


    public function destroy(Padron $padron)
    {
        // Elimina el registro específico de la base de datos
        $padron->delete();

        // Redirige a la lista de recursos con un mensaje de éxito
        return redirect()->route('padron.index')->with('success', 'Padron eliminado exitosamente.');
    }
}
