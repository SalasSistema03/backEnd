<?php

namespace App\Services\clientes;

use App\Models\At_cl\Calle;
use App\Models\At_cl\Propiedad;
use App\Models\At_cl\Tipo_inmueble;
use App\Models\At_cl\Usuario;
use App\Models\At_cl\Zona;
use App\Models\cliente\clientes;
use Illuminate\Container\Attributes\Log;
use Illuminate\Support\Facades\Log as FacadesLog;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class EnvioMailService
{
    /**
     * Envía un correo con criterios de venta y propiedades.
     *
     * @param array $criteriosVenta Array con criterios de venta
     * @param string|int $idCliente ID del cliente
     * @param array $propiedades Array con propiedades (opcional)
     * @return bool
     * @throws Exception
     */
    public function enviar($criteriosVenta, $idCliente, $propiedades = []): bool
    {
        $identificador = 0;
        // Validaciones básicas
        if (empty($idCliente)) {
            throw new Exception('ID Cliente es requerido');
        }

        if (!is_array($criteriosVenta)) {
            throw new Exception('Criterios de venta debe ser un array');
        }

        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host       = '10.10.10.128';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'consultas@salas.com';
            $mail->Password   = 'GALEON';
            $mail->Port       = 25;
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;

            // Remitente
            $mail->setFrom('Consultas@salas.com', 'Consulta');

            // Destinatario
            //$mail->addAddress('sistema@salas.com');

            // Preparar contenido del email
            $contenido = $this->prepararContenidoEmail($criteriosVenta, $idCliente, $propiedades, $identificador);
            // Resolver cliente antes de usarlo para buscar asesor
            $cliente = clientes::find($idCliente);
            $asesor = Usuario::find($cliente?->id_asesor_venta ?? null);
            // Destinatario (sanear y validar email)
            $emailTo = $this->sanitizeEmail($asesor?->email_interno ?? null);
            if (!$emailTo || !filter_var($emailTo, FILTER_VALIDATE_EMAIL)) {
                FacadesLog::error('Email de asesor inválido al enviar criterios', [
                    'cliente_id' => $idCliente,
                    'asesor_id' => $asesor?->id ?? null,
                    'email_bruto' => $asesor?->email_interno ?? null,
                    'email_saneado' => $emailTo,
                ]);
                throw new Exception('Destinatario inválido: email de asesor no válido');
            }
            $mail->addAddress($emailTo);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = "Consulta del Cliente " . (($cliente?->nombre) ?? 'Sin Datos');
            $mail->Body    = nl2br(htmlentities($contenido, ENT_QUOTES, 'UTF-8'));
            $mail->AltBody = "Se han registrado nuevos criterios de búsqueda para el cliente ID: " . (($cliente?->id_cliente) ?? 'Sin Datos');

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log del error antes de repropagar
            error_log('Error enviando email: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Prepara el contenido del email en formato legible
     */
    private function prepararContenidoEmail($criteriosVenta, $idCliente, $propiedades, $identificador): string
    {
        if ($identificador == 0) {
            $cliente = clientes::find($idCliente);

            $contenido = "DATOS DEL CLIENTE\n\n";
            $contenido .= "Nombre y Apellido: " . ($cliente?->nombre ?? 'Sin Datos') . "\n";
            $contenido .= "Telefono: " . ($cliente?->telefono ?? 'Sin Datos') . "\n";
            $contenido .= "Fecha de ingreso de consulta: " . date('d/m/Y H:i:s') . "\n\n\n";

            // Criterios de venta
            $contenido .= "CONSULTA\n\n";
            if (!empty($criteriosVenta)) {
                foreach ($criteriosVenta as $index => $criterio) {
                    $tipoInmueble = Tipo_inmueble::find($criterio['id_tipo_inmueble'] ?? null);
                    $zona = Zona::find($criterio['id_zona'] ?? null);

                    $contenido .= "Criterio " . ($index + 1) . ":\n";
                    $contenido .= "  - Tipo Inmueble: " . ($tipoInmueble?->inmueble ?? 'Sin Datos') . "\n";
                    $contenido .= "  - Dormitorios: " . ($criterio['cant_dormitorios'] ?? 'Sin Datos') . "\n";
                    $contenido .= "  - Cochera:" . ($criterio['cochera'] ?? 'Sin Datos') . "\n";
                    $contenido .= "  - Zona: " . ($zona?->name ?? 'Sin Datos') . "\n\n";
                    FacadesLog::info('Estos son los datos de Criterio ' . ($index + 1) . ': ' . json_encode($criterio));
                }
            } else {
                $contenido .= "No se registraron criterios de venta.\n\n";
            }

            // Propiedades
            $contenido .= "\nPROPIEDADES \n\n";
            if (!empty($propiedades) && is_array($propiedades)) {
                foreach ($propiedades as $index => $propiedad) {
                    $casa = Propiedad::find($propiedad['id_propiedad'] ?? null);
                    $calle = Calle::find($casa?->id_calle ?? null);

                    $contenido .= "Propiedad " . ($index + 1) . ":\n";
                    $contenido .= "  - Codigo Propiedad: " . ($casa?->cod_venta ?? 'Sin Datos') . "\n";
                    // Mostrar datos de calle de forma segura, sin asumir objeto
                    $contenido .= "  - Calle: " . (($calle?->name ?? 'Sin Datos') . ' ' . ($casa?->numero_calle ?? 'Sin Datos')) . "\n\n";
                    FacadesLog::info('Edyos  son los datos de Propiedad ' . ($index + 1) . ': ' . json_encode($propiedad));
                }
            } else {
                $contenido .= "No se registraron propiedades.\n\n";
            }

            /* $contenido .= "=== FIN DEL REGISTRO ==="; */

            return $contenido;
        }else{
            $cliente = clientes::find($idCliente);
            $asesor = Usuario::find($cliente?->id_asesor_venta ?? null);

            $contenido = "DATOS DEL CLIENTE\n\n";
            $contenido .= "Nombre y Apellido: " . ($cliente?->nombre ?? 'Sin Datos') . "\n";
            $contenido .= "Telefono: " . ($cliente?->telefono ?? 'Sin Datos') . "\n";
            $contenido .= "Fecha de ingreso de consulta: " . date('d/m/Y H:i:s') . "\n\n\n";
            $contenido .= "Asesor:" . ($asesor?->username ?? 'Sin Datos') . "\n\n\n";

            // Criterios de venta
            $contenido .= "CONSULTA\n\n";
            if (!empty($criteriosVenta && is_array($criteriosVenta))) {
                foreach ($criteriosVenta as $index => $criterio) {
                    $tipoInmueble = Tipo_inmueble::find($criterio['id_tipo_inmueble'] ?? null);
                    $zona = Zona::find($criterio['id_zona'] ?? null);

                    $contenido .= "Criterio " . ($index + 1) . ":\n";
                    $contenido .= "  - Tipo Inmueble: " . ($tipoInmueble?->inmueble ?? 'Sin Datos') . "\n";
                    $contenido .= "  - Dormitorios: " . ($criterio['cant_dormitorios'] ?? 'Sin Datos') . "\n";
                    $contenido .= "  - Cochera:" . ($criterio['cochera'] ?? 'Sin Datos') . "\n";
                    $contenido .= "  - Zona: " . ($zona?->name ?? 'Sin Datos') . "\n\n";
                    FacadesLog::info('Estos son los datos de Criterio ' . ($index + 1) . ': ' . json_encode($criterio));
                }
            } else {
                $contenido .= "No se registraron criterios de venta.\n\n";
            }

            // Propiedades
            $contenido .= "\nPROPIEDADES \n\n";
            if (!empty($propiedades) && is_array($propiedades)) {
                foreach ($propiedades as $index => $propiedad) {
                    $casa = Propiedad::find($propiedad['id_propiedad'] ?? null);
                    $calle = Calle::find($casa?->id_calle ?? null);

                    $contenido .= "Propiedad " . ($index + 1) . ":\n";
                    $contenido .= "  - Codigo Propiedad: " . ($casa?->cod_venta ?? 'Sin Datos') . "\n";
                    // Mostrar datos de calle de forma segura, sin asumir objeto
                    $contenido .= "  - Calle: " . (($calle?->name ?? 'Sin Datos') . ' ' . ($casa?->numero_calle ?? 'Sin Datos')) . "\n\n";
                    FacadesLog::info('Edyos  son los datos de Propiedad ' . ($index + 1) . ': ' . json_encode($propiedad));
                }
            } else {
                $contenido .= "No se registraron propiedades.\n\n";
            }

            /* $contenido .= "=== FIN DEL REGISTRO ==="; */

            return $contenido;
        }
    }


    public function enviarNuevoMail($criteriosVenta = [], $clienteId, $propiedades = []): bool
    {
        // Log seguro del payload (evitar errores de json_encode por tipos incorrectos)
        try {
            FacadesLog::info('Payload enviarNuevoMail: ' . json_encode([
                'criteriosVenta' => $criteriosVenta,
                'clienteId' => $clienteId,
                'propiedades' => $propiedades,
            ]));
        } catch (\Throwable $e) {
            FacadesLog::warning('No se pudo loguear el payload de enviarNuevoMail: ' . $e->getMessage());
        }
        $mail = new PHPMailer(true);
        $identificador = 1;

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host       = '10.10.10.128';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'consultas@salas.com';
            $mail->Password   = 'GALEON';
            $mail->Port       = 25;
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;

            // Remitente
            $mail->setFrom('consultas@salas.com', 'Consulta');

            // Destinatario
            //$mail->addAddress('sistema@salas.com');

            // Preparar contenido del email
            $contenido = $this->prepararContenidoEmail($criteriosVenta, $clienteId, $propiedades, $identificador);
            FacadesLog::info('ESTOS SON LOS DATOS DEL CLIENTE CLIENTEID ' . $clienteId);
            $datoCliente = clientes::find($clienteId);
            $asesor = Usuario::find($datoCliente?->id_asesor_venta ?? null);
            FacadesLog::info('ESTOS SON LOS DATOS DEL ASESOR ' . json_encode($asesor));
            // Destinatario (sanear y validar email)
            $emailTo = $this->sanitizeEmail($asesor?->email_interno ?? null);
            if (!$emailTo || !filter_var($emailTo, FILTER_VALIDATE_EMAIL)) {
                FacadesLog::error('Email de asesor inválido al enviar nuevo mail', [
                    'cliente_id' => $clienteId,
                    'asesor_id' => $asesor?->id ?? null,
                    'email_bruto' => $asesor?->email_interno ?? null,
                    'email_saneado' => $emailTo,
                ]);
                throw new Exception('Destinatario inválido: email de asesor no válido');
            }
            $mail->addAddress($emailTo);
            FacadesLog::info('Email destinatario validado', ['to' => $emailTo]);
            
            

            // Contenido
            $cliente = clientes::find($clienteId);
            $mail->isHTML(true);
            $mail->Subject = "Consulta del Cliente " . ($cliente?->nombre ?? 'Sin Datos');
            $mail->Body    = nl2br(htmlentities($contenido, ENT_QUOTES, 'UTF-8'));
            $mail->AltBody = "Se han registrado nuevos criterios de búsqueda para el cliente ID: " . ($cliente?->id_cliente ?? 'Sin Datos');

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log del error antes de repropagar
            error_log('Error enviando email: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sanea emails removiendo espacios invisibles (NBSP, etc.) y trim.
     * Devuelve null si el resultado queda vacío.
     */
    private function sanitizeEmail(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }
        // Reemplazar espacios de no separación comunes por espacios normales
        $clean = preg_replace('/\x{00A0}|\x{2007}|\x{202F}/u', ' ', $email);
        // Quitar espacios en blanco alrededor
        $clean = trim($clean ?? '');
        return $clean !== '' ? $clean : null;
    }

}
