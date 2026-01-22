// arma tabla para mostrar retencioneas abajo
async function datos_tabla() {

    try {
        const response = await fetch('/salas/salas/public/api/retenciones/tabla');

        const data = await response.json();

        mostrarDatos(data);

        return data;
    } catch (error) {
        const response = await fetch('api/retenciones/tabla');

        const data = await response.json();

        mostrarDatos(data);

        return data;

    }
}
let id_trasladable_modificar_registro;

function mostrarDatos(datos) {

    const contenedor = document.getElementById("contenedor");
    let info = "";

    datos.forEach(valor => {
        info += `
         <tr>
             <td class=" text-start">
                 ${valor.razon_social_retencion}
             </td>
             <td class=" text-end">
                 ${valor.cuit_retencion}
             </td>
             <td class=" align-items-center text-end">
                 ${valor.calcula_base}
             </td>
             <td>
                 ${valor.fecha_comprobante}
             </td>
             <td>
                 ${valor.numero_comprobante}
             </td>
             <td>
                 ${valor.importe_comprobante}
             </td>
             <td class=" text-end">
                 ${valor.importe_retencion}
             </td>
             <td>
                 ${valor.fecha_retencion}
             </td>
             <td>
                <button id="boton_exportacion" type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#modificaion_registros" data-id="${valor.id_comprobante}">
                    Modificar
                </button>
             </td>

         </tr>
     `;
        contenedor.innerHTML = info;
    })
}
// Cargar datos inicialmente
datos_tabla();
// Establecer intervalo para actualizar datos en vivo (opcional)
setInterval(datos_tabla, 2000); // Actualizar cada 5 segundos (en milisegundos)