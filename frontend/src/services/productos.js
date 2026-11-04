
const API_URL = '/api/productos.php'

// CONSULTAR PRODUCTOS
export async function obtenerProductos() {
    const respuesta = await fetch(API_URL)
    const datos = await respuesta.json()

    if (!respuesta.ok) {
        throw new Error(datos.error || 'No se pudieron obtener los productos.')
    }

    return datos
}

// REGISTRAR PRODUCTO
export async function crearProducto(producto) {
    const respuesta = await fetch(API_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(producto)
    })

    const datos = await respuesta.json()

    if (!respuesta.ok) {
        throw new Error(datos.error || 'No se pudo registrar el producto.')
    }

    return datos
}

// MODIFICAR PRODUCTO
export async function actualizarProducto(id, producto) {
    const respuesta = await fetch(`${API_URL}?id=${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(producto)
    })

    const datos = await respuesta.json()

    if (!respuesta.ok) {
        throw new Error(datos.error || 'No se pudo modificar el producto.')
    }

    return datos
}

// ELIMINAR PRODUCTO
export async function eliminarProducto(id) {
    const respuesta = await fetch(`${API_URL}?id=${id}`, {
        method: 'DELETE'
    })

    const datos = await respuesta.json()

    if (!respuesta.ok) {
        throw new Error(datos.error || 'No se pudo eliminar el producto.')
    }

    return datos
}
