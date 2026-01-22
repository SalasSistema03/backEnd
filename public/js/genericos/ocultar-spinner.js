
    function ocultarSpinner() {
        var spinner = document.querySelector('.spinner-wrapper');
        if (spinner) spinner.style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        ocultarSpinner(); // Lo oculta al cargar la página

        // Si querés ocultarlo al enviar cualquier formulario
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function() {
                ocultarSpinner();
            });
        });
    });

