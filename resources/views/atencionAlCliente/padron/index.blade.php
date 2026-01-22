@extends('layout.nav')

@section('title', 'Buscar Persona')

@section('content')

<div class="px-5">
    <form method="GET" action="{{ route('padron.index') }}" class="datosPropiedad" autocomplete="off">
        @csrf
        <div class="col-md-9">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="text-center" id="basic-addon1">Apellido:</label>
                    <input name= "apellido" type="text" class="form-control @error('piso') is-invalid @enderror"
                        id="apellido">
                </div>
                <div class="col-md-3">
                    <label class="text-center" id="basic-addon1">DNI:</label>
                    <input name= "dni" type="number" class="form-control @error('piso') is-invalid @enderror"
                        id="dni" min="0" max="9999999999">
                </div>
                <div class="col-md-3 pt-3">
                    <button type="submit" class="btn btn-primary w-100">
                        Buscar
                    </button>
                </div>
            </div>
        </div>
    </form>
    <div class="row d-flex justify-content-center align-items-center ">
        <table class="table table-striped table-hover text-center tabla">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>DNI</th>
                    <th>Telefono</th>
                    <th>Comentario</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($personas as $persona)
                    <tr>
                        <td>{{ $persona->nombre }}</td>
                        <td>{{ $persona->apellido }}</td>
                        <td>{{ $persona->documento }}</td>
                        <td>{{ $persona->telefonos->first()->phone_number ?? 'Sin datos' }}</td>
                        <td>{{ $persona->telefonos->first()->notes ?? 'Sin comentario' }}</td>
                        <td>
                            <a href="{{ route('padron.show', $persona->id) }}" class="btn btn-primary">
                                Ver Informacion
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
    
@endsection
