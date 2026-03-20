
document.addEventListener('DOMContentLoaded', function () {
    // Recuperar valores de localStorage al cargar la página
    const inputs = document.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        const savedValue = localStorage.getItem(input.name);
        if (savedValue) {
            input.value = savedValue;
        }

        // Guardar valores en localStorage al cambiar el contenido
        input.addEventListener('input', function () {
            localStorage.setItem(input.name, input.value);
        });
    });

    const form = document.querySelector('form');
    form.addEventListener('submit', function () {
        localStorage.clear(); // Limpiar localStorage al enviar el formulario
    });
});
