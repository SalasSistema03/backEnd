<?php

namespace App\Services\impuesto\EXP;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnidadesServices
{



    public function __construct() {}



    public function PadronUnidadesSyS()
    {
    $consulta = "SELECT 
    propiedades.carpeta,
    propiedades.id_casa,
    cc.comienza,
    cc.rescicion,
    cc.id_estado_contrato,
    cc.id_empresa,
    nomenclador.Calle,
    propiedades.altura,
    propiedades.piso_depto,
    se.a_cargo_expensas,
    se.quien_administra_expensas
FROM padron
INNER JOIN proveedores 
    ON proveedores.id_proveedor = padron.id_proveedor
INNER JOIN tipo_proveedores 
    ON tipo_proveedores.id_tipo_proveedor = proveedores.id_tipo_proveedor
INNER JOIN (
    SELECT id_casa, MAX(id_servicios_expensas) AS max_id
    FROM servicios_expensas
    GROUP BY id_casa
) AS sub_se 
    ON TRUE
INNER JOIN servicios_expensas se 
    ON se.id_casa = sub_se.id_casa 
    AND se.id_servicios_expensas = sub_se.max_id 
    AND se.id_proveedor = proveedores.id_proveedor
INNER JOIN propiedades 
    ON propiedades.id_casa = se.id_casa
LEFT JOIN (
    SELECT id_casa, MAX(id_contrato_cabecera) AS max_contrato
    FROM contratos_cabecera
    GROUP BY id_casa
) AS sub_cc 
    ON sub_cc.id_casa = se.id_casa
    INNER JOIN nomenclador ON nomenclador.Id_Nomenclador = propiedades.id_nomenclador
LEFT JOIN contratos_cabecera cc 
    ON cc.id_casa = sub_cc.id_casa 
    AND cc.id_contrato_cabecera = sub_cc.max_contrato
WHERE padron.razon_social LIKE '%'
  AND padron.es_proveedor = 'S'
  AND proveedores.id_tipo_proveedor = 37
  /*AND se.quien_administra_expensas = 'L'*/
ORDER BY  propiedades.carpeta;";
        $resultado = DB::connection('mysql2')->select($consulta);


        DB::connection('mysql9')->beginTransaction();
        $estado = 'Activo';
        DB::connection('mysql9')->statement('DELETE FROM exp_unidades_sys');
        foreach ($resultado as $row) {
            if($row->id_estado_contrato == 2 || $row->id_estado_contrato == 3 || $row->id_estado_contrato == 4){
                $estado = 'Inactivo';
            }else{
                $estado = 'Activo';
            }
            
            DB::connection('mysql9')->table('exp_unidades_sys')->insert([
                'folio' => $row->carpeta,
                'casa' => $row->id_casa,
                'comienza' => $row->comienza,
                'vencimiento' => $row->rescicion,
                'ubicacion' => $row->Calle . ' ' . $row->altura . ' - ' . $row->piso_depto,
                'comision' => $row->a_cargo_expensas,
                'administra' => $row->quien_administra_expensas,
                'estado' => $estado,
                'id_empresa' => $row->id_empresa,
                
            ]);
        }

        DB::connection('mysql9')->commit();
    }

}
