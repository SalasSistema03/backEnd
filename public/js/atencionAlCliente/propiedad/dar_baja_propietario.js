document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.dar-baja').forEach(button => {
        button.addEventListener('click', function() {
            const propiedadId = this.getAttribute('data-propiedad-id');
            const padronId = this.getAttribute('data-padron-id');
            const observacionesInput = document.querySelector(
                `input[name="observaciones"][data-padron-id="${padronId}"]`);
                event.preventDefault();

            // Validar si el campo de observaciones está vacío
            if (!observacionesInput || observacionesInput.value.trim() === '') {
                alert(
                    'Por favor, ingresa una observación antes de dar de baja al propietario.');
                observacionesInput.focus(); // Enfocar el campo de observaciones
                return;
            }

            if (confirm('¿Estás seguro de que deseas dar de baja a este propietario?')) {
                fetch("{{ route('propiedad.darDeBaja') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            propiedad_id: propiedadId,
                            padron_id: padronId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Propietario dado de baja exitosamente.');
                            // Recargar la página actual
                            location.reload();
                        } else {
                            alert('Hubo un error al dar de baja al propietario.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Hubo un error al procesar la solicitud.');
                    });
            }
        });
    });
});