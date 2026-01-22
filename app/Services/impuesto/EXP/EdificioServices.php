<?php

namespace App\Services\impuesto\EXP;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EdificioServices
{



    public function __construct() {}

    public function consultaBuscarFolio()
    {
        $sql = "SELECT 
    padron.id_proveedor AS id,
   /* padron.razon_social AS nombre,*/
    /*padron.cuit,*/
    se.nombre_consorcio
    /*tipo_proveedores.descripcion_tipo_proveedor AS rubro,
    proveedores.internos AS contacto,
    proveedores.pagina_web,
    padron.altura,*/
    /*se.id_casa,
    propiedades.carpeta,
    cc.comienza,
    cc.rescicion,
    nomenclador.Calle,
    propiedades.altura,
    propiedades.piso_depto,
    se.a_cargo_expensas,
    se.quien_administra_expensas*/
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
ORDER BY padron.razon_social;
";

        $resultado = DB::connection('mysql2')->select($sql);

        DB::connection('mysql9')->beginTransaction();

        // Vaciar (sin tocar estructura)
        DB::connection('mysql9')->statement('DELETE FROM exp_edificios');

        // Insertar nuevamente
        foreach ($resultado as $row) {
            DB::connection('mysql9')->table('exp_edificios')->insert([
                'direccion' => null,
                'nombre_consorcio' => $row->nombre_consorcio,
                'id_administrador_consorcio' => $row->id,
            ]);
        }
        DB::connection('mysql9')->commit();
        return $resultado;
    }
}
