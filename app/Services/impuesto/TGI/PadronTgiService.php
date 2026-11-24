<?php

namespace App\Services\impuesto\TGI;


use App\Models\impuesto\Tgi_padron;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PadronTgiService
{

    public function __construct() {}

    //Este metodo busca una tgi por partida
    public function buscarTgiPorPartida($partida, $folio = null)
    {
        $consulta = Tgi_padron::where('partida', trim($partida));

        if ($folio !== null) {
            $consulta->where('folio', ltrim(trim((string) $folio), '0'));
        }

        return $this->seleccionarRegistroParaCarga($consulta->get());
    }

    private function seleccionarRegistroParaCarga($registros)
    {
        $registros = collect($registros);

        return $registros->where('estado', 'ACTIVO')->sortByDesc('comienza')->first()
            ?? $registros->sortByDesc('comienza')->first();
    }


    //Esta función obtiene el padrón TGI desde la base de datos propia
    //Funcion utilizada por el servicio PadronImpuestoService
    public function obtenerPadronExistente()
    {
        return Tgi_padron::orderByRaw("
        CASE
            WHEN folio LIKE '50%' AND LENGTH(folio) = 5 THEN 0
            ELSE 1
        END
    ")
            ->orderBy('empresa')
            ->orderBy('folio')
            ->get();
    }



    //Esta consulta obtiene el padrón TGI desde la base de datos externa
    //Funcion utilizada por el servicio PadronImpuestoService
    public function consultaObtenerPadronTGI(Carbon $periodo)
    {
        $sql = "
       SELECT
    p.carpeta AS folio,
    CONCAT(n.Calle, ' ', p.altura) AS calle,
    pi.partida,
    pi.clave_internet AS clave,
    pi.quien_abona AS abona,
    pi.quien_administra AS administra,
    e.id_empresa AS empresa,
    ti.impuesto,
    cc.id_contrato_cabecera AS id_contrato_cabecera,
    cc.comienza AS comienza,
    cc.rescicion AS rescicion
FROM desarrollo.propiedades_impuestos pi
INNER JOIN desarrollo.tipos_impuestos ti
    ON pi.id_tipo_impuesto = ti.id_tipo_impuesto
INNER JOIN desarrollo.propiedades p
    ON p.id_casa = pi.id_casa
INNER JOIN desarrollo.nomenclador n
    ON n.Id_Nomenclador = p.id_nomenclador
LEFT JOIN desarrollo.impuestos i
    ON pi.id_casa = i.id_casa
INNER JOIN desarrollo.contratos_cabecera cc
    ON cc.id_casa = p.id_casa
INNER JOIN desarrollo.empresa e
    ON cc.id_empresa = e.id_empresa
WHERE ti.id_tipo_impuesto = 1
  AND cc.comienza <= ?
  AND cc.rescicion >= ?
GROUP BY
    pi.id_propiedad_impuesto,
    p.carpeta,
    CONCAT(n.Calle, ' ', p.altura),
    pi.partida,
    pi.clave_internet,
    pi.quien_abona,
    pi.quien_administra,
    e.id_empresa,
    ti.impuesto,
    cc.id_contrato_cabecera,
    cc.comienza,
    cc.rescicion
ORDER BY e.id_empresa, folio;
    ";

        $resultado = DB::connection('mysql2')->select($sql, [
            $periodo->copy()->endOfMonth()->toDateString(),
            $periodo->copy()->startOfMonth()->toDateString(),
        ]);
        //Log::info([$resultado]);
        //dd('hola');
        // Normalizar partida a 8 dígitos
        foreach ($resultado as $row) {
            if (isset($row->partida)) {
                $row->partida = str_pad($row->partida, 8, '0', STR_PAD_LEFT);
            }
        }
        return $resultado;
    }
}
