@extends('layout.nav')

@section('title', 'Cargar Persona')

@section('content')


    <form action="{{ route('padron.store') }}" method="POST" novalidate class="datosPropiedad px-3" autocomplete="off">
        @csrf
        <div class="row g-3">
            <div class="col-md-7">
                <div class="row g-3">
                    <!-- Campo para el primer nombre -->
                    <div class="col-md-4  @error('nombre') is-invalid @enderror" id="nombre">
                        <label>Nombre:</label>
                        <input class="form-control" type="text" name="nombre" value="{{ old('nombre') }}" required>
                    </div>

                    <!-- Campo para el apellido -->
                    <div class="col-md-4 @error('apellido') is-invalid @enderror" id="apellido">
                        <label>Apellido:</label>
                        <input class="form-control" type="text" name="apellido" value="{{ old('apellido') }}" required>
                    </div>
                    <!-- Campo para el documento -->
                    <div class="col-md-4 @error('documento') is-invalid @enderror" id="documento">
                        <label>Documento:</label>
                        <input class="form-control" type="number" name="documento" value="{{ old('documento') }}" min="0">
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4 @error('fecha_nacimiento') is-invalid @enderror" id="fecha_nacimiento">
                        <!-- Campo para la fecha de nacimiento -->
                        <label>Fecha de Nacimiento:</label>
                        <input class="form-control"type="date" name="fecha_nacimiento"
                            value="{{ old('fecha_nacimiento') }}">
                    </div>
                    <div class="col-md-4 @error('calle') is-invalid @enderror" id="calle">
                        <!-- Campo para la calle -->
                        <label>Calle:</label>
                        <input class="form-control"type="text" name="calle" value="{{ old('calle') }}" >
                    </div>
                    <div class="col-md-4 @error('numero_calle') is-invalid @enderror" id="numero_calle">
                        <!-- Campo para el número de la calle -->
                        <label>Número:</label>
                        <input class="form-control"type="number" name="numero_calle" value="{{ old('numero_calle') }}">
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4 @error('piso_departamento') is-invalid @enderror" id="piso_departamento">
                        <!-- Campo para el piso del apartamento -->
                        <label>Piso:</label>
                        <input class="form-control"type="text" name="piso_departamento"
                            value="{{ old('piso_departamento') }}">
                    </div>
                    <div class="col-md-4 @error('departamento') is-invalid @enderror" id="departamento">
                        <!-- Campo para la ciudad -->
                        <label>Ciudad:</label>
                        <input class="form-control"type="text" name="ciudad" value="{{ old('ciudad') }}" >
                    </div>
                    <div class="col-md-4 @error('provincia') is-invalid @enderror">
                        <!-- Campo para el estado -->
                        <label>Provincia:</label>
                        <input class="form-control"type="text" name="provincia" value="{{ old('provincia') }}" >
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <!-- Campo para notas adicionales -->
                        <label></label>
                        <label>Comentarios:</label>
                        <textarea id="textareaNotas" class="form-control" name="notes" rows="5"
                            placeholder="Escribe tus comentarios aquí...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="row g-1">
                    <div class="col-md-12">
                        <!-- Teléfonos -->
                        <br>
                        <div id="telefonos">
                            <div class="telefono d-flex gap-2">
                                <input class="form-control"type="text" name="telefonos[0][phone_number]"
                                    placeholder="Teléfono" required>
                                <input class="form-control"type="text" name="telefonos[0][notes]" placeholder="Notas">
                            </div>
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
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </form>

@endsection

@section('scripts')
     <script src="{{ asset('js/atencionAlCliente/padron/telefonos.js') }}"></script>
     <script src="{{ asset('js/atencionAlCliente/padron/localstorage.js') }}"></script>
@endsection
