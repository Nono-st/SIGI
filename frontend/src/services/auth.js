
const API_URL = '/api/auth.php'

// Función central para comunicarnos con el backend.
async function solicitarAPI(opciones = {}) {
    const respuesta = await fetch(API_URL, {
        ...opciones,
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            ...opciones.headers,
        },
    })

    const datos = await respuesta.json()

    if (!respuesta.ok) {
        const error = new Error(
            datos.error || 'Ocurrió un error al comunicarse con el servidor.'
        )

        error.status = respuesta.status
        throw error
    }

    return datos
}

// Consultar si existe una sesión activa.
export async function obtenerSesion() {
    try {
        const datos = await solicitarAPI({
            method: 'GET',
        })

        return datos.usuario
    } catch (error) {
        // Si PHP responde 401, simplemente no hay sesión.
        if (error.status === 401) {
            return null
        }

        throw error
    }
}

// Iniciar sesión.
export async function iniciarSesion(usuario, password) {
    const datos = await solicitarAPI({
        method: 'POST',
        body: JSON.stringify({
            usuario,
            password,
        }),
    })

    return datos.usuario
}

// Cerrar sesión.
export async function cerrarSesion() {
    return await solicitarAPI({
        method: 'DELETE',
    })
}
