@extends('layout.nav')
@section('title', 'Llamar Turnos')
@section('content')
    <div class="px-3">
        <div class="row justify-content-center text-center">
            <div class="col-md-6">
                <div class="card turnosCardToma" id="turnos-card-tomar">
                    <div class="card-header turnosTitulosToma">Llamado de Turnos</div>
                    <div class="card-body">
                        <form action="{{ route('turnos.llamado') }}" method="GET" autocomplete="off">
                            <div class="form-group mb-3">
                                <label for="sector">Sector</label>
                                <select class="form-control" id="sector" name="sector" required
                                    onchange="this.form.submit()">
                                    <option value="">Seleccione un sector...</option>
                                    @foreach ($sectores as $sector)
                                        <option value="{{ $sector->id }}"
                                            {{ isset($sectorSeleccionado) && $sectorSeleccionado == $sector->id ? 'selected' : '' }}>
                                            {{ $sector->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>


                        <div id="contenedor_form_llamar">
                            @include('turnos.componentes._form_llamarTurnos')
                        </div>

                        <div id="contenedor_form_llamarTurnosAFinalizar">
                            @include('turnos.componentes._form_llamarTurnosAFinalizar')
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card turnosCard" id="turnos-card-pendientes">
                    <div class="card-header turnosTitulos">Turnos Pendientes</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tablaDatos">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Tipo</th>
                                        <th>Sector</th>
                                        <th>Ingreso</th>
                                    </tr>
                                </thead>
                                <tbody>
                                     {{-- @php
                                       //quiero ordenar la variable por el dato created_at
                                       $turnos = $turnos->sortBy('created_at');
                                    @endphp  --}}
                                    @foreach ($turnos as $turno)
                                   {{--  @dd($turnos) --}}    
                                        <tr>
                                            <td>{{ $turno->numero_identificador }}</td>
                                            <td>{{ $turno->tipo_identificador }}</td>
                                            <td>{{ $turno->sector()->first()->nombre ?? 'Sin sector' }}</td>
                                            <td>{{ $turno->fecha_carga ? \Carbon\Carbon::parse($turno->fecha_carga)->format('H:i') : '' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <input type="hidden" class="btn btn-primary" value="Solicitar permiso de notificaciones"
                onclick="WebNotifications.askForPermission();" />
            <br /><br />
            <input type="hidden" class="btn btn-success" value="Mostrar notificación de prueba"
                onclick="showNotification();" />
            <div id="msgs" style="color:red;font-size:small;margin-top:20px;"></div>

        </div>
    </div>

{{--     <script>
        // Variables globales para controlar los turnos
        let turnosAnteriores = new Set(); // Almacena los números de turnos que ya conocemos
        let notificacionesActivadas = false;

        // Solo notificar si la pestaña no está visible o la ventana no tiene foco
        function shouldNotify() {
            // document.hidden cubre pestaña en background/minimizada; hasFocus cubre pérdida de foco
            return (typeof document.hidden !== 'undefined' ? document.hidden : false) || !document.hasFocus();
        }

        // Función para inicializar el sistema de notificaciones
        function inicializarNotificaciones() {
            // Solicitar permisos automáticamente al cargar la página
            if ("Notification" in window) {
                if (Notification.permission === "default") {
                    Notification.requestPermission().then(function(permission) {
                        if (permission === "granted") {
                            notificacionesActivadas = true;
                            //console.log(" Notificaciones activadas");
                            // Cargar turnos iniciales sin notificar
                            cargarTurnosIniciales();
                        } else {
                            console.log(" Permisos de notificación denegados");
                        }
                    });
                } else if (Notification.permission === "granted") {
                    notificacionesActivadas = true;
                    //console.log(" Notificaciones ya estaban activadas");
                    // Cargar turnos iniciales sin notificar
                    cargarTurnosIniciales();
                }
            }
        }

        // Cargar turnos iniciales (primera carga, sin notificaciones)
        function cargarTurnosIniciales() {
            const tabla = document.querySelector('#tablaDatos tbody');
            if (tabla) {
                const filas = tabla.querySelectorAll('tr');
                filas.forEach(fila => {
                    const numeroTurno = fila.querySelector('td:first-child')?.textContent?.trim();
                    if (numeroTurno) {
                        turnosAnteriores.add(numeroTurno);
                    }
                });
                //console.log(" Turnos iniciales cargados:", Array.from(turnosAnteriores));
            }
        }

        // Función para detectar nuevos turnos
        function detectarNuevosTurnos() {
            const tabla = document.querySelector('#tablaDatos tbody');
            if (!tabla) return;

            const turnosActuales = new Set();
            const filas = tabla.querySelectorAll('tr');

            // Recopilar todos los números de turno actuales
            filas.forEach(fila => {
                const numeroTurno = fila.querySelector('td:first-child')?.textContent?.trim();
                const tipoTurno = fila.querySelector('td:nth-child(2)')?.textContent?.trim();
                const sectorTurno = fila.querySelector('td:nth-child(3)')?.textContent?.trim();

                if (numeroTurno) {
                    turnosActuales.add(numeroTurno);

                    // Si es un turno nuevo y las notificaciones están activadas
                    if (!turnosAnteriores.has(numeroTurno) && notificacionesActivadas && turnosAnteriores.size >
                        0) {
                        //console.log(" Nuevo turno detectado:", numeroTurno);
                        mostrarNotificacionNuevoTurno(numeroTurno, tipoTurno, sectorTurno);
                    }
                }
            });

            // Actualizar la lista de turnos conocidos
            turnosAnteriores = new Set(turnosActuales);
        }

        // Función para mostrar notificación de nuevo turno
        function mostrarNotificacionNuevoTurno(numero, tipo, sector) {
            if (!("Notification" in window) || Notification.permission !== "granted") {
                return;
            }

            // Evitar mostrar si el usuario ya está mirando esta pestaña activa
            if (!shouldNotify()) {
                return;
            }

            // Crear la notificación
            const notif = new Notification("🔔 Nuevo Turno Disponible", {
                body: `Turno: ${numero}\nSector: ${sector}`,
                icon: "/path/to/icon.png", // Cambia por tu ruta de icono
                requireInteraction: true, // Mantiene visible hasta que el usuario interactúe
                tag: `turno-${numero}`, // Evita duplicados del mismo turno
                silent: false // Permite sonido
            });

            // Manejar clic en la notificación
            notif.onclick = function() {
                window.focus(); // Traer la ventana al frente
                notif.close();

                // Opcional: scroll hasta la tabla de turnos
                document.querySelector('#turnos-card-pendientes')?.scrollIntoView({
                    behavior: 'smooth'
                });
            };

            // Cerrar automáticamente después de 10 segundos (opcional)
           /*  setTimeout(() => {
                if (notif) {
                    notif.close();
                }
            }, 10000); */

            //console.log("🔔 Notificación mostrada para turno:", numero);
        }

        // Función refrescarTodo modificada para incluir detección de nuevos turnos
        function refrescarTodo() {
            const sectorSeleccionado = document.getElementById("sector")?.value;

            // Refrescar Turnos Pendientes
            fetch(`{{ route('turnos.llamado') }}?sector=${sectorSeleccionado}`)
                .then(response => response.text())
                .then(html => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');
                    let nuevaPendientes = doc.querySelector('#turnos-card-pendientes');

                    if (nuevaPendientes) {
                        document.querySelector('#turnos-card-pendientes').innerHTML = nuevaPendientes.innerHTML;

                        // 🔥 AQUÍ ES DONDE DETECTAMOS LOS NUEVOS TURNOS
                        detectarNuevosTurnos();
                    }
                })
                .catch(error => console.error("Error actualizando turnos pendientes:", error));

            // Actualizar Turnos a Llamar
            const contenedorLlamar = document.getElementById("contenedor_form_llamar");
            if (contenedorLlamar && sectorSeleccionado) {
                fetch(`{{ route('turnos.pendientesAllamar') }}?sector=${sectorSeleccionado}`)
                    .then(response => {
                        if (!response.ok) throw new Error("Error de servidor");
                        return response.text();
                    })
                    .then(html => {
                        contenedorLlamar.innerHTML = html;
                    })
                    .catch(error => console.error("Error actualizando turnos a llamar:", error));
            }

            // Actualizar Turnos a Finalizar
            const contenedorFinalizar = document.getElementById("contenedor_form_llamarTurnosAFinalizar");
            if (contenedorFinalizar && sectorSeleccionado) {
                fetch(`{{ route('turnos.pendientesAFinalizar') }}?sector=${sectorSeleccionado}`)
                    .then(response => {
                        if (!response.ok) throw new Error("Error de servidor");
                        return response.text();
                    })
                    .then(html => {
                        contenedorFinalizar.innerHTML = html;
                    })
                    .catch(error => console.error("Error actualizando turnos a finalizar:", error));
            }
        }

        // Función para mostrar notificación manual (mantienes tu función original)
        function showNotification() {
            if (!("Notification" in window)) {
                alert("Este navegador no soporta notificaciones.");
                return;
            }

            if (Notification.permission === "granted") {
                // Respetar condición: solo notificar si no está visible/enfocada
                if (!shouldNotify()) {
                    console.log("Omitiendo notificación porque la pestaña está visible y enfocada.");
                    return;
                }
                const notif = new Notification("Notificación de Prueba", {
                    body: "¡Esto es una prueba!",
                    requireInteraction: true
                });

                notif.onclick = function() {
                    window.focus();
                    notif.close();
                };
            } else if (Notification.permission !== "denied") {
                Notification.requestPermission().then(function(perm) {
                    if (perm === "granted") {
                        showNotification();
                    }
                });
            }
        }

        // 🚀 INICIALIZAR TODO AL CARGAR LA PÁGINA
        document.addEventListener('DOMContentLoaded', function() {
            //console.log("🔄 Inicializando sistema de notificaciones automáticas...");

            // Inicializar notificaciones
            inicializarNotificaciones();

            // Ejecutar refrescado cada 1 segundo (como ya tienes)
            setInterval(refrescarTodo, 1000);

            //console.log("✅ Sistema iniciado correctamente");
        });

        // Función para debug - puedes llamarla desde la consola
        function verTurnosConocidos() {
            console.log("Turnos conocidos:", Array.from(turnosAnteriores));
        }
    </script> --}}


<script>
        // Variables globales para controlar los turnos
        let turnosAnteriores = new Set(); // Almacena los números de turnos que ya conocemos
        let notificacionesActivadas = false;
        let primeraVez = true; // Nueva variable para controlar la primera carga

        // Solo notificar si la pestaña no está visible o la ventana no tiene foco
        function shouldNotify() {
            // document.hidden cubre pestaña en background/minimizada; hasFocus cubre pérdida de foco
            return (typeof document.hidden !== 'undefined' ? document.hidden : false) || !document.hasFocus();
        }

        // Función para inicializar el sistema de notificaciones
        function inicializarNotificaciones() {
            // Solicitar permisos automáticamente al cargar la página
            if ("Notification" in window) {
                if (Notification.permission === "default") {
                    Notification.requestPermission().then(function(permission) {
                        if (permission === "granted") {
                            notificacionesActivadas = true;
                            //console.log(" Notificaciones activadas");
                            // Cargar turnos iniciales sin notificar
                            cargarTurnosIniciales();
                        } else {
                            console.log(" Permisos de notificación denegados");
                        }
                    });
                } else if (Notification.permission === "granted") {
                    notificacionesActivadas = true;
                    //console.log(" Notificaciones ya estaban activadas");
                    // Cargar turnos iniciales sin notificar
                    cargarTurnosIniciales();
                }
            }
        }

        // Cargar turnos iniciales (primera carga, sin notificaciones)
        function cargarTurnosIniciales() {
            const tabla = document.querySelector('#tablaDatos tbody');
            if (tabla) {
                const filas = tabla.querySelectorAll('tr');
                filas.forEach(fila => {
                    const numeroTurno = fila.querySelector('td:first-child')?.textContent?.trim();
                    if (numeroTurno) {
                        turnosAnteriores.add(numeroTurno);
                    }
                });
                //console.log(" Turnos iniciales cargados:", Array.from(turnosAnteriores));
                primeraVez = false; // Marcamos que ya pasó la primera carga
            }
        }

        // Función para detectar nuevos turnos
        function detectarNuevosTurnos() {
            const tabla = document.querySelector('#tablaDatos tbody');
            if (!tabla) return;

            const turnosActuales = new Set();
            const filas = tabla.querySelectorAll('tr');

            // Recopilar todos los números de turno actuales
            filas.forEach(fila => {
                const numeroTurno = fila.querySelector('td:first-child')?.textContent?.trim();
                const tipoTurno = fila.querySelector('td:nth-child(2)')?.textContent?.trim();
                const sectorTurno = fila.querySelector('td:nth-child(3)')?.textContent?.trim();

                if (numeroTurno) {
                    turnosActuales.add(numeroTurno);

                    // Si es un turno nuevo y las notificaciones están activadas
                    // CAMBIO PRINCIPAL: Removida la condición turnosAnteriores.size > 0
                    // Agregada la condición !primeraVez para evitar notificar en la carga inicial
                    if (!turnosAnteriores.has(numeroTurno) && notificacionesActivadas && !primeraVez) {
                        //console.log(" Nuevo turno detectado:", numeroTurno);
                        mostrarNotificacionNuevoTurno(numeroTurno, tipoTurno, sectorTurno);
                    }
                }
            });

            // Actualizar la lista de turnos conocidos
            turnosAnteriores = new Set(turnosActuales);
        }

        // Función para mostrar notificación de nuevo turno
        function mostrarNotificacionNuevoTurno(numero, tipo, sector) {
            if (!("Notification" in window) || Notification.permission !== "granted") {
                return;
            }

            // Evitar mostrar si el usuario ya está mirando esta pestaña activa
            if (!shouldNotify()) {
                return;
            }

            // Crear la notificación
            const notif = new Notification("🔔 Nuevo Turno Disponible", {
                body: `Turno: ${numero}\nSector: ${sector}`,
                icon: "/path/to/icon.png", // Cambia por tu ruta de icono
                requireInteraction: true, // Mantiene visible hasta que el usuario interactúe
                tag: `turno-${numero}`, // Evita duplicados del mismo turno
                silent: false // Permite sonido
            });

            // Manejar clic en la notificación
            notif.onclick = function() {
                window.focus(); // Traer la ventana al frente
                notif.close();

                // Opcional: scroll hasta la tabla de turnos
                document.querySelector('#turnos-card-pendientes')?.scrollIntoView({
                    behavior: 'smooth'
                });
            };

            // Cerrar automáticamente después de 10 segundos (opcional)
           /*  setTimeout(() => {
                if (notif) {
                    notif.close();
                }
            }, 10000); */

            //console.log("🔔 Notificación mostrada para turno:", numero);
        }

        // Función refrescarTodo modificada para incluir detección de nuevos turnos
        function refrescarTodo() {
            const sectorSeleccionado = document.getElementById("sector")?.value;

            // Refrescar Turnos Pendientes
            fetch(`{{ route('turnos.llamado') }}?sector=${sectorSeleccionado}`)
                .then(response => response.text())
                .then(html => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');
                    let nuevaPendientes = doc.querySelector('#turnos-card-pendientes');

                    if (nuevaPendientes) {
                        document.querySelector('#turnos-card-pendientes').innerHTML = nuevaPendientes.innerHTML;

                        // 🔥 AQUÍ ES DONDE DETECTAMOS LOS NUEVOS TURNOS
                        detectarNuevosTurnos();
                    }
                })
                .catch(error => console.error("Error actualizando turnos pendientes:", error));

            // Actualizar Turnos a Llamar
            const contenedorLlamar = document.getElementById("contenedor_form_llamar");
            if (contenedorLlamar && sectorSeleccionado) {
                fetch(`{{ route('turnos.pendientesAllamar') }}?sector=${sectorSeleccionado}`)
                    .then(response => {
                        if (!response.ok) throw new Error("Error de servidor");
                        return response.text();
                    })
                    .then(html => {
                        contenedorLlamar.innerHTML = html;
                    })
                    .catch(error => console.error("Error actualizando turnos a llamar:", error));
            }

            // Actualizar Turnos a Finalizar
            const contenedorFinalizar = document.getElementById("contenedor_form_llamarTurnosAFinalizar");
            if (contenedorFinalizar && sectorSeleccionado) {
                fetch(`{{ route('turnos.pendientesAFinalizar') }}?sector=${sectorSeleccionado}`)
                    .then(response => {
                        if (!response.ok) throw new Error("Error de servidor");
                        return response.text();
                    })
                    .then(html => {
                        contenedorFinalizar.innerHTML = html;
                    })
                    .catch(error => console.error("Error actualizando turnos a finalizar:", error));
            }
        }

        // Función para mostrar notificación manual (mantienes tu función original)
        function showNotification() {
            if (!("Notification" in window)) {
                alert("Este navegador no soporta notificaciones.");
                return;
            }

            if (Notification.permission === "granted") {
                // Respetar condición: solo notificar si no está visible/enfocada
                if (!shouldNotify()) {
                    console.log("Omitiendo notificación porque la pestaña está visible y enfocada.");
                    return;
                }
                const notif = new Notification("Notificación de Prueba", {
                    body: "¡Esto es una prueba!",
                    requireInteraction: true
                });

                notif.onclick = function() {
                    window.focus();
                    notif.close();
                };
            } else if (Notification.permission !== "denied") {
                Notification.requestPermission().then(function(perm) {
                    if (perm === "granted") {
                        showNotification();
                    }
                });
            }
        }

        // 🚀 INICIALIZAR TODO AL CARGAR LA PÁGINA
        document.addEventListener('DOMContentLoaded', function() {
            //console.log("🔄 Inicializando sistema de notificaciones automáticas...");

            // Inicializar notificaciones
            inicializarNotificaciones();

            // Ejecutar refrescado cada 1 segundo (como ya tienes)
            setInterval(refrescarTodo, 1000);

            //console.log("✅ Sistema iniciado correctamente");
        });

        // Función para debug - puedes llamarla desde la consola
        function verTurnosConocidos() {
            console.log("Turnos conocidos:", Array.from(turnosAnteriores));
        }
    </script>

@endsection
