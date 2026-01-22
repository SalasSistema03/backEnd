/**
 * Gestión de recordatorios
 * Maneja el modal de recordatorios y su funcionalidad
 */

/**
 * Inicializa la funcionalidad del modal de recordatorio
 */
function inicializarRecordatorio() {
    const btnRecordatorio = document.getElementById('btn-recordatorio');
    const recordatorioModal = new bootstrap.Modal(document.getElementById('recordatorioModal'));
    const formRecordatorio = document.getElementById('formRecordatorio');
    const btnGuardarRecordatorio = document.getElementById('btnGuardarRecordatorio');
    
    // Elementos del formulario
    const fechaInput = document.getElementById('fecha_recordatorio');
    const intervaloInput = document.getElementById('intervalo_recordatorio');
    const cantidadInput = document.getElementById('cantidad_recordatorio');
    const repetirInput = document.getElementById('repetir_recordatorio');
    const proximoInput = document.getElementById('proximo_recordatorio');
    const finalizaInput = document.getElementById('finaliza_recordatorio');
    
    // Abrir modal de recordatorio al hacer clic en el botón de campana
    if (btnRecordatorio) {
        btnRecordatorio.addEventListener('click', function(e) {
            abrirModalRecordatorio(e, recordatorioModal, formRecordatorio, fechaInput, cantidadInput, repetirInput);
        });
    }
    
    // Ejecutar cálculo al cambiar cualquier input relevante
    ['fecha_recordatorio', 'intervalo_recordatorio', 'cantidad_recordatorio', 'repetir_recordatorio'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', () => calcularFechas(fechaInput, intervaloInput, cantidadInput, repetirInput, proximoInput, finalizaInput));
        }
    });
    
    // Guardar recordatorio
    if (btnGuardarRecordatorio) {
        btnGuardarRecordatorio.addEventListener('click', function() {
            guardarRecordatorio(formRecordatorio, btnGuardarRecordatorio, recordatorioModal);
        });
    }
}

/**
 * Abre el modal de recordatorio
 */
function abrirModalRecordatorio(e, recordatorioModal, formRecordatorio, fechaInput, cantidadInput, repetirInput) {
    e.preventDefault();
    
    // Obtener el cliente activo actual
    const clienteActivo = document.querySelector('.contacto.active');
    if (!clienteActivo) {
        alert('Por favor, selecciona un cliente primero.');
        return;
    }
    
    // Extraer el ID del cliente del onclick del elemento
    const onclickAttr = clienteActivo.getAttribute('onclick');
    const clienteId = onclickAttr.match(/showChat\((\d+)\)/)[1];
    
    // Establecer el ID del cliente en el formulario
    document.getElementById('recordatorio_cliente_id').value = clienteId;
    
    // Limpiar el formulario
    formRecordatorio.reset();
    
    // Establecer fecha y hora por defecto
    const ahora = new Date();
    const fechaHoy = ahora.toISOString().split('T')[0];
    const horaActual = ahora.toTimeString().slice(0, 5);
    
    fechaInput.value = fechaHoy;
    document.getElementById('hora_recordatorio').value = horaActual;
    cantidadInput.value = '1';
    repetirInput.value = '1';
    
    // Cerrar el menú de acciones
    const actionMenu = document.getElementById('action-menu');
    if (actionMenu.classList.contains('show')) {
        actionMenu.classList.remove('show');
    }
    
    // Mostrar el modal
    recordatorioModal.show();
    
    // Calcular fechas iniciales
    calcularFechas(fechaInput, document.getElementById('intervalo_recordatorio'), cantidadInput, repetirInput, document.getElementById('proximo_recordatorio'), document.getElementById('finaliza_recordatorio'));
}

/**
 * Calcula fechas automáticamente para el recordatorio
 */
function calcularFechas(fechaInput, intervaloInput, cantidadInput, repetirInput, proximoInput, finalizaInput) {
    const fechaInicio = fechaInput.value;
    const intervalo = intervaloInput.value;
    const cantidad = parseInt(cantidadInput.value || 1);
    const repetir = parseInt(repetirInput.value || 1);

    if (!fechaInicio || !intervalo) {
        proximoInput.value = "";
        finalizaInput.value = "";
        return;
    }

    const fecha = new Date(fechaInicio);
    let fechaActualizacion = new Date(fecha);
    let fechaFin = new Date(fecha);

    if (repetir === 1) {
        if (intervalo === 'Diario') {
            fechaActualizacion.setDate(fecha.getDate() + cantidad);
            fechaFin = new Date(fechaActualizacion);
        } else if (intervalo === 'Mensual') {
            fechaActualizacion.setMonth(fecha.getMonth() + cantidad);
            fechaFin = new Date(fechaActualizacion);
        }
    } else {
        if (intervalo === 'Diario') {
            fechaActualizacion.setDate(fecha.getDate() + cantidad);
            fechaFin.setDate(fecha.getDate() + (cantidad * repetir));
        } else if (intervalo === 'Mensual') {
            fechaActualizacion.setMonth(fecha.getMonth() + 1);
            fechaFin.setMonth(fecha.getMonth() + (cantidad * repetir));
        }
    }

    // Formato YYYY-MM-DD
    proximoInput.value = fechaActualizacion.toISOString().split('T')[0];
    finalizaInput.value = fechaFin.toISOString().split('T')[0];
}

/**
 * Guarda el recordatorio via AJAX
 */
function guardarRecordatorio(formRecordatorio, btnGuardarRecordatorio, recordatorioModal) {
    const formData = new FormData(formRecordatorio);
    
    // Agregar datos adicionales necesarios
    formData.append('activo', 1);
    
    // Deshabilitar el botón mientras se procesa
    btnGuardarRecordatorio.disabled = true;
    
    // Debug: Log form data
    console.log('Enviando datos del formulario:');
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }
    
    // Construir URL usando la configuración de Laravel
    const url = window.AsesoresConfig.urls.recordatorioStore;
    
    // Enviar datos via AJAX
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.text().then(text => {
            console.log('Raw response:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                throw new Error('Invalid JSON response');
            }
        });
    })
    .then(data => {
        //console.log('Parsed response data:', data);
        if (data.success) {
            alert('Recordatorio guardado exitosamente');
            recordatorioModal.hide();
            formRecordatorio.reset();
        } else {
            alert('Error al guardar el recordatorio: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        alert('Error al guardar el recordatorio: ' + error.message + '. Por favor, revisa la consola para más detalles.');
    })
    .finally(() => {
        btnGuardarRecordatorio.disabled = false;
    });
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', inicializarRecordatorio);
