async function obtenerRetencionesCuit(cuit) {
  try {
    try {
      // Verificar que el CUIT no esté vacío
      if (!cuit || cuit.trim() === '') {
        throw new Error("El CUIT es requerido");
      }

      // Crear URL con parámetro de consulta
      /*  const url = `/salas/salas/public/retenciones/obtener-por-cuit`; */
      const url = `/retenciones/obtener-por-cuit?cuit=${encodeURIComponent(cuit)}`;
      let response = await fetch(url);

      if (!response.ok) {
        throw new Error("Network response was not ok");
      }

      let data = await response.json();
      return data;

    } catch (error) {
      // Verificar que el CUIT no esté vacío
      if (!cuit || cuit.trim() === '') {
        throw new Error("El CUIT es requerido");
      }

      // Crear URL con parámetro de consulta
      /*  const url = `/salas/salas/public/retenciones/obtener-por-cuit`; */
      const url = `salas/salas/public/retenciones/obtener-por-cuit?cuit=${encodeURIComponent(cuit)}`;
      let response = await fetch(url);

      if (!response.ok) {
        throw new Error("Network response was not ok");
      }

      let data = await response.json();
      return data;

    }

  } catch (error) {
    console.error("Error:", error);
    // Mostrar mensaje de error al usuario
    alert(`Error al obtener retenciones: ${error.message}`);
    return [];
  }
}

async function mostrarDatosRetCuit(data) {
  let contenedor = document.getElementById("contenedor_retenciones_x_cuit");
  let info = "";

  // Check if datos is an array
  if (!Array.isArray(data)) {
    console.error("Expected an array but received:", data);
    contenedor.innerHTML = "<tr><td colspan='4' class='text-center'>Error al procesar los datos</td></tr>";
    return;
  }

  if (data.length === 0) {
    contenedor.innerHTML = "<tr><td colspan='4' class='text-center'>No se encontraron retenciones para este CUIT</td></tr>";
    return;
  }

  data.forEach((valor) => {
    info += `
           <tr>
               <td>
                   ${valor.razon_social_retencion}
               </td>
               <td>
                   ${valor.cuit_retencion}
               </td>
                <td>
                   ${valor.fecha_retencion}
               </td>
               <td>
                   ${valor.importe_retencion}
               </td>   
           </tr>
       `;
  });

  contenedor.innerHTML = info;
}

// Función para iniciar la búsqueda cuando el usuario lo solicite
async function buscarRetencionesPorCuit() {
  const cuitInput = document.getElementById("input_cuit_retencion");
  const cuit = cuitInput.value.trim();

  if (!cuit) {
    alert("Por favor, ingrese un CUIT válido");
    return;
  }

  // Mostrar indicador de carga
  const contenedor = document.getElementById("contenedor_retenciones_x_cuit");
  contenedor.innerHTML = "<tr><td colspan='4' class='text-center'>Cargando...</td></tr>";

  const retenciones = await obtenerRetencionesCuit(cuit);
  mostrarDatosRetCuit(retenciones);
}

// Agregar event listener para el botón de búsqueda
document.addEventListener('DOMContentLoaded', function () {
  // Verificar si estamos en la página correcta
  if (document.getElementById('input_cuit_retencion')) {
    // Agregar event listener para buscar al presionar Enter en el campo de CUIT
    document.getElementById('input_cuit_retencion').addEventListener('keypress', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        buscarRetencionesPorCuit();
      }
    });
  }
});