$(document).ready(function() {
    $('input, select, textarea').on('change', function() {
        var campo = $(this).attr('name'); // Obtener el nombre del campo
        var valor = $(this).val(); // Obtener el valor

        $.ajax({
            url: "{{ route('propiedad.guardarCambio') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                campo: campo, // Nombre del input/select
                valor: valor // Valor seleccionado o ingresado
            },
            success: function(response) {
                console.log(campo + ' guardado en la sesión');
            }
        });
    });
});