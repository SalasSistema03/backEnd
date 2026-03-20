// Función principal para inicializar el buscador de propietarios
function initPropietarioSearch(propietarios) {
    // Obtener elementos del DOM
    const searchInput = document.getElementById("search-propietario");
    const searchResults = document.getElementById("search-results-prop");
    const propietarioIdInput = document.getElementById("propietario_id");

    // Función para filtrar y mostrar resultados
    function mostrarResultados(searchTerm) {
        // Limpiar resultados anteriores
        searchResults.innerHTML = "";

        // Si el término de búsqueda es menor a 2 caracteres, ocultar resultados
        if (searchTerm.length < 2) {
            searchResults.style.display = "none";
            return;
        }

        // Filtrar propietarios que coincidan con el término de búsqueda
        const filteredPropietarios = propietarios
            .filter(propietario => propietario && propietario.nombre && propietario.apellido &&
                (`${propietario.nombre} ${propietario.apellido} ${propietario.documento}`.toLowerCase()
                    .includes(searchTerm.toLowerCase())))
            .slice(0, 10); // Limitar a 10 resultados

        // Mostrar resultados si hay coincidencias
        if (filteredPropietarios.length > 0) {
            searchResults.style.display = "block";

            // Crear elementos para cada resultado
            filteredPropietarios.forEach(propietario => {
                const resultado = document.createElement("div");
                resultado.className = "list-group-item list-group-item-action";
                // Mostrar solo nombre y apellido si documento es null o vacío
                if (!propietario.documento || propietario.documento.trim() === "") {
                    resultado.textContent = `${propietario.nombre} ${propietario.apellido}`;
                } else {
                    resultado.textContent = `${propietario.documento} ${propietario.nombre} ${propietario.apellido}`;
                }

                // Agregar evento click para seleccionar propietario
                resultado.addEventListener("click", () => {
                    if (!propietario.documento || propietario.documento.trim() === "") {
                        searchInput.value = `${propietario.nombre} ${propietario.apellido}`;
                    } else {
                        searchInput.value = `${propietario.documento} ${propietario.nombre} ${propietario.apellido}`;
                    }
                    propietarioIdInput.value = propietario.id;
                    searchResults.style.display = "none";
                });

                searchResults.appendChild(resultado);
            });
        } else {
            searchResults.style.display = "none";
        }
    }

    // Evento input para el buscador
    searchInput.addEventListener("input", function() {
        mostrarResultados(this.value);
    });

    // Evento click fuera del buscador para ocultar resultados
    document.addEventListener("click", function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = "none";
        }
    });
}

// Evento DOMContentLoaded para inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Esperamos que la vista pase los propietarios como parámetro
    const propietarios = window.propietariosData;
    
    if (propietarios) {
        initPropietarioSearch(propietarios);
    }
});