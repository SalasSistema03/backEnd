
let contador = 1;
let indiceComentarioActual = null; // Variable para saber qué unidad está editando su comentario

function agregarUnidad() {
    const div = document.createElement('div');
    div.classList.add('unidad-row', 'mb-3');
    div.setAttribute('data-index', contador);
    div.innerHTML = `
            <div class="row d-flex justify-content-between pb-2">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text text_modal_m" style="height: 34px;">Tipo</span>
                        <select class="form-select" name="repetir[${contador}][tipo]">
                            <option value="" selected>Seleccione un tipo...</option>
                            <option value="DEPARTAMENTO">DEPARTAMENTO</option>
                            <option value="COCHERA">COCHERA</option>
                            <option value="BAULERA">BAULERA</option>
                            <option value="LOCAL_COMERCIAL">LOCAL COMERCIAL</option>
                            <option value="OFICINA">OFICINA</option>
                            <option value="CASA">CASA</option>
                            <option value="TERRENO">TERRENO</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text text_modal_m" style="height: 34px; width: 87px;">Estado</span>
                        <select class="form-select" name="repetir[${contador}][estado]">
                            <option value="Activo">ACTIVO</option>
                            <option value="Inactivo">INACTIVO</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm w-100 h-100 btn-comentario"
                        data-index="${contador}" onclick="abrirModalComentario(${contador})">
                        <i class="fa-regular fa-comment"></i> Comentario
                    </button>
                </div>
            </div>
            <div class="row d-flex justify-content-between pb-2">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text text_modal_m" style="height: 34px;">Unidad</span>
                        <input type="number" class="form-control" name="repetir[${contador}][unidad]" style="height: 34px;">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text text_modal_m" style="height: 34px;">Piso</span>
                        <input type="number" class="form-control" name="repetir[${contador}][piso]" style="height: 34px;">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text text_modal_m" style="height: 34px; width: 87px;">Depto</span>
                        <input type="text" class="form-control" name="repetir[${contador}][depto]"
                            style="height: 34px;" oninput="this.value = this.value.toUpperCase();">
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger btn-sm" style="height: 34px;" 
                        onclick="eliminarUnidad(this)">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
            </div>
            <input type="hidden" name="repetir[${contador}][comentario]" class="comentario-hidden" data-index="${contador}">
            <hr>
        `;
    document.getElementById('unidades').appendChild(div);
    contador++;
}
