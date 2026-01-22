document.addEventListener('DOMContentLoaded', function () {
    const actionMenuButton = document.getElementById('action-menu-button');
    const actionMenu = document.getElementById('action-menu');

    actionMenuButton.addEventListener('click', function (event) {
        event.stopPropagation(); // Evita que el clic se propague al documento
        actionMenu.classList.toggle('show');
    });

    // Cierra el menú si se hace clic en cualquier otro lugar
    document.addEventListener('click', function () {
        if (actionMenu.classList.contains('show')) {
            actionMenu.classList.remove('show');
        }
    });
});