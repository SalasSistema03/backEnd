<?php
namespace App\Http\Controllers\clientes;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\clientes\EnvioMailService;

class EnvioMailConsultas extends Controller
{   
    protected EnvioMailService $envioMailService;

    public function __construct(EnvioMailService $envioMailService)
    {
        $this->envioMailService = $envioMailService;
    }

    public function enviaMail(Request $request)
    {
        // Validar datos del formulario
        $validated = $request->validate([
            'numeros' => 'required|string|email',
            'mensaje' => 'required|string',
        ]);

        try {
            $this->envioMailService->enviar($validated['numeros'], $validated['mensaje'], 'Prueba de correo PHP');
            return back()->with('success', 'El mensaje se envió correctamente.');
        } catch (Exception $e) {
            return back()->with('error', 'Error al enviar el mensaje: ' . $e->getMessage());
        }
    }
}
