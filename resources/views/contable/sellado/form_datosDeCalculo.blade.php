<!-- Modal Datos de Calculo-->
<div class="modal fade" id="modalDatosCalculo" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg ">
        <div class="modal-content">
            <div class="modal-body">
                <div class="container-fluid d-flex justify-content-center">
                    <form class="row d-flex justify-content-center " novalidate method="POST" action="{{ route('guardar.guardarDatosDeCalculo') }}">
                    
                        {{-- Token de seguridad obligatorio en Laravel --}}
                        @csrf
                        <!-- Valores Registrales -->
                        <div class="row modulosDatos contenedorSeccion">
                            <div class="col-md-12">
                                <h2 class="form-label titulos_carga_datos text-center">Valores Registrales</h2>
                            </div>
                            <div class="col-4">
                                <label for="validationCustom01" class="form-label lables_carga_datos">Limite</label>
                                <input type="number" class="form-control inputDatos" name="valor_limite1" value="{{ $valores['valorDatosRegistrales'][0]->valor_limite }}" required>
                                <input type="number" class="form-control inputDatos" name="valor_limite2" value="{{ $valores['valorDatosRegistrales'][1]->valor_limite }}" required>
                                <input type="number" class="form-control inputDatos" name="valor_limite3" value="{{ $valores['valorDatosRegistrales'][2]->valor_limite }}" required>
                            </div>
                            <div class="col-4">

                                <label for="validationCustom01" class="form-label lables_carga_datos">Valor</label>
                                <input type="number" class="form-control inputDatos" name="precio1" value="{{ $valores['valorDatosRegistrales'][0]->precio }}" required>
                                <input type="number" class="form-control inputDatos" name="precio2" value="{{ $valores['valorDatosRegistrales'][1]->precio }}" required>
                                <input type="number" class="form-control inputDatos" name="precio3" value="{{ $valores['valorDatosRegistrales'][2]->precio }}" required>
                            </div>
                            <div class="col-4">
                                <div class="form-check">
                                    <label for="check_registro" class="form-label lables_carga_datos">C/Registrales </label>
                                    <input class="form-check-input" type="checkbox" name="check_registro" value="1" id="check_registro">
                                </div>
                                <div class="form-label lables_carga_datos">Registrop Extra </div>
                                <input type="number" class="form-control inputDatos" name="valor_registro_extra" value="{{ $valor_registro_extra}}" required>
                            </div>
                        </div>

                        <!-- Valor Adm Cochera y Precio Hoja -->
                        <div class="row p-0 d-flex justify-content-between">
                            <div class="col-md-6 contenedorSeccion_medio">
                                <!-- Se elimina el row innecesario dentro de la columna -->
                                <div class="text-center">
                                    <h2 class="form-label titulos_carga_datos">Valor Administrativo</h2>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label lables_carga_datos"> G. Adm. Coch. 6</label>
                                        <input type="number" class="form-control inputDatos" name="valor_con_cochera_6" value="{{ $valores['valorGastoAdminitrativo'][0]->valor }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label lables_carga_datos">G. Adm. Base</label>
                                        <input type="number" class="form-control inputDatos" name="valor_con_base" value="{{ $valores['valorGastoAdminitrativo'][2]->valor }}" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label lables_carga_datos">G. Adm. Coch. 12</label>
                                        <input type="number" class="form-control inputDatos" name="valor_con_cochera_12" value="{{ $valores['valorGastoAdminitrativo'][1]->valor }}" required>
                                    </div>
                                    <div class="col-md-6 d-flex justify-content-center align-items-center">
                                        <div class="form-check">
                                            <label for="check_valor_ga" class="form-label lables_carga_datos">C/ G. Adm.</label>
                                            <input class="form-check-input" type="checkbox" name="check_valor_ga" value="1" id="check_valor_ga">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 contenedorSeccion_medio">
                                <!-- Se elimina el row innecesario dentro de la columna -->
                                <div class="text-center">
                                    <h2 class="form-label titulos_carga_datos">Precio Hoja</h2>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="number" class="form-control inputDatos" name="precio_hoja" value="{{ $valores['valorHoja'] }}" required>
                                    </div>

                                    <div class="col-md-6 form-check">
                                        <div class="form-check">
                                            <label class="form-label lables_carga_datos">C/Hoja</label>
                                            <input class="form-check-input" type="checkbox" name="check_precio_hoja" value="1" id="check_precio_hoja">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Valor Sellado -->
                        <div class="row modulosDatos contenedorSeccion ">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <h2 for="validationCustom01" class="form-label titulos_carga_datos">Valor Sellado</h2>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <label for="validationCustom01" class="form-label lables_carga_datos">Vivienda</label>
                                    <input type="number" class="form-control inputDatos" name="sellado_vivienda_valor" value="{{ $valores['valorSellado'][0]->valor  }}" required>

                                </div>
                                <div class="col-md-4">
                                    <label for="validationCustom01" class="form-label lables_carga_datos">Comercio</label>
                                    <input type="number" class="form-control inputDatos" name="sellado_comercio_valor" value="{{ $valores['valorSellado'][1]->valor  }}" required>
                                 
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <label class="form-label lables_carga_datos" for="invalidCheck">C/Sellado</label>
                                        <input class="form-check-input" type="checkbox" name="check_sellado_valor" value="1" id="check_sellado_valor">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
