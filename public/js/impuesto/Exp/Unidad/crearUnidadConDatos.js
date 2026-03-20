function crearUnidadConDatos(index, padron) {
            const contenedorUnidades = document.getElementById('unidades');
            const div = document.createElement('div');
            div.classList.add('unidad-row', 'mb-3');
            div.setAttribute('data-index', index);

            div.innerHTML = `
        <div class="row d-flex justify-content-between pb-2">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text text_modal_m" style="height: 34px;">Tipo</span>
                    <select class="form-select" name="repetir[${index}][tipo]" id="selectTipo${index}">
                        <option value="">Seleccione un tipo...</option>
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
                    <select class="form-select" name="repetir[${index}][estado]" id="selectEstado${index}">
                        <option value="Activo">ACTIVO</option>
                        <option value="Inactivo">INACTIVO</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <button type="button" class="btn btn-outline-secondary btn-sm w-100 h-100 btn-comentario"
                    data-index="${index}" onclick="abrirModalComentario(${index})">
                    <i class="fa-regular fa-comment"></i> Comentario
                </button>
            </div>
        </div>
        <div class="row d-flex justify-content-between pb-2">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text text_modal_m" style="height: 34px;">Unidad</span>
                    <input type="number" class="form-control" name="repetir[${index}][unidad]" 
                        value="${padron.unidad || ''}" style="height: 34px;">
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text text_modal_m" style="height: 34px;">Piso</span>
                    <input type="number" class="form-control" name="repetir[${index}][piso]" 
                        value="${padron.piso || ''}" style="height: 34px;">
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text text_modal_m" style="height: 34px; width: 87px;">Depto</span>
                    <input type="text" class="form-control" name="repetir[${index}][depto]" 
                        value="${padron.depto || ''}" style="height: 34px;" oninput="this.value = this.value.toUpperCase();">
                </div>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger btn-sm" style="height: 34px;" 
                    onclick="eliminarUnidad(this)">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </div>
        </div>
        <input type="hidden" name="repetir[${index}][id]" value="${padron.id || ''}">
        <input type="hidden" name="repetir[${index}][comentario]" class="comentario-hidden" 
            data-index="${index}" value="${padron.observaciones || ''}">
        <hr>
    `;

            contenedorUnidades.appendChild(div);

            // Preseleccionar el edificio y tipo después de agregar al DOM
            setTimeout(() => {
                const selectEdificio = document.getElementById(`selectEdificio${index}`);
                if (selectEdificio && padron.id_edificio) {
                    selectEdificio.value = padron.id_edificio;
                    console.log(`Edificio ${index} seleccionado:`, padron.id_edificio);
                }

                const selectTipo = document.getElementById(`selectTipo${index}`);
                if (selectTipo && padron.tipo) {
                    selectTipo.value = padron.tipo;
                    console.log(`Tipo ${index} seleccionado:`, padron.tipo);
                }

                // PRESELECCIONAR EL ESTADO
                const selectEstado = document.getElementById(`selectEstado${index}`);
                if (selectEstado && padron.estado) {
                    selectEstado.value = padron.estado;
                    console.log(`Estado ${index} seleccionado:`, padron.estado);
                } else if (selectEstado) {
                    // Si no hay estado definido, dejar "Activo" por defecto
                    selectEstado.value = 'Activo';
                    console.log(`Estado ${index} por defecto: Activo`);
                }

                // Verificar si hay comentario y aplicar clase verde
                const botonComentario = div.querySelector(`.btn-comentario[data-index="${index}"]`);
                if (botonComentario && padron.observaciones && padron.observaciones.trim() !== '') {
                    botonComentario.classList.add('btn-comentario-con-texto');
                }
            }, 100);
        }