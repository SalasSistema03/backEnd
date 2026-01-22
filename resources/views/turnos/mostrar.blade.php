@extends('layout.notnav')
@section('title', 'Visualizador')
@section('content')
    <div class="px-3">
        <div class="row justify-content-center text-center">
            <div class="col-md-12">
                <div id="turnos-card">
                    {{-- <div class="card-header">Turnos Llamados</div> --}}
                    <div class="card-body">

                        @if (isset($turno) && $turno)
                            {{-- @dump($turno) --}}
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="turnosTitulosMostrar">Sector</th>
                                        <th class="turnosTitulosMostrar">Tipo</th>
                                        <th class="turnosTitulosMostrar">Número</th>
                                        <th class="turnosTitulosMostrar">Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($turno as $turnos)
                                        <tr>
                                            <td class="turnosMostrar p-0">
                                                {{ $turnos->sector()->first()->nombre ?? 'Sin sector' }}</td>
                                            <td class="turnosMostrar p-0">{{ $turnos->tipo_identificador ?? 'Sin sector' }}
                                            </td>
                                            <td class="turnosMostrarNumero  p-0">
                                                {{ $turnos->numero_identificador ?? 'Sin sector' }}</td>
                                            <td class="turnosMostrar p-0">{{ $turnos->usuario_id ?? 'Sin Usuario' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{-- @foreach ($turno as $turnos)
                                
                            
                                {{ $turnos->sector()->first()->nombre ?? 'Sin sector' }}
                                {{ $turnos->tipo_identificador ?? 'Sin sector' }}
                                {{ $turnos->numero_identificador ?? 'Sin sector' }}
                                {{ $turnos->usuario_id ?? 'Sin Usuario' }}
                            @endforeach --}}



                            {{-- @dump($turno) --}}
                        @endif
                        {{-- <h5>Turno: {{ $turno-> }} )</h5>
                        <p>Sector: {{ $turno->sector }}</p>
                        <p>Fecha de carga: {{ $turno->fecha_carga }}</p>
                        <form action="{{ route('turnos.finalizar', $turno->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-danger">Finalizar</button>
                        </form> --}}

                    </div>
                </div>
            </div>
        </div>
    </div>
    <audio id="notification-sound" preload="auto">
        <source src="{{ asset('sound/notification.wav') }}" type="audio/wav">
        Tu navegador no soporta el elemento de audio.
    </audio>

    <script>
        // Cargar el audio al inicio
        document.addEventListener('DOMContentLoaded', function() {
            const sound = document.getElementById('notification-sound');
            // Cargar el sonido
            sound.load();
            console.log('Audio cargado:', sound.readyState);
        });
        function refrescarCard() {
            fetch("{{ route('turnos.mostrar') }}")
                .then(response => response.text())
                .then(html => {
                    // Extrae solo el contenido de la card
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');
                    let nuevaCard = doc.querySelector('#turnos-card');
                    if (nuevaCard) {
                        document.querySelector('#turnos-card').innerHTML = nuevaCard.innerHTML;
                    }
                });
        }
        setInterval(refrescarCard, 1000);
    </script>

    {{-- scrip para adaptar sonido --}}
    <script>
        let lastTbodyHtml = "";

        function playSound() {
            try {
                const sound = document.getElementById('notification-sound');
                console.log('Reproduciendo sonido...');
                sound.currentTime = 0; // Reiniciar el sonido si ya se está reproduciendo
                sound.play().then(() => {
                    console.log('Sonido reproducido con éxito');
                }).catch(error => {
                    console.error('Error al reproducir el sonido:', error);
                    // Intentar reproducir con un clic del usuario (requerido por algunos navegadores)
                    document.addEventListener('click', function onClick() {
                        sound.play().catch(e => console.error('Error en segundo intento:', e));
                        document.removeEventListener('click', onClick);
                    });
                });
            } catch (e) {
                console.error('Error en playSound:', e);
            }
        }


        let ultimoContenido = '';

        function refrescarCard() {
            console.log('Actualizando tarjeta...');
            fetch("{{ route('turnos.mostrar') }}")
                .then(response => {
                    if (!response.ok) throw new Error('Error en la respuesta del servidor');
                    return response.text();
                })
                .then(html => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');
                    let nuevaCard = doc.querySelector('#turnos-card');
                    
                    if (nuevaCard) {
                        let nuevoContenido = nuevaCard.innerHTML;
                        
                        // Verificar si hay cambios
                        if (nuevoContenido !== ultimoContenido) {
                            console.log('Cambio detectado en el contenido');
                            
                            // Verificar si hay turnos en la nueva tarjeta
                            let nuevoTbody = nuevaCard.querySelector('tbody');
                            if (nuevoTbody && nuevoTbody.textContent.trim() !== '') {
                                console.log('Reproduciendo sonido para nuevo turno');
                                playSound();
                            }
                            
                            // Actualizar el contenido
                            document.querySelector('#turnos-card').innerHTML = nuevoContenido;
                            ultimoContenido = nuevoContenido;
                        } else {
                            console.log('Sin cambios en el contenido');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error al actualizar la tarjeta:', error);
                });
        }
        setInterval(refrescarCard, 10000);
    </script>

@endsection
