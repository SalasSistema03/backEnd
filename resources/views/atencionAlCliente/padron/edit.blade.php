@extends('layout.nav')

@section('title', 'Editar Persona')

@section('content')

    <form action="{{ route('padron.update', $padron->id) }}" method="POST" novalidate autocomplete="off">
        @csrf
        @method('PUT')


        <div class="row g-3 datosPropiedad px-3">
            <div class="col-md-7">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label>Nombre:</label>
                        <input class="form-control" type="text" name="nombre" value="{{ $padron->nombre }}" >
                    </div>
                    <div class="col-md-4">
                        <label>Apellido:</label>
                        <input class="form-control" type="text" name="apellido" value="{{ $padron->apellido }}" >
                    </div>
                    <div class="col-md-4">
                        <label>Documento:</label>
                        <input class="form-control" type="number" name="documento" value="{{ $padron->documento }}"
                            >
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label>Fecha de Nacimiento:</label>
                        <input class="form-control" type="date" name="fecha_nacimiento"
                            value="{{ $padron->fecha_nacimiento }}" >
                    </div>
                    <div class="col-md-4">
                        <label>Calle:</label>
                        <input class="form-control" type="text" name="calle" value="{{ $padron->calle }}" >
                    </div>
                    <div class="col-md-4">
                        <label>Número:</label>
                        <input class="form-control" type="number" name="numero_calle" value="{{ $padron->numero_calle }}"
                            >
                    </div>

                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label>Piso:</label>
                        <input class="form-control" type="text" name="piso_departamento"
                            value="{{ $padron->piso_departamento }}" >
                    </div>
                    <div class="col-md-4">
                        <label>Ciudad:</label>
                        <input class="form-control" type="text" name="ciudad" value="{{ $padron->ciudad }}" >
                    </div>
                    <div class="col-md-4">
                        <label>Provincia:</label>
                        <input class="form-control" type="text" name="provincia" value="{{ $padron->provincia }}"
                            >
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label>Notas:</label>
                        <textarea class="form-control" name="notes" rows="3" >{{ $padron->notes }}</textarea>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="row g-1">
                    <div class="col-md-12">
                        <label>Teléfonos:</label>
                        <div id="telefonos">
                            @foreach ($padron->telefonos as $telefono)
                                <div class="d-flex gap-2 mb-2 telefono-container">
                                    <input type="hidden" name="telefonos[{{ $loop->index }}][id]" value="{{ $telefono->id }}">
                                    <input class="form-control" type="text" name="telefonos[{{ $loop->index }}][phone_number]" value="{{ $telefono->phone_number }}">
                                    <input class="form-control" type="text" name="telefonos[{{ $loop->index }}][notes]" value="{{ $telefono->notes }}" placeholder="Notas">
                                </div>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-end mt-2">
                            <button class="btn btn-primary" type="button" onclick="agregarTelefono()">Agregar
                                Teléfono</button>
                        </div>
                    </div>

                </div>
                
            </div> 
            <div class="col-md-12 d-flex justify-content-center align-items-center mt-4">
                <!-- Botón para enviar el formulario -->
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </div>
    </form>

@endsection



<script>
    // Set contador to the number of existing phones to avoid conflicts
    let contador = {{ count($padron->telefonos) }};

    function agregarTelefono() {
        const div = document.createElement('div');
        div.classList.add('d-flex', 'gap-2', 'mb-2');
        div.innerHTML = `
            <input class="form-control" type="text" name="telefonos[${contador}][phone_number]" placeholder="Nuevo Teléfono" required>
            <input class="form-control" type="text" name="telefonos[${contador}][notes]" placeholder="Notas">
        `;
        
        document.getElementById('telefonos').appendChild(div);
        contador++;
    }
</script>


@section('scripts')
     
     <script src="{{ asset('js/atencionAlCliente/padron/localstorage.js') }}"></script>
@endsection
