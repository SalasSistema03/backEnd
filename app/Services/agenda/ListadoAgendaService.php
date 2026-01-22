<?php

namespace App\Services\agenda;

use App\Models\agenda\Agenda;
use App\Models\cliente\Usuario_sector;
use App\Models\At_cl\Usuario;
use App\Models\agenda\Notas;
use App\Models\At_cl\Propiedad;
use App\Models\cliente\clientes;
use Carbon\Carbon;
use Barryvdh\Snappy\Facades\SnappyPdf;

/**
 * Service responsible for listing, filtering and exporting agenda notes
 *
 * Provides methods to retrieve advisors, filter agenda notes by multiple
 * criteria, enrich notes with related data and generate PDF reports.
 *
 * @category   Services
 * @package    App\Services\agenda
 * @author     Salas Sistemas
 * @copyright  Copyright (c) 2026
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 * @version    Release: 1.0.0
 * @since      Class available since Release 1.0.0
 */
class ListadoAgendaService
{
    /**
     * Retrieves advisors belonging to the sales sector
     *
     * @return array list of advisors with id, name and username
     * @access public
     */
    public function getAsesoresVenta()
    {
        $usuarioSectorVenta = Usuario_sector::where('venta', 'S')->get();
        $usuarios = Usuario::all();

        return $usuarioSectorVenta->map(function ($us) use ($usuarios) {
            $usuario = $usuarios->firstWhere('id', $us->id_usuario);

            return [
                'id_usuario' => $us->id_usuario,
                'name'       => $usuario?->name,
                'username'   => $usuario?->username,
            ];
        });
    }

    /**
     * Retrieves advisors belonging to the rental sector
     *
     * @return array list of rental advisors with id, name and username
     * @access public
     */
    public function getAsesoresAlquiler()
    {
        $usuariosAlquiler = Agenda::where('sector_id', 3)->get();
        $usuarios = Usuario::all();

        return $usuariosAlquiler->map(function ($us) use ($usuarios) {
            $usuario = $usuarios->firstWhere('id', $us->usuario_id);

            return [
                'id_usuario' => $us->usuario_id,
                'name'       => $usuario?->name,
                'username'   => $usuario?->username,
            ];
        });
    }

    /**
     * Retrieves advisors grouped by sector
     *
     * @return array advisors grouped by sales and rental
     * @access public
     */
    public function getAsesores()
    {
        return [
            'asesores'       => $this->getAsesoresVenta(),
            'alquilerAsesor' => $this->getAsesoresAlquiler(),
        ];
    }

    /**
     * Retrieves filtered agenda notes by advisor
     *
     * @param string $fechaInicio start date for filtering
     * @param string $fechaFin    end date for filtering
     * @param int    $asesorId    advisor identifier
     * @param string $estado      note state filter
     * @param string $pertenece   agenda context identifier
     *
     * @return object collection of filtered notes
     * @access public
     */
    public function obtenerNotasFiltradas($fechaInicio, $fechaFin, $asesorId, $estado, $pertenece)
    {
        if ($pertenece == 'PropiedadesxAsesorV') {
            $query = Notas::whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->join('agenda', 'notas.agenda_id', '=', 'agenda.id')
                ->where('agenda.sector_id', '=', '2')
                ->where('agenda.usuario_id', $asesorId);
        } elseif ($pertenece == 'PropiedadesxAsesorA') {
            $query = Notas::whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->join('agenda', 'notas.agenda_id', '=', 'agenda.id')
                ->where('agenda.sector_id', '=', '3')
                ->where('agenda.usuario_id', $asesorId);
        }

        if ($estado == '1') {
            $query->where('notas.activo', '=', '1');
        } elseif ($estado == '0') {
            $query->where('notas.activo', '=', '0');
        }

        return $query->orderBy('fecha', 'desc')->get();
    }

