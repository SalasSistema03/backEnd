<?php

return [
    // Carpeta física donde se guardan las fotos de inventario (UNC).
    // Estructura: {ruta}\{propiedad_id}\{año_reserva}\{id_proceso_propiedad}\{archivo}
    'fotos_ruta_fisica' => env('INVENTARIO_FOTOS_RUTA', '\\\\10.10.10.183\\Compartida\\INVENTARIO'),

    // Prefijo de la URL pública (alias de Apache que apunta a la carpeta anterior).
    'fotos_url_base' => '/inventario',
];
