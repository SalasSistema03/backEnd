<!DOCTYPE html>
<html lang="en">
@php
    use Carbon\Carbon;
    $mes = Carbon::now()->month;
@endphp

<head>
    <link rel="shortcut icon" href="{{ asset('image/logo' . $mes . '.png') }}">
    <meta charset="UTF-8">
    <!-- Configuración para que la página sea responsive-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Carga de Bootstrap CSS desde un CDN (Content Delivery Network). Bootstrap proporciona un conjunto de estilos prediseñados para facilitar el diseño responsivo. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Carga de tu archivo CSS personalizado. Este archivo puede contener estilos específicos de tu aplicación o ajustes adicionales. -->
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <!-- Carga de Bootstrap JS (junto con su dependiencia Popper.js) para habilitar funcionalidades dinámicas como los dropdowns, modales, etc. -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <!-- Título de la página, que aparecerá en la pestaña del navegador. -->
    <title>Ingreso a Sistema SALAS</title>
</head>

<body>

    <div class="g-3 m-3">
        <div class="row d-flex justify-content-center align-items-center mb-5">
            <!-- Imagen del logo que se muestra en la parte superior del formulario -->
            {{-- <img src="../public/image/logo.png" alt="Logo" class="header-img" id="logo"> --}}
            <img src="{{ asset('image/logo' . $mes . '.png') }}" alt="Logo" class="header-img logo" id="logo">

        </div>

        <!-- Formulario  -->
        <form class="row g-3 d-flex justify-content-center align-items-center" method="POST"
            action="{{ route('usuarioVerificacion') }}" autocomplete="off" novalidate>
            @csrf
            <!-- Primer campo: Usuario -->
            <div class="col-md-12 d-flex justify-content-center align-items-center">
                <div class="col-md-3">
                    <!-- Etiqueta para el campo de usuario -->
                    <label for="user" class="form-label">Usuario</label>
                    <input type="text" class="form-control inputsLogin @error('usuario') is-invalid @enderror" id="user"
                        value="{{ old('usuario') }}" name="usuario" required>
                    @error('usuario')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Segundo campo: Contraseña -->
            <div class="col-md-12  d-flex justify-content-center align-items-center">
                <div class="col-md-3">
                    <!-- Etiqueta para el campo de contraseña -->
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control inputsLogin @error('password') is-invalid @enderror" id="password" 
                        value="" name = "password" required >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Botón de enviar el formulario -->

            <div class="col-md-12 mt-5 d-flex justify-content-center align-items-center">
                <div class="col-md-3 d-flex justify-content-center align-items-center">
                    <button class="btn btn-primary w-100" type="submit">Ingresar</button>
                </div>
            </div>

        </form>
    </div>


</body>

</html>
