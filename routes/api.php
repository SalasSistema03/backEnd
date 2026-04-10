<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\contable\sellado\SelladoController;
use App\Services\usuarios_y_permisos\PermisoService;
use App\Services\usuarios_y_permisos\UsuarioService;
use App\Http\Controllers\turnos\TurnoController;
use App\Http\Controllers\At_cl\CalleController;
use App\Http\Controllers\At_cl\Tipo_inmuebleController;
use App\Http\Controllers\At_cl\ZonaController;
use App\Http\Controllers\At_cl\ProvinciaController;
use App\Http\Controllers\At_cl\EstadoGeneralController;
use App\Http\Controllers\At_cl\EstadoVentaController;
use App\Http\Controllers\At_cl\UsuariosController;
use App\Http\Controllers\At_cl\EstadoAlquilerController;
use App\Http\Controllers\At_cl\PropiedadController;
use App\Services\At_cl\PadronService;
use App\Http\Controllers\At_cl\PadronController;
use App\Http\Controllers\At_cl\Exportar_PDF_atcl\Pdf_alquiler;
use App\Http\Controllers\clientes\ClientesController;
use App\Services\clientes\Permisos;
use App\Http\Controllers\clientes\AsesoresController;
use App\Http\Controllers\agenda\AgendaController;
use App\Models\At_cl\Propiedad;

