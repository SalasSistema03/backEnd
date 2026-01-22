
import { mostrarAlertaError } from '../core/utils.js';
function validarDatosCliente() {
    // Obtener el formulario
    let isValid = true; // Bandera para controlar si todos los campos son válidos

    // Validación del campo nombre
    const nombre = document.getElementById("nombre");
    if (nombre.value.trim() === "") {
        isValid = false;
        mostrarAlertaError("El campo Nombre es obligatorio", "!Atención!");
        nombre.focus(); // Coloca el foco en el campo con error
        return false; // Detiene la ejecución
    }

    // Validación del campo teléfono
    const telefono = document.getElementById("telefono");
    if (telefono.value.trim() === "") {
        isValid = false;
        mostrarAlertaError("El campo Teléfono es obligatorio", "!Atención!");
        telefono.focus();
        return false;
    } else if (!/^\d{7,20}$/.test(telefono.value.trim())) {  // Acepta números con 7 a 20 dígitos
        isValid = false;
        mostrarAlertaError("El campo Teléfono debe ser un número válido", "!Atención!");
        telefono.focus();
        return false;
    }

    // Validación del sector de asesor
/*     const sectorAsesor = document.getElementById("sector_asesor");
    if (sectorAsesor.value === "") {
        isValid = false;
        mostrarAlertaError("Selecciona un Sector de Asesor", "!Atención!");
        sectorAsesor.focus();
        return false;
    } */

    // Validación de la asignación del asesor
    const idAsesor = document.getElementById("id_asesor_cliente");
    if (idAsesor.disabled || idAsesor.value === "") {
        isValid = false;
        mostrarAlertaError("Selecciona un Asesor", "!Atención!");
        idAsesor.focus();
        return false;
    }

    // Validación del ingreso por
    const ingreso = document.getElementById("ingreso");
    if (ingreso.value === "") { 
        isValid = false;
        mostrarAlertaError("Selecciona una opción para Ingreso por", "!Atención!");
        ingreso.focus();
        return false;
    }

    // Validación de la inmobiliaria (si aplica)
    const perteneceInmobiliaria = document.getElementById("pertenece_a_inmobiliaria");
    const nombreInmobiliaria = document.getElementById("nombre_de_inmobiliaria");
    if (perteneceInmobiliaria.value === "S" && nombreInmobiliaria.value.trim() === "") {
        isValid = false;
        mostrarAlertaError("Si pertenece a una inmobiliaria, debes proporcionar el nombre de la inmobiliaria.", "!Atención!");
        nombreInmobiliaria.focus();
        return false;
    }

    // Si todas las validaciones son correctas, retorna true
    return isValid;
}


function validarCriteriosBusqueda() {
    let isValid = true;

    // Validación del tipo de inmueble
    const tipoInmueble = document.getElementById("id_tipo_inmueble");
    console.log("Tipo de Inmueble:", tipoInmueble.value);
    if (tipoInmueble.value === "") {
        isValid = false;
        mostrarAlertaError("Selecciona un Tipo de Inmueble", "!Atención!");
        tipoInmueble.focus();
        return false;
    }

    // Validación de la zona
    const zona = document.getElementById("zona");
    if (zona.value === "") {
        isValid = false;
        mostrarAlertaError("Selecciona una Zona", "!Atención!");
        zona.focus();
        return false;
    }

    // Si todo es válido, retornar true
    return isValid;
}



export { validarDatosCliente, validarCriteriosBusqueda }

