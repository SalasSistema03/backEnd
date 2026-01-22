@extends('layout.nav')

@section('title', 'Consorcios Expensas')

@section('content')
    <div class="container">

        <h1>Padron Consorcio</h1>
        <div class="row">
            <div class="col-md-10">
                <form action="{{ route('exp_consorcio.filtro') }}" method="GET" class="row mb-3" autocomplete="off">
                    @csrf
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control"
                            placeholder="Buscar por nombre, direccion, altura..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </form>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btnSalas" data-bs-toggle="modal" data-bs-target="#CargarConsorcio">
                    Cargar Consorcio
                </button>
            </div>
        </div>

        <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
            <table class="table table-striped table-hover">
                <thead class="table-light" style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">
                    <tr>
                        <th>Nombre Consorcio</th>
                        <th>Direccion</th>
                        <th>Administrador Consorcio</th>
                        <th>Modificar</th>
                    </tr>
                </thead>
                <tbody class="tabla_consorcio">
                    @foreach ($edificios as $edificio)
                        {{--  @dd($edificio) - --}}
                        <tr>
                            <td class="align-middle">{{ $edificio->nombre_consorcio ?? '' }}</td>
                            <td class="align-middle">{{ $edificio->direccion ?? '' }} {{ $edificio->altura ?? '' }}</td>
                            <td class="align-middle">{{ $edificio->administrador->nombre ?? '' }}</td>
                            <td class="align-middle">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalEditarConsorcio" data-id="{{ $edificio->id }}"
                                    data-nombre="{{ $edificio->nombre_consorcio }}" data-calle="{{ $edificio->direccion }}"
                                    data-altura="{{ $edificio->altura }}"
                                    data-administra="{{ $edificio->id_administrador_consorcio }}">
                                    Modificar
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>



    <script src="{{ asset('js/atencionAlCliente/propiedad/cargarPropiedad.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log(@json($calle));
            initCalleSearch(@json($calle));
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('modalEditarConsorcio');
            if (!modalEl) return;

            let calleEditInitialized = false;

            function initCalleSearchEdit(calles) {
                const searchInput = document.getElementById('search-calle-edit');
                const searchResults = document.getElementById('search-results-edit');
                const calleIdInput = document.getElementById('calle_id_edit');
                if (!searchInput || !searchResults || !calleIdInput) return;
                if (calleEditInitialized) return;
                calleEditInitialized = true;
                searchInput.addEventListener('input', function(e) {
                    e.stopPropagation();
                    const searchTerm = this.value.toLowerCase();
                    searchResults.innerHTML = '';
                    if (searchTerm.length < 2) {
                        searchResults.style.display = 'none';
                        return;
                    }
                    const filtered = calles.filter(c => c.name.toLowerCase().includes(searchTerm)).slice(0,
                        10);
                    if (filtered.length > 0) {
                        searchResults.style.display = 'block';
                        filtered.forEach(c => {
                            const div = document.createElement('div');
                            div.className = 'list-group-item list-group-item-action';
                            div.textContent = c.name;
                            div.addEventListener('click', (ev) => {
                                ev.stopPropagation();
                                searchInput.value = c.name;
                                calleIdInput.value = c.id;
                                searchResults.style.display = 'none';
                            });
                            searchResults.appendChild(div);
                        });
                    } else {
                        searchResults.style.display = 'none';
                    }
                });
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                        searchResults.style.display = 'none';
                    }
                });
            }

            modalEl.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                if (!button) return;
                const id = button.getAttribute('data-id');
                const nombre = button.getAttribute('data-nombre');
                const calleNombre = button.getAttribute('data-calle');
                const altura = button.getAttribute('data-altura');
                const administraId = button.getAttribute('data-administra');
                console.log(administraId);

                const idInput = document.getElementById('edit_id');
                const nombreInput = document.getElementById('edit_nombre');
                const alturaInput = document.getElementById('edit_altura');
                const calleTextInput = document.getElementById('search-calle-edit');
                const calleIdInput = document.getElementById('calle_id_edit');
                const adminSelect = document.getElementById('administrador-edit');

                if (idInput) idInput.value = id || '';
                if (nombreInput) nombreInput.value = nombre || '';
                if (alturaInput) alturaInput.value = altura || '';
                if (calleTextInput) {
                    calleTextInput.value = calleNombre || '';
                }
                if (calleIdInput) {
                    calleIdInput.value = '';
                }
                if (adminSelect) {
                    const targetVal = (administraId || '').toString().trim();
                    if (targetVal === '') {
                        adminSelect.selectedIndex = -1;
                    } else {
                        adminSelect.value = targetVal;
                        if (adminSelect.value !== targetVal) {
                            let matched = false;
                            for (const opt of adminSelect.options) {
                                if (opt.value != null && opt.value.toString().trim() === targetVal) {
                                    opt.selected = true;
                                    matched = true;
                                    break;
                                }
                            }
                            if (!matched) {
                                // Fallback: intentar por texto (caso en que targetVal es el NOMBRE del administrador)
                                const targetName = targetVal.toString().trim().toLowerCase();
                                for (const opt of adminSelect.options) {
                                    const txt = (opt.textContent || opt.innerText || '').toString().trim()
                                        .toLowerCase();
                                    if (txt === targetName) {
                                        opt.selected = true;
                                        matched = true;
                                        break;
                                    }
                                }
                                if (!matched) {
                                    console.warn('Admin not matched by ID nor text', {
                                        targetVal,
                                        values: [...adminSelect.options].map(o => o.value),
                                        texts: [...adminSelect.options].map(o => (o.textContent ||
                                            '').trim())
                                    });
                                }
                            }
                        }
                    }
                    adminSelect.dispatchEvent(new Event('change'));
                }

                initCalleSearchEdit(@json($calle));
            });
        });
    </script>

    @include('impuesto.expensas.modales-expensas.modal-cargar-consorcio')
    @include('impuesto.expensas.modales-expensas.modal-editar-consorcio')
@endsection
