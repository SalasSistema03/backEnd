@if (isset($turnosPendientes) && $turnosPendientes->count())
    @php
        $turnoMasViejo = $turnosPendientes->sortBy('created_at')->first();
    @endphp

    @if ($turnoMasViejo)
        <form action="{{ route('turnos.llamar') }}" method="POST" id="form_llamar"
            style="display:inline;margin-top:5px" autocomplete="off">
            @csrf
            <input type="hidden" name="turno_id" value="{{ $turnoMasViejo->id }}">
            <button type="submit" class="btn btn-success btn-sm">Llamar</button>
            <span class="text-muted">
                - Turno: {{ $turnoMasViejo->numero_identificador }}
            </span>
        </form>
    @endif
@else
    <p class="text-muted">No hay turnos pendientes.</p>
@endif