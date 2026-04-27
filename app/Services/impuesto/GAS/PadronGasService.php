<?php

namespace App\Services\impuesto\GAS;


use App\Models\impuesto\Gas_padron;
use Illuminate\Support\Facades\DB;


class PadronGasService
{

    public function __construct() {}




//Esta función obtiene el padrón TGI desde la base de datos propia
    //Funcion utilizada por el servicio PadronImpuestoService
    public function obtenerPadronExistente()
    {
        return Gas_padron::orderByRaw("
        CASE
            WHEN folio LIKE '50%' AND LENGTH(folio) = 5 THEN 0
            ELSE 1
        END
    ")
            ->orderBy('empresa')
            ->orderBy('folio')
            ->get();
    }



    //Esta consulta obtiene el padrón GAS desde la base de datos externa
    //Funcion utilizada por el servicio PadronGasService
    public function consultaObtenerPadronGAS()
    {
        $sql = "
                SELECT
                        prop.carpeta as folio,
                        CONCAT(nom.Calle, ' ', prop.altura) AS calle,
                        gas.nro_cliente_gas AS partida,
                        gas.nro_persona_gas AS clave,
                        gas.a_cargo_gas as abona,
                        gas.quien_administra_gas as administra,
                        cc.id_empresa,
                        cc.comienza as comienza,
                        cc.rescicion as rescicion
                        FROM
                            desarrollo.propiedades prop
                        INNER JOIN desarrollo.nomenclador nom ON prop.id_nomenclador = nom.id_nomenclador
                        INNER JOIN desarrollo.servicios_gas gas ON prop.id_casa = gas.id_casa

                        INNER JOIN desarrollo.contratos_cabecera cc ON cc.id_contrato_cabecera = (
                            SELECT MAX(id_contrato_cabecera)
                            FROM desarrollo.contratos_cabecera
                            WHERE id_casa = prop.id_casa
                        )
                        INNER JOIN desarrollo.empresa emp ON emp.id_empresa = cc.id_empresa
                        WHERE
                            (gas.nro_cliente_gas != '' AND gas.nro_persona_gas != '') AND
                            (gas.nro_cliente_gas != '0' AND gas.nro_persona_gas != '0')

                        ORDER BY
                            cc.id_empresa, folio;

               ";

        $resultado = DB::connection('mysql2')->select($sql);

        // Array para almacenar los registros únicos por partida
        $registrosUnicos = [];

        foreach ($resultado as $row) {
            // Normalizar partida a 10 dígitos
            if (isset($row->partida)) {
                $row->partida = str_pad($row->partida, 11, '0', STR_PAD_LEFT);
            }

            // Lógica de prioridad para duplicados
            $partida = $row->partida;

            if (!isset($registrosUnicos[$partida])) {
                // Si no existe esta partida, agregar el registro
                $registrosUnicos[$partida] = $row;
            } else {
                // Si ya existe, comparar prioridades
                $registroExistente = $registrosUnicos[$partida];
                $prioridadActual = $this->getPrioridad($row->administra);
                $prioridadExistente = $this->getPrioridad($registroExistente->administra);

                // Si el nuevo registro tiene mayor prioridad, reemplazar
                if ($prioridadActual < $prioridadExistente) {
                    $registrosUnicos[$partida] = $row;
                }
            }
        }

        // Convertir el array asociativo a array indexado
        return array_values($registrosUnicos);
    }

    /**
     * Método auxiliar para obtener la prioridad del campo administra
     * L = 1 (máxima prioridad), P = 2, I = 3, otros = 4
     */
    private function getPrioridad($administra)
    {
        switch (strtoupper(trim($administra))) {
            case 'L':
                return 1;
            case 'P':
                return 2;
            case 'I':
                return 3;
            default:
                return 4;
        }
    }

    //Este metodo busca una tgi por partida
    public function buscarGasPorPartida($partida)
    {
        // Buscar en la tabla tgi_padron por el campo 'partida'
        $partida = trim($partida); // elimina espacios
        $gasPadron = Gas_padron::where('partida', $partida)->first();

        return $gasPadron; // Retorna el registro encontrado o null si no existe
    }

}
