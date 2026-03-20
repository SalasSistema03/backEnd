document.addEventListener('DOMContentLoaded', function() {
    const sectorGuardado = localStorage.getItem('sectorSeleccionado');
    
    if (sectorGuardado) {
        const btnGuardado = document.getElementById(sectorGuardado);
        if (btnGuardado) {
            // Usar Bootstrap Tab API para activar la pestaña
            if (window.bootstrap) {
                const tab = new bootstrap.Tab(btnGuardado);
                tab.show();
            } else if (window.$ && $.fn.tab) {
                // Para Bootstrap 4 o si usas jQuery
                $(btnGuardado).tab('show');
            }
        }
    }

    // Guardar el sector seleccionado al hacer click
    document.querySelectorAll('.sector-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.id;
            localStorage.setItem('sectorSeleccionado', id);
        });
    });

    // ⬇️ AGREGADO: cuando se abre el modal, usar sector guardado
    const modal = document.getElementById("calendarEventModal");
    if (modal) {
        modal.addEventListener("show.bs.modal", function () {
            let sector = localStorage.getItem("sectorSeleccionado");
            
            if (!sector) {
                const activePane = document.querySelector(".tab-pane.show");
                if (activePane) {
                    sector = activePane.id + "-tab";
                }
            }

            console.log("Sector usado al abrir modal:", sector);

            if (sector === "Ventas-tab") {
                //document.getElementById("div-buscar-propiedad").style.display = "block";
                document.getElementById("div-codigo-propiedad").style.display = "block";
                //document.getElementById("div-calle-propiedad").style.display = "block";
            } else {
                //document.getElementById("div-buscar-propiedad").style.display = "none";
               // document.getElementById("div-codigo-propiedad").style.display = "none";
                //document.getElementById("div-calle-propiedad").style.display = "none";
            }
        });
    }
});
