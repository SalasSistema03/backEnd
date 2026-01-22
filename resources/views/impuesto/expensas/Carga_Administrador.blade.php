<!-- filepath: c:\xampp\htdocs\salas\salas\resources\views\impuesto\expensas\Carga_Administrador.blade.php -->
@extends('layout.nav')

@section('title', 'Padron Administradores Expensas')

@section('content')
    <div class="container">

        <h1>Padron Administradores Expensas</h1>
        <div class="row">
            
            <div class="col-md-10">
                <form action="{{ route('exp_administrador_consorcio.filtro') }}" method="GET" class="row mb-3"
                    autocomplete="off">
                    @csrf
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                            placeholder="Buscar por nombre, cuit, telefono..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </form>
            </div>
            <div class="col-md-2">
                <form action="{{ route('exp_administrador_consorcio.CargarAdministradores') }}" method="GET" class="row mb-3"
                    autocomplete="off">
                    @csrf
                    <div class="col-auto" style="margin-bottom: 5px;">
                        <button type="submit" class="btn btnSalas w-100">Actualizar Padrón</button>
                    </div>
                </form>
            </div>

        </div>

        <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
            <table class="table table-striped table-hover">
                <thead class="table-light" style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">
                    <tr>
                        <th>Nombre</th>
                        <th>Cuit</th>
                        <th>Contacto</th>
                        <th>Pagina Web</th>
                        <th>Direccion</th>

                    </tr>
                </thead>
               {{--  @dd($proveedores) --}}
                <tbody class="table_administradores align-middle">
                    @foreach ($proveedores as $proveedor)
                        <td>{{ $proveedor->nombre ?? '' }}</td>
                        <td>{{ $proveedor->cuit ?? '' }}</td>
                        <td>{{ $proveedor->contacto ?? '' }}</td>
                        <td>{{ $proveedor->pagina_web ?? '' }}</td>
                        <td>{{ $proveedor->direccion ?? '' }} {{ $proveedor->altura ?? '' }}</td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
