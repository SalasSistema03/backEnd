function eliminarUnidad(boton) {
    const baseUrl = window.location.origin; 
     //console.log('baseUrl', baseUrl);
    
    const unidadRow = boton.closest('.unidad-row');
    const inputId = unidadRow.querySelector('input[name*="[id]"]');
    const id = inputId ? inputId.value : null;
    //console.log('ID:', id);

    if (unidadRow) {
        unidadRow.remove();
    }


    fetch(`${baseUrl}/exp-unidades/eliminar/${id}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (response.ok) {
                // Remove the row from the table
                const row = boton.closest('tr');
                if (row) {
                    row.remove();
                }

                /* Swal.fire({
                    icon: 'success',
                    title: 'Unidad eliminada correctamente',
                    showConfirmButton: false,
                    timer: 1500
                }); */
            } else {
                throw new Error('Error al eliminar la unidad');
            }
        })
        .catch(error => {
            console.error('Error al eliminar la unidad:', error);
            alert(error.message || 'Error al eliminar la unidad');
        });
}
