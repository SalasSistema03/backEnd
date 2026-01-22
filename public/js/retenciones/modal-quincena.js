async function ejecuta_suma(mesAnio) {
    let anio = mesAnio.substring(0, 4);
    let mes = mesAnio.substring(5, 7);
    

  
    async function suma_quincena(anio, mes) {
        try {
            try {
                const response = await fetch('salas/salas/public/retenciones/suma-quincena', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        anio: anio,
                        mes: mes
                    })
                });
    
                if (!response.ok) {
                    throw new Error('Error en la petición');
                }
    
                return await response.json();
            } catch (error) {
                const response = await fetch('/retenciones/suma-quincena', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        anio: anio,
                        mes: mes
                    })
                });
    
                if (!response.ok) {
                    throw new Error('Error en la petición');
                }
    
                return await response.json();
            }
            
        } catch (error) {
            console.error('Error:', error);
            throw error;
        }
    }

  
    try {
        let total_quincena = await suma_quincena(anio, mes);
        let suma_primera = 0;
        let suma_segunda = 0;

        total_quincena.forEach((valor) => {
            
            if (valor.fecha_comprobante) {
                const monto_suma = parseFloat(valor.importe_retencion);
               /*  const dia_suma = new Date(valor.fecha_comprobante).getDate(); */
               const [anio, mes, dia] = valor.fecha_comprobante.split('-').map(Number);
const dia_suma = new Date(anio, mes - 1, dia).getDate();  // mes - 1 porque en JavaScript los meses van de 0 a 11
                console.log("dia_sumaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa",dia_suma,valor.fecha_comprobante);
                if (dia_suma <= 15) {
                    suma_primera += monto_suma;
                } else {
                    suma_segunda += monto_suma;
                }
                console.log(suma_primera);
                console.log(suma_segunda);
            }
        });

        return {
            suma_primera: parseFloat(suma_primera.toFixed(2)),
            suma_segunda: parseFloat(suma_segunda.toFixed(2)),
        };
    } catch (error) {
        console.error('Error en ejecuta_suma:', error);
        return {
            suma_primera: 0,
            suma_segunda: 0
        };
    }
}
  
 // Resto de tu código del event listener se mantiene igual
document.addEventListener("DOMContentLoaded", () => {
    const boton_suma_quincena = document.getElementById("boton_suma_quincena");
    let suma_primer_quincena = document.getElementById("suma_primer_quincena");
    let suma_segunda_quincena = document.getElementById("suma_segunda_quincena");
    
    boton_suma_quincena.addEventListener("click", async () => {
        let fecha_suma_retencion = document.getElementById("fecha_suma_retencion");
        let mesAnio = fecha_suma_retencion.value;
        
        if (!mesAnio) {
            alert('Por favor seleccione una fecha');
            return;
        }

        try {
            const resultados = await ejecuta_suma(mesAnio);
            console.log("Suma primera quincenaAAAAAAAAAAAAAA:", resultados);
            console.log("Suma primera quincena:", resultados.suma_primera);
            console.log("Suma segunda quincena:", resultados.suma_segunda);
            suma_primer_quincena.value = resultados.suma_primera;
            suma_segunda_quincena.value = resultados.suma_segunda;
        } catch (error) {
            suma_primer_quincena.value = "0";
            suma_segunda_quincena.value = "0";
            console.error("Ocurrió un error:", error.message);
        }
    });
});