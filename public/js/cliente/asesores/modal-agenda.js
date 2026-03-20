document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('calendarEventModal');

    modal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;

        // Obtén los valores de los atributos data-*
        
        const id_criterio_venta = document.getElementById('input-id-criterio').value;
        //console.log("id_criterio_venta: yaaaaaaaaaaaaaaaaaaaaa",id_criterio_venta);
        // Asigna los valores a los campos del modal
        modal.querySelector('input[name="id_criterio"]').value = id_criterio_venta;
        
    });
});
 

