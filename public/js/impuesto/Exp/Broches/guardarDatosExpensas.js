import { getBaseUrl, postData } from '../../../core/ajax.js'; // ajusta la ruta según tu estructura
import { mostrarExito, mostrarError, mostrarAlertaError } from '../../../core/utils.js';


export async function guardarDatosExpensas(index) {
    const vencimiento = document.querySelector(`input[name="fecha_vencimiento_${index}"]`).value;
    const importe_extraordinaria = document.querySelector(`input[name="importe_extraordinaria_${index}"]`).value;
    const importe_ordinaria = document.querySelector(`input[name="importe_ordinaria_${index}"]`).value;
    const total = document.querySelector(`input[name="total_${index}"]`).value;
    const periodo = document.querySelector(`input[name="periodo_${index}"]`).value;
    const anio = document.querySelector(`input[name="anio_${index}"]`).value;
    const id_unidad = document.querySelector(`input[name="id_unidad_${index}"]`).value;
    const id_administrador = document.querySelector(`input[name="id_administrador_${index}"]`).value;
    const tipo = document.querySelector(`p[name="tipo_${index}"]`).textContent;

    if (!vencimiento || !periodo || !anio || !id_unidad) {
        mostrarAlertaError('Por favor, complete todos los campos obligatorios antes de guardar.');
        return;
    }

    const payload = {
        vencimiento,
        importe_extraordinaria,
        importe_ordinaria,
        total,
        periodo,
        anio,
        id_unidad,
        tipo,
        id_administrador
    };

    const baseUrl = getBaseUrl(); 
    const result = await postData(`${baseUrl}/exp-broche-expensas/guardar`, payload);

    if (result.ok && result.data.success) {
        mostrarExito('Datos guardados correctamente', '🎉');
                
    } else {
        console.error('Error:', result.data);
        mostrarError('Error al guardar: ' + (result.data.message || 'Error desconocido, consulte a sistema'));
    }
}
