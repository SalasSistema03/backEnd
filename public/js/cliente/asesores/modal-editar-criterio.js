document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalEditarCriterio');

    modal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;

        // Obtén los valores de los atributos data-*
        const id = button.getAttribute('data-id-criterio');
        const categoria = button.getAttribute('data-categoria');
        const tipo = button.getAttribute('data-tipo');
        const zona = button.getAttribute('data-zona');
        const dorm = button.getAttribute('data-dorm');
        const cochera = button.getAttribute('data-cochera');
        const observaciones = button.getAttribute('data-observaciones');
        const estado = button.getAttribute('data-estado');
        const precio_hasta = button.getAttribute('data-precio-hasta');

        // Asigna los valores a los campos del modal
        modal.querySelector('input[name="id_criterio"]').value = id;
        modal.querySelector('select[name="categoria"]').value = categoria;
        modal.querySelector('select[name="tipo_inmueble"]').value = tipo;
        modal.querySelector('select[name="zona"]').value = zona;
        modal.querySelector('input[name="dormitorios"]').value = dorm;
        modal.querySelector('select[name="cochera"]').value = cochera;
        modal.querySelector('textarea[name="observaciones_criterio_venta"]').value = observaciones;
        modal.querySelector('select[name="estado_criterio_venta"]').value = estado;
        modal.querySelector('input[name="precio_hasta"]').value = precio_hasta;
    });
});
 


