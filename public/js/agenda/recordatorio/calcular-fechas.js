document.addEventListener("DOMContentLoaded", function () {
    const fechaInput = document.getElementById('fecha');
    const intervaloInput = document.getElementById('intervalo');
    const cantidadInput = document.getElementById('cantidad');
    const repetirInput = document.getElementById('repetir');
    const proximoInput = document.getElementById('proximo-carga');
    const finalizaInput = document.getElementById('finaliza-carga');

    function calcularFechas() {

        const fechaInicio = fechaInput.value;
        const intervalo = intervaloInput.value;
        const cantidad = parseInt(cantidadInput.value || 1); // default 1
        const repetir = parseInt(repetirInput.value || 1); // default 1

        if (!fechaInicio || !intervalo) {
            proximoInput.value = "";
            finalizaInput.value = "";
            return;
        }

        const fecha = new Date(fechaInicio);
        let fechaActualizacion = new Date(fecha);
        let fechaFin = new Date(fecha);

        if (repetir === 1) {
            if (intervalo === 'Diario') {
                fechaActualizacion.setDate(fecha.getDate() + cantidad);
                fechaFin = new Date(fechaActualizacion);
            } else if (intervalo === 'Mensual') {
                fechaActualizacion.setMonth(fecha.getMonth() + cantidad);
                fechaFin = new Date(fechaActualizacion);
            }
        } else {
            if (intervalo === 'Diario') {
                fechaActualizacion.setDate(fecha.getDate() + cantidad);
                fechaFin.setDate(fecha.getDate() + (cantidad * repetir));
            } else if (intervalo === 'Mensual') {
                fechaActualizacion.setMonth(fecha.getMonth() + 1);
                fechaFin.setMonth(fecha.getMonth() + (cantidad * repetir));
            }
        }

        // Formato YYYY-MM-DD
        proximoInput.value = fechaActualizacion.toISOString().split('T')[0];
        finalizaInput.value = fechaFin.toISOString().split('T')[0];
    }

    // Ejecutar al cambiar cualquier input relevante
    ['fecha', 'intervalo', 'cantidad', 'repetir'].forEach(id => {
        document.getElementById(id).addEventListener('input', calcularFechas);
    });

    calcularFechas(); // Ejecutar al cargar por si ya hay valores

    const fechaInputModal = document.getElementById('fecha-modal');
    const intervaloInputModal = document.getElementById('intervalo-modal');
    const cantidadInputModal = document.getElementById('cantidad-modal');
    const repetirInputModal = document.getElementById('repetir-modal');
    const proximoInputModal = document.getElementById('proximo-modal');
    const finalizaInputModal = document.getElementById('finaliza-modal');

    function calcularFechasModal() {
        const fechaInicio = fechaInputModal.value;
        const intervalo = intervaloInputModal.value;
        const cantidad = parseInt(cantidadInputModal.value || 1); // default 1
        const repetir = parseInt(repetirInputModal.value || 1); // default 1
        console.log(fechaInicio);

        if (!fechaInicio || !intervalo) {
            proximoInputModal.value = "";
            finalizaInputModal.value = "";
            return;
        }

        const fecha = new Date(fechaInicio);
        let fechaActualizacion = new Date(fecha);
        let fechaFin = new Date(fecha);

        if (repetir === 1) {
            if (intervalo === 'Diario') {
                fechaActualizacion.setDate(fecha.getDate() + cantidad);
                fechaFin = new Date(fechaActualizacion);
            } else if (intervalo === 'Mensual') {
                fechaActualizacion.setMonth(fecha.getMonth() + cantidad);
                fechaFin = new Date(fechaActualizacion);
            }
        } else {
            if (intervalo === 'Diario') {
                fechaActualizacion.setDate(fecha.getDate() + cantidad);
                fechaFin.setDate(fecha.getDate() + (cantidad * repetir));
            } else if (intervalo === 'Mensual') {
                fechaActualizacion.setMonth(fecha.getMonth() + 1);
                fechaFin.setMonth(fecha.getMonth() + (cantidad * repetir));
            }
        }

        // Formato YYYY-MM-DD
        proximoInputModal.value = fechaActualizacion.toISOString().split('T')[0];
        finalizaInputModal.value = fechaFin.toISOString().split('T')[0];
    }

    document.getElementById('editarRecordatorioModal').addEventListener('shown.bs.modal', function () {
        // Llamamos la función de cálculo una vez al abrir el modal
        calcularFechasModal();

        // Registramos los listeners solo al abrir el modal
        ['fecha-modal', 'intervalo-modal', 'cantidad-modal', 'repetir-modal'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.removeEventListener('input',
                    calcularFechasModal); // Limpieza por si ya existía
                el.addEventListener('input', calcularFechasModal);
            }
        });
    });
});