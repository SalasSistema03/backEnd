/* document.addEventListener('DOMContentLoaded', function() {
    const fechaInput = document.getElementById('fechaPrincipal');
    const tabContent = document.getElementById('v-pills-tabContent');
    let sectorSeleccionado = null;
    document.querySelectorAll('.sector-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            sectorSeleccionado = this.getAttribute('data-sector');
            mostrarTablaSiListo();
        });
    });
    fechaInput.addEventListener('change', mostrarTablaSiListo);

    function mostrarTablaSiListo() {
        if (fechaInput.value && sectorSeleccionado) {
            tabContent.style.display = '';
        } else {
            tabContent.style.display = 'none';
        }
    }
    mostrarTablaSiListo();
}); */