    /**
     * Retrieves filtered agenda notes without advisor constraint
     *
     * @param string $fechaInicio start date for filtering
     * @param string $fechaFin    end date for filtering
     * @param string $estado      note state filter
     * @param string $pertenece   agenda context identifier
     *
     * @return object collection of filtered notes
     * @access public
     */
    public function obtenerNotasFiltradasSinAsesor($fechaInicio, $fechaFin, $estado, $pertenece)
    {
        if ($pertenece == 'PropiedadesxAsesorV') {
            $query = Notas::whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->join('agenda', 'notas.agenda_id', '=', 'agenda.id')
                ->where('agenda.sector_id', '=', '2');
        } elseif ($pertenece == 'PropiedadesxAsesorA') {
            $query = Notas::whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->join('agenda', 'notas.agenda_id', '=', 'agenda.id')
                ->where('agenda.sector_id', '=', '3');
        }

        if ($estado == '1') {
            $query->where('notas.activo', '=', '1');
        } elseif ($estado == '0') {
            $query->where('notas.activo', '=', '0');
        }

        return $query->orderBy('fecha', 'desc')->get();
    }

    /**
     * Enriches agenda notes with related user, client and property data
     *
     * @param object $notas      collection of notes
     * @param string $pertenece  agenda context identifier
     *
     * @return object enriched notes collection
     * @access public
     */
    public function enriquecerNotas($notas, $pertenece)
    {
        $usuarios = Usuario::whereIn('id', $notas->pluck('usuario_id'))
            ->pluck('username', 'id')
            ->toArray();

        $clientes = clientes::whereIn('id_cliente', $notas->pluck('cliente_id'))
            ->pluck('nombre', 'id_cliente')
            ->toArray();

        if ($pertenece == 'PropiedadesxAsesorV') {
            $propiedades = Propiedad::whereIn('id', $notas->pluck('propiedad_id'))
                ->pluck('cod_venta', 'id')
                ->toArray();
        } else {
            $propiedades = Propiedad::whereIn('id', $notas->pluck('propiedad_id'))
                ->pluck('cod_alquiler', 'id')
                ->toArray();
        }

        return $notas->map(function ($nota) use ($usuarios, $clientes, $propiedades) {
            $nota->username  = $usuarios[$nota->usuario_id] ?? 'Sin usuario';
            $nota->cliente   = $clientes[$nota->cliente_id] ?? 'Sin cliente';
            $nota->propiedad = $propiedades[$nota->propiedad_id] ?? 'Sin propiedad';

            try {
                $nota->horaInicioFormatada = $nota->hora_inicio
                    ? Carbon::createFromFormat('H:i:s', $nota->hora_inicio)->format('H:i')
                    : '';
                $nota->horaFinFormatada = $nota->hora_fin
                    ? Carbon::createFromFormat('H:i:s', $nota->hora_fin)->format('H:i')
                    : '';
            } catch (\Exception $e) {
                $nota->horaInicioFormatada = $nota->hora_inicio;
                $nota->horaFinFormatada    = $nota->hora_fin;
            }

            return $nota;
        });
    }

    /**
     * Generates a PDF report for agenda notes
     *
     * @param object $notas     collection of notes
     * @param string $pertenece agenda context identifier
     *
     * @return object generated PDF instance
     * @access public
     */
    public function generarPdfListadoEstados($notas, $pertenece)
    {
        return SnappyPdf::loadView(
            'atencionAlCliente.propiedad.pdf.pdfListadoEstados',
            compact('notas', 'pertenece')
        )
            ->setOption('page-size', 'legal')
            ->setOption('orientation', 'landscape')
            ->setOption('margin-top', 20)
            ->setOption('margin-bottom', 15)
            ->setOption('margin-left', 15)
            ->setOption('margin-right', 15)
            ->setOption('disable-smart-shrinking', true)
            ->setOption('footer-left', 'Salas Inmobiliaria')
            ->setOption('footer-font-size', 3)
            ->setOption('footer-center', 'Page [page] of [toPage]')
            ->setOption('zoom', 0.85);
    }
}
