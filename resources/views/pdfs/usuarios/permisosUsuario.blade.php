<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        {!! file_get_contents(public_path('css/pdfStyles.css')) !!}

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            color: #1a1a1a;
        }

        /* ===== Cabecera del Reporte ===== */
        .report-header {
            border-bottom: 2px solid rgb(0, 85, 185);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .report-title {
            color: rgb(0, 85, 185);
            text-transform: uppercase;
        }

        .user-card {
            border: 1px solid rgb(0, 85, 185);
            padding: 6px 15px;
            border-radius: 4px;
            background-color: #f4f8fc;
        }

        /* ===== Leyenda ===== */
        .leyenda {
            font-size: 0.8rem;
            text-align: right;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #d0d0d0;
        }
        .leyenda span { margin-left: 20px; }

        /* ===== Contenedor general del árbol ===== */
        .tree-wrapper {
            width: 100%;
        }

        /* ===== NIVEL 0: Módulo (raíz) ===== */
        .nodo-modulo {
            border: 1px solid rgb(0, 85, 185);
            border-radius: 3px;
            margin-bottom: 6px;
        }
        .nodo-modulo-header {
            padding: 8px 12px;
            font-weight: bold;
            font-size: 0.95rem;
            text-transform: uppercase;
            break-after: avoid-page;
            page-break-after: avoid;
        }
        .modulo-permitido { 
            background-color: rgb(0, 85, 185); 
            color: #ffffff; 
        }
        .modulo-denegado  { 
            background-color: #f8f9fa; 
            color: #72777a; 
            border-bottom: 1px solid #e0e0e0;
        }

        .nodo-modulo-body {
            padding: 8px 10px 10px 10px;
        }

        /* ===== NIVEL 1 y 2: filas del árbol ===== */
        .fila-arbol {
            width: 100%;
            padding: 2px 0;
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        .tree-prefix {
            font-family: 'Courier New', Courier, monospace;
            color: #6c757d;
            white-space: pre;
            font-size: 0.85rem;
        }

        .nodo-vista {
            font-size: 0.85rem;
        }
        .nodo-vista .icon { font-size: 0.8rem; }

        .nodo-boton {
            font-size: 0.78rem;
        }

        .texto-permitido { color: #0f172a; font-weight: bold; }
        .texto-denegado  { color: #94a3b8; text-decoration: line-through; }

        /* ===== Badges / Acciones ===== */
        .badge-accion {
            font-size: 0.7rem;
            padding: 2px 10px;
            border-radius: 4px;
            display: inline-block;
            letter-spacing: 0.3px;
        }
        .badge-permitido {
            border: 1.5px solid rgb(0, 85, 185);
            color: rgb(0, 85, 185);
            background-color: #eaf2fd;
            font-weight: bold;
        }
        .badge-denegado {
            border: 1px dotted #94a3b8;
            color: #94a3b8;
            text-decoration: line-through;
        }

        .sin-datos {
            font-size: 0.75rem;
            color: #6c757d;
            font-style: italic;
        }

        .icon { font-family: monospace; font-size: 1rem; margin-right: 4px; }
        .status-text {
            float: right;
            font-size: 0.7rem;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

    <!-- ===================== CABECERA ===================== -->
    <div class="row report-header align-items-center mx-0 mt-2">
        <div class="col-4">
            @php
                $logoPath = public_path('image/logo.png');
                $logoBase64 = file_exists($logoPath)
                    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
                    : null;
            @endphp
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" style="max-height: 45px;">
            @endif
        </div>
        <div class="col-8 text-end">
            <h4 class="mb-2 fw-bolder report-title">Reporte de Permisos</h4>
            <div class="user-card d-inline-block text-start">
                <div style="font-size: 0.7rem; color: rgb(0, 85, 185); font-weight: bold;">USUARIO</div>
                <div style="font-size: 0.95rem; font-weight: bold; color: #1a1a1a;">
                    {{ $data['usuario']->username }}
                    <span style="font-weight: normal; color: #555;">({{ $data['usuario']->name }})</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== LEYENDA ===================== -->
    <div class="leyenda">
        <span><span class="icon fw-bold" style="color: rgb(0, 85, 185);">✔️</span> Acceso Habilitado</span>
        <span><span class="icon" style="color: #94a3b8;">✖</span> Acceso Denegado</span>
    </div>

    <!-- ===================== ÁRBOL DE PERMISOS ===================== -->
    <div class="tree-wrapper">
        @foreach ($data['arbol'] as $nav)
            @php
                $tieneNav = in_array($nav->id, $data['navs_permitidos']);
            @endphp

            <!-- ---------- NIVEL 0: MÓDULO ---------- -->
            <div class="nodo-modulo">
                <div class="nodo-modulo-header {{ $tieneNav ? 'modulo-permitido' : 'modulo-denegado' }}">
                    <span class="icon">{{ $tieneNav ? '✔️' : '✖' }}</span>
                    📁 {{ $nav->menu }}
                    <span class="status-text">{{ $tieneNav ? 'HABILITADO' : 'BLOQUEADO' }}</span>
                </div>

                <div class="nodo-modulo-body">
                    @forelse ($nav->vistas as $vista)
                        @php
                            $tieneVista = in_array($vista->id, $data['vistas_permitidas']);
                            $conectorVista = $loop->last ? '└────── ' : '├────── ';
                            $continuacionVista = $loop->last ? '    ' : '│   ';
                        @endphp

                        <!-- ---------- NIVEL 1: VISTA ---------- -->
                        <div class="fila-arbol nodo-vista {{ $tieneVista ? 'texto-permitido' : 'texto-denegado' }}">
                            <span class="tree-prefix">{{ $conectorVista }}</span>
                            <span class="icon">{{ $tieneVista ? '✔️' : '✖' }}</span>
                            {{ $vista->nombre_visual }}
                        </div>

                        <!-- ---------- NIVEL 2: BOTONES ---------- -->
                        @if($vista->botones->count() > 0)
                            @foreach($vista->botones as $boton)
                                @php
                                    $tieneBoton = in_array($boton->id, $data['botones_permitidos']);
                                    $conectorBoton = $loop->last ? '└────────────────────── ' : '├────────────────────── ';
                                    $prefijoBoton = $continuacionVista . $conectorBoton;
                                @endphp
                                <div class="fila-arbol nodo-boton">
                                    <span class="tree-prefix">{{ $prefijoBoton }}</span>
                                    <span class="badge-accion {{ $tieneBoton ? 'badge-permitido' : 'badge-denegado' }}">
                                        {{ $boton->nombre_visual }}
                                    </span>
                                </div>
                            @endforeach
                        @else
                            <div class="fila-arbol">
                                <span class="tree-prefix">{{ $continuacionVista }}└───────────────────── </span>
                                <span class="sin-datos">(Sin acciones configurables)</span>
                            </div>
                        @endif
                    @empty
                        <div class="fila-arbol">
                            <span class="sin-datos">Sin vistas asignadas.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>