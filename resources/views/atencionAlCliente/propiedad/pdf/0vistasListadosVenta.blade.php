@extends('layout.nav')
@section('title', 'Listar Venta')
@section('content')

    <div class="d-flex">
        <!-- Contenedor Izquierdo: Listado con Scroll -->
        <div class="left-panel col-3">
            <h2 class="h5 mb-4">Opciones</h2>
            <div>
                {{-- <div class="option-button" onclick="loadForm('contacto')">
                    Listar Propiedades General
                </div> --}}
                <div class="option-button" onclick="loadForm('listado-propiedades')">
                    Listar Propiedades
                </div>
                <div class="option-button" onclick="loadForm('registro')">
                    Listar Propietarios
                </div>
                <div class="option-button" onclick="loadForm('criterios-activos')">
                    Listar Criterios Activos
                </div>
                <div class="option-button" onclick="loadForm('ofrecimiento')">
                    Listar Ofrecimiento
                </div>
                <div class="option-button" onclick="loadForm('devoluciones')">
                    Listar Devoluciones
                </div>
                <div class="option-button" onclick="loadForm('conversaciones')">
                    Listar Conversaciones
                </div>
            </div>
        </div>

        <!-- Contenedor Derecho: Contenido Estático (Formularios Predefinidos) -->
        <div class="right-panel col-9">
            <div id="content">
                <!-- Mensaje de Bienvenida -->
                <div id="welcome" class="welcome">
                    <h1 class="mb-4">Bienvenido</h1>
                    <p>Selecciona una opción del listado a la izquierda para cargar un formulario.</p>
                </div>

                <!-- Formulario de Contacto -->
                {{-- <div id="contacto" class="form-section">
                    <div class="card border-primary mx-2">
                        <div class="card-header bg-transparent border-primary">
                            <label for="">Listar Propiedades General</label>
                        </div>
                        <div class="card-body text-primary">
                            <form class="row" id="formEstado" method="GET"
                                action="{{ route('propiedades.Venta.Estados-view') }}" target="_blank" autocomplete="off">

                                <div class="col-md-6">
                                    <label class="form-label" for="search-calle">Calle</label>
                                    <input type="text" id="search-calle"
                                        class="form-control @error('calle') is-invalid @enderror"
                                        placeholder="Buscar calle..."
                                        value="{{ optional(App\Models\At_cl\Calle::find(old('calle')))->name }}">
                                    <input type="hidden" id="calle_id" name="calle" value="{{ old('calle') }}">
                                    <div id="search-results" class="list-group mt-2"
                                        style="position: absolute; z-index: 1000;">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="search-calle">Zona</label>
                                    @php
                                        $selectedZonas = (array) request('zona');
                                        $selectedZonas = array_values(
                                            array_filter($selectedZonas, fn($v) => $v !== null && $v !== ''),
                                        );
                                        $selectedZonasCount = count($selectedZonas);
                                    @endphp
                                    <div class="dropdown w-100">
                                        <button class="form-control text-start dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                                            id="zonaDropdownBtn">
                                            {{ $selectedZonasCount > 0 ? 'Seleccionar zonas (' . $selectedZonasCount . ')' : 'Seleccionar zonas' }}
                                        </button>
                                        <div class="dropdown-menu p-2 w-100"
                                            style="max-height: 280px; overflow:auto; min-width: 260px;">
                                            <div id="zonaList">
                                                @foreach ($zonas as $z)
                                                    <div class="form-check zona-item">
                                                        <input class="form-check-input zona-checkbox" type="checkbox"
                                                            name="zona[]" id="zona_{{ $z->id }}"
                                                            value="{{ $z->id }}"
                                                            {{ in_array($z->id, (array) request('zona')) ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="zona_{{ $z->id }}">{{ $z->name }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="" class="form-label">Tipo</label>
                                    <select name="tipo" id="tipo" class="form-control">
                                        <option value="">Seleccione un inmueble</option>
                                        @foreach ($tipos as $tipo)
                                            <option value="{{ $tipo->id }}">{{ $tipo->inmueble }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="" class="form-label">Estado</label>
                                    <input type="hidden" name="tipoPdf" value="">
                                    <select name="estado_id" id="estado_id" class="form-control">
                                        <option value="">Seleccione un estado</option>
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado->id }}">{{ $estado->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="" class="form-label">Importe desde</label>
                                    <input type="number"
                                        class="form-control  @error('importe_minimo') is-invalid @enderror"
                                        id="importe_minimo" name="importe_minimo" min="0"
                                        placeholder="Importe mínimo" value="{{ request('importe_minimo') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="" class="form-label">Importe hasta</label>
                                    <input type="number"
                                        class="form-control @error('importe_maximo') is-invalid @enderror"
                                        id="importe_maximo" name="importe_maximo" min="0"
                                        placeholder="Importe máximo" value="{{ request('importe_maximo') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="" class="form-label">Ordenar por</label>
                                    <select name="orden" id="orden" class="form-control">
                                        <option value="">Sin orden</option>
                                        <option value="precio_asc">Precio (menor a mayor)</option>
                                        <option value="precio_desc">Precio (mayor a menor)</option>
                                        <option value="estado">Estado</option>
                                        <option value="tipo">Tipo de inmueble</option>
                                        <option value="zona">Zona</option>
                                        <option value="calle">Calle</option>
                                        <option value="codigo">Codigo</option>
                                    </select>
                                </div>
                                <input type="hidden" name="pertenece" value="estadosVentaGeneral">
                                @if ($tieneAccesoInformacionVenta)
                                    <div class="col-md-12 bg-transparent border-primary mt-2">
                                        <button type="submit" class="btn btn-primary w-100 mt-2">Listar</button>
                                    </div>
                                @else
                                    <div class="col-md-12 bg-transparent border-primary mt-2">
                                        <button type="submit" class="btn btn-primary w-100 mt-2" disabled>Listar</button>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div> --}}

                <!-- Formulario de Contacto -->
                <div id="listado-propiedades" class="form-section">
                    <div class="card border-primary mx-2">
                        <div class="card-header bg-transparent border-primary">
                            <label for="">Listar Propiedades </label>
                        </div>
                        <div class="card-body text-primary">
                            <form class="row" id="formEstado" method="GET"
                                action="{{ route('propiedades.Venta.Estados-view') }}" target="_blank"
                                autocomplete="off">

                                <div class="col-md-6">
                                    <label class="form-label" for="search-calle">Calle</label>
                                    <input type="text" id="search-calle"
                                        class="form-control @error('calle') is-invalid @enderror"
                                        placeholder="Buscar calle..."
                                        value="{{ optional(App\Models\At_cl\Calle::find(old('calle')))->name }}">
                                    <input type="hidden" id="calle_id" name="calle" value="{{ old('calle') }}">
                                    <div id="search-results" class="list-group mt-2"
                                        style="position: absolute; z-index: 1000;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="search-calle">Zona</label>
                                    @php
                                        $selectedZonas = (array) request('zona');
                                        $selectedZonas = array_values(
                                            array_filter($selectedZonas, fn($v) => $v !== null && $v !== ''),
                                        );
                                        $selectedZonasCount = count($selectedZonas);
                                    @endphp
                                    <div class="dropdown w-100">
                                        <button class="form-control text-start dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                                            id="zonaDropdownBtn">
                                            {{ $selectedZonasCount > 0 ? 'Seleccionar zonas (' . $selectedZonasCount . ')' : 'Seleccionar zonas' }}
                                        </button>
                                        <div class="dropdown-menu p-2 w-100"
                                            style="max-height: 280px; overflow:auto; min-width: 260px;">
                                            <div id="zonaList">
                                                @foreach ($zonas as $z)
                                                    <div class="form-check zona-item">
                                                        <input class="form-check-input zona-checkbox" type="checkbox"
                                                            name="zona[]" id="zona_{{ $z->id }}"
                                                            value="{{ $z->id }}"
                                                            {{ in_array($z->id, (array) request('zona')) ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="zona_{{ $z->id }}">{{ $z->name }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="" class="form-label">Tipo</label>
                                    <select name="tipo" id="tipo" class="form-control">
                                        <option value="">Seleccione un inmueble</option>
                                        @foreach ($tipos as $tipo)
                                            <option value="{{ $tipo->id }}">{{ $tipo->inmueble }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="" class="form-label">Estado</label>
                                    <input type="hidden" name="tipoPdf" value="">
                                    <select name="estado_id" id="estado_id" class="form-control">
                                        <option value="">Seleccione un estado</option>
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado->id }}">{{ $estado->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="" class="form-label">Importe desde</label>
                                    <input type="number"
                                        class="form-control  @error('importe_minimo') is-invalid @enderror"
                                        id="importe_minimo" name="importe_minimo" min="0"
                                        placeholder="Importe mínimo" value="{{ request('importe_minimo') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="" class="form-label">Importe hasta</label>
                                    <input type="number"
                                        class="form-control @error('importe_maximo') is-invalid @enderror"
                                        id="importe_maximo" name="importe_maximo" min="0"
                                        placeholder="Importe máximo" value="{{ request('importe_maximo') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="" class="form-label">Ordenar por</label>
                                    <select name="orden" id="orden" class="form-control">
                                        <option value="">Sin orden</option>
                                        <option value="precio_asc">Precio (menor a mayor)</option>
                                        <option value="precio_desc">Precio (mayor a menor)</option>
                                        <option value="estado">Estado</option>
                                        <option value="tipo">Tipo de inmueble</option>
                                        <option value="zona">Zona</option>
                                        <option value="calle">Calle</option>
                                        <option value="codigo">Codigo</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="" class="form-label">Informacion a mostrar</label>
                                    <div class="dropdown w-100">
                                        <button class="form-control text-start dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                                            id="infoDropdownBtn">
                                            Selecciona la información a mostrar
                                        </button>
                                        <div class="dropdown-menu p-3 w-100"
                                            style="max-height: 280px; overflow:auto; min-width: 300px;">
                                            <div class="row" id="infoList">
                                                @php
                                                    $campos = [
                                                        'c_venta' => 'C. Venta',
                                                        'direccion' => 'Dirección',
                                                        'zona' => 'Zona',
                                                        'p_d' => 'P / D',
                                                        'inm' => 'Inm.',
                                                        'ph' => 'PH',
                                                        'e_venta' => 'E. Venta',
                                                        'autorizacion' => 'Autorización',
                                                        'exclusivo' => 'Exclusivo',
                                                        'condicionada' => 'Condicionada',
                                                        'comparte' => 'Comparte',
                                                        'llave' => 'Llave',
                                                        'cartel' => 'Cartel',
                                                        'precio' => 'Precio',
                                                        'propietario' => 'Propietario',
                                                        'folio' => 'Folio',
                                                        'dormitorio' => 'Dormitorio',
                                                        'cochera' => 'Cochera',
                                                        'foto' => 'Foto',
                                                        'video' => 'Video',
                                                        'documentacion' => 'Documentación',
                                                    ];
                                                @endphp

                                                @foreach ($campos as $key => $label)
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input campo-checkbox" type="checkbox"
                                                                name="campos_seleccionados[]" value="{{ $key }}"
                                                                id="campo_{{ $key }}"
                                                                {{ in_array($key, old('campos_seleccionados', [])) ? 'checked' : 'checked'}}>
                                                            <label class="form-check-label"
                                                                for="campo_{{ $key }}">
                                                                {{ $label }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="pertenece" value="listado-propiedades">
                                @if ($tieneAccesoInformacionVenta)
                                    <div class="col-md-12 bg-transparent border-primary mt-2">
                                        <button type="submit" class="btn btn-primary w-100 mt-2">Listar</button>
                                    </div>
                                @else
                                    <div class="col-md-12 bg-transparent border-primary mt-2">
                                        <button type="submit" class="btn btn-primary w-100 mt-2" disabled>Listar</button>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Registro -->
                <div id="registro" class="form-section">
                    <div class="card border-primary mx-2">
                        <div class="card-header bg-transparent border-primary">
                            <label for="">Listar Propietarios</label>
                        </div>
                        <div class="card-body text-primary">
                            <form class="row" id="formEstadosPropietario" method="GET"
                                action="{{ route('propiedades.Venta.Estados-view') }}" target="_blank"
                                autocomplete="off">
                                <div class="col-md-12">
                                    <label for="" class="form-label">Propietario</label>
                                    <input type="text" id="search-propietario" name="propietario"
                                        class="form-control" placeholder="Buscar propietarios por nombre o dni">
                                    <input type="hidden" id="propietario_id" name="propietarioo" value="">
                                    <input type="hidden" id="propiedad_id" name="propiedad_id"
                                        value="{{ session('propiedad_id') }}">
                                    <div id="search-results-prop" class="list-group mt-2"
                                        style="position: absolute; z-index: 1000;"></div>
                                </div>
                                <input type="hidden" name="pertenece" value="estadoPropietarioV">
                                @if ($tieneAccesoPropietario)
                                    <div class="col-md-12 bg-transparent border-primary mt-2">
                                        <button type="submit" class="btn btn-primary w-100 mt-2">Listar</button>
                                    </div>
                                @else
                                    <div class="col-md-12 bg-transparent border-primary mt-2">
                                        <button type="submit" class="btn btn-primary w-100 mt-2" disabled>Listar</button>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Criterios Activos -->
                <div id="criterios-activos" class="form-section">
                    <div class="card border-primary mx-2">
                        <div class="card-header bg-transparent border-primary">
                            <label for="">Listar Criterios Activos</label>
                        </div>
                        <div class="card-body text-primary">
                            <form class="row" id="formEstadosPropietario" method="GET"
                                action="{{ route('propiedades.Venta.Estados-view') }}" target="_blank"
                                autocomplete="off">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="" class="form-label">Asesor</label>
                                        <select name="asesor" id="asesor" class="form-select">
                                            <option value="">Seleccione un asesor</option>
                                            @foreach ($asesores as $asesor)
                                                <option value="{{ $asesor['id_usuario'] }}">
                                                    {{ $asesor['username'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="" class="form-label">zona</label>
                                        <select name="zona" id="zona" class="form-select">
                                            <option value="">Seleccione una zona</option>
                                            @foreach ($zonas as $zona)
                                                <option value="{{ $zona['id'] }}">{{ $zona['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>


                                    <div class="col-md-3">
                                        {{--       @dd($tipos) --}}
                                        <label for="" class="form-label">Tipo Inmueble</label>
                                        <select name="tipo_inmueble" id="tipo_inmueble" class="form-select">
                                            <option value="">Seleccione un inmueble</option>
                                            @foreach ($tipos as $tipo)
                                                <option value="{{ $tipo['id'] }}">{{ $tipo['inmueble'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label for="" class="form-label">Dormitorios</label>
                                        <input type="number" name="dormitorios" class="form-control" placeholder="0"
                                            min="0">
                                    </div>
                                </div>


                                <div class="row">

                                    <div class="col-md-3">
                                        <label for="" class="form-label">Precio Minimo</label>
                                        <input type="number" name="precio_minimo" id="precio_minimo"
                                            class="form-control" placeholder="$" min="0">
                                    </div>

                                    <div class="col-md-3">
                                        <label for="" class="form-label">Pecio Maximo</label>
                                        <input type="number" name="precio_maximo" id="precio_maximo"
                                            class="form-control" placeholder="$" min="0">
                                    </div>

                                    <div class="col-md-3">
                                        <label for="" class="form-label">Estado</label>
                                        <select name="estado" id="estado" class="form-select">
                                            <option value="">Seleccione un estado</option>
                                            <option value="Potable">Potable</option>
                                            <option value="Medio">Medio</option>
                                            <option value="No Potable">No potable</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="">
                                    <input type="hidden" name="pertenece" value="criterios-activos">
                                    @if ($tieneAccesoInformacionVentaPropiedadAsesor)
                                        <div class="col-md-12 bg-transparent border-primary mt-2">
                                            <button type="submit" class="btn btn-primary w-100 mt-2">Listar</button>
                                        </div>
                                    @else
                                        <div class="col-md-12 bg-transparent border-primary mt-2">
                                            <button type="submit" class="btn btn-primary w-100 mt-2"
                                                disabled>Listar</button>
                                        </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Ofrecimientos -->
                <div id="ofrecimiento" class="form-section">
                    <div class="card border-primary mx-2">
                        <div class="card-header bg-transparent border-primary">
                            <label for="">Listar Devoluciones</label>
                        </div>
                        <div class="card-body text-primary">
                            <form class="row" id="formOfrecimiento" method="GET"
                                action="{{ route('propiedades.Venta.Estados-view') }}" target="_blank"
                                autocomplete="off">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="" class="form-label">Desde</label>
                                        <input type="date" name="fecha_desde" id="fecha_desde" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="" class="form-label">Hasta</label>
                                        <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control"
                                            required>
                                    </div>
                                </div>
                                <div class="">
                                    <input type="hidden" name="pertenece" value="ofrecimiento">
                                    @if ($tieneAccesoInformacionVentaOfrecimiento)
                                        <div class="col-md-12 bg-transparent border-primary mt-2">
                                            <button type="submit" class="btn btn-primary w-100 mt-2">Listar</button>
                                        </div>
                                    @else
                                        <div class="col-md-12 bg-transparent border-primary mt-2">
                                            <button type="submit" class="btn btn-primary w-100 mt-2"
                                                disabled>Listar</button>
                                        </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Devoluciones-->
                <div id="devoluciones" class="form-section">
                    <div class="card border-primary mx-2">
                        <div class="card-header bg-transparent border-primary">
                            <label for="">Listar Devoluciones</label>
                        </div>
                        <div class="card-body text-primary">
                            <form class="row" id="formDevoluciones" method="GET"
                                action="{{ route('propiedades.Venta.Estados-view') }}" target="_blank"
                                autocomplete="off">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="" class="form-label">Codigo</label>
                                        <input type="number" name="codigo" id="codigo" class="form-control"
                                            min="0">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="" class="form-label">Desde</label>
                                        <input type="date" name="fecha_desde" id="fecha_desde" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="" class="form-label">Hasta</label>
                                        <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control"
                                            required>
                                    </div>
                                </div>
                                <div class="">
                                    <input type="hidden" name="pertenece" value="devoluciones">
                                    @if ($tieneAccesoInformacionVentaDevoluciones)
                                        <div class="col-md-12 bg-transparent border-primary mt-2">
                                            <button type="submit" class="btn btn-primary w-100 mt-2">Listar</button>
                                        </div>
                                    @else
                                        <div class="col-md-12 bg-transparent border-primary mt-2">
                                            <button type="submit" class="btn btn-primary w-100 mt-2"
                                                disabled>Listar</button>
                                        </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Conversaciones-->
                <div id="conversaciones" class="form-section">
                    <div class="card border-primary mx-2">
                        <div class="card-header bg-transparent border-primary">
                            <label for="">Listar Conversaciones</label>
                        </div>
                        <div class="card-body text-primary">
                            <form class="row" id="formConversaciones" method="GET"
                                action="{{ route('propiedades.Venta.Estados-view') }}" target="_blank"
                                autocomplete="off">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="" class="form-label">Asesor</label>
                                        <select name="asesor" id="asesor" class="form-select">
                                            <option value="">Seleccione un asesor</option>
                                            @foreach ($asesores as $asesor)
                                                <option value="{{ $asesor['id_usuario'] }}">
                                                    {{ $asesor['username'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="">
                                    <input type="hidden" name="pertenece" value="conversaciones">
                                    @if ($tieneAccesoInformacionVentaConversacion)
                                        <div class="col-md-12 bg-transparent border-primary mt-2">
                                            <button type="submit" class="btn btn-primary w-100 mt-2">Listar</button>
                                        </div>
                                    @else
                                        <div class="col-md-12 bg-transparent border-primary mt-2">
                                            <button type="submit" class="btn btn-primary w-100 mt-2"
                                                disabled>Listar</button>
                                        </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection



@section('scripts')
    <script>
        function loadForm(formType) {
            // Ocultar todas las secciones en el contenedor derecho
            const sections = document.querySelectorAll('#content > div');
            sections.forEach(section => section.style.display = 'none');
            // Mostrar la sección correspondiente
            const target = document.getElementById(formType);
            if (target) {
                target.style.display = 'block';
            } else {
                // Si no hay formulario válido, mostrar bienvenida
                document.getElementById('welcome').style.display = 'block';
            }
        }
    </script>


    <script>
        // Oculta el spinner apenas se carga esta vista y al enviar el formulario
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar mensaje de bienvenida inicialmente
            document.getElementById('welcome').style.display = 'block';
            var spinner = document.querySelector('.spinner-wrapper');
            if (spinner) spinner.style.display = 'none';

            var form = document.getElementById('formEstado');
            if (form) {
                form.addEventListener('submit', function() {
                    if (spinner) spinner.style.display = 'none';
                });
            }
            var form = document.getElementById('formEstadosPropietario');
            if (form) {
                form.addEventListener('submit', function() {
                    if (spinner) spinner.style.display = 'none';
                });
            }

            initCalleSearch(@json($calle));
        });

        // Pasar los datos de propietarios al archivo JS
        window.propietariosData = @json($propietarios);
    </script>

    <script src="{{ asset('js/atencionAlCliente/propiedad/pdf/listar_propietarios_alquiler.js') }}"></script>
    <script src="{{ asset('js/atencionAlCliente/propiedad/cargarPropiedad.js') }}"></script>
    <script src="{{ asset('js/genericos/ocultar-spinner.js') }}"></script>
@endsection
