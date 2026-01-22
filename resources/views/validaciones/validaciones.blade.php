@extends('layout.nav')

@section('title', 'Asignar Permisos a Usuario')

@section('content')

    <div class=" px-3">

        <form class="row" method="GET" action="{{ route('validaciones.index') }}">

            <div class="col-md-3">
                <label for="usuario" class="form-label">Seleccionar Usuario</label>
                <select name="usuario_id" id="usuario" class="form-select" onchange="this.form.submit()">
                    <option value="" selected disabled>Selecciona un usuario</option>
                    @foreach ($usuarios as $usuario)
                        <option value="{{ $usuario->id }}"
                            {{ isset($usuario_id) && $usuario_id == $usuario->id ? 'selected' : '' }}>
                            {{ $usuario->username }} ({{ $usuario->name }})
                        </option>
                    @endforeach
                </select>
            </div>
        </form>


        <form method="POST" action="{{ route('validaciones.store') }}">
            @csrf
            <input type="hidden" name="usuario_id" value="{{ $usuario_id }}">


            <br>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-12 pb-1">

                            <div class="list-group">
                                @foreach ($nav as $navs)
                                    <div>
                                        <a href="#{{ $navs->menu }}" class="list-group-item list-group-item-action">
                                            {{ $navs->menu }}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary w-100"
                                onclick="return validarUsuarioSeleccionado()">Guardar</button>
                        </div>
                    </div>

                </div>
                <div class="col-md-9">
                    <div class=" border rounded p-3  overflow-auto" style="height: 465px;">
                        @foreach ($nav as $navs)
                            <div class="border rounded p-2">
                                <div>
                                    <div class="form-check col-md-3 nav_validacion">

                                        <label class="form-check-label menu_validacion"
                                            for="menu_{{ $navs->id }}"id="{{ $navs->menu }}">
                                            {{ $navs->menu }}
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    @php
                                        $seccion = '-';
                                    @endphp
                                    @foreach ($vistas as $vista)
                                        @if ($vista->Seccion != $seccion && $vista->menu_id == $navs->id)
                                            @php
                                                $seccion = $vista->Seccion;
                                            @endphp
                                            <label for="" class=" titulos_validacion form-label ">
                                                {{ $vista->Seccion }}
                                            </label>
                                            @foreach ($vistas as $vista)
                                                @if ($vista->menu_id == $navs->id && $vista->Seccion == $seccion)
                                                    <div class="form-check col-md-6 px-5">
                                                        <input class="form-check-input" type="checkbox" name="vistas[]"
                                                            value="{{ $navs->id . '|' . $vista->id }}"
                                                            id="vista_{{ $vista->id }}"
                                                            {{ $permisos->where('vista_id', $vista->id)->isNotEmpty() ? 'checked' : '' }}>
                                                        <label class="vistas_validacion form-check-label"
                                                            for="vista_{{ $vista->id }}">
                                                            {{ $vista->nombre_visual }}
                                                        </label>
                                                        <div class="row">
                                                            @foreach ($botones as $boton)
                                                                @if ($boton->vista_id == $vista->id)
                                                                    <div class=" col-md-6 px-5">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="botones[]"
                                                                            value="{{ $navs->id . '|' . $vista->id . '|' . $boton->id }}"
                                                                            id="boton_{{ $boton->id }}"
                                                                            {{ $permisos->where('boton_id', $boton->id)->isNotEmpty() ? 'checked' : '' }}>
                                                                        <label class="botones_validacion form-check-label"
                                                                            for="boton_{{ $boton->id }}">
                                                                            {{ $boton->nombre_visual }}
                                                                        </label>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                            <div class="row">
                                                                @if ($vista->Seccion == 'Agenda')
                                                                    <label class="titulos_validacion">Sectores</label>

                                                                        <div class="sectores-container row">
                                                                            @foreach ($sectores as $sector)
                                                                                <div class="form-check col-md-4 ps-5">
                                                                                    <input class="form-check-input"
                                                                                        type="checkbox" name="sectores[]"
                                                                                        value="{{ $sector->id }}"
                                                                                        id="sector_{{ str_replace(' ', '_', strtolower($sector->nombre)) }}"
                                                                                        {{ isset($sectoresAsignados) && in_array($sector->id, $sectoresAsignados) ? 'checked' : '' }}>
                                                                                    <label class="form-check-label botones_validacion"
                                                                                        for="sector_{{ str_replace(' ', '_', strtolower($sector->nombre)) }}">
                                                                                        {{ $sector->nombre }}
                                                                                    </label>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>

                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                </div>

                            </div>
                            <br>
                        @endforeach
                    </div>
                </div>
            </div>
        </form>

    </div>

    <script>
        function validarUsuarioSeleccionado() {
            const usuarioSeleccionado = document.getElementById('usuario').value;
            if (!usuarioSeleccionado) {
                alert('Por favor, selecciona un usuario antes de guardar.');
                return false;
            }
            return true;
        }
    </script>
@endsection
