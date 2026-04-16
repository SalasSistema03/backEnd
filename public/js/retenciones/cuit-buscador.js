const cuit_retenciones = document.getElementById('cuit_retenciones');
const razon_social_retenciones = document.getElementById('razon_social_retenciones');

cuit_retenciones.addEventListener('input', async () => {
    const valorCuit = cuit_retenciones.value;
    if (valorCuit.length === 11) {
        console.log(valorCuit);

        let todos_padron = await personasCargadas()
        todos_padron.forEach(valor => {
            if (valor.cuit_retencion == cuit_retenciones.value) {
                console.log(valor)
                razon_social_retenciones.value = valor.razon_social_retencion
            }
        });

    } else {
        razon_social_retenciones.value = "Completar con los 11 digitos del CUIT"
    }
});
async function personasCargadas() {
    try {
        const response = await fetch('/salas/salas/public/api/retenciones/personas');
        const data = await response.json();
        return data;
    }catch (error) {
        const response = await fetch('/api/retenciones/personas');
        const data = await response.json();
        return data;
    }
    
};


const cuit_carga_retenciones = document.getElementById("cuit_carga_retenciones");
const razon_social_carga_retenciones = document.getElementById("razon_social_carga_retenciones");
cuit_carga_retenciones.addEventListener("input", () => {
    const valorCuit = cuit_carga_retenciones.value;
    //console.log(valorCuit.length )
    if (valorCuit.length === 11) {
        razon_social_carga_retenciones.value = "";
    } else {
        razon_social_carga_retenciones.value =
        "Completar con los 11 digitos del CUIT";
    }
  });