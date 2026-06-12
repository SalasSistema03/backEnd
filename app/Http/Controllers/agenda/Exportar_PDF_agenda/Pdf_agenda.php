<?php

namespace App\Http\Controllers\agenda\Exportar_PDF_agenda;

use App\Models\agenda\Notas;
use App\Models\agenda\Sectores;
use App\Models\At_cl\Propiedad;
use App\Models\cliente\clientes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\usuarios_y_permisos\Usuario;

class Pdf_agenda
{
    public function listarAgenda(Request $request)
    {



        $query = Notas::query();



        // FILTRO ESTADO
        if ($request->filled('estado')) {

            if ($request->estado == '1') {

                $query->where('activo', 1);
            } else {

                $query->where('activo', 0);
            }
        }

        // FILTRO FECHA INICIO
        if ($request->filled('fecha_inicio') && $request->fecha_inicio !== 'null') {
            $query->whereDate('fecha', '>=', $request->fecha_inicio);
        }

        // FILTRO FECHA FIN
        if ($request->filled('fecha_fin') && $request->fecha_fin !== 'null') {
            $query->whereDate('fecha', '<=', $request->fecha_fin);
        }

        // FILTRO SECTOR
        if ($request->filled('sector') && $request->sector !== 'null') {
            $query->whereHas('agenda', function ($q) use ($request) {

                $q->where('sector_id', $request->sector);
            });
        }






        //Obtenemos el nombre del sector
        $sector = Sectores::find($request->sector);
        $sectorNombre = $sector ? $sector->nombre : '';
        //Obtenemos el rango de fechas y lo ponemos en un array
        $rangoFechas = [$request->fecha_inicio, $request->fecha_fin];
        //Obtenemos el estado
        $estado = $request->estado == '1' ? 'Activo' : 'Inactivo';









        if ($request->listado == 'listadoAgenda') {

            $pertenece = $request->listado;

            // FILTRO USUARIO
            if ($request->filled('usuario') && $request->usuario !== 'null') {
                $query->where('usuario_id', $request->usuario);
            }

            // EJECUTAR QUERY
            $datos = $query->orderBy('fecha', 'desc')->get();

            //obtenemos los username
            foreach ($datos as $dato) {
                $usuario = Usuario::find($dato->usuario_id);
                $borro = Usuario::find($dato->quien_borro);
                $dato->usuario_id = $usuario ? $usuario->username : '';
                $dato->quien_borro = $borro ? $borro->username : '';

                //Asignamos el username al creado por si no es null
                if ($dato->creado_por != null) {
                    $creadoPor = Usuario::find($dato->creado_por);
                    $dato->creado_por = $creadoPor ? $creadoPor->username : '';
                } else {
                    $dato->creado_por = '';
                }

                // CORREGIDO: find() ya te da el registro correcto, no uses ->first()
                if ($dato->cliente_id != null) {
                    $clientedata = clientes::find($dato->cliente_id);
                    $dato->datos_cliente = $clientedata;
                }

                // CORREGIDO: Para usar find con relaciones (with), se hace de esta manera:
                if ($dato->propiedad_id != null) {
                    $propiedadata = Propiedad::with('calle', 'zona')->find($dato->propiedad_id);
                    $dato->datos_propiedad = $propiedadata;
                }
            }






            //Obtenemos el nombre del usuario
            $usuarioNombre = '';
            if ($request->filled('usuario') && $request->usuario !== 'null') {
                $usuario = Usuario::find($request->usuario);
                $usuarioNombre = $usuario ? $usuario->username : $request->usuario;
            } elseif ($datos->isNotEmpty()) {
                $usuarioNombre = $datos->first()->usuario_id ?: '-';
            }

            //Log::info('Datos', [$datos]);

            $html = view('pdfs.agenda.listadoAgenda', compact('datos', 'rangoFechas', 'sectorNombre', 'estado', 'usuarioNombre', 'pertenece'))->render();
        } elseif ($request->listado == 'AgendaMuestra') {

            $pertenece = $request->listado;

            // Traemos solo los que tienen cliente_id
            $query->whereNotNull('cliente_id');

            // Vinculamos con cliente y propiedad (cargamos todo de una vez)
            $query->with(['cliente', 'propiedad.calle']);

            $datos = $query->orderBy('usuario_id', 'asc')->get();

            // Obtenemos los usuario_id únicos de los datos
            $usuarioIds = $datos->pluck('usuario_id')->unique()->filter();

            // Traemos todos los usuarios necesarios en UNA sola consulta
            $usuarios = Usuario::whereIn('id', $usuarioIds)->pluck('username', 'id');

            // --- CONTEO: username => cantidad ---
            $conteoUsuarios = $datos->groupBy('usuario_id')
                ->map(function ($items, $usuarioId) use ($usuarios) {
                    $username = $usuarios->get($usuarioId, 'Desconocido');
                    return [
                        'username' => $username,
                        'cantidad' => $items->count(),
                    ];
                })
                ->sortByDesc('cantidad')
                ->values(); // Resetear índices

            // Formato que pediste: ["1174-13", "c102-4", ...]
            $conteoFormateado = $conteoUsuarios->map(function ($item) {
                return $item['username'] . '-' . $item['cantidad'];
            })->toArray();

            // --- FOREACH ORIGINAL: Asignar username a creado_por ---
            foreach ($datos as $dato) {
                // Reemplazar usuario_id por username
                $dato->usuario_id = $usuarios->get($dato->usuario_id, 'Desconocido');

                // Tu código existente de creado_por
                if ($dato->creado_por != null) {
                    $creadoPor = $usuarios->get((int) $dato->creado_por)
                        ?? Usuario::find($dato->creado_por)?->username;
                    $dato->creado_por = $creadoPor ?? '';
                } else {
                    $dato->creado_por = '';
                }
            }

            // Log para verificar
            // Log::info('Conteo de usuarios', $conteoFormateado);
            Log::info('Datos', [$datos]);

            $html = view('pdfs.agenda.listadoAgenda', compact(
                'datos',
                'pertenece',
                'rangoFechas',
                'sectorNombre',
                'estado',
                'sector',
                'conteoUsuarios',      // Array con username y cantidad
                'conteoFormateado'     // ["1174-13", "c102-4", ...]
            ))->render();
        }

        return response()->streamDownload(function () use ($html) {
            echo \Spatie\Browsershot\Browsershot::html($html)
                ->format('A4')
                ->margins(10, 1, 10, 1)
                ->showBackground()
                ->emulateMedia('print')
                ->setOption('displayHeaderFooter', true)
                ->setOption('headerTemplate', '<div style="font-size:10px; color:#666; width:100%; display:flex; justify-content:space-between; padding:0 20px;"><span style="text-align:left;"></span><span style="text-align:right;">Página <span class="pageNumber"></span> de <span class="totalPages"></span></span></div>')
                ->landscape()
                /* vertical */
                /* ->portrait() */
                ->setOption('footerTemplate', '<div style="font-size:10px; color:#666; width:100%; display:flex; justify-content:space-between; padding:0 20px;"><span style="text-align:left;">Salas Inmobiliaria</span><span style="text-align:center;">'  . '</span>  <span style="text-align:right;" class="date"></span></div>')
                ->pdf();
        }, 'ficha_propiedad.pdf');
    }
}
