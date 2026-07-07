<?php

namespace App\Http\Controllers\agenda\Exportar_PDF_agenda;

use App\Models\agenda\Notas;
use App\Models\agenda\Sectores;
use App\Models\At_cl\Propiedad;
use App\Models\cliente\clientes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\usuarios_y_permisos\Usuario;
use Barryvdh\Snappy\Facades\SnappyPdf;

class Pdf_agenda
{
    public function listarAgenda(Request $request)
    {
        try {
            // Eager Loading: Traemos las relaciones de cliente y propiedad de una sola vez para evitar N+1 queries.
            // Asumo que en el modelo Notas tenés las relaciones 'cliente' y 'propiedad'.
            $query = Notas::with(['cliente', 'propiedad.calle', 'propiedad.zona']);

            // --- APLICACIÓN DE FILTROS ---
            if ($request->filled('estado')) {
                $query->where('activo', $request->estado == '1' ? 1 : 0);
            }

            if ($request->filled('fecha_inicio') && $request->fecha_inicio !== 'null') {
                $query->whereDate('fecha', '>=', $request->fecha_inicio);
            }

            if ($request->filled('fecha_fin') && $request->fecha_fin !== 'null') {
                $query->whereDate('fecha', '<=', $request->fecha_fin);
            }

            if ($request->filled('sector') && $request->sector !== 'null') {
                $query->whereHas('agenda', function ($q) use ($request) {
                    $q->where('sector_id', $request->sector);
                });
            }

            // Datos generales para la vista
            $sector = Sectores::find($request->sector);
            $sectorNombre = $sector ? $sector->nombre : 'Todos los sectores';
            $rangoFechas = [$request->fecha_inicio, $request->fecha_fin];
            $estado = $request->estado == '1' ? 'Activo' : 'Inactivo';

            $pertenece = $request->listado;
            $html = '';

            // =======================================================
            // CASO 1: LISTADO AGENDA
            // =======================================================
            if ($pertenece == 'listadoAgenda') {
                
                if ($request->filled('usuario') && $request->usuario !== 'null') {
                    $query->where('usuario_id', $request->usuario);
                }

                $datos = $query->orderBy('fecha', 'desc')->get();

                // OPTIMIZACIÓN DE USUARIOS: Buscamos todos los IDs juntos (usuario_id, creado_por, quien_borro)
                $userIds = $datos->pluck('usuario_id')
                    ->merge($datos->pluck('quien_borro'))
                    ->merge($datos->pluck('creado_por'))
                    ->filter()->unique();

                $usuarios = Usuario::whereIn('id', $userIds)->pluck('username', 'id');

                // Asignamos los nombres a variables NUEVAS para no pisar los IDs originales
                foreach ($datos as $dato) {
                    $dato->nombre_usuario = $usuarios->get($dato->usuario_id, '-');
                    $dato->nombre_quien_borro = $usuarios->get($dato->quien_borro, '-');
                    $dato->nombre_creado_por = $usuarios->get($dato->creado_por, '-');
                    
                    // Como ya usamos with(), no hace falta el find() si usás $dato->cliente en la vista.
                    // Pero por compatibilidad con tu Blade, lo guardamos en las variables que usabas:
                    $dato->datos_cliente = $dato->cliente; 
                    $dato->datos_propiedad = $dato->propiedad;
                }

                $usuarioNombre = '';
                if ($request->filled('usuario') && $request->usuario !== 'null') {
                    $usuario = Usuario::find($request->usuario);
                    $usuarioNombre = $usuario ? $usuario->username : $request->usuario;
                } elseif ($datos->isNotEmpty()) {
                    $usuarioNombre = $datos->first()->nombre_usuario ?: '-';
                }

                $html = view('pdfs.agenda.listadoAgenda', compact('datos', 'rangoFechas', 'sectorNombre', 'estado', 'usuarioNombre', 'pertenece'))->render();

            // =======================================================
            // CASO 2: AGENDA MUESTRA
            // =======================================================
            } elseif ($pertenece == 'AgendaMuestra') {
                
                $query->whereNotNull('cliente_id');
                $datos = $query->orderBy('usuario_id', 'asc')->get();

                $usuarioIds = $datos->pluck('usuario_id')->merge($datos->pluck('creado_por'))->filter()->unique();
                $usuarios = Usuario::whereIn('id', $usuarioIds)->pluck('username', 'id');

                $conteoUsuarios = $datos->groupBy('usuario_id')->map(function ($items, $usuarioId) use ($usuarios) {
                        return [
                            'username' => $usuarios->get($usuarioId, 'Desconocido'),
                            'cantidad' => $items->count(),
                        ];
                    })->sortByDesc('cantidad')->values();

                $conteoFormateado = $conteoUsuarios->map(function ($item) {
                    return $item['username'] . '-' . $item['cantidad'];
                })->toArray();

                foreach ($datos as $dato) {
                    // Usamos variables nuevas
                    $dato->nombre_usuario = $usuarios->get($dato->usuario_id, 'Desconocido');
                    $dato->nombre_creado_por = $usuarios->get($dato->creado_por, '');
                }

                $html = view('pdfs.agenda.listadoAgenda', compact(
                    'datos', 'pertenece', 'rangoFechas', 'sectorNombre', 'estado', 'sector', 'conteoUsuarios', 'conteoFormateado'
                ))->render();
            }

       // =======================================================
            // GENERACIÓN DEL PDF (CON SNAPPY)
            // =======================================================
      $pdf = SnappyPdf::loadHTML($html)
                ->setOption('page-size', 'A4')
                ->setOption('orientation', 'landscape')
                ->setOption('margin-top', 10)
                ->setOption('margin-bottom', 10)
                ->setOption('margin-left', 1)
                ->setOption('margin-right', 1)
                ->setOption('footer-left', 'Salas Inmobiliaria')
                ->setOption('footer-right', 'Página [page] de [toPage]')
                ->setOption('footer-font-size', 8)
                // --- ESTAS DOS OPCIONES SOLUCIONAN EL BLOQUEO ---
                ->setOption('enable-local-file-access', true)
                ->setOption('load-error-handling', 'ignore'); // Si no puede descargar un CDN, sigue igual en vez de dar error 500

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="listado_agenda.pdf"'
            ]);

        }catch (\Exception $e) {
            Log::error('Error al generar PDF de Agenda: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}