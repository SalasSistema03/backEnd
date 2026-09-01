<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RetencionesCuitExport implements FromArray, WithHeadings
{
    protected $registros;

    public function __construct(array $registros)
    {
        $this->registros = $registros;
    }

    public function array(): array
    {
        return array_map(function ($r) {
            return [
                $r['razon_social_retencion'] ?? '',
                $r['cuit_retencion'] ?? '',
                $r['fecha_comprobante'] ?? '',
                $r['importe_comprobante'] ?? '',
                $r['importe_retencion'] ?? '',
            ];
        }, $this->registros);
    }

    public function headings(): array
    {
        return [
            'Razon Social',
            'CUIT',
            'Fecha',
            'Importe Comprobante',
            'Importe Retención',
        ];
    }
}