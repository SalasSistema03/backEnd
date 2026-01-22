<?php

namespace App\Http\Controllers\buscadorPdf;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Response;


class BuscadorPdfController
{
    public function verPDF(Request $request)
    {
        $rutaBase = '\\\\10.10.10.190\\Compartido\\PDF AFIP-WSAFIPFE\\Inmobiliaria Salas\\';

        $empresa = $request->input('empresa');
        $comprobante = $request->input('comprobante');
        $quien = $request->input('quien');
        $tipo = $request->input('tipo');
        $letra = $request->input('letra');
        $numero = $request->input('numero');

        if ($empresa == 'Salas') {
            $punto = '4';
        } elseif ($empresa == 'Dolly') {
            $punto = '5';
        } elseif ($empresa == 'Florencia') {
            $punto = '1';
        }
        else {
            $punto = '4';
        }
        $puntos = str_pad($punto, 4, '0', STR_PAD_LEFT);
        $numeros = str_pad($numero, 8, '0', STR_PAD_LEFT);
        $nombreArchivo = sprintf('%s-%s-%s.pdf', $letra, $puntos, $numeros);

        if ($comprobante == 'Opp Concatenadas') {
            // Buscar y combinar PDFs similares
            return $this->buscarYCombinarPDFs($rutaBase, $empresa, $comprobante, $tipo, $numero, $letra, $puntos,$numeros);
        } else {
            // Ruta base donde se encuentran los PDFs
            $rutaCompleta = $rutaBase . $empresa . "\\" . $comprobante . "\\" . $quien . "\\" . $tipo . "\\" . $letra . "\\" . $nombreArchivo;
            
            if (!file_exists($rutaCompleta)) {
                return back()->with('error', 'Archivo no encontrado: ' . $nombreArchivo);
            }
            // Crear nuevo nombre de archivo con el tipo de comprobante
            $nombreDescarga = $comprobante . ' ' . $nombreArchivo;

            return response(file_get_contents($rutaCompleta))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $nombreDescarga . '"');


              
        }
    }

    /**
     * Busca PDFs similares y los combina en un solo archivo
     */
    private function buscarYCombinarPDFs($rutaBase, $empresa, $comprobante, $tipo, $numeros, $letra, $puntos,$numero)
    {
        // Directorio donde buscar los PDFs
        $directorioBase = $rutaBase . $empresa . "\\" . $comprobante . "\\" . $tipo;
        
        // Verificar si el directorio existe
        if (!is_dir($directorioBase)) {
            return back()->with('error', 'Directorio no encontrado: ' . $directorioBase);
        }

        // Buscar archivos PDF en el directorio
        $archivosEncontrados = [];
        $archivos = scandir($directorioBase);
        $contador = 0;
        $nombreArchivo = sprintf('%s %s-%s-%s.pdf',"OppConcatenada", "X", $puntos, $numero);

       

        foreach ($archivos as $archivo) {
            if (pathinfo($archivo, PATHINFO_EXTENSION) === 'pdf') {
               if(strpos($archivo, $numeros) !== false) {
                   $archivosEncontrados[] = $directorioBase . "\\" . $archivo;
               }
            }
        }
        
 
        /* dd($archivosEncontrados); */
        

        if (empty($archivosEncontrados)) {
            return back()->with('error', 'No se encontraron PDFs similares');
        }

        // Método simplificado: devolver el primer PDF encontrado
        // En una implementación real, aquí es donde combinarías los PDFs
        if (count($archivosEncontrados) == 1) {
            // Si solo hay un PDF, devolverlo directamente
            
            
            return response()->file($archivosEncontrados[0], [
                'Content-Type' => 'application/pdf',
            ]);
        } else {
            $pdf = new Fpdi();

            foreach ($archivosEncontrados as $archivoEncontrado) {
                $pageCount = $pdf->setSourceFile($archivoEncontrado);
        
                for ($page = 1; $page <= $pageCount; $page++) {
                    $templateId = $pdf->importPage($page);
                    $size = $pdf->getTemplateSize($templateId);
        
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);
                }
            }
            
            // Capturamos el contenido sin guardarlo
            return response($pdf->Output('S')) // 'S' = return as string
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $nombreArchivo . '"');
            // Si hay múltiples PDFs, intentar combinarlos usando un método alternativo
            
        }
    }


    public function index()
    {
        return view('buscaPdf.buscadorPdf');
    }
}