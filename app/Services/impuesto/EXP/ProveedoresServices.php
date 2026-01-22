<?php

namespace App\Services\impuesto\EXP;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use App\Models\impuesto\Exp_administrador_consorcio;

class ProveedoresServices
{



    public function __construct() {}



    public function actualizarPadronProveedores()
    {
        $resultado = "SELECT DISTINCT
        padron.id_proveedor AS id,
        padron.razon_social AS nombre,
        padron.cuit,
        tipo_proveedores.descripcion_tipo_proveedor AS rubro,
        proveedores.internos AS contacto,
        proveedores.pagina_web,
        padron.altura,
        nomenclador.calle AS direccion
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
        INNER JOIN nomenclador ON nomenclador.Id_Nomenclador = padron.id_nomenclador
        LEFT JOIN contratos_cabecera cc 
            ON cc.id_casa = sub_cc.id_casa 
            AND cc.id_contrato_cabecera = sub_cc.max_contrato
        WHERE padron.razon_social LIKE '%' AND padron.es_proveedor = 'S' AND proveedores.id_tipo_proveedor = 37
        ORDER BY padron.razon_social;";
        $resultado = DB::connection('mysql2')->select($resultado);

        DB::connection('mysql9')->beginTransaction();

        // Vaciar (sin tocar estructura)
        DB::connection('mysql9')->statement('DELETE FROM exp_administrador_consorcio');

        // Insertar nuevamente
        foreach ($resultado as $row) {
            DB::connection('mysql9')->table('exp_administrador_consorcio')->insert([
                'id'         => $row->id,
                'nombre'     => $row->nombre,
                'cuit'       => $row->cuit,
                'rubro'      => $row->rubro,
                'contacto'   => $row->contacto,
                'pagina_web' => $row->pagina_web,
                'direccion'  => $row->direccion,
                'altura'     => $row->altura,
            ]);
        }

        DB::connection('mysql9')->commit();

        return $resultado;
    }

    public function filtrarAdministradores(?string $search = null): Collection
    {
        $search = trim($search ?? '');

        return Exp_administrador_consorcio::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('cuit', 'like', "%{$search}%")
                        ->orWhere('contacto', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombre', 'asc')
            ->get();
    }
}
