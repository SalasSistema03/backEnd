/**
 * utils.js
 * ----------------------------------------
 * Archivo de utilidades genéricas para el sistema.
 * Contiene funciones auxiliares que pueden ser reutilizadas en distintos módulos,
 * como formateo de fechas, validación básica de campos, capitalización de textos
 * o limpieza de formularios.
 *
 * No depende de lógica de negocio específica.
 */


// utils.js


export function mostrarExito(mensaje, titulo = '') {
    Swal.fire({
        toast: true,
        position: 'top-end',  // esquina superior derecha
        icon: 'success',
        title: titulo,
        text: mensaje,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        // Opcional: cerrar al pasar el mouse
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
}

export function mostrarError(mensaje, titulo = '') {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: titulo,
        text: mensaje,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
}

export function mostrarAlertaError(mensaje, titulo = 'Atención') {
    Swal.fire({
        icon: 'warning',
        title: titulo,
        text: mensaje,
        confirmButtonText: 'Aceptar',
        // Opcional para que no se cierre tocando fuera
        allowOutsideClick: false
    });
}


export function pregunaConfirmacion(mensaje, titulo = 'Atención') {
    return Swal.fire({
        title: titulo,
        text: mensaje,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, actualizar"
    });
}
