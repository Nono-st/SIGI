const API_URL =  '/api/usuarios.php'

// Funcion central para realizar peticiones al backend

async function solicitarAPI(url, opciones = {}) {
    const respuesta = await fetch(url, {
        ...opciones,
        credentials: 'include',
        headers: {
            'Content-type': 'application/json',
            ...opciones.headers,
        },
    })

    const datos = await respuesta.json()

    if(!respuesta.ok){
        throw new Error(
            datos.error || 'Ocurrio un error al comunicarse con el servidor'
        )
    }
    return datos
}

// obtener todos los usuarios.
export async function obtenerUsuarios() {
    const datos = await solicitarAPI(API_URL)
    return datos.usuarios
}

// Crear un usuario.
export async function crearUsuario(usuario) {
    return await solicitarAPI(API_URL, {
        method: 'POST',
        body: JSON.stringify(usuario),
    })
}

// modificar usuario.

export async function actualizarUsuario(id, usuario) {
    return await solicitarAPI(`${API_URL}?id=${id}`, {
        method: 'PUT',
        body: JSON.stringify(usuario),
    })
}

// Eliminar un usuario

// Eliminar un usuario
export async function eliminarUsuario(id) {
    return await solicitarAPI(`${API_URL}?id=${id}`, {
        method: 'DELETE',
    })
}