<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <title>@yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">

</head>

<body>

    @php
        $navsUnicos = $permisos->where('usuario_id', session('usuario_id'))->unique('nav_id');
        $vistasUnicos = $permisos->where('usuario_id', session('usuario_id'))->unique('vista_id');
        $botonesUnicos = $permisos->where('usuario_id', session('usuario_id'))->unique('boton_id');
        use App\Models\usuarios_y_permisos\Nav;
        use App\Models\usuarios_y_permisos\Vista;
        use App\Models\usuarios_y_permisos\Botones;
        // Obtener todos los registros de la tabla navs
        $navs = Nav::all();
        $vistas = Vista::all();
        $mes = session('mes');

        $SeccionUnicas = $vistas->unique('Seccion');

    @endphp

    <nav class="navbar navbar-expand-lg sticky-top mb-1">
        <a class="navbar-brand" id="contenedor_logo" href="{{ route('home') }}">

            <img src="{{ asset('image/logo'.$mes.'.png') }}" alt="Logo" class="header-img" id="logo_nav">

        </a>
        
        {{-- <a class="navbar-brand" id="contenedor_logo" href="{{ route('home') }}">
        <div class="contenedor_logo">
            <img src="{{ asset('image/logo.png') }}" alt="Logo" class="header-img" id="logo_nav">
        </div>
        </a> --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">

                <li class="nav-item dropdown">
                    <a id="usuarioNavbar" class="nav-link  dropdown dropdown-toggle" href="#" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-regular fa-user me-2"></i> {{ session('usuario_interno') }}
                    </a>
                    
                    <ul class="dropdown-menu">
                        @if (session('admin') == 1)
                            <li><a class="dropdown-item" href="{{ route('registro.index') }}">Usuarios</a></li>

                            <li><a class="dropdown-item" href="{{ route('validaciones.index') }}">Permisos</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                        @endif

                        <li><a class="dropdown-item" href="{{ route('logout') }}">Salir</a></li>
                    </ul>
                </li>

            </ul>

        </div>
    </nav>



    @yield('content')

    <script>
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Seleccionar todos los elementos del menú que tienen submenús
            let dropdownItems = document.querySelectorAll(".dropend > .titulo_submenu");

            dropdownItems.forEach(function(item) {
                item.addEventListener("click", function(e) {
                    e.preventDefault(); // Evita la navegación
                    e.stopPropagation(); // Evita que el menú principal se cierre

                    let submenu = this.nextElementSibling; // Selecciona el submenú

                    if (!submenu) return; // Si no hay submenú, salir

                    // Cierra otros submenús abiertos antes de abrir este
                    document.querySelectorAll(".contenedor_submenu.show").forEach(function(
                        openMenu) {
                        if (openMenu !== submenu) {
                            openMenu.classList.remove("show");
                        }
                    });

                    // Alterna la clase 'show' en el submenú actual
                    submenu.classList.toggle("show");
                });
            });

            // Cerrar el submenú si se hace clic fuera
            document.addEventListener("click", function(e) {
                document.querySelectorAll(".contenedor_submenu.show").forEach(function(submenu) {
                    if (!submenu.contains(e.target)) {
                        submenu.classList.remove("show");
                    }
                });
            });
        });
    </script>


    @yield('scripts')
</body>

</html>
