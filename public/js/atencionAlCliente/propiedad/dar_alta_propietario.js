document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.dar-alta').forEach(button => {
        button.addEventListener('click', function() {
            const propiedadId = this.getAttribute('data-propiedad-id');
            const padronId = this.getAttribute('data-padron-id');

            if (confirm('¿Estás seguro de que deseas dar de alta a este propietario?')) {
             /*    fetch("{{ route('propiedad.darDeAlta') }}", { */
                    fetch(window.RUTA_PROPIEDAD + '?codigo=' + encodeURIComponent(query))
                       /*  method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            propiedad_id: propiedadId,
                            padron_id: padronId
                        })
                    }) */
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Propietario dado de alta exitosamente.');
                            location.reload(); // Recargar la página actual
                        } else {
                            alert('Hubo un error al dar de alta al propietario.');
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