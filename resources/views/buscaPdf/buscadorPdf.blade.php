@extends('layout.nav')

@section('title', 'Buscador de PDF')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Buscar PDF</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('ver.pdf') }}" method="GET" class="form-inline row" target="_blank" id="formPdf" autocomplete="off">
                        @csrf
                        <input type="hidden" name="admin" value="0">
                        <div class="col-md-4">
                            <label for="empresa" class="form-label">Empresa</label>
                            <select name="empresa" id="empresa" class="form-control" required>
                                <option value="Salas">Central</option>
                                <option value="Dolly">Candioti</option>
                                <option value="Florencia">Sur</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="comprobante" class="form-label">Comprobante</label>
                            <select name="comprobante" id="comprobante" class="form-control" required>
                                <option value="Facturas">Facturas</option>
                                <option value="Notas de Credito">Notas de Credito</option>
                                <option value="Notas de Debito">Notas de Debito</option>
                                <option value="Opp Concatenadas">Opp Concatenadas</option>
                                <option value="Ordenes de Pago">Ordenes de Pago</option>
                                <option value="Ordenes de Cobro">Ordenes de Cobro</option>
                                <option value="Recibos">Recibos</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="quien" class="form-label">Quien</label>
                            <select name="quien" id="quien" class="form-control" required>
                                <option value="Inquilinos">Inquilinos</option>
                                <option value="Propietarios">Propietarios</option>
                                <option value="Proveedores">Proveedores</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select name="tipo" id="tipo" class="form-control" required>
                                <option value="Originales">Originales</option>
                                <option value="Duplicados">Duplicados</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="letra" class="form-label">Letra </label>
                            <select name="letra" id="letra" class="form-control" required>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="X">X</option>                        
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="numero" class="form-label">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero" placeholder="Número" required>
                        </div>
                        
                        <div class="col-md-12">
                            <hr>
                        </div>
                        <div class="col-md-12 d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary w-75" >Buscar PDF</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- <script>
    // Oculta el spinner apenas se carga esta vista y al enviar el formulario
    document.addEventListener('DOMContentLoaded', function() {
        var spinner = document.querySelector('.spinner-wrapper');
        if (spinner) spinner.style.display = 'none';

        var form = document.getElementById('formPdf');
        if (form) {
            form.addEventListener('submit', function() {
                if (spinner) spinner.style.display = 'none';
            });
        }
    });
</script> --}}
<script src="{{ asset('js/genericos/ocultar-spinner.js') }}"></script>
@endsection
