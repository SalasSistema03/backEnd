const suma_retenciones = document.getElementById("suma_retenciones");
const importe_retencion = document.getElementById("importe_retencion");
let reg_base;
suma_retenciones.addEventListener("input", async () => {

  const inputString = suma_retenciones.value.replace(",", "."); // Reemplaza comas por puntos
  const numbers = parseExpression(inputString);
  const sum = numbers.reduce((acc, num) => acc + num, 0);
  importe_retenciones.value = sum.toFixed(2); // Formatea el resultado con 2 decimales
  // ver si corresponde segun busqueda de cuit en base de datos segun fecha de comprobante
  // obtiene de base de datos

  async function datos_base_porcentual() {
    let response = null;
    const urls = [
      '/api/retenciones/base-porcentual',
      '/salas/salas/public/api/retenciones/base-porcentual'
  ];
  
  for (const url of urls) {
      try {
          const response = await fetch(url);
          if (response.ok) {
              return await response.json();
          }
      } catch (error) {
          /* console.log(`Failed to fetch from ${url}, trying next URL...`); */
      }
  }
    const data = await response.json();
    return data;
  }
  let bases_porcentual = await datos_base_porcentual();
  let base = bases_porcentual[0].dato;
  let porcentual = bases_porcentual[1].dato;


  const parametros = {
    cuit_retencion: cuit_retenciones.value,
    fecha_comprobante: fecha_retenciones.value,
  };

  let existenComprobantes = await verificaBaseRetencion(parametros);
  console.log(existenComprobantes);

  if (existenComprobantes != null) {
    base = 0;
    reg_base = "N";
  } else {
    reg_base = "S";
  }
  /* console.log(base);
  console.log(porcentual);
  console.log(importe_retenciones.value); */
  let importe_ret = (importe_retenciones.value - base) * porcentual;
  importe_retencion.value = importe_ret.toFixed(2);
});

function parseExpression(expression) {
  const terms = expression.split(/(\+|\-|\*)/).map((term) => term.trim());
  let numbers = [];
  let currentNumber = 0;
  let currentOperator = "+";

  terms.forEach((term) => {
    if (term === "+" || term === "-" || term === "*") {
      currentOperator = term;
    } else {
      const num = parseFloat(term);
      switch (currentOperator) {
        case "+":
          currentNumber += num;
          break;
        case "-":
          currentNumber -= num;
          break;
        case "*":
          currentNumber *= num;
          break;
      }
    }
  });
  numbers.push(currentNumber);
  return numbers;
}




async function verificaBaseRetencion(parametros) {
  try {
    console.log("Enviando solicitud con parámetros:", parametros);

    // Construir la URL con los parámetros
    const url = new URL("/api/retenciones/comprobante", window.location.origin);

    // Añadir parámetros a la URL
    Object.keys(parametros).forEach(key => {
      if (parametros[key] !== undefined && parametros[key] !== '') {
        url.searchParams.append(key, parametros[key]);
      }
    });

    console.log("URL de la petición:", url.toString());

    const response = await fetch(url, {
      method: "GET",
      headers: {
        "Accept": "application/json"
      }
    });

    console.log("Respuesta recibida - Estado:", response.status, response.statusText);

    const data = await response.json();
    console.log("Datos recibidos:", data);

    if (!response.ok) {
      throw new Error(`Error del servidor: ${response.status} ${response.statusText}`);
    }

    if (data.exito) {
      console.log("Datos devueltos exitosamente:", data.datos);
      return data.datos;
    } else {
      console.warn("La petición no fue exitosa:", data.message || 'Sin mensaje de error');
      return null;
    }
  } catch (error) {
    console.error("Error en verificaBaseRetencion:", error);
    return null;
  }
}


document.getElementById('form_retenciones').addEventListener('submit', function(e) {
  const valorJS = reg_base;

  /* document.getElementById('importe_retenciones2').value = importe_retencion; */
 /*  document.getElementById('importe_retencion2').value = importe_retencion; */
  document.getElementById('calcula_base').value = valorJS;
  
});