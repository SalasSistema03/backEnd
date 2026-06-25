<?php

namespace App\Services\impuesto\EXP;

use App\Models\At_cl\Calle as At_clCalle;
use App\Models\impuesto\Exp_edificio;
use App\Models\impuesto\Exp_administrador_consorcio;
use Illuminate\Support\Facades\DB;


class ExpEdificiosService
{
    /**
     * Obtiene y filtra la lista de edificios, incluyendo relaciones necesarias.
     */
    public function obtenerEdificiosFiltrados(?string $search = null): array
    {
        $search = trim($search ?? '');

        // Usamos with('administrador') para solucionar el problema N+1.
        // Carga la relación de forma optimizada (Eager Loading).
        $edificios = Exp_edificio::with('administrador')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre_consorcio', 'like', "%{$search}%")
                        ->orWhere('direccion', 'like', "%{$search}%")
                        ->orWhere('altura', 'like', "%{$search}%");
                });
            })
            ->get();

        // Estos catálogos son necesarios para llenar los <select> en los modales/formularios de Vue
        $administradores = Exp_administrador_consorcio::orderBy('nombre', 'asc')->get();
        $calles = At_clCalle::all();

        return [
            'edificios'       => $edificios,
            'administradores' => $administradores,
            'calles'          => $calles,
        ];
    }

    /**
     * Crea un nuevo edificio.
     */
    public function crearEdificio(array $data): Exp_edificio
    {
        // Obtenemos el nombre de la calle a partir del ID
        $direccion = At_clCalle::find($data['calle'])->name;

        return Exp_edificio::create([
            'direccion'                  => $direccion,
            'nombre_consorcio'           => $data['nombre'],
            'id_administrador_consorcio' => $data['administrador'],
            'altura'                     => $data['altura'],
        ]);
    }

    /**
     * Actualiza un edificio existente.
     */
    public function actualizarEdificio(int $id, array $data): void
    {
        $edificio = Exp_edificio::findOrFail($id);

        $payload = [
            'nombre_consorcio' => $data['nombre'],
            'altura'           => $data['altura'],
        ];

        // Lógica original: si viene la calle, actualiza dirección y administrador
        // Nota: En tu código original guardabas el 'nombre' del administrador en la columna 'id_...'. 
        // He mantenido esa lógica, pero ten cuidado si la columna es estrictamente numérica.
        if (!empty($data['calle'])) {
            $payload['direccion'] = At_clCalle::find($data['calle'])->name;

            $admin = Exp_administrador_consorcio::find($data['administra']);
            $payload['id_administrador_consorcio'] = $admin ? $admin->nombre : null;
        } else {
            $payload['id_administrador_consorcio'] = $data['administra'];
        }

        $edificio->update($payload);
    }


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
