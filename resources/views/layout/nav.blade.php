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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <title>@yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>






</head>

<body>
    {{-- ------------------------spinner--------------------------- --}}
    <div class="spinner-wrapper">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    @php
        use App\Models\At_cl\Usuario;
        $usuario = Usuario::find(session('usuario_id'));
        $notificaciones =
            $usuario?->unreadNotifications->filter(function ($notification) {
                $data = $notification->data;

                return (isset($data['fecha'], $data['activo']) &&
                    $data['fecha'] <= now()->toDateString() &&
                    $data['activo'] == 1)  ||
                    (isset($data['es_asesor_activo']) && $data['es_asesor_activo'] == 1) ||
                    (isset($data['es_criterio']) && $data['es_criterio'] == 1) ;
            }) ?? collect();

         /* dd($notificaciones);  */ 
        $cantidadNotificaciones = $notificaciones->count();
        //dd($cantidadNotificaciones);
    @endphp
    <script>
        const spinnerWrapper = document.querySelector(".spinner-wrapper");
        const spinner = document.querySelector(".spinner-border");

        // Function to show spinner
        function showSpinner() {
            spinnerWrapper.style.display = "flex";
            spinnerWrapper.style.opacity = "1";
        }

        // Function to hide spinner
        function hideSpinner() {
            spinnerWrapper.style.opacity = "0";
            setTimeout(() => {
                spinnerWrapper.style.display = "none";
            }, 300);
        }

        // Add event listeners for AJAX requests
        $(document).ajaxStart(function() {
            // Verificar si estamos en la búsqueda de calles
            const searchCalleInput = document.getElementById('search-calle');
            const isSearchingCalle = searchCalleInput && searchCalleInput.matches(':focus');
            const isCalleSearch = window.location.pathname.includes('propiedad');

            const searchPdfButton = document.getElementById('btnBuscarPdf');
            // Mostrar spinner si no estamos en la búsqueda de calles
            if (!isSearchingCalle && !isCalleSearch) {
                showSpinner();
            }
        });

        $(document).ajaxStop(function() {
            hideSpinner();
        });





        // Add event listeners for form submissions
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar si estamos en la vista de búsqueda de PDF
            const isPdfSearchPage = window.location.pathname.includes('buscaPdf');
            const isListadoView = window.location.pathname.includes('Listado-view');
            // Si estamos en la vista de búsqueda de PDF, ocultar el spinner
            if (isPdfSearchPage || isListadoView) {
                const spinner = document.querySelector('.spinner-wrapper');
                if (spinner) {
                    spinner.style.display = 'none';
                    spinner.style.opacity = '0';
                    spinner.style.visibility = 'hidden';
                }
                return; // No agregar más event listeners
            }

            // Para otras páginas, mantener la lógica normal
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Evitar que el spinner se muestre durante la búsqueda de calles
                    const searchCalleInput = document.getElementById('search-calle');
                    if (!searchCalleInput || !searchCalleInput.matches(':focus')) {
                        showSpinner();
                    }
                });
            });
        });
        window.addEventListener("load", function() {
            hideSpinner();
        });
    </script>

    {{-- @dump(session('usuario_id'))
    @dump(session('usuario_nombre'))  --}}
    {{-- ------------------------NAV--------------------------- --}}
    @php
        use Carbon\Carbon;
        $navsUnicos = $permisos->where('usuario_id', session('usuario_id'))->unique('nav_id');
        $vistasUnicos = $permisos->where('usuario_id', session('usuario_id'))->unique('vista_id');
        $botonesUnicos = $permisos->where('usuario_id', session('usuario_id'))->unique('boton_id');
        $SeccionUnicas = $vistas->unique('Seccion');
        use App\Models\usuarios_y_permisos\Nav;
        use App\Models\usuarios_y_permisos\Vista;
        use App\Models\usuarios_y_permisos\Botones;
        // Obtener todos los registros de la tabla navs
        $navs = Nav::all();
        $vistas = Vista::all();
        $mes = Carbon::now()->month;

        
    @endphp
    <nav class="navbar navbar-expand-lg sticky-top mb-3">
        <!-- Logo -->

        <a class="navbar-brand" id="contenedor_logo" href="{{ route('home') }}">

            <img src="{{ asset('image/logo' . $mes . '.png') }}" alt="Logo" class="header-img" id="logo_nav">

        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">

                @foreach ($navs as $nav)
                    <div>
                        @foreach ($navsUnicos as $navUnico)
                            @if ($nav->id == $navUnico->nav_id)
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle item_menu" href="#" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ $nav->menu }}
                                    </a>
                                    <ul class="dropdown-menu">
                                        @foreach ($SeccionUnicas as $seccion)
                                            @if ($nav->id == $seccion->menu_id)
                                                <li class="nav-item dropend mb-1">
                                                    <a class="nav-link dropdown-toggle titulo_submenu" href="#"
                                                        role="button">
                                                        {{ $seccion->Seccion }}
                                                    </a>

                                                    <ul class="dropdown-menu contenedor_submenu">
                                                        @foreach ($vistas as $vista)
                                                            @if ($seccion->Seccion == $vista->Seccion)
                                                                @if ($vista->es_nav == 1)
                                                                    <li><a class="dropdown-item"
                                                                            href="{{ route($vista->ruta) }}">
                                                                            {{ $vista->nombre_visual }}</a>
                                                                    </li>
                                                                @endif
                                                            @endif
                                                        @endforeach
                                                        {{-- @endif --}}
                                                    </ul>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        @endforeach
                    </div>
                @endforeach


                {{-- ------------------------CAMPANA--------------------------- --}}


                {{-- NOTIFICACIONES CAMPANITA --}}
                <div class="cuadro_notificaciones dropdown">
                    <button class="btn btnIconsNotification position-relative pt-2 dropdown-toggle"
                        data-bs-toggle="dropdown">
                        @if ($cantidadNotificaciones > 0)
                            <span class="position-absolute translate-middle badge rounded-pill notification_counter">
                                <div class="text_counter">
                                    {{ $cantidadNotificaciones > 99 ? '99+' : $cantidadNotificaciones }}
                                </div>
                                <span class="visually-hidden">unread messages</span>
                            </span>
                        @endif
                        <i class="fa-regular fa-bell"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                        style="padding: 15px; max-width: 400px; max-height: 600px; overflow-y: auto; font-size: 11px;">
                        {{-- La variable notificaciones no viene de ningun controlador sino que sale de arriva en esta misma vista --}}
                        @forelse ($notificaciones as $notification)
                            {{-- @dd($notificaciones) --}}

                            @if ($notification->data['pertenece'] == 'cliente' || $notification->data['pertenece'] == 'criterio' )
                                <li class="notificacion_cliente dropdown-item small notificacion-item "
                                    data-id="{{ $notification->id }}">
                                    <strong>{{ strtoupper($notification->data['descripcion']) }}</strong><br>
                                    <small>{{ strtoupper($notification->data['pertenece']) }} -
                                        {{ $notification->data['fecha'] }} - {{ $notification->data['hora'] ?? '' }}
                                    </small>
                                </li>
                            @else
                                <li class="dropdown-item small notificacion-item" data-id="{{ $notification->id }}">
                                    <strong>{{ strtoupper($notification->data['descripcion']) }}</strong><br>
                                    <small>{{ strtoupper($notification->data['pertenece']) }} -
                                        {{ $notification->data['fecha'] }} - {{ $notification->data['hora'] ?? '' }}
                                    </small>
                                </li>
                            @endif
                        @empty
                            <li class="dropdown-item text-muted">Sin notificaciones</li>
                        @endforelse
                    </ul>

                </div>




                {{-- ------------------------logout--------------------------- --}}
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
        {{-- QUIERO PONER UNA ALERTA ROJA --}}
        
    </nav>
    {{-- <div class="alert alert-danger" role="alert">
        <strong>!!ALERTA!!</strong> No utilizar, en mantenimiento
    </div> --}}

    {{-- -----------------------TOAST--------------------------- --}}
    @if (session('success') || session('error') || $errors->any())
        <div id="toast" class="toast-notification {{ session('error') || $errors->any() ? 'error' : 'success' }}">
            <div class="toast-icon">
                @if (session('success'))
                    <i class="fas fa-check-circle"></i>
                @else
                    <i class="fas fa-exclamation-circle"></i>
                @endif
            </div>
            <div class="toast-content">
                <div class="toast-title">
                    @if (session('success'))
                        Éxito
                    @else
                        Error
                    @endif
                </div>
                <div class="toast-message">
                    {{ session('success') ?? (session('error') ?? $errors->first()) }}
                </div>
            </div>
            <button class="toast-close" onclick="this.parentElement.style.display='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('toast');
            if (toast) {
                // Show toast with animation
                setTimeout(() => {
                    toast.classList.add('show');
                    toast.style.animation = 'slideIn 0.5s forwards';
                }, 100);

                // Auto hide after 5 seconds
                const autoHide = setTimeout(() => {
                    hideToast(toast);
                }, 5000);

                // Hide on click outside or close button
                toast.addEventListener('click', function(e) {
                    if (e.target.closest('.toast-close') || !e.target.closest('.toast-content')) {
                        hideToast(toast);
                        clearTimeout(autoHide);
                    }
                });
            }


            function hideToast(toastElement) {
                toastElement.style.animation = 'fadeOut 0.5s forwards';
                setTimeout(() => {
                    toastElement.remove();
                }, 500);
            }
        });
    </script>



    @yield('content')

    {{-- este script pertenece a el tigger como el de 
    propiedades donde aparecen la llave --}}
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


    {{-- -----------------------AGENDA--------------------------- --}}
    @if (session('usuario_id'))
        <script>
            window.UsuarioAgenda = {
                id: {{ session('usuario_id') }},
                nombre: "{{ session('usuario_nombre') }}"
            };
        </script>
    @endif
    <script>
        if (window.UsuarioAgenda) {

            function chequearEventos() {
                fetch('{{ route('agenda.hoy') }}')
                    .then(response => response.json())
                    .then(eventos => {
                        /*  console.log('entro'); */
                        const ahora = new Date();
                        eventos.forEach(evento => {
                            const fechaHoraEvento = new Date(`${evento.fecha}T${evento.hora_inicio}`);
                            

                            //MODIFICAR
                            const diferencia = (fechaHoraEvento - ahora) / 60000 ; // minutos




                            
                            console.log(diferencia);
                            //console.log(diferencia);
                            if (diferencia > -5 && diferencia < 15) {
                                console.log(evento);
                                if (evento.activo == 1 && evento.realizado == 0) {
                                    let titulo = evento.descripcion == null ?
                                        `${evento.hora_inicio}` :
                                        `${evento.hora_inicio} - ${evento.descripcion}`;
                                    /* alert(`${evento.descripcion} a las ${evento.hora_inicio}`); */
                                    Swal.fire({
                                        title: titulo,
                                        text: "¿Desea marcar el evento como realizado?",
                                        icon: "question",
                                        showCancelButton: true,
                                        confirmButtonColor: "#0055b9",
                                        cancelButtonColor: "#00af9af5",
                                        cancelButtonText: "Posponer",
                                        confirmButtonText: "Realizado!"
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            fetch('{{ route('nota.marcarRealizada') }}', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                    },
                                                    body: JSON.stringify({
                                                        nota_id: evento
                                                            .id // ← acá usás el ID que ya viene
                                                    })
                                                })
                                                .then(res => res.json())
                                                .then(data => {
                                                    if (data.success) {
                                                        Swal.fire({
                                                            title: "Evento Realizado!",
                                                            icon: "success",
                                                            showConfirmButton: false,
                                                            timer: 1500
                                                        });
                                                    } else {
                                                        throw new Error('No se pudo actualizar');
                                                    }
                                                })
                                                .catch(error => {
                                                    console.error('Error:', error);
                                                    Swal.fire({
                                                        title: "Error",
                                                        text: "No se pudo marcar como realizado.",
                                                        icon: "error"
                                                    });
                                                });
                                        }

                                    });
                                }
                            }
                        });
                    });
            }

            // Verificá cada minuto
            setInterval(chequearEventos, 60000);
            chequearEventos(); // primera vez al cargar
        }
    </script>
    {{-- -----------------------NOTIFICACIONES--------------------------- --}}
    

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.notificacion-item').forEach(function(item) {
                item.addEventListener('click', function() {
                    const notificacionId = this.dataset.id;
                    const pertenece = this.querySelector('small')?.textContent?.toLowerCase() || '';

                    // Si es una notificación de cliente/asesor, marcarla como leída
                    if (pertenece.includes('cliente') || pertenece.includes('criterio')) {
                        // Hacer petición AJAX para marcar como leída
                        fetch('{{ route('notificacion.marcarLeida') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    notificacion_id: notificacionId
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Redirigir a la página de asesores
                                    window.location.href = "{{ url('/asesores') }}";
                                } else {
                                    console.error('Error al marcar notificación:', data
                                    .message);
                                    // Redirigir de todas formas
                                    window.location.href = "{{ url('/asesores') }}";
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                // Redirigir de todas formas
                                window.location.href = "{{ url('/asesores') }}";
                            });
                    } else if (pertenece.includes('recordatorio')) {
                        window.location.href = "{{ url('/recordatorio') }}";
                    }
                });
            });
        });
    </script>


    @yield('scripts')
</body>

</html>
