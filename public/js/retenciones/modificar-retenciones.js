document.addEventListener("DOMContentLoaded", async () => {
    let cuit_retenciones_m = document.getElementById("cuit_retenciones_m");
    let fecha_comprobante_m = document.getElementById("fecha_comprobante_m");
    let importe_comprobante_m = document.getElementById("importe_comprobante_m");
    let fecha_retenciones_m;
    let numero_comprobante_retenciones_m = document.getElementById(
        "numero_comprobante_retenciones_m"
    );
    let importe_retenciones_m = document.getElementById("importe_retenciones_m");
    let importe_retencion_m = document.getElementById("importe_retencion_m");
    let calcula_base_m = document.getElementById("calcula_base_m");
    const boton_modifica_retencion = document.getElementById(
        "boton_modifica_retencion"
    );
    let id;

    const modal = document.getElementById("modificaion_registros");
    modal.addEventListener("show.bs.modal", async (event) => {
        // Obtener el botón que activó el modal
        const ids = event.relatedTarget;

        // Extraer la información de los datos del botón
        id = ids.getAttribute("data-id");
        console.log(id)
        async function traerSeleccion(id) {
            try {
                try {
                    let response = await fetch(
                        `/api/retenciones/tabla/modificar/${id}`
    
                    );
    
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
    
                    
    
                    data = await response.json();
                    console.log(data);
    
    
                    console.log(data.cuit_retencion);
                    /* cuit_retenciones_m.value = data.cuit_retencion;
                    cuit_retenciones_mi.value = data.cuit_retencion;
                    fecha_comprobante_m.value = data.fecha_comprobante;
                    numero_comprobante_retenciones_m.value = data.numero_comprobante;
                    importe_retenciones_m.value = data.importe_comprobante;
                    importe_retencion_m.value = data.importe_retencion;
                    importe_retencion_mi.value = data.importe_retencion;
                    calcula_base_m.value = data.calcula_base;
                    id_comprobante.value = id; */
                    cuit_retenciones_m.value = data.cuit_retencion;
                    fecha_comprobante_m.value = data.fecha_comprobante;
                    numero_comprobante_retenciones_m.value = data.numero_comprobante;
                    importe_retenciones_m.value = data.importe_comprobante;
                    importe_retencion_m.value = data.importe_retencion;
                    calcula_base_m.value = data.calcula_base;
                    importe_retencion_mi.value = data.importe_retencion;
                   /*  importe_comprobante_m.value = data.importe_comprobante; */
                    cuit_retenciones_mi.value = data.cuit_retencion;
                    id_comprobante.value = id;
    
    
                    importe_retenciones_m.addEventListener("input", async () => {
                        const inputString = importe_retenciones_m.value.replace(",", "."); // Reemplaza comas por puntos
                        const numbers = parseExpression(inputString);
                        const sum = numbers.reduce((acc, num) => acc + num, 0);
                        importe_retencion_m.value = sum.toFixed(2); // Formatea el resultado con 2 decimales
                        async function datos_base_porcentual() {
                            const response = await fetch(
                                "/api/retenciones/base-porcentual"
                            );
                            const data = await response.json();
                            mostrarDatos(data);
                            return data;
                        }
                        let bases_porcentual = await datos_base_porcentual();
                        let base = bases_porcentual[0].dato;
                        let porcentual = bases_porcentual[1].dato;
    
                        if (calcula_base_m.value === "N") {
                            base = 0;
                            console.log(calcula_base_m.value);
                            reg_base = "N";
                        } else {
                            console.log(calcula_base_m.value);
                            reg_base = "S";
                        }
                        console.log("-----");
                        console.log(id);
    
                        console.log(base);
                        console.log(porcentual);
                        console.log(importe_retencion_m.value);
                        let importe_ret;
                        if (importe_retencion_m.value - base <= 0) {
                            importe_ret = 0.0;
                        } else {
                            importe_ret = (importe_retencion_m.value - base) * porcentual;
                        }
                    });
                    
                } catch (error) {
                    let response = await fetch(
                        `salas/salas/public/api/retenciones/tabla/modificar/${id}`
    
                    );
    
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
    
                    
    
                    data = await response.json();
                    console.log(data);
    
    
                    console.log(data.cuit_retencion);
                    /* cuit_retenciones_m.value = data.cuit_retencion;
                    cuit_retenciones_mi.value = data.cuit_retencion;
                    fecha_comprobante_m.value = data.fecha_comprobante;
                    numero_comprobante_retenciones_m.value = data.numero_comprobante;
                    importe_retenciones_m.value = data.importe_comprobante;
                    importe_retencion_m.value = data.importe_retencion;
                    importe_retencion_mi.value = data.importe_retencion;
                    calcula_base_m.value = data.calcula_base;
                    id_comprobante.value = id; */
                    cuit_retenciones_m.value = data.cuit_retencion;
                    fecha_comprobante_m.value = data.fecha_comprobante;
                    numero_comprobante_retenciones_m.value = data.numero_comprobante;
                    importe_retenciones_m.value = data.importe_comprobante;
                    importe_retencion_m.value = data.importe_retencion;
                    calcula_base_m.value = data.calcula_base;
                    importe_retencion_mi.value = data.importe_retencion;
                   /*  importe_comprobante_m.value = data.importe_comprobante; */
                    cuit_retenciones_mi.value = data.cuit_retencion;
                    id_comprobante.value = id;
    
    
                    importe_retenciones_m.addEventListener("input", async () => {
                        const inputString = importe_retenciones_m.value.replace(",", "."); // Reemplaza comas por puntos
                        const numbers = parseExpression(inputString);
                        const sum = numbers.reduce((acc, num) => acc + num, 0);
                        importe_retencion_m.value = sum.toFixed(2); // Formatea el resultado con 2 decimales
                        async function datos_base_porcentual() {
                            const response = await fetch(
                                "/api/retenciones/base-porcentual"
                            );
                            const data = await response.json();
                            mostrarDatos(data);
                            return data;
                        }
                        let bases_porcentual = await datos_base_porcentual();
                        let base = bases_porcentual[0].dato;
                        let porcentual = bases_porcentual[1].dato;
    
                        if (calcula_base_m.value === "N") {
                            base = 0;
                            console.log(calcula_base_m.value);
                            reg_base = "N";
                        } else {
                            console.log(calcula_base_m.value);
                            reg_base = "S";
                        }
                        console.log("-----");
                        console.log(id);
    
                        console.log(base);
                        console.log(porcentual);
                        console.log(importe_retencion_m.value);
                        let importe_ret;
                        if (importe_retencion_m.value - base <= 0) {
                            importe_ret = 0.0;
                        } else {
                            importe_ret = (importe_retencion_m.value - base) * porcentual;
                        }
                    });
                    
                }
                

            } catch (error) {
                console.error("Error:", error);
            }
        }

        traerSeleccion(id);
    });
});



