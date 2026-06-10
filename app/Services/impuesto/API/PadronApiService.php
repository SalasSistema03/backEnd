<?php

namespace App\Services\impuesto\API;

use App\Models\impuesto\Api_padron;
use App\Models\impuesto\Tgi_padron;
use Illuminate\Support\Facades\DB;


class PadronApiService
{

    public function __construct() {}

    //Este metodo busca una tgi por partida
    public function buscarApiPorPartida($partida)
    {
        // Buscar en la tabla tgi_padron por el campo 'partida'
         
        $apiPadron = Api_padron::where('partida', $partida)->first();

        return $apiPadron; // Retorna el registro encontrado o null si no existe
    }


    //Esta función obtiene el padrón API desde la base de datos propia
    //Funcion utilizada por el servicio PadronImpuestoService
    public function obtenerPadronExistente()
    {
        return Api_padron::orderByRaw("
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
    public function consultaObtenerPadronAPI()
    {
        $sql = "
        SELECT
            p.carpeta AS folio,
            CONCAT(n.Calle, ' ', p.altura) AS calle,
            pi.partida,
            pi.clave_internet as clave,
            pi.quien_abona as abona,
            pi.quien_administra as administra,
            e.id_empresa as empresa,
            ti.impuesto,
            cc.comienza as comienza,
            cc.rescicion as rescicion
        FROM desarrollo.propiedades_impuestos pi
        INNER JOIN desarrollo.tipos_impuestos ti ON pi.id_tipo_impuesto = ti.id_tipo_impuesto
        INNER JOIN desarrollo.propiedades p ON p.id_casa = pi.id_casa
        INNER JOIN desarrollo.nomenclador n ON n.Id_Nomenclador = p.id_nomenclador
        LEFT JOIN desarrollo.impuestos i ON pi.id_casa = i.id_casa
        INNER JOIN desarrollo.contratos_cabecera cc ON cc.id_casa = p.id_casa
        INNER JOIN desarrollo.empresa e ON cc.id_empresa = e.id_empresa
        WHERE ti.id_tipo_impuesto = 3
          AND cc.comienza <= CURDATE()
          AND cc.rescicion >= CURDATE()
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
             cc.comienza,
             cc.rescicion
        ORDER BY e.id_empresa, FOLIO
    ";

        $resultado = DB::connection('mysql2')->select($sql);
        // Normalizar partida a 8 dígitos
        foreach ($resultado as $row) {
            if (isset($row->partida)) {
                $row->partida = str_replace(['-', '/'], '', $row->partida);
            }
        } 
        return $resultado;
    }
}
