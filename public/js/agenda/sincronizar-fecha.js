document.addEventListener('DOMContentLoaded', function() {
    const fechaPrincipal = document.getElementById('fechaPrincipal');
    const fechaNota = document.getElementById('fechaNota');
    if (fechaPrincipal && fechaNota) {
        // Sincroniza el valor inicial
        fechaNota.value = fechaPrincipal.value;
        // Actualiza el valor del formulario cuando cambie el input principal
        fechaPrincipal.addEventListener('change', function() {
            fechaNota.value = fechaPrincipal.value;
        });
    }
});