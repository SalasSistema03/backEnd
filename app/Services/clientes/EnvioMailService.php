<?php

namespace App\Services\clientes;

use App\Models\At_cl\Calle;
use App\Models\At_cl\Propiedad;
use App\Models\At_cl\Tipo_inmueble;
use App\Models\usuarios_y_permisos\Usuario;
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
    private function prepararContenidoEmail($criteriosVenta, $idCliente, $propiedades, $identificador, $sector = 'venta'): string
    {
        if ($identificador == 0) {
            $cliente = clientes::find($idCliente);

            $contenido = "DATOS DEL CLIENTE\n\n";
            $contenido .= "Nombre y Apellido: " . ($cliente?->nombre ?? 'Sin Datos') . "\n";
            $contenido .= "Telefono: " . ($cliente?->telefono ?? 'Sin Datos') . "\n";
            $contenido .= "Fecha de ingreso de consulta: " . date('d/m/Y H:i:s') . "\n";
            if (!empty($cliente?->observaciones)) {
                $contenido .= "Observaciones: " . $cliente->observaciones . "\n";
            }
            if (!empty($cliente?->observaciones_alq)) {
                $contenido .= "Observaciones Alquiler: " . $cliente->observaciones_alq . "\n";
            }
            $contenido .= "\n";

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

                    $codigo = $sector === 'alquiler' ? ($casa?->cod_alquiler ?? 'Sin Datos') : ($casa?->cod_venta ?? 'Sin Datos');
                    $contenido .= "Propiedad " . ($index + 1) . ":\n";
                    $contenido .= "  - Codigo Propiedad: " . $codigo . "\n";
                    // Mostrar datos de calle de forma segura, sin asumir objeto
                    $contenido .= "  - Calle: " . (($calle?->name ?? 'Sin Datos') . ' ' . ($casa?->numero_calle ?? 'Sin Datos')) . "\n\n";
                    FacadesLog::info('Edyos  son los datos de Propiedad ' . ($index + 1) . ': ' . json_encode($propiedad));
                }
            } else {
                $contenido .= "No se registraron propiedades.\n\n";
            }

            /* $contenido .= "=== FIN DEL REGISTRO ==="; */

            return $contenido;
        } else {
            $cliente = clientes::find($idCliente);
            $idAsesor = $sector === 'alquiler' ? $cliente?->id_asesor_alquiler : $cliente?->id_asesor_venta;
            $asesor = Usuario::find($idAsesor ?? null);

            $contenido = "DATOS DEL CLIENTE\n\n";
            $contenido .= "Nombre y Apellido: " . ($cliente?->nombre ?? 'Sin Datos') . "\n";
            $contenido .= "Telefono: " . ($cliente?->telefono ?? 'Sin Datos') . "\n";
            $contenido .= "Fecha de ingreso de consulta: " . date('d/m/Y H:i:s') . "\n";
            if (!empty($cliente?->observaciones)) {
                $contenido .= "Observaciones: " . $cliente->observaciones . "\n";
            }
            if (!empty($cliente?->observaciones_alq)) {
                $contenido .= "Observaciones Alquiler: " . $cliente->observaciones_alq . "\n";
            }
            $contenido .= "\nAsesor:" . ($asesor?->username ?? 'Sin Datos') . "\n\n";

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

                    $codigo = $sector === 'alquiler' ? ($casa?->cod_alquiler ?? 'Sin Datos') : ($casa?->cod_venta ?? 'Sin Datos');
                    $contenido .= "Propiedad " . ($index + 1) . ":\n";
                    $contenido .= "  - Codigo Propiedad: " . $codigo . "\n";
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


    /* public function enviarNuevoMail($criteriosVenta = [], $clienteId, $propiedades = [], $sector = 'venta'): bool
    {
        // Log seguro del payload
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
            // Configuración SMTP
            $mail->isSMTP();
            $mail->Host       = '10.10.10.128';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'consultas@salas.com';
            $mail->Password   = 'GALEON';
            $mail->Port       = 25;
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
            $mail->setFrom('consultas@salas.com', 'Consulta');

            // Preparar contenido del email
            $contenido = $this->prepararContenidoEmail($criteriosVenta, $clienteId, $propiedades, $identificador, $sector);
            $datoCliente = clientes::find($clienteId);
            $idAsesor = $sector === 'alquiler' ? $datoCliente?->id_asesor_alquiler : $datoCliente?->id_asesor_venta;
            $asesor = Usuario::find($idAsesor ?? null); */

    // Validar email del asesor
    /* if($idAsesor === 4){
                $mail->isSMTP();
                $mail->Host       = 'mail.salasinmobiliaria.com.ar';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'ventas@salasinmobiliaria.com.ar';   // ← ¡esto está mal!
                $mail->Password   = 'lascasas99';                // ← ¡esto está mal!
                //$mail->Port       = 25;
                $mail->Port       = 587;
                //$mail->SMTPSecure = false;
                $mail->SMTPSecure = 'tls';
                $mail->SMTPAutoTLS = false;
                $mail->setFrom('consultas@salas.com', 'Consulta');
                $emailTo = $this->sanitizeEmail($asesor?->email_externo ?? null);
            }else{  */

    /*  $emailTo = $this->sanitizeEmail($asesor?->email_interno ?? null); */
    /*  }  */

    /* if (!$emailTo || !filter_var($emailTo, FILTER_VALIDATE_EMAIL)) {
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

            // Enviar email
            $cliente = clientes::find($clienteId);
            $mail->isHTML(true);
            $mail->Subject = "Consulta del Cliente " . ($cliente?->nombre ?? 'Sin Datos');
            $mail->Body    = nl2br(htmlentities($contenido, ENT_QUOTES, 'UTF-8'));
            $mail->AltBody = "Se han registrado nuevos criterios de búsqueda para el cliente ID: " . ($cliente?->id_cliente ?? 'Sin Datos');
            $mail->send();

            // ============================================================
            // ENVÍO DE SMS (SOLO PARA ALQUILER)
            // ============================================================
            if ($sector === 'alquiler') {
                $telefonoSms = $asesor?->telf_laboral;
                if (!empty($telefonoSms)) {
                    // 1. Limpiar número: solo dígitos (sacamos todo lo que no sea número)
                    $telefonoSms = preg_replace('/[^0-9]/', '', $telefonoSms);

                    // 2. Agregar prefijo +54
                    $telefonoSms = '+54' . $telefonoSms;

                    // 3. Construir mensaje en UNA SOLA LÍNEA (espacios, sin \n)
                    $partes = [];
                    $partes[] = "Cliente: " . ($cliente?->nombre ?? '');
                    $partes[] = "Ingreso: " . date('d/m/Y H:i');
                    $partes[] = "Tel: " . ($cliente?->telefono ?? '');

                    $obs = '';
                    if (!empty($cliente?->observaciones_alq)) {
                        $obs = $cliente->observaciones_alq;
                    } elseif (!empty($cliente?->observaciones)) {
                        $obs = $cliente->observaciones;
                    }
                    if (!empty($obs)) {
                        $partes[] = "Observaciones: " . $obs;
                    }

                    if (!empty($criteriosVenta)) {
                        $criterio = $criteriosVenta[0];
                        $tipoInmueble = Tipo_inmueble::find($criterio['id_tipo_inmueble'] ?? null);
                        $zona = Zona::find($criterio['id_zona'] ?? null);
                        $tipo = $tipoInmueble?->inmueble ?? '';
                        $dorm = $criterio['cant_dormitorios'] ?? '0';
                        $cochera = $criterio['cochera'] ?? 'NO';
                        $zonaNombre = $zona?->name ?? '';
                        $partes[] = "Inmueble: $tipo  Dorm: $dorm";
                        $partes[] = "Cochera: $cochera  Zona: $zonaNombre";
                    }

                    if (!empty($propiedades) && is_array($propiedades)) {
                        $prop = $propiedades[0];
                        $casa = Propiedad::find($prop['id_propiedad'] ?? null);
                        $calle = Calle::find($casa?->id_calle ?? null);
                        $codigo = $sector === 'alquiler' ? ($casa?->cod_alquiler ?? '') : ($casa?->cod_venta ?? '');
                        $direccion = ($calle?->name ?? '') . ' ' . ($casa?->numero_calle ?? '');
                        $partes[] = "Propiedad: $codigo - $direccion";
                    }

                    // UNIR CON ESPACIOS (exactamente como Python)
                    $contenidoSms = implode(' ', $partes);

                    // 4. Eliminar acentos y caracteres especiales
                    $unwanted_array = array(
                        'Š' => 'S',
                        'š' => 's',
                        'Ž' => 'Z',
                        'ž' => 'z',
                        'À' => 'A',
                        'Á' => 'A',
                        'Â' => 'A',
                        'Ã' => 'A',
                        'Ä' => 'A',
                        'Å' => 'A',
                        'Æ' => 'A',
                        'Ç' => 'C',
                        'È' => 'E',
                        'É' => 'E',
                        'Ê' => 'E',
                        'Ë' => 'E',
                        'Ì' => 'I',
                        'Í' => 'I',
                        'Î' => 'I',
                        'Ï' => 'I',
                        'Ñ' => 'N',
                        'Ò' => 'O',
                        'Ó' => 'O',
                        'Ô' => 'O',
                        'Õ' => 'O',
                        'Ö' => 'O',
                        'Ø' => 'O',
                        'Ù' => 'U',
                        'Ú' => 'U',
                        'Û' => 'U',
                        'Ü' => 'U',
                        'Ý' => 'Y',
                        'Þ' => 'B',
                        'ß' => 'Ss',
                        'à' => 'a',
                        'á' => 'a',
                        'â' => 'a',
                        'ã' => 'a',
                        'ä' => 'a',
                        'å' => 'a',
                        'æ' => 'a',
                        'ç' => 'c',
                        'è' => 'e',
                        'é' => 'e',
                        'ê' => 'e',
                        'ë' => 'e',
                        'ì' => 'i',
                        'í' => 'i',
                        'î' => 'i',
                        'ï' => 'i',
                        'ð' => 'o',
                        'ñ' => 'n',
                        'ò' => 'o',
                        'ó' => 'o',
                        'ô' => 'o',
                        'õ' => 'o',
                        'ö' => 'o',
                        'ø' => 'o',
                        'ù' => 'u',
                        'ú' => 'u',
                        'û' => 'u',
                        'ý' => 'y',
                        'þ' => 'b',
                        'ÿ' => 'y',
                        'º' => ''
                    );
                    $contenidoSms = strtr($contenidoSms, $unwanted_array);

                    // 5. Normalizar espacios (eliminar saltos de línea, tabs, múltiples espacios)
                    $contenidoSms = preg_replace('/\s+/', ' ', $contenidoSms);
                    $contenidoSms = trim($contenidoSms);

                    FacadesLog::info("📱 Enviando SMS a $telefonoSms: " . $contenidoSms);

                    // 6. UNA SOLA PETICIÓN (sin duplicados)
                    try {
                        $response = \Illuminate\Support\Facades\Http::withBasicAuth('admin', 'admin')
                            ->timeout(15)
                            ->asForm()
                            ->post('http://10.10.10.252/goform/WIAMsgSend', [
                                'CurrentPort' => '255',
                                'Addressee'   => $telefonoSms,
                                'MsgInfo'     => $contenidoSms
                            ]);

                        $body = $response->body();
                        if ($response->successful() && strpos($body, 'Send failed!') === false) {
                            FacadesLog::info("✅ SMS enviado a $telefonoSms. Resp: " . $body);
                        } else {
                            FacadesLog::error("❌ Fallo SMS a $telefonoSms. Status: " . $response->status() . " Resp: " . $body);
                        }
                    } catch (\Exception $e) {
                        FacadesLog::error('⚠️ Error de conexión SMS a ' . $telefonoSms . ': ' . $e->getMessage());
                    }
                } else {
                    FacadesLog::warning('No se pudo enviar SMS: el asesor no tiene telf_laboral.');
                }
            }

            return true;
        } catch (Exception $e) {
            error_log('Error enviando email: ' . $e->getMessage());
            throw $e;
        }
    } */



    public function enviarNuevoMail($criteriosVenta = [], $clienteId, $propiedades = [], $sector = 'venta'): bool
    {
        // Log seguro del payload
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
            $mail->isSMTP();

            // Obtener datos del cliente y asesor
            $datoCliente = clientes::find($clienteId);
            $idAsesor = $sector === 'alquiler' ? $datoCliente?->id_asesor_alquiler : $datoCliente?->id_asesor_venta;
            $asesor = Usuario::find($idAsesor ?? null);

            // Configuración SMTP según el asesor
            if ($idAsesor === 4) {
                // Servidor externo
                $mail->Host       = 'mail.salasinmobiliaria.com.ar';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'ventas@salasinmobiliaria.com.ar';
                $mail->Password   = 'lascasas99';
                $mail->Port       = 587;
                $mail->SMTPSecure = 'tls';
                $mail->SMTPAutoTLS = false;
                $mail->setFrom('ventas@salasinmobiliaria.com.ar', 'Ventas Salas');
                $emailTo = $this->sanitizeEmail($asesor?->email_externo ?? null);

    // Desactivar verificación del certificado SSL
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
            } else {
                // Servidor interno (por defecto)
                $mail->Host       = '10.10.10.128';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'consultas@salas.com';
                $mail->Password   = 'GALEON';
                $mail->Port       = 25;
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
                $mail->setFrom('consultas@salas.com', 'Consulta');

                $emailTo = $this->sanitizeEmail($asesor?->email_interno ?? null);
            }

            // Validar email del asesor
            if (!$emailTo || !filter_var($emailTo, FILTER_VALIDATE_EMAIL)) {
                FacadesLog::error('Email de asesor inválido al enviar nuevo mail', [
                    'cliente_id' => $clienteId,
                    'asesor_id' => $asesor?->id ?? null,
                    'email_bruto' => $asesor?->email_interno ?? $asesor?->email_externo ?? null,
                    'email_saneado' => $emailTo,
                ]);
                throw new Exception('Destinatario inválido: email de asesor no válido');
            }
            $mail->addAddress($emailTo);
            FacadesLog::info('Email destinatario validado', ['to' => $emailTo]);

            // Preparar contenido del email
            $contenido = $this->prepararContenidoEmail($criteriosVenta, $clienteId, $propiedades, $identificador, $sector);

            // Enviar email
            $cliente = clientes::find($clienteId);
            $mail->isHTML(true);
            $mail->Subject = "Consulta del Cliente " . ($cliente?->nombre ?? 'Sin Datos');
            $mail->Body    = nl2br(htmlentities($contenido, ENT_QUOTES, 'UTF-8'));
            $mail->AltBody = "Se han registrado nuevos criterios de búsqueda para el cliente ID: " . ($cliente?->id_cliente ?? 'Sin Datos');
            $mail->send();

            // ============================================================
            // ENVÍO DE SMS (SOLO PARA ALQUILER)
            // ============================================================
            if ($sector === 'alquiler') {
                $telefonoSms = $asesor?->telf_laboral;
                if (!empty($telefonoSms)) {
                    // 1. Limpiar número: solo dígitos
                    $telefonoSms = preg_replace('/[^0-9]/', '', $telefonoSms);

                    // 2. Agregar prefijo +54
                    $telefonoSms = '+54' . $telefonoSms;

                    // 3. Construir mensaje en UNA SOLA LÍNEA
                    $partes = [];
                    $partes[] = "Cliente: " . ($cliente?->nombre ?? '');
                    $partes[] = "Ingreso: " . date('d/m/Y H:i');
                    $partes[] = "Tel: " . ($cliente?->telefono ?? '');

                    $obs = '';
                    if (!empty($cliente?->observaciones_alq)) {
                        $obs = $cliente->observaciones_alq;
                    } elseif (!empty($cliente?->observaciones)) {
                        $obs = $cliente->observaciones;
                    }
                    if (!empty($obs)) {
                        $partes[] = "Observaciones: " . $obs;
                    }

                    if (!empty($criteriosVenta)) {
                        $criterio = $criteriosVenta[0];
                        $tipoInmueble = Tipo_inmueble::find($criterio['id_tipo_inmueble'] ?? null);
                        $zona = Zona::find($criterio['id_zona'] ?? null);
                        $tipo = $tipoInmueble?->inmueble ?? '';
                        $dorm = $criterio['cant_dormitorios'] ?? '0';
                        $cochera = $criterio['cochera'] ?? 'NO';
                        $zonaNombre = $zona?->name ?? '';
                        $partes[] = "Inmueble: $tipo  Dorm: $dorm";
                        $partes[] = "Cochera: $cochera  Zona: $zonaNombre";
                    }

                    if (!empty($propiedades) && is_array($propiedades)) {
                        $prop = $propiedades[0];
                        $casa = Propiedad::find($prop['id_propiedad'] ?? null);
                        $calle = Calle::find($casa?->id_calle ?? null);
                        $codigo = $sector === 'alquiler' ? ($casa?->cod_alquiler ?? '') : ($casa?->cod_venta ?? '');
                        $direccion = ($calle?->name ?? '') . ' ' . ($casa?->numero_calle ?? '');
                        $partes[] = "Propiedad: $codigo - $direccion";
                    }

                    // Unir con espacios
                    $contenidoSms = implode(' ', $partes);

                    // 4. Eliminar acentos y caracteres especiales
                    $unwanted_array = array(
                        'Š' => 'S',
                        'š' => 's',
                        'Ž' => 'Z',
                        'ž' => 'z',
                        'À' => 'A',
                        'Á' => 'A',
                        'Â' => 'A',
                        'Ã' => 'A',
                        'Ä' => 'A',
                        'Å' => 'A',
                        'Æ' => 'A',
                        'Ç' => 'C',
                        'È' => 'E',
                        'É' => 'E',
                        'Ê' => 'E',
                        'Ë' => 'E',
                        'Ì' => 'I',
                        'Í' => 'I',
                        'Î' => 'I',
                        'Ï' => 'I',
                        'Ñ' => 'N',
                        'Ò' => 'O',
                        'Ó' => 'O',
                        'Ô' => 'O',
                        'Õ' => 'O',
                        'Ö' => 'O',
                        'Ø' => 'O',
                        'Ù' => 'U',
                        'Ú' => 'U',
                        'Û' => 'U',
                        'Ü' => 'U',
                        'Ý' => 'Y',
                        'Þ' => 'B',
                        'ß' => 'Ss',
                        'à' => 'a',
                        'á' => 'a',
                        'â' => 'a',
                        'ã' => 'a',
                        'ä' => 'a',
                        'å' => 'a',
                        'æ' => 'a',
                        'ç' => 'c',
                        'è' => 'e',
                        'é' => 'e',
                        'ê' => 'e',
                        'ë' => 'e',
                        'ì' => 'i',
                        'í' => 'i',
                        'î' => 'i',
                        'ï' => 'i',
                        'ð' => 'o',
                        'ñ' => 'n',
                        'ò' => 'o',
                        'ó' => 'o',
                        'ô' => 'o',
                        'õ' => 'o',
                        'ö' => 'o',
                        'ø' => 'o',
                        'ù' => 'u',
                        'ú' => 'u',
                        'û' => 'u',
                        'ý' => 'y',
                        'þ' => 'b',
                        'ÿ' => 'y',
                        'º' => ''
                    );
                    $contenidoSms = strtr($contenidoSms, $unwanted_array);

                    // 5. Normalizar espacios
                    $contenidoSms = preg_replace('/\s+/', ' ', $contenidoSms);
                    $contenidoSms = trim($contenidoSms);

                    FacadesLog::info("📱 Enviando SMS a $telefonoSms: " . $contenidoSms);

                    // 6. Enviar SMS
                    try {
                        $response = \Illuminate\Support\Facades\Http::withBasicAuth('admin', 'admin')
                            ->timeout(15)
                            ->asForm()
                            ->post('http://10.10.10.252/goform/WIAMsgSend', [
                                'CurrentPort' => '255',
                                'Addressee'   => $telefonoSms,
                                'MsgInfo'     => $contenidoSms
                            ]);

                        $body = $response->body();
                        if ($response->successful() && strpos($body, 'Send failed!') === false) {
                            FacadesLog::info("✅ SMS enviado a $telefonoSms. Resp: " . $body);
                        } else {
                            FacadesLog::error("❌ Fallo SMS a $telefonoSms. Status: " . $response->status() . " Resp: " . $body);
                        }
                    } catch (\Exception $e) {
                        FacadesLog::error('⚠️ Error de conexión SMS a ' . $telefonoSms . ': ' . $e->getMessage());
                    }
                } else {
                    FacadesLog::warning('No se pudo enviar SMS: el asesor no tiene telf_laboral.');
                }
            }

            return true;
        } catch (Exception $e) {
          FacadesLog::error('Error enviando email: ' . $e->getMessage(), [
        'trace' => $e->getTraceAsString(),
        'cliente_id' => $clienteId,
        'asesor_id' => $idAsesor,
        'email_to' => $emailTo ?? null,
    ]);
    // También lo mandamos al error_log de PHP por si acaso
    error_log('Error enviando email: ' . $e->getMessage());
    throw $e; // o podrías devolver false si prefieres no interrumpir
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
