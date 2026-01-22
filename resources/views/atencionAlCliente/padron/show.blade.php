@extends('layout.nav')

@section('title', 'Visualizar Persona')

@section('content')

    <div class="row g-3 datosPropiedad px-3">
        <div class="col-md-7">
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Nombre:</label>
                    <input class="form-control" type="text" name="nombre" value="{{ $padron->nombre }}" disabled>
                </div>
                <div class="col-md-4">
                    <label>Apellido:</label>
                    <input class="form-control" type="text" name="apellido" value="{{ $padron->apellido }}" disabled>
                </div>
                <div class="col-md-4">
                    <label>Documento:</label>
                    <input class="form-control" type="text" name="documento" value="{{ $padron->documento }}" disabled>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Fecha de Nacimiento:</label>
                    <input class="form-control" type="date" name="fecha_nacimiento"
                        value="{{ $padron->fecha_nacimiento }}" disabled>
                </div>
                <div class="col-md-4">
                    <label>Calle:</label>
                    <input class="form-control" type="text" name="calle" value="{{ $padron->calle }}" disabled>
                </div>
                <div class="col-md-4">
                    <label>Número:</label>
                    <input class="form-control" type="number" name="numero_calle" value="{{ $padron->numero_calle }}"
                        disabled>
                </div>

            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Piso:</label>
                    <input class="form-control" type="text" name="piso_departamento"
                        value="{{ $padron->piso_departamento }}" disabled>
                </div>
                <div class="col-md-4">
                    <label>Ciudad:</label>
                    <input class="form-control" type="text" name="ciudad" value="{{ $padron->ciudad }}" disabled>
                </div>
                <div class="col-md-4">
                    <label>Provincia:</label>
                    <input class="form-control" type="text" name="provincia" value="{{ $padron->provincia }}" disabled>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-12">
                    <label>Notas:</label>
                    <textarea class="form-control" name="notes" rows="3" disabled>{{ $padron->notes }}</textarea>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="row g-1">
                <div class="col-md-12">
                    <label>Teléfonos:</label>

                    @forelse ($padron->telefonos as $telefono)
                        <div class="d-flex gap-2 mb-2">
                            <input class="form-control" type="text" value="{{ $telefono->phone_number }}" disabled>
                            <input class="form-control" type="text" value="{{ $telefono->notes }}" placeholder="Notas"
                                disabled>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>

        <div class="col-md-12 d-flex justify-content-center align-items-center mt-4">
            <!-- Botón con enlace -->
             @if ($tieneAccesoEditarPadron) 
            <button class="btn btn-primary" onclick="window.location='{{ route('padron.edit', $padron->id) }}'">Editar</button>
             @else
            <button class="btn btn-primary" disabled>Editar</button>
            @endif 
        </div>
    </div>





@endsection
