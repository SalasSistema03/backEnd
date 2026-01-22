document.addEventListener('DOMContentLoaded', function () {
    const clienteTelefono = document.getElementById('clientelefono');
    const clienteNombre = document.getElementById('cliente-nombre');
    const divBuscarPropiedad = document.getElementById('div-buscar-propiedad');
    const divCodigoPropiedad = document.getElementById('div-codigo-propiedad');
    const divCallePropiedad = document.getElementById('div-calle-propiedad');
    const selectorBusqueda = document.getElementById('tipo_busqueda');
    const divSelectorBusqueda = selectorBusqueda ? selectorBusqueda.closest('.col-md-6') : null;
    
   
    


    document.querySelectorAll('.sector-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.id; // por ejemplo: "Baños-tab"
            console.log('esto sale por id',id);
            
            if (id == 'Ventas-tab' || id == 'Alquiler-tab') {

                if (divBuscarPropiedad) divBuscarPropiedad.style.display = 'block';
                if (divCodigoPropiedad) divCodigoPropiedad.style.display = 'block';
                if (divCallePropiedad) divCallePropiedad.style.display = 'block';
                if (clienteTelefono) clienteTelefono.style.display = 'block';
                if (clienteNombre) clienteNombre.style.display = 'block';
                if (id == 'Ventas-tab') {
                    if (clienteTelefono) clienteTelefono.style.display = 'none';
                    if (clienteNombre) clienteNombre.style.display = 'none';

                } 
               
                if (selectorBusqueda && divCodigoPropiedad && divCallePropiedad) {
                    // Manejar el cambio de selector (solo para crear nuevas notas)
                    selectorBusqueda.onchange = function () {
                        const tipoBusqueda = this.value;
    
                        if (tipoBusqueda === 'codigo') {
                            // Mostrar búsqueda por código
                            divCodigoPropiedad.style.display = 'block';
                            divCallePropiedad.style.display = 'none';
    
                            // Limpiar campo de calle
                            document.getElementById('calle-autocomplete').value = '';
                            document.getElementById('calle_id').value = '';
                        } else {
                            // Mostrar búsqueda por calle
                            divCodigoPropiedad.style.display = 'none';
                            divCallePropiedad.style.display = 'block';
    
                            // Limpiar campo de código
                            document.getElementById('propiedad').value = '';
                            document.getElementById('propiedad-codigo-real').value = '';
                        }
                    };
    
                    // Detectar qué campo tiene valor al abrir una nota existente
                    function detectarCampoUsado() {
                        const codigoPropiedad = document.getElementById('propiedad').value;
                        const callePropiedad = document.getElementById('calle-autocomplete').value;
                        const notaId = document.getElementById('nota-id').value;
                        const activeBtn = document.querySelector('.sector-btn.active') || document.querySelector('.sector-btn');
                        const currentId = activeBtn ? activeBtn.id : '';
    
                        // Si hay ID de nota, estamos editando una nota existente
                        if (notaId && notaId.trim() !== '') {
                            // Ocultar el selector en notas existentes
                            if (divSelectorBusqueda) {
                                divSelectorBusqueda.style.display = 'none';
                            }
    
                            if (currentId == 'Alquiler-tab') {
                                if (clienteTelefono) clienteTelefono.style.display = 'none';
                                if (clienteNombre) clienteNombre.style.display = 'none';
                               
                            } 
                            if (codigoPropiedad && codigoPropiedad.trim() !== '') {
                                // Se usó código de propiedad
                                divCodigoPropiedad.style.display = 'block';
                                divCallePropiedad.style.display = 'none';
                            } else if (callePropiedad && callePropiedad.trim() !== '') {
                                // Se usó calle
                                divCodigoPropiedad.style.display = 'none';
                                divCallePropiedad.style.display = 'block';
                            } else {
                                // No se usó ninguno, ocultar ambos
                                divCodigoPropiedad.style.display = 'none';
                                divCallePropiedad.style.display = 'none';
                            }
                        } else {
                            // Es una nota nueva, mostrar el selector
                            if (divSelectorBusqueda) {
                                divSelectorBusqueda.style.display = 'block';
                            }
    
                            // Aplicar la selección actual
                            const tipoBusqueda = selectorBusqueda.value;
                            if (tipoBusqueda === 'codigo') {
                                divCodigoPropiedad.style.display = 'block';
                                divCallePropiedad.style.display = 'none';
                            } else {
                                divCodigoPropiedad.style.display = 'none';
                                divCallePropiedad.style.display = 'block';
                            }
                        }
                    }
    
                    // Ejecutar la detección al abrir el modal
                    const modal = document.getElementById('calendarEventModal');
                    if (modal) {
                        modal.addEventListener('shown.bs.modal', detectarCampoUsado, { once: true });
                    }
                }
                
            } else {
                // Ocultar todo lo que no sea descripción para otros sectores
                if (divBuscarPropiedad) divBuscarPropiedad.style.display = 'none';
                if (divCodigoPropiedad) divCodigoPropiedad.style.display = 'none';
                if (divCallePropiedad) divCallePropiedad.style.display = 'none';
                if (clienteTelefono) clienteTelefono.style.display = 'none';
                if (clienteNombre) clienteNombre.style.display = 'none';
            }
            console.log('ID:', id);
        });
        // Script para controlar el selector de búsqueda por código o calle
        document.addEventListener('DOMContentLoaded', function () {
            const selectorBusqueda = document.getElementById('tipo_busqueda');
            const divSelectorBusqueda = selectorBusqueda ? selectorBusqueda.closest('.col-md-6') : null;

            const divCodigoPropiedad = document.getElementById('div-codigo-propiedad');
            const divCallePropiedad = document.getElementById('div-calle-propiedad');
            const divBuscarPropiedad = document.getElementById('div-buscar-propiedad');

            if (divCallePropiedad) divCallePropiedad.style.display = 'none';
            if (divCodigoPropiedad) divCodigoPropiedad.style.display = 'none';
            if (divBuscarPropiedad) divBuscarPropiedad.style.display = 'none';

            
        });
    });


});

