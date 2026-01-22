document.getElementById('fechaPrincipal').addEventListener('change', function() {
    window.location.href = '?fecha=' + this.value;
});