@extends('layout.nav')

@section('title', 'Sellado')

@section('content')

    <div class="container-fluid">
       
         <!-- Se incluye el formulario REGISTRO SELLADO-->
        @include('contable.sellado.form_registroSellado')


                            <!-- Tabla de registros sellados -->
        <div id="contenedor_tabla">
          

            <table class="table table-striped table-hover" id="tablaDatos">
                <thead>
                    <tr>
                         <th>Folio</th>
                         <th>Nombre</th>
                         <th>Meses</th>
                         <th>Total</th>
                         <th>Hojas</th>
                         <th>Informe</th>
                         <th>Tipo</th>
                         <th>Inq/Prop</th>
                         <th>Sellado</th>
                         <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($valores as $valor)
                        <tr class="tablaDatos_registros">
                            <td>{{ $valor->folio }}</td>
                            <td class="td_nombre">{{ $valor->nombre }}</td>
                            <td>{{ $valor->cantidad_meses }}</td>
                            <td>{{ $valor->gasto_administrativo}}</td>
                            <td>{{ $valor->hojas }}</td>
                            <td>{{ $valor->informe }}</td>
                            <td>{{ $valor->tipo_contrato }}</td>
                            <td>{{ $valor->inq_prop }}</td>
                            <td>{{ $valor->sellado }}</td>
                            <!--  <td>{{ $valor->usuario_id }}</td>  -->
                            
                            <td>{{ $valor->usuario->username ?? '-'}}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No hay registros sellados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    
    </div>
    <script src="{{ asset('js/genericos/ocultar-spinner.js') }}"></script>
@endsection
