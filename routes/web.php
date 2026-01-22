<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\At_cl\UsuariosController;
use App\Http\Controllers\sys\PropiedadesController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\NavController;
use App\Http\Controllers\buscadorPdf\BuscadorPdfController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\clientes\AsesoresController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/* Ruta de principal por defecto */

Route::get('/', function () {
    return response()->json(['error'=>'Inicie sesion'], Response::HTTP_UNAUTHORIZED);
})->name('login');

Route::get('/get_server_time', function () {
    // Aseguramos que la hora sea en la zona horaria de Argentina
    $serverTime = now(config('app.timezone'));
    // Ajustamos la hora sumando 5 horas (UTC-3)
    $serverTime->addHours(5);
    return response()->json(['server_time' => $serverTime->toIso8601String()]);
});

Route::get('/log', function () {
    return view('login');
})->name('log');


Route::get('/logout', function () {
    session()->flush();
    return redirect()->route('log');
})->name('logout');
Route::post('usuarioVerificacion', [UsuariosController::class, 'verificacion'])->name('usuarioVerificacion');

/* Agrupación de rutas protegidas */
Route::get('/clientes/buscar-telefono', [\App\Http\Controllers\agenda\AgendaController::class, 'buscarClientesPorTelefono']);
Route::get('/propiedades/buscar-codigo', [\App\Http\Controllers\agenda\AgendaController::class, 'buscarPropiedadPorCodigo']);
Route::get('/buscar-calle', [\App\Http\Controllers\agenda\AgendaController::class, 'buscarPropiedadPorCalle']);
Route::get('/agenda/hoy', [\App\Http\Controllers\agenda\AgendaController::class, 'eventosDelDia'])->name('agenda.hoy');


Route::group(['middleware' => function ($request, $next) {

    // Verificar si el usuario tiene una sesión activa
    if (!session()->has('usuario_id')) {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        return redirect()->route('log')->with('error', 'Debes iniciar sesión primero.');
    }

    return $next($request);
}], function () {
    Route::post('/agregar-propietario-sesion', function (Request $request) {
        $propietarios = session()->get('propietarios', []);
        $propietarios[] = [
            'id' => $request->input('propietario_id'),
            'nombre' => $request->input('nombre')
        ];
        session()->put('propietarios', $propietarios);

        return response()->json(['success' => true]);
    });

    //NAV PERMISOS
    Route::get('/nav', [NavController::class, 'index'])->name('nav.index');

    Route::get('/test', [PropiedadesController::class, 'index'])->name('test');

    //Atención al Cliente
    require __DIR__ . '/at_cl.php';


    //CONTABLE ----> SELLADO
    require __DIR__ . '/contable.php';

    //CLIENTES.
    require __DIR__ . '/clientes.php';

    //ASESORES
    require __DIR__ . '/asesores.php';

    /* TURNOS */
    require __DIR__ . '/turnos.php';

    /* AGENDA */
    require __DIR__ . '/agenda.php';

    /* RETENCIONES */
    require __DIR__ . '/retenciones.php';

    /* Exportar PDF */
    require __DIR__ . '/Exportar_PDF_routes/atcl_ventas.php';
    require __DIR__ . '/Exportar_PDF_routes/atcl_alquiler.php';
    require __DIR__ . '/Exportar_PDF_routes/agenda_pdf.php';
    require __DIR__ . '/Exportar_PDF_routes/impuesto_pdf.php';


    /* IMPUESTOS */
    require __DIR__ . '/tgi_routes.php';
    require __DIR__ . '/api_routes.php';
    /*EXPENSAS*/
    require __DIR__ . '/expensas.php';


    /*PDF*/
    Route::get('/buscaPdf', [BuscadorPdfController::class, 'index'])->name('buscaPdf');
    Route::get('/ver-pdf', [BuscadorPdfController::class, 'verPDF'])->name('ver.pdf');

    /* NOTIFICACIONES */
    Route::post('/notificaciones/{id}/leer', [NotificacionController::class, 'marcarUnaComoLeida'])
        ->name('notificaciones.leer');
});


// Ruta para el buscador de PDF
Route::controller(BuscadorPdfController::class)->group(function () {
    Route::get('/buscador-pdf', 'index')->name('buscador.pdf');
    Route::get('/ver-pdf', 'verPDF')->name('ver.pdf');
});

Route::get('/test', function () {
    try {
        $valores = DB::connection('mysql3')->table('valor_hoja')->get();
        return $valores;
    } catch (\Exception $e) {
        return $e->getMessage(); // Muestra el error si algo falla
    }
});


