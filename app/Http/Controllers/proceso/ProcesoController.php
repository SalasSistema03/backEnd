<?php

namespace App\Http\Controllers\proceso;


use App\Http\Controllers\Controller;
use App\Models\At_cl\Propiedad;
use App\Models\proceso\Proceso_propiedad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Services\contable\sellado\PermitirAccesoSelladoService;
use App\Models\proceso\Historial_estado_reserva;
use App\Models\usuarios_y_permisos\Usuario;

class ProcesoController extends Controller
{

    public function subirReservas(Request $request)
    {
        //Log::info('subirReservas', $request->all());  estado_alquiler_inicial
        DB::beginTransaction();
        try {
            $comprobantePath = null;
            $usuario_id = auth('api')->id();

            // Manejar comprobante (archivo PDF o JPG)
            if ($request->hasFile('comprobante')) {
                $file = $request->file('comprobante');
                $extension = strtolower($file->getClientOriginalExtension());

                if (!in_array($extension, ['pdf', 'jpg', 'jpeg'])) {
                    return response()->json(['error' => 'Formato de comprobante no válido.'], 400);
                }

                // Año a usar para la carpeta (ej: 2026). Usa la fechaReserva si viene, sino el año actual.
                $year = date('Y', strtotime($request->fechaReserva ?? now()));
                $sharedFolder = '\\\\10.10.10.152\\Compartida\\RESERVAS\\' . $year;

                if (!File::exists($sharedFolder)) {
                    File::makeDirectory($sharedFolder, 0777, true);
                }

                $fileName = 'reserva_' . ($request->idPropiedad ?? '0') . '_' . time() . '.' . $extension;
                $destinationPath = $sharedFolder . '\\' . $fileName;

                // Copiar el archivo subido a la carpeta compartida usando copy para UNC/network share
                if (!copy($file->getPathname(), $destinationPath)) {
                    throw new \RuntimeException('No se pudo copiar el comprobante a la carpeta compartida.');
                }

                // Eliminar el archivo temporal si la copia fue correcta
                @unlink($file->getPathname());

                $comprobantePath = $destinationPath;
            }

            $historial = Historial_estado_reserva::create([
                'id_estado' => 1,
                'observaciones' => 'Reserva creada',
                'quien_cargo' => $request->asesor ?? null,
                'fecha_carga' => now(),
            ]);

            $proceso = Proceso_propiedad::create([
                'asesor' => $request->asesor ?? null,
                'fecha_reserva' => $request->fechaReserva ?? null,
                'fecha_fin_reserva' => $request->fechaFinReserva ?? null,
                'id_cliente' => $request->idCliente ?? null,
                'reservante' => $request->nombreReservante ?? null,
                'id_propiedad' => $request->idPropiedad ?? null,
                'tipo_reserva' => $request->tipo ?? null,
                'moneda' => $request->moneda ?? null,
                'monto_reserva' => $request->montoReserva ?? null,
                'monto_aceptado' => 0,
                'documentacion' => $comprobantePath ?? $request->documentacion ?? null,
                'id_historial_estado_reserva' => $historial->id,
                'quien_cargo' => $usuario_id,
            ]);

            $historial->update(['id_proceso_propiedad' => $proceso->id]);

            $propiedad = Propiedad::find($request->idPropiedad);
            $estadoInicial = $propiedad ? $propiedad->id_estado_alquiler : null;

            if ($propiedad) {
                $propiedad->update([
                    'id_estado_alquiler' => 5,
                    'updated_at' => now(),
                    'last_modified_by' => $usuario_id,
                ]);
            }

            $proceso->update([
                'estado_alquiler_inicial' => $estadoInicial
            ]);

            DB::commit();

            return response()->json(['success' => true, 'path' => $comprobantePath], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error subirReservas: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al subir la reserva.'], 500);
        }
    }

    public function obtenerReservas(Request $request)
    {
        //Log::info('obtenerReservas', $request->all());
        $usuario_id = auth('api')->id();
        $accessService = new PermitirAccesoSelladoService($usuario_id);

        if ($request->estado == null) {

            // Obtener los IDs de historial que están activos en proceso_propiedad
            $idsHistorialesActivos = Proceso_propiedad::whereNotNull('id_historial_estado_reserva') // ajustá el nombre
                ->pluck('id_historial_estado_reserva');

            if ($accessService->tieneAcceso('listarAsesoresReserva')) {
                $data = Historial_estado_reserva::with([
                    'estado',
                    'proceso_propiedad.propiedad.folios',
                    'proceso_propiedad.propiedad.calle',
                    'proceso_propiedad.cliente',
                ])
                    ->whereIn('id', $idsHistorialesActivos)
                    ->orderBy('id_estado', 'asc')
                    ->get();
            } else {
                $data = Historial_estado_reserva::with([
                    'estado',
                    'proceso_propiedad.propiedad.folios',
                    'proceso_propiedad.propiedad.calle',
                    'proceso_propiedad.cliente',
                ])
                    ->whereIn('id', $idsHistorialesActivos)
                    ->whereHas('proceso_propiedad', function ($query) use ($usuario_id) {
                        $query->where('asesor', $usuario_id);
                    })
                    ->orderBy('id_estado', 'asc')
                    ->get();
            }
        } else {
            // Obtener los IDs de historial que están activos en proceso_propiedad
            $idsHistorialesActivos = Proceso_propiedad::whereNotNull('id_historial_estado_reserva') // ajustá el nombre
                ->pluck('id_historial_estado_reserva');

            if ($accessService->tieneAcceso('listarAsesoresReserva')) {
                $data = Historial_estado_reserva::with([
                    'estado',
                    'proceso_propiedad.propiedad.folios',
                    'proceso_propiedad.propiedad.calle',
                    'proceso_propiedad.cliente',
                ])
                    ->whereIn('id', $idsHistorialesActivos)
                    ->where('id_estado', $request->estado)
                    ->orderBy('id_estado', 'asc')
                    ->get();
            } else {
                $data = Historial_estado_reserva::with([
                    'estado',
                    'proceso_propiedad.propiedad.folios',
                    'proceso_propiedad.propiedad.calle',
                    'proceso_propiedad.cliente',
                ])
                    ->whereIn('id', $idsHistorialesActivos)
                    ->where('id_estado', $request->estado)
                    ->whereHas('proceso_propiedad', function ($query) use ($usuario_id) {
                        $query->where('asesor', $usuario_id);
                    })
                    ->orderBy('id_estado', 'asc')
                    ->get();
            }
        }


        return response()->json(['resultado' => $data]);
    }

    public function guardarEstado(Request $request)
    {
        //Log::info($request->all());
        //dd('hola');

        // 1. Obtener el ID del usuario autenticado vía JWT/Token
        $usuario_id = auth('api')->id();
        try {
            $data =  Historial_estado_reserva::create([
                'id_estado' => $request->estado,
                'observaciones' => $request->detalle,
                'fecha_carga' => now(),
                'quien_cargo' => $usuario_id,
                'id_proceso_propiedad' => $request->idProcesoPropiedad
            ]);
            if ($request->estado == 3) {

                $data->update(['fecha_firma' => now()]);
            }
            if ($request->estado == 4) {
                $proceso = Proceso_propiedad::find($request->idProcesoPropiedad);
                $propiedad = Propiedad::find($proceso->id_propiedad);
                $propiedad->update([
                    'id_estado_alquiler' => $proceso->estado_alquiler_inicial,
                    'updated_at' => now(),
                    'last_modified_by' => $usuario_id,
                ]);
            }
            $propiedadactualizada = Proceso_propiedad::where('id', $request->idProcesoPropiedad)
                ->update(['id_historial_estado_reserva' => $data->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error guardarEstado: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al guardar el estado.'], 500);
        }
    }

    public function getHistorial(Request $request)
    {
        //Log::info($request->all());
        $data = Historial_estado_reserva::where('id_proceso_propiedad', $request->id)
            ->with([
                'estado'
            ])
            ->orderBy('id', 'desc')
            ->get();

        foreach ($data as $item) {
            $item->quien_cargo = Usuario::find($item->quien_cargo)->username;
        }
        Log::info($data);
        return response()->json(['resultado' => $data]);
    }

    public function getReservaIdentificadas(Request $request)
    {

        $idsHistorialesActivos = Proceso_propiedad::whereNotNull('id_historial_estado_reserva')
            ->when($request->filled('data'), function ($query) use ($request) {
                $query->where('monto_aceptado', $request->data);
            })
            ->pluck('id_historial_estado_reserva');

        $data = Historial_estado_reserva::with([
            'estado',
            'proceso_propiedad.propiedad.folios',
            'proceso_propiedad.propiedad.calle',
            'proceso_propiedad.cliente',
        ])
            ->whereIn('id', $idsHistorialesActivos)
            ->orderBy('id_estado', 'asc')
            ->get();

        return response()->json(['resultado' => $data]);
        /* $response = Proceso_propiedad::where('monto_aceptado', '=', $request->data)->get();
        return response()->json(['resultado' => $response]); */
    }

    public function guardarReservaIdentificada(Request $request)
    {

        Log::info($request->all());
        $usuario_id = auth('api')->id();
        try {
            $proceso = Proceso_propiedad::find($request->id);
            $proceso->update([
                'monto_aceptado' => 1,
                'quien_modifico' => $usuario_id,
            ]);
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error guardarReservaIdentificada: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al guardar la reserva.'], 500);
        }
    }


    public function obtenerComprobante(Request $request)
    {

        try {


            $rutaCompleta = $request->input('documentacion'); // ej: \\10.10.10.152\Compartida\RESERVAS\2026\reserva_64_1782225619.jpeg

            if (!file_exists($rutaCompleta)) {
                return response()->json(['error' => 'Archivo no encontrado'], 404);
            }

            $extension = strtolower(pathinfo($rutaCompleta, PATHINFO_EXTENSION));
            $nombreArchivo = basename($rutaCompleta);

            $contentType = match ($extension) {
                'pdf'        => 'application/pdf',
                'jpg', 'jpeg' => 'image/jpeg',
                'png'        => 'image/png',
                default      => 'application/octet-stream',
            };

            return response(file_get_contents($rutaCompleta))
                ->header('Content-Type', $contentType)
                ->header('Content-Disposition', 'inline; filename="' . $nombreArchivo . '"');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error obtenerComprobante: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al obtener el comprobante.'], 500);
        }
    }
}
