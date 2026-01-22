<h6>Turnos a finalizar</h6>
@if (isset($turnosPendientes) && $turnosPendientes->count())
    @php
        $usuarioId = session('usuario_id');
    @endphp

    @foreach ($turnosPendientes->sortBy('fecha_carga') as $turno)
        <div class="mb-2">
            
            <span class="text-muted">Turno: {{ $turno->numero_identificador }}</span>

            @if ($turno->tomo_usuario_id == $usuarioId)
                <form action="{{ route('turnos.finalizar', $turno->id) }}" method="POST" style="display:inline;" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-primary btn-sm">Finalizar</button>
                </form>
            @endif
        </div>
    @endforeach
@else
    <p class="text-muted">No hay turnos llamados.</p>
@endif

