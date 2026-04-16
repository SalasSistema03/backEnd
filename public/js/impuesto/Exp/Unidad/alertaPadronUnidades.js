
document.addEventListener('DOMContentLoaded', function () {
    const btnActualizar = document.getElementById('btnActualizarPadron');
    if (!btnActualizar) return;

    btnActualizar.addEventListener('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Confirmar acción',

            html: '¿Estás seguro de que quieres actualizar el padrón?<br>Se borrarán todas las modificaciones',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = btnActualizar.href;
            }
        });
    });
});

