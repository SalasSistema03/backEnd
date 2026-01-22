@extends('layout.nav')

@section('title', 'Registrar Usuario')

@section('content')


    <!-- Formulario -->
    <form method="POST" action="{{ route('registro.store') }}" autocomplete="off" novalidate
        class="registration-form row d-flex justify-content-center">
        @csrf

        <!-- Información Personal -->



        <div class="col-md-5 px-3 card-salas-registro">
            <h3 class="section-title card-header text-center">Información Personal</h3>
            <div class="form-group card-body row">
                <div class="col-md-6">

                    <label for="name" class="form-label">
                        <i class="icon-user"></i>
                        Nombre Completo
                    </label>
                    <input type="text" id="name" name="name"
                        class="form-input form-control @error('name') error @enderror" value="{{ old('name') }}"
                        placeholder="Ingrese su nombre completo" required>
                    @error('name')
                        <span class="error-message text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="nombre_interno" class="form-label">
                        <i class="icon-id"></i>
                        Nombre Interno
                    </label>
                    <input type="text" id="nombre_interno" name="nombre_interno"
                        class="form-input form-control @error('nombre_interno') error @enderror"
                        value="{{ old('nombre_interno') }}" placeholder="Usuario interno" required>
                    @error('nombre_interno')
                        <span class="error-message text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="contraseña" class="form-label">
                        <i class="icon-lock"></i>
                        Contraseña
                    </label>
                    <div class="password-wrapper">
                        <input type="password" id="contraseña" name="password"
                            class="form-input form-control  @error('password') error @enderror"
                            placeholder="Mínimo 3 caracteres" required>
                    </div>
                    @error('password')
                        <span class="error-message text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="fecha_nacimiento" class="form-label">
                        <i class="icon-calendar"></i>
                        Fecha de Nacimiento
                    </label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                        class="form-input form-control @error('fecha_nacimiento') error @enderror"
                        value="{{ old('fecha_nacimiento') }}" required>
                    @error('fecha_nacimiento')
                        <span class="error-message text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>




        <!-- Información de Contacto -->
        <div class="col-md-5 px-3 card-salas-registro">
            <h3 class="section-title card-header text-center">Información de Contacto</h3>
            <div class="form-group card-body row">
                <div class="col-md-6">
                    <label for="tel_interno" class="form-label">
                        <i class="icon-phone"></i>
                        Teléfono Interno
                    </label>
                    <input type="tel" id="tel_interno" name="tel_interno"
                        class="form-input form-control @error('tel_interno') error @enderror"
                        value="{{ old('tel_interno') }}" placeholder="Ext. 1234" required>
                    @error('tel_interno')
                        <span class="error-message text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">

                    <label for="tel_laboral" class="form-label">
                        <i class="icon-phone-alt"></i>
                        Teléfono Laboral
                    </label>
                    <input type="tel" id="tel_laboral" name="tel_laboral"
                        class="form-input form-control @error('tel_laboral') error @enderror"
                        value="{{ old('tel_laboral') }}" placeholder="+54 342 4545-454" required>
                    @error('tel_laboral')
                        <span class="error-message text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="email_interno" class="form-label">
                        <i class="icon-mail"></i>
                        Email Interno
                    </label>
                    <input type="email" id="email_interno" name="email_interno"
                        class="form-input form-control @error('email_interno') error @enderror"
                        value="{{ old('email_interno') }}" placeholder="usuario@salas.com" required>
                    @error('email_interno')
                        <span class="error-message text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-6">

                    <label for="email_externo" class="form-label">
                        <i class="icon-mail-alt"></i>
                        Email Externo
                    </label>
                    <input type="email" id="email_externo" name="email_externo"
                        class="form-input form-control @error('email_externo') error @enderror"
                        value="{{ old('email_externo') }}" placeholder="usuario@salasinmobiliaria.com">
                    @error('email_externo')
                        <span class="error-message text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

        </div>

        <!-- Botón de Envío -->
        <div class="col-md-12 d-flex justify-content-center">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i>
                Guardar
            </button>

        </div>
    </form>



@endsection
