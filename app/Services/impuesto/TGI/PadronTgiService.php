<?php

namespace App\Services\impuesto\TGI;


use App\Models\impuesto\Tgi_padron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PadronTgiService
{



    public function __construct() {}



    public function obtenerPadronTGI()
    {

        $padronExistente = Tgi_padron::all(); // registros actuales en la tabla
        $nuevoPadron = $this->consultaObtenerPadronTGI(); // registros nuevos desde la consulta externa

        // Convertir a colecciones con claves únicas
        $existente = collect($padronExistente)->mapWithKeys(function ($item) {
            return [$item->folio . '-' . $item->partida => $item];
        });

        $nuevo = collect($nuevoPadron)->mapWithKeys(function ($item) {
            return [$item->folio . '-' . $item->partida => $item];
        });

        // 🔍 1. Detectar nuevos registros
        $nuevosRegistros = $nuevo->diffKeys($existente);



        // 🔁 0. Reactivar registros que existen pero están INACTIVOS
        $reactivar = $nuevo->filter(function ($registro, $key) use ($existente) {
            return isset($existente[$key]) && $existente[$key]->estado === 'INACTIVO';
        });

        foreach ($reactivar as $registro) {
            Tgi_padron::where('folio', $registro->folio)
                ->where('partida', $registro->partida)
                ->update([
                    'estado' => 'ACTIVO',
                    'calle' => !empty($registro->calle)
                        ? mb_convert_encoding($registro->calle, 'UTF-8', 'UTF-8')
                        : '',
                    'clave' => is_numeric($registro->clave) ? $registro->clave : null,
                    'abona' => $registro->abona ?? '',
                    'administra' => $registro->administra ?? '',
                    'empresa' => $registro->empresa ?? 0,
                    'comienza' => $registro->comienza ?? 0,
                    'rescicion' => $registro->rescicion ?? 0,
                ]);
        }


        foreach ($nuevo as $key => $registro) {
            $registroExistente = $existente[$key] ?? null;
            if ($registroExistente) {
                Tgi_padron::where('folio', $registro->folio)->where('partida', $registro->partida)->update([
                    'estado' => 'ACTIVO',
                    'calle' => !empty($registro->calle) ? mb_convert_encoding($registro->calle, 'UTF-8', 'UTF-8') : '',
                    'clave' => is_numeric($registro->clave) ? $registro->clave : null,
                    'abona' => $registro->abona ?? '',
                    'administra' => $registro->administra ?? '',
                    'empresa' => $registro->empresa ?? 0,
                    'comienza' => $registro->comienza ?? 0,
                    'rescicion' => $registro->rescicion ?? 0,
                ]);
            } else {
                Tgi_padron::create(
                    [
                        'folio' => is_numeric($registro->folio) ? $registro->folio : null,
                        'calle' => !empty($registro->calle) ? mb_convert_encoding($registro->calle, 'UTF-8', 'UTF-8') : '',
                        'partida' => $registro->partida ?? '',
                        'clave' => is_numeric($registro->clave) ? $registro->clave : null,
                        'abona' => $registro->abona ?? '',
                        'administra' => $registro->administra ?? '',
                        'empresa' => $registro->empresa ?? 0,
                        'estado' => 'ACTIVO',
                        'comienza' => $registro->comienza ?? 0,
                        'rescicion' => $registro->rescicion ?? 0,
                    ]
                );
            }
        }


        // 🔄 2. Detectar registros que ya no están y marcar como INACTIVO
        $registrosInactivos = $existente->diffKeys($nuevo);

        foreach ($registrosInactivos as $registro) {
            Tgi_padron::where('folio', $registro->folio)
                ->where('partida', $registro->partida)
                ->update(['estado' => 'INACTIVO']);
        }

        return [
            'nuevos' => $nuevosRegistros->values(),
            'inactivos' => $registrosInactivos->values(),
        ];
    }


    //Este metodo busca una tgi por partida
    public function buscarTgiPorPartida($partida)
    {
        // Buscar en la tabla tgi_padron por el campo 'partida'
        $$partida = trim($partida); // elimina espacios
        $tgiPadron = Tgi_padron::where('partida', $partida)->first();

        return $tgiPadron; // Retorna el registro encontrado o null si no existe
    }


    //Esta función obtiene el padrón TGI desde la base de datos propia
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

    //Este metodo obtiene el registro de la tabla tgi_padron filtrado por folio y empresa
    public function obtenerRegistroPadronManual($folio, $empresa)
    {
        return Tgi_padron::where('folio', $folio)
            ->where('empresa', $empresa)
            ->get();
    }


    //Esta consulta obtiene el padrón TGI desde la base de datos externa
    private function consultaObtenerPadronTGI()
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
        WHERE ti.id_tipo_impuesto = 1
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
                $row->partida = str_pad($row->partida, 8, '0', STR_PAD_LEFT);
            }
        }
        return $resultado;
    }
}
