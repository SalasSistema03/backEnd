<?php

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
use App\Http\Controllers\At_cl\Exportar_PDF_atcl\ListadoPdfAtcl;
use App\Http\Controllers\clientes\ClientesController;
use App\Services\clientes\Permisos;
use App\Http\Controllers\clientes\AsesoresController;
use App\Http\Controllers\agenda\AgendaController;
use App\Models\At_cl\Propiedad;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\impuesto\ImpuestosController;
use App\Http\Controllers\impuesto\Exportar_PDF_impuesto\PdfImpuestoController;
use App\Http\Controllers\contable\retenciones\RetencionController;
use App\Http\Controllers\contable\buscadorComprobante\BuscadorPdfController;
use App\Models\usuarios_y_permisos\Usuario;
use App\Http\Controllers\agenda\Exportar_PDF_agenda\Pdf_agenda;
// --- IMPORTACIONES UNIDAS DE AMBAS RAMAS ---
use App\Services\At_cl\PropiedadService;
use App\Services\clientes\UsuarioSectorService;
use App\Http\Controllers\proceso\ProcesoController;
use App\Http\Controllers\impuesto\Expensas\ExpensasController;


Route::prefix('v1')->group(function () {

    // 1. GRUPO DE AUTENTICACIÓN (URL: api/v1/auth/...)
    // Solo para login y registro (Rutas públicas)
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::middleware('auth:api')->group(function () {
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
            Route::get('turnos/pendientes', [TurnoController::class, 'getTurnosPendientes']);
            Route::get('turnos/llamados', [TurnoController::class, 'getTurnosLlamados']);
            Route::get('turnos/completados', [TurnoController::class, 'getTurnosCompletados']);
            Route::post('turnos/cargar', [TurnoController::class, 'postCargarTurnoController']);
            Route::put('turnos/finalizar/{id}', [TurnoController::class, 'finalizarturno']);
            Route::put('turnos/llamar/{id}', [TurnoController::class, 'putLlamarTurno']);
            //rutas que se usan a services/Api/Atcl/atclApi
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
            Route::get('padron/buscar', [PadronService::class, 'BuscarPadron']);
            Route::post('padron/cargar', [PadronController::class, 'CargarPadron']);
        });
    });

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
        Route::post('/broches/pdf/fichaPropiedad', [PropiedadController::class, 'fichaPropiedad']);

        // Turnos (URL: api/v1/turnos/...)
        Route::get('sectoresturno', [TurnoController::class, 'getSectores']);
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
        Route::get('sellado/exportar-registros', [SelladoController::class, 'exportarexportarRegistrosSelladoController']);


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
        Route::get('/api/notificaciones/traer-notificaciones', [NotificacionController::class, 'traerNotificaciones']);
        Route::post('/api/notificaciones/marcar-como-leida/{id}', [NotificacionController::class, 'marcarUnaComoLeida']);
        Route::get('/usuariosConAgenda/{sector_id}', [Usuario::class, 'usuariosQueTienenAgenda']);

        //Listado Agenda
        Route::post('/listado-agenda', [Pdf_agenda::class, 'listarAgenda']);


        //Impuestos
        Route::get('/actualizar_padron/{impuesto}', [ImpuestosController::class, 'actualizarPadron']);
        Route::get('/padron_impuesto/{impuesto}', [ImpuestosController::class, 'filtradoPadron']);
        Route::put('/actualizar_registro_impuesto', [ImpuestosController::class, 'actualizarImpuesto']);


        Route::get('/padron_carga', [ImpuestosController::class, 'padronCarga']);
        Route::post('/carga_manual', [ImpuestosController::class, 'cargaManual']);
        Route::post('/carga_nuevo_manual', [ImpuestosController::class, 'cargaNuevoManual']);
        Route::post('/nuevo_impuesto', [ImpuestosController::class, 'cargarNuevoImpuesto']);
        Route::get('/exportar_faltantes', [ImpuestosController::class, 'exportarFaltantes']);
        Route::get('/sumar_montos', [ImpuestosController::class, 'sumarMontos']);
        Route::get('/mostrar_broches', [ImpuestosController::class, 'MostrarBroche']);
        Route::get('/guardar_num_broches', [ImpuestosController::class, 'guardarBroches']);
        Route::get('/guardar_num_broche_salas', [ImpuestosController::class, 'guardarBrocheSALAS']);
        Route::get('/exportar_broches', [PdfImpuestoController::class, 'PDF_broche']);
        Route::get('/exportar_broches_salas', [PdfImpuestoController::class, 'PDF_BorcheSalas']);
        Route::get('/modificar_bajado', [ImpuestosController::class, 'modificarBajadoController']);
        Route::put('/modificar_estado', [ImpuestosController::class, 'modificarEstadoTGIController']);
        Route::delete('/eliminar_impuesto', [ImpuestosController::class, 'eliminarRegistro']);
        Route::post('/broches/pdf', [PdfImpuestoController::class, 'descargaPdf']);
        Route::get('/broches/sin_controlar', [ImpuestosController::class, 'sinControlar']);
        Route::put('/gas_bajado', [ImpuestosController::class, 'gasBajado']);
        Route::put('/gas_rechazar', [ImpuestosController::class, 'gasRechazar']);

        // CONTABLE - RETENCIONES (URL: api/v1/retenciones)
        Route::get('retenciones/padronRetencion/{cuil}', [RetencionController::class, 'getPadronRetencionCUILController']);
        Route::get('retenciones/basePorcentual', [RetencionController::class, 'getBasePorcentualController']);
        Route::get('retenciones/retencionPorCUIT/{cuit}', [RetencionController::class, 'getRetencionPorCUITController']);
        Route::post('retenciones/calcularRetencion', [RetencionController::class, 'getCalculoRetencion']);
        Route::get('retenciones/provincias', [RetencionController::class, 'getProvinciasController']);
        Route::get('retenciones/verificar-comprobante', [RetencionController::class, 'getVerficarComprobanteController']);
        Route::get('retenciones/tablaRetenciones', [RetencionController::class, 'getTablaRetencionesController']);
        Route::put('retenciones/modificarBasePorcentual', [RetencionController::class, 'modificarBasePorcentualController']);
        Route::post('retenciones/guardarComprobante', [RetencionController::class, 'postComprobanteController']);
        Route::post('retenciones/guardarPersonaRetencion', [RetencionController::class, 'postPersonaRetencionController']);
        Route::put('retenciones/modificarRegistro/{id}', [RetencionController::class, 'modgiciarRegistroRetencionController']);
        Route::get('/retenciones/suma-quincena', [RetencionController::class, 'obtenerSumasMensualesController']);
        Route::get('/retenciones/exportar-retenciones', [RetencionController::class, 'exportarRetencionesTXTController']);

        // CONTABLE - BUSCADOR PDF (URL: api/v1/buscador-pdf)
        Route::post('contable/comprobantes/verPDF', [BuscadorPdfController::class, 'verPDF']);

        //LISTADO ATCL
        Route::post('/broches/pdf/listadoPropiedad', [ListadoPdfAtcl::class, 'listadoPropiedad']);
        Route::get('propietarios/activos', [PadronController::class, 'padronActivos']);

        // --- RUTAS INTEGRADAS DESDE HEAD ---
        Route::get('asesoresAlquiler', [UsuarioSectorService::class, 'getAllUsuarioSector']);
        Route::get('/propiedad/buscar-por-codigo/{cod_alquiler}', [PropiedadService::class, 'buscarPropiedadesAlquiler']);
        Route::post('/subir-reservas', [ProcesoController::class, 'subirReservas']);
        Route::get('/obtener-reservas', [ProcesoController::class, 'obtenerReservas']);
        Route::post('/guardar-estado', [ProcesoController::class, 'guardarEstado']);
        Route::get('/getHistorialReservaAlquiler', [ProcesoController::class, 'getHistorial']);
        Route::get('/getReservaIdentificada', [ProcesoController::class, 'getReservaIdentificadas']);
        Route::post('/guardarReservaIdentificada', [ProcesoController::class, 'guardarReservaIdentificada']);
        Route::post('/alquiler/obtener-comprobante', [ProcesoController::class, 'obtenerComprobante']);
        Route::get('/getHistorialContrato', [ProcesoController::class, 'getHistorialContrato']);



        // --- RUTAS INTEGRADAS DESDE EXPENSAS ---
        // EXPENSAS
        Route::get('/expensas/unidades', [ExpensasController::class, 'getPadronUnidadesController']);
        Route::get('/expensas/filtro-unidades-completo', [ExpensasController::class, 'filtroUnidadesCompleto']);
        Route::post('/expensas/completar-carga', [ExpensasController::class, 'completarCargaUnidadesController']);
        Route::post('/expensas/actualizar-padron', [ExpensasController::class, 'actualizarPadronUnidadesController']);
        Route::delete('/expensas/eliminar-unidad/{id}', [ExpensasController::class, 'eliminarUnidadController']);

        // 1. Endpoint para llenar la tabla en Vue.js (Lee datos)
        Route::get('/expensas/administradores', [ExpensasController::class, 'getAdministradoresController']);
        // 2. Endpoint para el botón "Actualizar Padrón" (Modifica datos)
        Route::post('/expensas/sincronizar-administradores', [ExpensasController::class, 'sincronizarAdministradoresController']);

        Route::get('/expensas/obtener-edificio', [ExpensasController::class, 'obtenerEdificios']);
        Route::post('/expensas/crear-edificio', [ExpensasController::class, 'crearEdificio']);
        Route::put('/expensas/modificar-edificio/{id}', [ExpensasController::class, 'actualizarEdificio']);

        Route::get('/expensas/broche', [ExpensasController::class, 'getBrochesController']);
        Route::get('/expensas/broche-buscar', [ExpensasController::class, 'brocheExpensasBuscar']);
        Route::post('/expensas/broche-guardar', [ExpensasController::class, 'guardarBrocheExpensaController']);
        Route::put('/expensas/broche-editar/{id}', [ExpensasController::class, 'editarBroche']);
        Route::delete('/expensas/broche-eliminar/{id}', [ExpensasController::class, 'eliminarBrocheController']);
        Route::get('/expensas/broche/descargar-pdf', [ExpensasController::class, 'descargarBrocheExpensas']);

        //Proceso Contrato
        Route::get('getEstadoProcesoContrato', [ProcesoController::class, 'getEstadosContrato']);
        //Route::get('/getHistorialContrato', [ProcesoController::class, 'getHistorialContrato']);
        Route::get('/verificaPermisoUsuario/{botonNombre}', [Permisos::class, 'traerUsuarioPorBoton']);
        Route::post('/ActualizarEstadoContrato', [ProcesoController::class, 'ActualizarEstadoContrato']);
    }); // <--- Aquí cierra el middleware('auth:api') unificado
}); // <--- Aquí cierra el prefix('v1')


// Ruta de redirección por defecto si falla el token
Route::get('/', [AuthController::class, 'unauthorized'])->name('login');
