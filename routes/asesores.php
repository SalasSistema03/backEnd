<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\clientes\AsesoresController;


 // Rutas específicas PRIMERO
 Route::post('/asesores/enviar-mensaje', [AsesoresController::class, 'enviarMensaje'])->name('asesores.enviar-mensaje');
 Route::get('/asesores/conversacion/{criterioId}', [AsesoresController::class, 'getConversacion'])->name('asesores.get-conversacion');
 Route::put('/clientes/modificar-datos-personales/{id}', [AsesoresController::class, 'modificarDatosPersonales'])->name('clientes.modificar-datos-personales');
 Route::put('/clientes/modificar-criterio/{id}', [AsesoresController::class, 'modificarCriterio'])->name('clientes.modificar-criterio');
 Route::get('/chatpropiedad/{propiedadId}', [AsesoresController::class, 'getPropiedad']);
 Route::put('/clientes/devolver-mensaje/{id}', [AsesoresController::class, 'devolverMensaje'])->name('clientes.devolver-mensaje');

 Route::post('/historialCodOfrecimiento', [AsesoresController::class, 'guardarHistorialCodOfrecimiento'])
 ->name('historial.codigo.ofrecimiento');
 Route::get('/historialCodOfrecimiento/{id}', [AsesoresController::class, 'obtenerHistorialCod']);
 

 // Resource route AL FINAL
 Route::resource('/asesores', AsesoresController::class);

