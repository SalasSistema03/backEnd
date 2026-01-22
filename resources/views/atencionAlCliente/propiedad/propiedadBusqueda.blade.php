@extends('layout.nav')

@section('title', 'Buscar Propiedad')

@section('content')
    <div class="conteiner px-3">
        <!-- Formulario de búsqueda de propiedades con filtros -->
        <div class="row d-flex justify-content-center align-items-center ">
            <form id="formularioBuscarInmueble" class="row g-3" method="GET" action="{{ route('propiedad.index') }}"
                novalidate autocomplete="off">
                <div class="row d-flex justify-content-center">
                    <!-- Filtro de tipo de oferta (Venta o Alquiler) -->
                    <div class="col-md-2">
                        <strong for="" class="titlePropiedad">Tipo de Busqueda</strong>
                        <select class="form-select @error('oferta') is-invalid @enderror"
                            aria-label="Default select example" name="oferta" required>
                            <option value="0" {{ request('oferta') == 0 ? 'selected' : '' }}>-</option>
                            <option value="1" {{ request('oferta') == 1 ? 'selected' : '' }}>Venta</option>
                            <option value="2" {{ request('oferta') == 2 ? 'selected' : '' }}>Alquiler</option>
                        </select>
                    </div>
                    <!-- Campo de búsqueda por dirección o código -->
                    <div class="col-md-2">
                        <strong for="search_term" class="titlePropiedad">Código</strong>
                        <input type="number" class="form-control  @error('search_term') is-invalid @enderror"
                            id="search_term" name="search_term" placeholder="N°" min="0"
                            value="{{ request('search_term') }}">
                    </div>
                    <!-- Campo de búsqueda por calle-->
                    <div class="col-md-3">
                        <strong for="search_term" class="titlePropiedad">Calle</strong>
                        <input type="text" id="search-calle" class="form-control" placeholder="Buscar calle..."
                            value="{{ optional(App\Models\At_cl\Calle::find(request('calle')))->name }}">
                        <input type="hidden" id="calle_id" name="calle" value="{{ request('calle') }}">
                        <div id="search-results" class="list-group mt-2" style="position: absolute; z-index: 1000; ">
                        </div>
                    </div>



                    <!-- Campo de búsqueda por tipo de inmueble-->
                    <div class="col-md-2">
                        <strong for="" class="titlePropiedad">Tipo Inmueble</strong>
                        @php
                            $selectedTipos = (array) request('tipo_inmueble');
                            $selectedCount = count($selectedTipos);
                        @endphp
                        <div class="dropdown w-100">
                            <button class="form-control text-start dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                data-bs-auto-close="outside" aria-expanded="false" id="tipoInmuebleDropdownBtn">
                                {{ $selectedCount > 0 ? 'Selec. inmuebles (' . $selectedCount . ')' : 'Selec. inmuebles' }}
                            </button>
                            <div class="dropdown-menu p-2 w-100" style="max-height: 240px; overflow:auto;">
                                @foreach ($tipo_inmueble as $tipo)
                                    <div class="form-check">
                                        <input class="form-check-input tipo-inmueble-checkbox" type="checkbox"
                                            name="tipo_inmueble[]" id="tipo_inm_{{ $tipo->id }}"
                                            value="{{ $tipo->id }}"
                                            {{ in_array($tipo->id, (array) request('tipo_inmueble')) ? 'checked' : '' }}>
                                        <label class="form-check-label"
                                            for="tipo_inm_{{ $tipo->id }}">{{ $tipo->inmueble }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Campo de búsqueda por cochera-->
                    <div class="col-md-1">
                        <strong for="" class="titlePropiedad">Cochera</strong>
                        <select class="form-select @error('cochera') is-invalid @enderror"
                            aria-label="Default select example" name="cochera">
                            <option value="0"{{ request('cochera') == 0 ? 'selected' : '' }}>-</option>
                            <option value="1"{{ request('cochera') == 1 ? 'selected' : '' }}>SI</option>
                            <option value="2"{{ request('cochera') == 2 ? 'selected' : '' }}>NO</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <strong for="" class="titlePropiedad">Habitaciones</strong>
                        <input type="number" class="form-control  @error('cantidad_dormitorios') is-invalid @enderror"
                            id="" name = "cantidad_dormitorios" min="0" placeholder="Dormitorios"
                            value="{{ request('cantidad_dormitorios') }}">

                    </div>

                </div>
                <div class="row d-flex justify-content-center">
                    <div class="col-md-2">
                        <strong for="" class="titlePropiedad">Zonas</strong>
                        @php
                            $selectedZonas = (array) request('zona');
                            $selectedZonas = array_values(
                                array_filter($selectedZonas, fn($v) => $v !== null && $v !== ''),
                            );
                            $selectedZonasCount = count($selectedZonas);
                        @endphp
                        <div class="dropdown w-100">
                            <button class="form-control text-start dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                data-bs-auto-close="outside" aria-expanded="false" id="zonaDropdownBtn">
                                {{ $selectedZonasCount > 0 ? 'Selec. zonas (' . $selectedZonasCount . ')' : 'Selec. zonas' }}
                            </button>
                            <div class="dropdown-menu p-2 w-100"
                                style="max-height: 280px; overflow:auto; min-width: 260px;">
                                <input type="text" id="zonaSearchInput" class="form-control mb-2"
                                    placeholder="Buscar zona...">
                                <div id="zonaList">
                                    @foreach ($zona as $z)
                                        <div class="form-check zona-item">
                                            <input class="form-check-input zona-checkbox" type="checkbox" name="zona[]"
                                                id="zona_{{ $z->id }}" value="{{ $z->id }}"
                                                {{ in_array($z->id, (array) request('zona')) ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="zona_{{ $z->id }}">{{ $z->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Campo de búsqueda por importe minimo-->
                    <div class="col-md-2">
                        <strong for="" class="titlePropiedad">Importe desde</strong>
                        <input type="number" class="form-control  @error('importe_minimo') is-invalid @enderror"
                            id="" name = "importe_minimo" min="0" placeholder="Importe mínimo"
                            value="{{ request('importe_minimo') }}">
                    </div>
                    <!-- Campo de búsqueda por importe maximo-->
                    <div class="col-md-2">
                        <strong for="" class="titlePropiedad">Importe hasta</strong>
                        <input type="number" class="form-control @error('importe_maximo') is-invalid @enderror"
                            id="" name = "importe_maximo" min="0"placeholder="Importe máximo"
                            value="{{ request('importe_maximo') }}">
                    </div>
                    <div class="col-md-2">
                        <strong for="" class="titlePropiedad">Ordenar por</strong>
                        <select name="orden" id="orden" class="form-control">
                            <option value="">Sin orden</option>
                            <option value="precio_asc" {{ request('orden') == 'precio_asc' ? 'selected' : '' }}>Precio
                                (menor a mayor)</option>
                            <option value="precio_desc" {{ request('orden') == 'precio_desc' ? 'selected' : '' }}>Precio
                                (mayor a menor)</option>
                            <option value="cochera" {{ request('orden') == 'cochera' ? 'selected' : '' }}>Cochera</option>
                            <option value="habitaciones" {{ request('orden') == 'habitaciones' ? 'selected' : '' }}>
                                Habitaciones</option>
                            <option value="tipo" {{ request('orden') == 'tipo' ? 'selected' : '' }}>Tipo de inmueble
                            </option>
                            <option value="zona" {{ request('orden') == 'zona' ? 'selected' : '' }}>Zona</option>
                            <option value="calle" {{ request('orden') == 'calle' ? 'selected' : '' }}>Calle</option>
                            <option value="banio" {{ request('orden') == 'banio' ? 'selected' : '' }}>Baños</option>
                        </select>
                    </div>
                </div>
                <div class="row d-flex justify-content-end pt-3">

                    <!-- Botón de filtrado -->
                    <div class="col-9 d-flex justify-content-end align-items-end">
                        <button type="submit" class="btn btn-primary"><strong> Filtrar </strong></button>
                    </div>
                    <!-- Botón de ampliar -->
                    <div
                        class="col-3 form-check form-switch form-check-reverse d-flex align-items-end justify-content-start">
                        <div class="row">
                            <div class="col-3 d-flex justify-content-start align-items-center">
                                <input class="form-check-input" type="checkbox" id="flexSwitchCheckReverse"
                                    name="ampliar" value="true">
                            </div>

                            <div class="col-9 d-flex justify-content-start align-items-center">
                                <strong class="form-check-label ms-2 titlePropiedad"
                                    for="flexSwitchCheckReverse">Ampliar</strong>
                            </div>
                        </div>

                    </div>

                    <!-- Botón de cargar propiedad -->
                </div>
            </form>
        </div>

        <!-- Sección de resultados de búsqueda -->
        <br>
        <div class="row d-flex justify-content-center align-items-center ">
            <table class="table table-striped table-hover text-center tabla">
                <thead>
                    <tr>
                        <th>- C. V. - </th>
                        <th> - C. A. - </th>
                        <th>DIRECCION</th>
                        <th>ZONA</th>
                        {{-- <th>BARRIO</th> --}}
                        <th>INMUEBLE</th>
                        <th>DORM.</th>
                        <th>BAÑOS</th>
                        <th>COCHERA</th>
                        <th>PRECIO ALQUILER</th>
                        <th>PRECIO VENTA</th>
                        <th>DETALLE</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($propiedad as $propiedades)

                        <tr>
                            
                            @if ($propiedades->id_estado_venta == 1)
                                <td class="text-center estado_venta">
                            @elseif($propiedades->id_estado_venta == 2)
                                <td class="text-center estado_venta_compartida">
                            @elseif($propiedades->id_estado_venta == 3)
                                <td class="text-center estado_finalizado">
                            @elseif($propiedades->id_estado_venta == 4)
                                <td class="text-center estado_baja_temporal">
                            @elseif($propiedades->id_estado_venta == 5)
                                <td class="text-center estado_rest">
                            @elseif($propiedades->id_estado_venta == 6)
                                <td class="text-center estado_retirada">
                            @elseif($propiedades->id_estado_venta == 7)
                                <td class="text-center estado_baja">
                            @else
                                <td class="text-center ">
                            @endif
                            
                            
                            
                                 {{-- {{ $propiedades->id_estado_venta }}  --}}
                                @if ($propiedades->cod_venta)
                                    {{ $propiedades->cod_venta }}
                                @else
                                    -
                                @endif
                            </td>
                            @if ($propiedades->id_estado_alquiler == 1)
                                <td class="text-center estado_venta">
                            @elseif($propiedades->id_estado_alquiler == 2)
                                <td class="text-center estado_venta_compartida">
                            @elseif($propiedades->id_estado_alquiler == 3)
                                <td class="text-center estado_finalizado">
                            @elseif($propiedades->id_estado_alquiler == 4)
                                <td class="text-center estado_baja_temporal">
                            @elseif($propiedades->id_estado_alquiler == 5)
                                <td class="text-center estado_rest">
                            @elseif($propiedades->id_estado_alquiler == 6)
                                <td class="text-center estado_retirada">
                            @elseif($propiedades->id_estado_alquiler == 7)
                                <td class="text-center estado_baja">
                            @else
                                <td class="text-center ">
                            @endif
                               {{--  {{ $propiedades->id_estado_alquiler }}  --}}
                                @if ($propiedades->cod_alquiler)
                                    {{ $propiedades->cod_alquiler }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ optional($propiedades->calle)->name . ' - ' . ($propiedades->numero_calle ?? 'Sin datos') }}</td>
                            <td>{{  strtoupper($propiedades->zona->name ?? 'Sin datos') }}</td>
                            {{-- <td>{{ $propiedades->barrio->name ?? 'Sin datos' }}</td> --}}
                            <td>{{ $propiedades->tipoInmueble->inmueble ?? 'Sin datos' }}</td>
                            <td>{{ $propiedades->cantidad_dormitorios ?? 'Sin datos' }}</td>
                            <td>{{ $propiedades->banios ?? 'Sin datos' }}</td>
                            <td>{{ $propiedades->cochera ?? 'Sin datos' }}</td>
                            {{--   <td> --}}
                            @if ($propiedades->id_estado_alquiler == 1 || $propiedades->id_estado_alquiler == 2)
                                <td class="estado_busqueda">
                                    <div class = "alquilado">
                                        @if (
                                            $propiedades->precio &&
                                                $propiedades->precio->moneda_alquiler_pesos !== null &&
                                                $propiedades->precio->moneda_alquiler_dolar !== null)
                                            $ {{ $propiedades->precio->moneda_alquiler_pesos }} / U$D
                                            {{ $propiedades->precio->moneda_alquiler_dolar }}
                                        @elseif (
                                            $propiedades->precio &&
                                                $propiedades->precio->moneda_alquiler_pesos !== null &&
                                                $propiedades->precio->moneda_alquiler_dolar === null)
                                            $ {{ $propiedades->precio->moneda_alquiler_pesos }}
                                            {{--   @dump($propiedades->precio)  --}}
                                        @elseif (
                                            $propiedades->precio &&
                                                $propiedades->precio->moneda_alquiler_pesos === null &&
                                                $propiedades->precio->moneda_alquiler_dolar !== null)
                                            U$D {{ $propiedades->precio->moneda_alquiler_dolar }}
                                        @else
                                            - {{-- @dump($propiedades)  --}}
                                        @endif

                                    </div>


                                </td>
                            @elseif ($propiedades->id_estado_alquiler == 3)
                                <td class="estado_busqueda">
                                    <div class = "pendiente">
                                        @if (
                                            $propiedades->precio &&
                                                $propiedades->precio->moneda_alquiler_pesos !== null &&
                                                $propiedades->precio->moneda_alquiler_dolar !== null)
                                            $ {{ $propiedades->precio->moneda_alquiler_pesos }} / U$D
                                            {{ $propiedades->precio->moneda_alquiler_dolar }}
                                        @elseif (
                                            $propiedades->precio &&
                                                $propiedades->precio->moneda_alquiler_pesos !== null &&
                                                $propiedades->precio->moneda_alquiler_dolar === null)
                                            $ {{ $propiedades->precio->moneda_alquiler_pesos }}
                                            {{--   @dump($propiedades->precio)  --}}
                                        @elseif (
                                            $propiedades->precio &&
                                                $propiedades->precio->moneda_alquiler_pesos === null &&
                                                $propiedades->precio->moneda_alquiler_dolar !== null)
                                            U$D {{ $propiedades->precio->moneda_alquiler_dolar }}
                                        @else
                                            - {{-- @dump($propiedades)  --}}
                                        @endif
                                    </div>
                                </td>
                            @else
                                <td>
                                    -
                                </td>
                            @endif


                            {{-- </td> --}}
                            {{-- @dump($propiedades->id_estado_venta) --}}
                            @if ($propiedades->id_estado_venta == 1)
                                <td class="estado_busqueda">
                                    <div class="venta">
                                        @if (
                                            $propiedades->precio &&
                                                $propiedades->precio->moneda_venta_dolar !== null &&
                                                $propiedades->precio->moneda_venta_pesos !== null)
                                            U$D {{ $propiedades->precio->moneda_venta_dolar }} / $
                                            {{ $propiedades->precio->moneda_venta_pesos }}
                                            {{--   @dump($propiedades->precio)   --}}
                                        @elseif(
                                            $propiedades->precio &&
                                                $propiedades->precio->moneda_venta_pesos !== null &&
                                                $propiedades->precio->moneda_venta_dolar === null)
                                            ${{ $propiedades->precio->moneda_venta_pesos }}
                                        @elseif(
                                            $propiedades->precio &&
                                                $propiedades->precio->moneda_venta_dolar !== null &&
                                                $propiedades->precio->moneda_venta_pesos === null)
                                            U$D {{ $propiedades->precio->moneda_venta_dolar }}
                                        @else
                                            -
                                            {{--    - @dump($propiedades->precio)   --}}
                                        @endif
                                        {{--  @dump($propiedades)
                                    @dump($propiedades->moneda_venta_dolar) --}}
                                    </div>
                                </td>
                            @elseif ($propiedades->id_estado_venta == 2)
                                <td class="estado_busqueda"> 
                                    <div class="venta_compartida">
                                        @if (
                                            $propiedades->precio &&
                                                $propiedades->precio->moneda_venta_dolar !== null &&
                                                $propiedades->precio->moneda_venta_pesos !== null)
                                            U$D {{ $propiedades->precio->moneda_venta_dolar }} / $
                                            {{ $propiedades->precio->moneda_venta_pesos }}
                                            {{--   @dump($propiedades->precio)   --}}
                                        @elseif(
                                            $propiedades->precio &&
                                                $propiedades->precio->moneda_venta_pesos !== null &&
                                                $propiedades->precio->moneda_venta_dolar === null)
                                            ${{ $propiedades->precio->moneda_venta_pesos }}
                                        @elseif(
                                            $propiedades->precio &&
                                                $propiedades->precio->moneda_venta_dolar !== null &&
                                                $propiedades->precio->moneda_venta_pesos === null)
                                            U$D {{ $propiedades->precio->moneda_venta_dolar }}
                                        @else
                                            -
                                            {{--    - @dump($propiedades->precio)   --}}
                                        @endif
                                        {{--  @dump($propiedades)
                                @dump($propiedades->moneda_venta_dolar) --}}
                                    </div>
                                </td>
                            @else
                                <td>
                                    -
                                </td>
                            @endif

                            <td>
                                <div class="d-flex justify-content-center pb-1">
                                    <a href="{{ route('propiedad.show', $propiedades->id) }}"
                                        class="btn btn-primary btn-sm">
                                        <strong>Ver</strong>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center">Sin resultados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
    {{-- script listado de calle --}}
    <script src="{{ asset('js/atencionAlCliente/propiedad/cargarPropiedad.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Limpiar calle_id si el usuario borra el nombre de la calle
            const searchCalle = document.getElementById('search-calle');
            const calleId = document.getElementById('calle_id');
            if (searchCalle && calleId) {
                searchCalle.addEventListener('input', function() {
                    if (this.value.trim() === '') {
                        calleId.value = '';
                    }
                });
            }

            // Pasamos el array de calles a la función inicializadora
            initCalleSearch(@json($calle));

            // Actualizar etiqueta del dropdown de Tipo Inmueble según cantidad seleccionada
            const tipoInmBtn = document.getElementById('tipoInmuebleDropdownBtn');
            const tipoChecks = document.querySelectorAll('.tipo-inmueble-checkbox');

            function updateTipoInmLabel() {
                if (!tipoInmBtn) return;
                const count = Array.from(tipoChecks).filter(c => c.checked).length;
                tipoInmBtn.textContent = count > 0 ? `Selec. inmuebles (${count})` : 'Selec. inmuebles';
            }

            if (tipoChecks.length) {
                tipoChecks.forEach(chk => chk.addEventListener('change', updateTipoInmLabel));
                updateTipoInmLabel();
            }

            // Manejo del dropdown de Zonas: actualizar contador y filtrar por buscador
            const zonaBtn = document.getElementById('zonaDropdownBtn');
            const zonaChecks = document.querySelectorAll('.zona-checkbox');
            const zonaSearchInput = document.getElementById('zonaSearchInput');
            const zonaItems = document.querySelectorAll('#zonaList .zona-item');

            function updateZonaLabel() {
                if (!zonaBtn) return;
                const count = Array.from(zonaChecks).filter(c => c.checked).length;
                zonaBtn.textContent = count > 0 ? `Selec. zonas (${count})` : 'Selec. zonas';
            }

            function filterZonas() {
                const term = (zonaSearchInput?.value || '').toLowerCase().trim();
                zonaItems.forEach(item => {
                    const label = item.querySelector('label')?.textContent?.toLowerCase() || '';
                    item.style.display = label.includes(term) ? '' : 'none';
                });
            }

            if (zonaChecks.length) {
                zonaChecks.forEach(chk => chk.addEventListener('change', updateZonaLabel));
                updateZonaLabel();
            }
            if (zonaSearchInput) {
                zonaSearchInput.addEventListener('input', filterZonas);
                filterZonas();
            }
        });
    </script>

@endsection
