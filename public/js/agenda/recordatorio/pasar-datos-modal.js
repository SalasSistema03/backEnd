
// Script para manejar la edición de recordatorios
document.addEventListener('DOMContentLoaded', function() {
    // Obtener el modal
    const editarModal = document.getElementById('editarRecordatorioModal');

    // Escuchar cuando se abre el modal
    editarModal.addEventListener('show.bs.modal', function(event) {
        // Botón que activó el modal
        const button = event.relatedTarget;

        // Extraer información de los atributos data-*
        const recordatorioId = button.getAttribute('data-id');
        const descripcion = button.getAttribute('data-descripcion');
        const fechaInicio = button.getAttribute('data-fecha_inicio');
        const hora = button.getAttribute('data-hora');
        const intervalo = button.getAttribute('data-intervalo');
        const cantidad = button.getAttribute('data-cantidad');
        const agendaId = button.getAttribute('data-agenda_id');
        const repetir = button.getAttribute('data-repetir');
        const agendaNombre = button.getAttribute('data-agenda_nombre');

        // Rellenar los campos del formulario
        document.getElementById('recordatorio_id').value = recordatorioId || '';
        document.getElementById('fecha-modal').value = fechaInicio || '';
        document.getElementById('hora-modal').value = hora || '';
        document.getElementById('descripcion-modal').value = descripcion || '';
        document.getElementById('cantidad-modal').value = cantidad || '';
        document.getElementById('repetir-modal').value = repetir || '';
        const selectAgenda = document.getElementById('agenda_nombre-modal');

        if (agendaNombre && selectAgenda) {
            const buscado = agendaNombre.trim().toLowerCase();

            for (let i = 0; i < selectAgenda.options.length; i++) {
                const textoOpcion = selectAgenda.options[i].textContent.trim().toLowerCase();

                if (textoOpcion.includes(buscado) || buscado.includes(textoOpcion)) {
                    selectAgenda.selectedIndex = i;
                    break;
                }
            }
        }

        // Seleccionar la opción correcta en el select de intervalo
        const selectIntervalo = document.getElementById('intervalo-modal');
        if (intervalo) {
            selectIntervalo.value = intervalo;
        } else {
            selectIntervalo.selectedIndex = 0; // Seleccionar "Seleccione"
        }

        // Actualizar la acción del formulario con el ID del recordatorio
        const form = document.getElementById('formEditarRecordatorio');
    });

    // Limpiar el formulario cuando se cierra el modal
    editarModal.addEventListener('hidden.bs.modal', function() {
        const form = document.getElementById('formEditarRecordatorio');
        form.reset();

        // Limpiar campos hidden
        document.getElementById('recordatorio_id').value = '';

        // Resetear selects a su primera opción
        document.getElementById('intervalo-modal').selectedIndex = 0;
        document.getElementById('agenda_id-modal').selectedIndex = 0;
    });
});




