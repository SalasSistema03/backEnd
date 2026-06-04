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
        //Log::info('Request recibido para listar agenda', ['request' => $request->all()]);


        $query = Notas::query();

        // FILTRO ESTADO
        if ($request->filled('estado')) {

            if ($request->estado == '1') {
                $query->where('activo', 1);
            } else {
                $query->where('activo', 0);
            }
        }

        // FILTRO USUARIO
        if ($request->filled('usuario') && $request->usuario !== 'null') {
            $query->where('usuario_id', $request->usuario);
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

        // EJECUTAR QUERY
        $datos = $query->orderBy('fecha', 'desc')->get();

        //si los datos de cliente_id no son null, traemos todos los datos del cliente


        //obtenemos los username
        foreach ($datos as $dato) {
            $usuario = Usuario::find($dato->usuario_id);
            $borro = Usuario::find($dato->quien_borro);
            $dato->usuario_id = $usuario ? $usuario->username : '';
            $dato->quien_borro = $borro ? $borro->username : '';

            //Asignamos el username al creado por si no es null
            if($dato->creado_por != null){
                $creadoPor = Usuario::find($dato->creado_por);
                $dato->creado_por = $creadoPor ? $creadoPor->username : '';
            } else {
                $dato->creado_por = '';
            }

            //Traemos todos los datos del cliente si corresponde
            if ($dato->cliente_id != null) {
                $clientedata = clientes::find($dato->cliente_id)->first();
                $dato->datos_cliente = $clientedata;
                //Log::info('clientedata', [$clientedata]);
            }

            //Traemos los datos de la propiedad si corresponde
            if ($dato->propiedad_id != null) {
                $propiedadata = Propiedad::find($dato->propiedad_id)
                ->with('calle', 'zona')
                ->first();
                $dato->datos_propiedad = $propiedadata;
            }
        }
        //Obtenemos el rango de fechas y lo ponemos en un array
        $rangoFechas = [$request->fecha_inicio, $request->fecha_fin];
        //Obtenemos el nombre del sector
        $sector = Sectores::find($request->sector);
        $sectorNombre = $sector ? $sector->nombre : '';

        $estado = $request->estado == '1' ? 'Activo' : 'Inactivo';

        //Obtenemos el nombre del usuario
        $usuarioNombre = '';
        if ($request->filled('usuario') && $request->usuario !== 'null') {
            $usuario = Usuario::find($request->usuario);
            $usuarioNombre = $usuario ? $usuario->username : $request->usuario;
        } elseif ($datos->isNotEmpty()) {
            $usuarioNombre = $datos->first()->usuario_id ?: '-';
        }

        //Log::info('Datos', [$datos]);

        $html = view('pdfs.agenda.listadoAgenda', compact('datos', 'rangoFechas','sectorNombre','estado','usuarioNombre'))->render();

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
