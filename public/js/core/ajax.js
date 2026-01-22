/**
 * ajax.js
 * ----------------------------------------
 * Archivo que junta todas las funciones relacionadas con solicitudes AJAX al servidor.
 * Provee métodos reutilizables para consumir rutas (endpoints) de Laravel, las que respondne un Json.
 * utilizando fetch o jQuery, incluyendo el manejo automático del token CSRF.
 *
 * Ideal para llamadas GET, POST, PUT y DELETE sin recargar la página.
 */

// Obtener el token CSRF del meta tag
export function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

// Obtener la base URL (dinámica desde Blade)
export function getBaseUrl() {
    return document.querySelector('meta[name="base-url"]').getAttribute('content');
}


// utils.js o como lo llames
export async function getData(url) {
    console.log('URL:', url);
    const response = await fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest' // <- muy importante para que Laravel sepa que es AJAX
        }
    });

    const data = await response.json().catch(() => null); // Por si no hay JSON (evita romper)
    console.log('Response Data:', data);

    return {
        ok: response.ok,
        status: response.status,
        data
    };
}



// Función para enviar POST con fetch
export async function postData(url, data = {}) {
    console.log('URL:', url);
    console.log('Data:', data);
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest' // <- muy importante para que Laravel sepa que es AJAX
            },
            body: JSON.stringify(data)
        });
        console.log('Response:', response); 

        const responseData = await response.json();

        return {
            ok: response.ok,
            status: response.status,
            data: responseData
        };

    } catch (error) {
        return {
            ok: false,
            status: 500,
            data: {
                success: false,
                message: 'Error de red o del servidor: ' + error.message
            }
        };
    }
}



export async function deleteData(url) {
    console.log('URL:', url);
    try {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const responseData = await response.json().catch(() => null);

        return {
            ok: response.ok,
            status: response.status,
            data: responseData
        };
    } catch (error) {
        return {
            ok: false,
            status: 500,
            data: {
                success: false,
                message: 'Error de red o del servidor: ' + error.message
            }
        };
    }
}





