<?php

namespace App\Services\contable\retenciones;

use App\Models\Contable\retenciones\Base_porcentual;
use App\Models\Contable\retenciones\Comprobante_retencion;
use App\Models\Contable\retenciones\Padron_retenciones;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RetencionService
{

    //GET
    // Busca en la tabla padron_retencion por el cuil y devuelve los datos asociados a ese cuil
    public function getPadronRetencionCUILService($cuil)
    {
        //Busca por cuit_retencion en la tabla padron_retencion
        $padrones = Padron_retenciones::where('cuit_retencion', $cuil)->firstOrFail();
        return response()->json($padrones);
    }

    //GET
    public function getBasePorcentual()
    {
        return Base_porcentual::all();
    }

    // GET
    public function getVerificarComprobanteService($cuit, $fecha)
    {

        // Solo devolvemos el primer registro o null
        return Comprobante_retencion::where('cuit_retencion', $cuit)
            ->whereYear('fecha_comprobante', date('Y', strtotime($fecha)))
            ->whereMonth('fecha_comprobante', date('m', strtotime($fecha)))
            ->first();
    }

    //GET
    public function getCalculoRetencionService($monto, $cuit, $fecha)
    {
        // 1. Buscamos si el CUIT ya tiene retenciones en el mismo MES y AÑO
        $yaTieneComprobante = Comprobante_retencion::where('cuit_retencion', $cuit)
            ->whereYear('fecha_comprobante', date('Y', strtotime($fecha)))
            ->whereMonth('fecha_comprobante', date('m', strtotime($fecha)))
            ->exists(); // Devuelve true si encuentra al menos uno

        // 2. Obtenemos los parámetros (Base y Porcentaje)
        $bases_porcentuales = Base_porcentual::all();
        $valorBaseConfigurada = $bases_porcentuales[0]->dato;
        $valorPorcentual = $bases_porcentuales[1]->dato;

        // 3. LA LÓGICA DE NEGOCIO
        // Si ya tiene comprobante, la base es 0 (porque ya se usó el beneficio en la primera factura)
        // Si NO tiene, se le resta la base al monto.
        if ($yaTieneComprobante) {
            $baseAAplicar = 0;
            $reg_base = "N";
        } else {
            $baseAAplicar = $valorBaseConfigurada;
            $reg_base = "S";
        }

        // 4. EL CÁLCULO FINAL
        // (Monto total de la factura - Base) * Porcentaje
        // Usamos max(0, ...) para evitar que si el monto es menor a la base, el impuesto dé negativo
        // Convertimos explícitamente a float por si vienen como strings
        $montoSujetoARetencion = max(0, (float)$monto - (float)$baseAAplicar);
        $totalRetencion = $montoSujetoARetencion * $valorPorcentual;

        // 5. RESPUESTA PARA VUE
        return [
            'total_retencion' => round($totalRetencion, 2),
            'reg_base'        => $reg_base,
            'base_aplicada'   => $baseAAplicar // Dato extra útil para debug
        ];
    }

    //GET
    public function getTablaRetencionesService()
    {
        $fechaActual = Carbon::now();
        $anioActual = $fechaActual->year;
        $mesActual = $fechaActual->month;

        // Solo obtenemos los datos
        return Comprobante_retencion::select([
            'comprobante_retencion.cuit_retencion',
            'comprobante_retencion.fecha_comprobante',
            'comprobante_retencion.fecha_retencion',
            'comprobante_retencion.id_comprobante',
            'comprobante_retencion.calcula_base',
            'comprobante_retencion.importe_comprobante',
            'comprobante_retencion.importe_retencion',
            'comprobante_retencion.numero_comprobante',
            'comprobante_retencion.suma_comprobante',
            'padron_retencion.razon_social_retencion'
        ])
            ->leftJoin('padron_retencion', 'comprobante_retencion.cuit_retencion', '=', 'padron_retencion.cuit_retencion')
            ->where(function ($query) use ($anioActual, $mesActual) {
                $query->whereYear('comprobante_retencion.fecha_comprobante', $anioActual)
                    ->whereMonth('comprobante_retencion.fecha_comprobante', $mesActual)
                    ->orWhere('comprobante_retencion.fecha_comprobante', '0000-00-00');
            })
            ->orderBy('comprobante_retencion.id_comprobante', 'DESC')
            ->orderBy('padron_retencion.razon_social_retencion', 'ASC')
            ->get();
    }


    //GET - Esta funcion devuelve la retencion por cuit
    public function getRetencionPorCUITService(String $cuit)
    {
        return Comprobante_retencion::select([
            'comprobante_retencion.calcula_base',
            'comprobante_retencion.cuit_retencion',
            'comprobante_retencion.fecha_comprobante',
            'comprobante_retencion.fecha_retencion',
            'comprobante_retencion.id_comprobante',
            'comprobante_retencion.importe_comprobante',
            'comprobante_retencion.importe_retencion',
            'comprobante_retencion.numero_comprobante',
            'comprobante_retencion.suma_comprobante',
            'padron_retencion.razon_social_retencion'
        ])
            ->leftJoin('padron_retencion', 'comprobante_retencion.cuit_retencion', '=', 'padron_retencion.cuit_retencion')
            ->where('comprobante_retencion.cuit_retencion', $cuit)
            ->orderBy('comprobante_retencion.id_comprobante', 'DESC')
            ->orderBy('padron_retencion.razon_social_retencion', 'ASC')
            ->get();
    }


    //POST - Este metodo se encarga de guardar el registro de retención
    public function postComprobanteService($objetoRetencion)
    {
        //traemos el userId qu eesta usando el service
        $userId = session('userId');


        try {
            // 1. Guardar el registro de comprobante
            $comprobante = new Comprobante_retencion();
            $comprobante->fecha_comprobante = $objetoRetencion->fecha_comprobante;
            $comprobante->numero_comprobante = $objetoRetencion->numero_comprobante;
            $comprobante->suma_comprobante = $objetoRetencion->suma_comprobante;
            $comprobante->importe_comprobante = $objetoRetencion->importe_comprobante;
            $comprobante->cuit_retencion = $objetoRetencion->cuit_retencion;
            $comprobante->importe_retencion = $objetoRetencion->importe_retencion;
            $comprobante->calcula_base = $objetoRetencion->calcula_base;
            $comprobante->fecha_retencion = $objetoRetencion->fecha_retencion;
            $comprobante->last_modified_by = $userId; // Usamos el userId del servicio
            $comprobante->save();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }


    //POST - Este metodo guarda una nueva persona en la tabla de retenciones
    public function postPersonaRetencionService(Padron_retenciones $objetoRetencion)
    {
        $objetoRetencion->save();
        return $objetoRetencion;        
    }


    //PUT
    public function modificarBasePorcentualService($request)
    {
        try {
            // Update base
            if ($request->porcentual_dato != null && $request->base_dato != null) {
                $porcentual = Base_porcentual::where('id_base_porcentual', 2)->first();
                $porcentual->dato = $request->porcentual_dato;
                $porcentual->last_modified_by = 1;
                $porcentual->save();

                $base = Base_porcentual::where('id_base_porcentual', 1)->first();
                $base->dato = $request->base_dato;
                $base->last_modified_by = 1;
                $base->save();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }


    public function modgiciarRegistroRetencionService(int $id, array $datos)
    {
        $registros = Comprobante_retencion::findOrFail($id);

        if (!$registros) {
            return null;
        }

        $registros->update($datos);
        return $registros;
    }


    /**
     * Calcula la suma de retenciones divididas por quincena.
     */
    public function obtenerSumasMensualesService($anio, $mes)
    {
        // Carbon se encarga de manejar correctamente los límites del mes
        $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth();
        $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth();

        // Ejecutamos una sola consulta agrupada por lógica condicional (CASE WHEN)
        return Comprobante_retencion::whereBetween('fecha_comprobante', [$fechaInicio, $fechaFin])
            ->select(DB::raw("
                SUM(CASE WHEN DAY(fecha_comprobante) <= 15 THEN importe_retencion ELSE 0 END) as suma_primera,
                SUM(CASE WHEN DAY(fecha_comprobante) > 15 THEN importe_retencion ELSE 0 END) as suma_segunda
            "))
            ->first();
    }



    public function generarContenidoTxtService()
    {
        $fechaUpdate = Carbon::now()->toDateString();
        // Nota: Agregué el filtro de año y mes si fuera necesario, 
        // pero mantengo tu lógica de 'fecha_retencion' null.
        $comprobantes = Comprobante_retencion::whereNull('fecha_retencion')->get();
        
        $contenido = "";

        foreach ($comprobantes as $comprobante) {
            // Lógica de fechas
            $fechaObj = (empty($comprobante->fecha_comprobante) || $comprobante->fecha_comprobante === '0000-00-00') 
                ? now() 
                : Carbon::parse($comprobante->fecha_comprobante);

            $fecha = $fechaObj->format('d/m/Y');
            $fechaEmision = now()->format('d/m/Y'); 

            // Formateo de campos (Padding)
            $codigo = str_pad("6", 2, "0", STR_PAD_LEFT);
            $numeroComprobante = str_pad($comprobante->numero_comprobante, 12, "0", STR_PAD_LEFT);
            
            $importeComp = str_replace(".", ",", $comprobante->importe_comprobante);
            $importeComprobante = str_pad($importeComp, 20, " ", STR_PAD_LEFT);
            
            $codigo_impr = str_pad("217", 4, "0", STR_PAD_LEFT);
            $codigo_reg = str_pad("31", 3, "0", STR_PAD_LEFT);
            $codigo_opera = "1";
            $baseCalculo = str_pad($importeComp, 14, " ", STR_PAD_LEFT);
            $codigoCod = str_pad("1", 2, "0", STR_PAD_LEFT);
            $ret_suj_cond = "0";
            
            $importeRet = str_replace(".", ",", $comprobante->importe_retencion);
            $importeRetencion = str_pad($importeRet, 14, " ", STR_PAD_LEFT);
            
            $porcentajeExc = str_pad("0,00", 6, " ", STR_PAD_LEFT);
            $fechaPublicacion = str_pad(" ", 10, " ", STR_PAD_LEFT);
            $tipoDoc = str_pad("80", 2, " ", STR_PAD_LEFT);
            $documentoRetencion = str_pad($comprobante->cuit_retencion, 20, " ", STR_PAD_RIGHT);
            $numeroCero = str_pad("0", 28, "0", STR_PAD_LEFT);

            // Construcción de línea
            $linea = $codigo . $fecha . $numeroComprobante . $importeComprobante . $codigo_impr . $codigo_reg . $codigo_opera . $baseCalculo . $fechaEmision . $codigoCod . $ret_suj_cond . $importeRetencion . $porcentajeExc . $fechaPublicacion . $tipoDoc . $documentoRetencion . $numeroCero . "\r\n";
            $contenido .= $linea;

            // Actualización del registro
            $comprobante->update([
                'fecha_retencion' => $fechaUpdate,
                'last_modified_by' => Auth::id(),
            ]);
        }

        return $contenido;
    }

}
