document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalEditarPersona');

    modal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;

        // Obtén los valores de los atributos data-*
        const id = button.getAttribute('data-id-cliente');
        const nombre = button.getAttribute('data-nombre');
        const telefono = button.getAttribute('data-telefono');
        const observaciones = button.getAttribute('data-observaciones');
        const nombre_de_inmobiliaria = button.getAttribute('data-nombre-inmobiliaria');       

        // Asigna los valores a los campos del modal
        modal.querySelector('input[name="id_cliente"]').value = id;
        modal.querySelector('input[name="nombre"]').value = nombre;
        modal.querySelector('input[name="telefono"]').value = telefono;
        modal.querySelector('textarea[name="observaciones"]').value = observaciones;
        modal.querySelector('input[name="nombre_de_inmobiliaria"]').value = nombre_de_inmobiliaria;
      
    });
});
 


