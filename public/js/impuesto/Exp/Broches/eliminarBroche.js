import { getBaseUrl, deleteData } from '../../../core/ajax.js';

export async function eliminarBroche(id_broche) {
    const baseUrl = getBaseUrl();

    if (confirm('¿Está seguro de eliminar este broche?')) {
        const result = await deleteData(`${baseUrl}/exp-broche-expensas/eliminar/${id_broche}`);

        if (result.ok && result.data.success) {
            alert('Broche eliminado exitosamente');
            location.reload();
        } else {
            console.error('Error:', result.data);
            alert(result.data.message || 'Error al eliminar el broche');
        }
    }
}

window.eliminarBroche = eliminarBroche;

