<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\agenda\Agenda;
use App\Models\agenda\notas;
use App\Models\agenda\Sectores;
use App\Models\cliente\clientes;
use App\Models\At_cl\Propiedad;
use App\Models\agenda\Recordatorio;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $usuario_id = session('usuario')->id;
        $hoy = Carbon::today()->toDateString();

        // Notas del usuario para hoy
        $notas = notas::where('usuario_id', $usuario_id)
            ->whereDate('fecha', $hoy)
            ->orderBy('hora_inicio', 'asc')
            ->get();

        // Todas las agendas, sectores, clientes y propiedades
        $agendas = Agenda::all()->keyBy('id');
        $sectores = Sectores::all()->keyBy('id');
        $clientes = clientes::all()->keyBy('id_cliente'); // O 'id' si tu modelo usa ese campo
        $propiedades = Propiedad::all()->keyBy('id'); // O el campo PK correcto

        // Vincular y preparar la data para la vista
        $notasVinculadas = $notas->map(function ($nota) use ($agendas, $sectores, $clientes, $propiedades) {
            $agenda = $agendas->get($nota->agenda_id);
            $sector = $agenda ? $sectores->get($agenda->sector_id) : null;
            $cliente = $nota && isset($nota->cliente_id) ? $clientes->get($nota->cliente_id) : null;
            //con el propiedad_id quiero obtener el name de la calle que esta en la tabla calles
            //$propiedad = $nota && isset($nota->propiedad_id) ? $propiedades->get($nota->propiedad_id) : null;
            $propiedad = $nota && $nota->propiedad_id
                ? Propiedad::with('calle')->find($nota->propiedad_id)
                : null;

            $nombreCalle = $propiedad?->calle?->name;  // ← nombre de la calle

            /*  dd($propiedad);  */

            return [
                'descripcion' => $nota->descripcion,
                'hora_inicio' => $nota->hora_inicio,
                'hora_fin' => $nota->hora_fin,
                'sector' => $sector ? $sector->nombre : '',
                'cliente' => $cliente ? $cliente->nombre : '',
                /*  'propiedad_venta' => $propiedad ? ($propiedad->cod_venta ?? $propiedad->id) : '',
                'propiedad_alquiler' => $propiedad ? ($propiedad->cod_alquiler ?? $propiedad->id) : '', */
                'nombre_calle' => $nombreCalle ?? '',
                'numero_calle' => $propiedad->numero_calle ?? '',
            ];
        });



        $hoy = Carbon::today()->toDateString();


        //$dolarData = Http::get('https://dolarapi.com/v1/dolares')->json();
        $dolarOficial = Http::get('https://dolarapi.com/v1/dolares/oficial')->json();
        $dolarBlue = Http::get('https://dolarapi.com/v1/dolares/blue')->json();

        //quiero combinar los dos json
        $dolarData = [
            'oficial' => $dolarOficial,
            'blue' => $dolarBlue
        ];


        $agenda = Agenda::where('usuario_id', $usuario_id)->get();
        $sectores_ids = $agenda->pluck('sector_id')->filter()->unique()->toArray();
        $recordatorio = Recordatorio::where('activo', 1)
            ->where(function ($query) use ($usuario_id, $sectores_ids, $hoy) {
                // Mis recordatorios
                $query->where('usuario_carga', $usuario_id);

                // O recordatorios de mis sectores
                if (!empty($sectores_ids)) {
                    $query->orWhereHas('agenda', function ($q) use ($sectores_ids) {
                        $q->whereIn('sector_id', $sectores_ids);
                    });
                }
            })
            ->where(function ($query) use ($hoy) {
                $query->whereDate('fecha_inicio', '<=', $hoy); // Fecha inicio es hoy
            })
            ->with('agenda.sector')
            ->orderBy('hora', 'asc')
            ->get();

        return view('home', [
            'hoy' => $hoy,
            'notasVinculadas' => $notasVinculadas,
            'recordatorio' => $recordatorio,
            'dolares' => $dolarData
        ]);
    }


    public function getDolarOficial()
    {
        $response = Http::get('https://dolarapi.com/v1/dolares/oficial');
        return $response->json();
    }
}
