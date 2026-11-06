const API_URL = '/api/categorias.php'

// CONSULTAR CATEGORIAS

export async function  obtenerCategorias() {
    const respuesta = await fetch(API_URL, {
        credentials: 'include'
    })

    const datos = await respuesta.json()

    if(!respuesta.ok){
        throw new Error(datos.error || "No se pudieron obtener las categoiras")
    }
    return datos
}

// REGISTRAR CATEGORIA
export async function crearCategoria(categoria) {
    const respuesta = await fetch(API_URL, {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(categoria)
    })

    const datos = await respuesta.json()

    if (!respuesta.ok) {
        throw new Error(datos.error || 'No se pudo registrar la categoría.')
    }

    return datos
}

// MODIFICAR CATEGORIA
export async function actualizarCategoria(id, categoria) {
    const respuesta = await fetch(`${API_URL}?id=${id}`, {
        method: 'PUT',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(categoria)
    })

    const datos = await respuesta.json()

    if (!respuesta.ok) {
        throw new Error(datos.error || 'No se pudo modificar la categoría.')
    }

    return datos
}

// ELIMINAR CATEGORIA
export async function eliminarCategoria(id) {
    const respuesta = await fetch(`${API_URL}?id=${id}`, {
        method: 'DELETE',
        credentials: 'include'
    })

    const datos = await respuesta.json()

    if (!respuesta.ok) {
        throw new Error(datos.error || 'No se pudo eliminar la categoría.')
    }

    return datos
}
