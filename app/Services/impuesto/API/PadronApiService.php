<?php

namespace App\Services\impuesto\API;


use App\Models\impuesto\Api_padron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PadronApiService
{



    public function __construct() {}



    public function obtenerPadronAPI()
    {
        $padronExistente = Api_padron::all(); // registros actuales en la tabla
        $nuevoPadron = $this->consultaObtenerPadronAPI(); // registros nuevos desde la consulta externa

        // Función helper para normalizar la clave
        $normalizarClave = function ($folio, $partida) {
            // Remover todos los caracteres no numéricos de la partida
            $partidaLimpia = preg_replace('/[^0-9]/', '', $partida);
            return $folio . '-' . $partidaLimpia;
        };

        // Convertir a colecciones con claves normalizadas
        $existente = collect($padronExistente)->mapWithKeys(function ($item) use ($normalizarClave) {
            return [$normalizarClave($item->folio, $item->partida) => $item];
        });

        $nuevo = collect($nuevoPadron)->mapWithKeys(function ($item) use ($normalizarClave) {
            return [$normalizarClave($item->folio, $item->partida) => $item];
        });

        // 🔍 1. Detectar nuevos registros
        $nuevosRegistros = $nuevo->diffKeys($existente);



        // 🔁 0. Reactivar registros que existen pero están INACTIVOS
        $reactivar = $nuevo->filter(function ($registro, $key) use ($existente) {
            return isset($existente[$key]) && $existente[$key]->estado === 'INACTIVO';
        });

        foreach ($reactivar as $registro) {
            Api_padron::where('folio', $registro->folio)
                ->where('partida', $registro->partida)
                ->update([
                    'estado' => 'ACTIVO',
                    'calle' => !empty($registro->calle)
                        ? mb_convert_encoding($registro->calle, 'UTF-8', 'UTF-8')
                        : '',
                    'abona' => $registro->abona ?? '',
                    'administra' => $registro->administra ?? '',
                    'empresa' => $registro->empresa ?? 0,
                    'comienza' => $registro->comienza ?? 0,
                    'rescicion' => $registro->rescicion ?? 0,
                ]);
        }


        foreach ($nuevosRegistros as $registro) {
            $partidaLimpia = preg_replace('/[^A-Za-z0-9]/', '', $registro->partida);
            $registroExistente = Api_padron::where('folio', $registro->folio)
                ->where('partida', $partidaLimpia)
                ->first();


            if ($registroExistente && $registroExistente->estado === 'INACTIVO') {
                // Reactivar el registro

                $registroExistente->update([
                    'estado' => 'ACTIVO',
                    'calle' => !empty($registro->calle)
                        ? mb_convert_encoding($registro->calle, 'UTF-8', 'UTF-8')
                        : '',
                    'abona' => $registro->abona ?? '',
                    'administra' => $registro->administra ?? '',
                    'empresa' => $registro->empresa ?? 0,
                    'comienza' => $registro->comienza ?? 0,
                    'rescicion' => $registro->rescicion ?? 0,
                ]);
            } else {
                // Crear nuevo registro
                Api_padron::create([
                    'folio' => is_numeric($registro->folio) ? $registro->folio : null,
                    'calle' => !empty($registro->calle)
                        ? mb_convert_encoding($registro->calle, 'UTF-8', 'UTF-8')
                        : '',
                    'partida' => $partidaLimpia ?? '',
                    'abona' => $registro->abona ?? '',
                    'administra' => $registro->administra ?? '',
                    'empresa' => $registro->empresa ?? 0,
                    'estado' => 'ACTIVO',
                    'comienza' => $registro->comienza ?? 0,
                    'rescicion' => $registro->rescicion ?? 0,
                ]);
            }
        }


        // 🔄 2. Detectar registros que ya no están y marcar como INACTIVO
        $registrosInactivos = $existente->diffKeys($nuevo);

        foreach ($registrosInactivos as $registro) {
            Api_padron::where('folio', $registro->folio)
                ->where('partida', $registro->partida)
                ->update(['estado' => 'INACTIVO']);
        }

        return [
            'nuevos' => $nuevosRegistros->values(),
            'inactivos' => $registrosInactivos->values(),
        ];
    }


    //Este metodo busca una apii por partida
    public function buscarApiPorPartida($partida)
    {
        // *Buscar en la tabla tgi_padron por el campo 'partida'
        $partida = trim($partida); // elimina espacios

        $apiPadron = Api_padron::where('partida', $partida)->first();
        /* dd($apiPadron); */
        return $apiPadron; // Retorna el registro encontrado o null si no existe
    }


    //Esta función obtiene el padrón Api desde la base de datos propia
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

    //Este metodo obtiene el registro de la tabla api_padron filtrado por folio y empresa
    public function obtenerRegistroPadronManual($folio, $empresa)
    {

        return Api_padron::where('folio', $folio)
            ->where('empresa', $empresa)
            ->first();
    }


    //Esta consulta obtiene el padrón Api desde la base de datos externa
    private function consultaObtenerPadronAPI()
    {
         $sql = "
        SELECT 
            p.carpeta AS folio,
            CONCAT(n.Calle, ' ', p.altura) AS calle,
            pi.partida,
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
            pi.quien_abona,
            pi.quien_administra,
            e.id_empresa,
            ti.impuesto,
             cc.comienza,
             cc.rescicion
        ORDER BY e.id_empresa, FOLIO
    "; 

    /* $sql = "SELECT 
    p.carpeta AS folio,
    CONCAT(n.Calle, ' ', p.altura) AS calle,
    pi.partida,
    pi.quien_abona AS abona,
    pi.quien_administra AS administra,
    e.id_empresa AS empresa,
    ti.impuesto,
    cc.comienza,
    cc.rescicion
FROM desarrollo.propiedades_impuestos pi
INNER JOIN desarrollo.tipos_impuestos ti ON pi.id_tipo_impuesto = ti.id_tipo_impuesto
INNER JOIN desarrollo.propiedades p ON p.id_casa = pi.id_casa
INNER JOIN desarrollo.nomenclador n ON n.Id_Nomenclador = p.id_nomenclador
INNER JOIN desarrollo.contratos_cabecera cc ON cc.id_casa = p.id_casa
INNER JOIN desarrollo.empresa e ON cc.id_empresa = e.id_empresa
INNER JOIN (
    SELECT 
        pi.partida,
        MAX(cc2.rescicion) AS max_rescicion
    FROM desarrollo.propiedades_impuestos pi
    INNER JOIN desarrollo.propiedades p2 ON p2.id_casa = pi.id_casa
    INNER JOIN desarrollo.contratos_cabecera cc2 ON cc2.id_casa = p2.id_casa
    WHERE pi.id_tipo_impuesto = 3
    GROUP BY pi.partida
) mx ON mx.partida = pi.partida AND cc.rescicion = mx.max_rescicion
WHERE ti.id_tipo_impuesto = 3
AND cc.rescicion != '0000-00-00'
ORDER BY e.id_empresa, folio"; */

        $resultado = DB::connection('mysql2')->select($sql);
        return $resultado;
       /*  AND cc.comienza <= CURDATE()
          AND cc.rescicion >= CURDATE() */
    }
}
