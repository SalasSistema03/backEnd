
function initCalleSearch(calles) {
    const searchInput = document.getElementById("search-calle");
    const searchResults = document.getElementById("search-results");
    const calleIdInput = document.getElementById("calle_id");

    // Evitar que el spinner se muestre durante la búsqueda de calles
    searchInput.addEventListener("input", function (e) {
        e.stopPropagation(); // Evitar que el evento suba al documento
        const searchTerm = this.value.toLowerCase();
        searchResults.innerHTML = "";

        if (searchTerm.length < 2) {
            searchResults.style.display = "none";
            return;
        }

        const filteredCalles = calles
            .filter((calle) => calle.name.toLowerCase().includes(searchTerm))
            .slice(0, 10); // Limitamos a 10 resultados

        if (filteredCalles.length > 0) {
            searchResults.style.display = "block";

            filteredCalles.forEach((calle) => {
                const div = document.createElement("div");
                div.className = "list-group-item list-group-item-action";
                div.textContent = calle.name;
                div.addEventListener("click", (e) => {
                    e.stopPropagation(); // Evitar que el evento suba al documento
                    searchInput.value = calle.name;
                    calleIdInput.value = calle.id;
                    searchResults.style.display = "none";
                });
                searchResults.appendChild(div);
            });
        } else {
            searchResults.style.display = "none";
        }
    });

    // Cerrar resultados cuando se hace clic fuera
    document.addEventListener("click", function (e) {
        if (
            !searchInput.contains(e.target) &&
            !searchResults.contains(e.target)
        ) {
            searchResults.style.display = "none";
        }
    });
}




function initPersonaSearch(buscarPropietarios) {
    const searchInput = document.getElementById("search-persona");
    const searchResults = document.getElementById("search-results-persona");
    const personaIdInput = document.getElementById("persona_id");

    searchInput.addEventListener("input", function () {
        const searchTerm = this.value.toLowerCase();
        searchResults.innerHTML = "";

        if (searchTerm.length < 2) {
            searchResults.style.display = "none";
            return;
        }

        // Filtrar por nombre y apellido combinados
        const filteredPersonas = buscarPropietarios
            .filter((persona) => persona.name.toLowerCase().includes(searchTerm))
            .slice(0, 10); // Limitar a 10 resultados

        if (filteredPersonas.length > 0) {
            searchResults.style.display = "block";

            filteredPersonas.forEach((persona) => {
                const div = document.createElement("div");
                div.className = "list-group-item list-group-item-action";
                div.textContent = persona.name;
                div.addEventListener("click", () => {
                    searchInput.value = persona.name;
                    personaIdInput.value = persona.id;
                    searchResults.style.display = "none";

                    // Aquí puedes agregar la lógica para asignar la persona a la propiedad
                    asignarPersona(persona.id);
                });
                searchResults.appendChild(div);
            });
        } else {
            searchResults.style.display = "none";
        }
    });

    // Cerrar resultados cuando se hace clic fuera
    document.addEventListener("click", function (e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = "none";
        }
    });
}

// Función para asignar una persona a la propiedad
function asignarPersona(personaId) {
    const propiedadId = "{{ $propiedad->id }}";

    fetch("{{ route('propiedad.asignarPersona') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            propiedad_id: propiedadId,
            persona_id: personaId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Persona asignada exitosamente.');
            location.reload(); // Recargar la página para reflejar los cambios
        } else {
            alert('Hubo un error al asignar la persona.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Hubo un error al procesar la solicitud.');
    });
}


