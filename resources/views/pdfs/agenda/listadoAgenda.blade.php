<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Inline the CSS instead of using file:// --}}
    <style>
        {!! file_get_contents(public_path('css/pdfStyles.css')) !!}
    </style>
</head>


<body>

    <div class="col-3">
        <img src="{{ public_path('image/logo.png') }}" class="logo">
    </div>
    <!-- {{$datos}} -->

    @php
    $fechaAnterior = null;
    @endphp

    @foreach ($datos as $item)
    @if($fechaAnterior !== $item['fecha'])
    <div class="d-flex justify-content-center listado_agenda_fecha">
        {{ \Carbon\Carbon::parse($item['fecha'])->format('d/m/Y') }}
    </div>
    @php
    $fechaAnterior = $item['fecha'];
    @endphp
    @endif

    <div class="row">
        <div class="col-md-1">
            {{ $item['hora_inicio']}}

        </div>
        <div class="col-md-11">
            {{ $item['descripcion']}}
        </div>
        <div>
            motivo de la eliminación: {{$item['motivo']}}
            realizado por el usuario {{$item['quien_borro']}}
        </div>
    </div>
    @endforeach


</body>

</html>