Route::prefix('v1')->group(function () {

    // 1. GRUPO DE AUTENTICACIÓN (URL: api/v1/auth/...)
    // Solo para login y registro (Rutas públicas)
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::middleware('auth:api')->group(function(){
            Route::get('logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('me', [AuthController::class, 'me']);
            Route::get('nav', [PermisoService::class, 'getMenuData']);
            Route::get('permisos-navegacion', [PermisoService::class, 'getPermisosNavegacion']);
            Route::get('nombres-de-usuarios', [UsuarioService::class, 'getNombresDeUsuarios']);
            Route::get('datos-generales/{id_usuario}', [UsuarioService::class, 'getDatosGenerales']);
            Route::put('update-datos-generales/{id_usuario}', [UsuarioService::class, 'updateDatosGenerales']);
            Route::get('sectores', [TurnoController::class, 'getSectores']);


            //Filtrado
            Route::get('propiedad/buscar', [PropiedadController::class, 'buscaPropiedad']);
            //show propiedad
            Route::get('propiedad/muestra', [PropiedadController::class, 'MuestraPropiedad']);
            Route::post('propiedad/actualizar', [PropiedadController::class, 'actualizarPropiedad']);
            Route::get('propiedad/descargar-fotos/{id}', [PropiedadController::class, 'descargarFotos']);
            Route::post('propiedad/guardar-novedad', [PropiedadController::class, 'guardarNovedad']);
            Route::get('/propiedades/pdf/pdfPlantillaPropiedad/{id}/{tipoBTN}', [Pdf_alquiler::class, 'generarPDFpantillaPropiedad'])->name('propiedades.pdf.pdfPlantillaPropiedad');

        });
    });
    //CONTABLE - SELLADO

    // 2. GRUPO PROTEGIDO (URL: api/v1/...)
    // Requieren Token, pero NO llevan "auth" en la URL
    Route::middleware('auth:api')->group(function () {

        // Sesión y Usuario
        Route::get('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);

        // Servicios de Navegación y Usuarios
        //Route::get('nav', [PermisoService::class, 'getMenuData']);
        Route::get('permisos-navegacion', [PermisoService::class, 'getPermisosNavegacion']);
        Route::get('nombres-de-usuarios', [UsuarioService::class, 'getNombresDeUsuarios']);
        Route::get('datos-generales/{id_usuario}', [UsuarioService::class, 'getDatosGenerales']);
        Route::put('update-datos-generales/{id_usuario}', [UsuarioService::class, 'updateDatosGenerales']);

        // Atcl (URL: Variables generales de atcl)
        Route::get('calles', [CalleController::class, 'getCalles']);
        Route::get('tipos-inmueble', [Tipo_inmuebleController::class, 'getTiposInmueble']);
        Route::get('zonas', [ZonaController::class, 'getZonas']);
        Route::get('provincias', [ProvinciaController::class, 'getProvincias']);
        Route::get('estado-general', [EstadoGeneralController::class, 'getEstadoGeneral']);
        Route::get('estado-venta', [EstadoVentaController::class, 'getEstadoVenta']);
        Route::get('captador-interno', [UsuariosController::class, 'getCaptadorInterno']);
        Route::get('asesor', [UsuariosController::class, 'getAsesor']);
        Route::get('estado-alquiler', [EstadoAlquilerController::class, 'getEstadoAlquiler']);
        Route::post('propiedad/guardar/{id}', [PropiedadController::class, 'guardarPropiedad']);
        Route::get('propiedades/buscar-venta', [PropiedadController::class, 'buscarPropiedadesVenta']);
        Route::get('padron/buscar', [PadronService::class, 'BuscarPadron']);
        Route::post('padron/cargar', [PadronController::class, 'CargarPadron']);

        // Turnos (URL: api/v1/turnos/...)
        Route::get('sectores', [TurnoController::class, 'getSectores']);
        Route::get('turnos/pendientes', [TurnoController::class, 'getTurnosPendientes']);
        Route::get('turnos/llamados', [TurnoController::class, 'getTurnosLlamados']);
        Route::get('turnos/completados', [TurnoController::class, 'getTurnosCompletados']);
        Route::post('turnos/cargar', [TurnoController::class, 'postCargarTurnoController']);
        Route::put('turnos/finalizar/{id}', [TurnoController::class, 'finalizarturno']);
        Route::put('turnos/llamar/{id}', [TurnoController::class, 'putLlamarTurno']);

        // CONTABLE - SELLADO (URL: api/v1/sellado)
        Route::get('sellado', [SelladoController::class, 'getDatosSelladoController']);
        Route::get('sellado/datos-calculo', [SelladoController::class, 'getDatosCalculo']);
        Route::post('sellado/guardar-valor-registro-extra', [SelladoController::class, 'guardarValorRegistroExtraController']);
        Route::post('sellado/guardar-valor-gasto-administrativo', [SelladoController::class, 'guardarValorGastoAdministrativoController']);
        Route::post('sellado/guardar-valor-hoja', [SelladoController::class, 'guardarValorHojaController']);
        Route::post('sellado/guardar-valor-sellado', [SelladoController::class, 'guardarValorSelladoController']);
        Route::post('sellado/calcular', [SelladoController::class, 'calcularSelladoController']);
        Route::post('sellado/guardar', [SelladoController::class, 'guardarSelladoController']);
        Route::delete('sellado/eliminar', [SelladoController::class, 'eliminarRegistroSelladoController']);


        //clientes
        Route::post('/clientes/guardar', [ClientesController::class, 'guardar']);
        Route::get('cliente/{telefono?}', [ClientesController::class, 'clientePorTelefono']);
        Route::get('/tieneAcceso/{usuarioId}/{botonNombre}', [SelladoController::class, 'tieneAccesoUsuario']);
        Route::get('/verificaPermisoAsesor/{botonNombre}', [Permisos::class, 'verificarAccesoBotones_Elementos']);

        //Asesores
        Route::get('/asesores', [AsesoresController::class, 'Asesores']);
        Route::put('/clientes/modificar-criterio', [AsesoresController::class, 'modificarCriterio']);
        Route::put('/clientes/modificar-datos-personales', [AsesoresController::class, 'modificarDatosPersonales']);
        Route::post('/historialCodOfrecimiento', [AsesoresController::class, 'guardarHistorialCodOfrecimiento']);
        Route::post('/asesores/enviar-mensaje', [AsesoresController::class, 'enviarMensaje']);
        Route::put('/clientes/devolver-mensaje', [AsesoresController::class, 'devolverMensaje']);
        Route::get('/historialCodOfrecimiento/{id}', [AsesoresController::class, 'obtenerHistorialCod']);

        //Agenda
        Route::get('/sectores', [AgendaController::class, 'buscarSectores']);
        Route::get('/usuarios-sector/{id_sector}/{fecha}', [AgendaController::class, 'traerUsuarioSector']);
        Route::get('/propiedad/buscar-por-codigo-calle/{codigo_calle}/{sector}', [Propiedad::class, 'buscarPorCodigoCalle']);
        Route::post('/cargar-nota', [AgendaController::class, 'store']);
        Route::get('/buscarCliente/{clienteId}', [AgendaController::class, 'buscarClientesPorTelefono']);
        Route::put('/borrar-nota/{id}/{motivo}', [AgendaController::class, 'destroy']);

    }); // <--- Aquí cierra el middleware
});


// Ruta de redirección por defecto si falla el token
Route::get('/', [AuthController::class, 'unauthorized'])->name('login');
