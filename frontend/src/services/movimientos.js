const API_URL = '/api/movimientos.php'

// CONSULTAR MOVIMIENTOS

export async function obtenerMovimientos() {
    const respuesta = await fetch(API_URL, {
        credentials: 'include'
    })

    const datos = await respuesta.json()

    if (!respuesta.ok) {
        throw new Error(datos.error || 'No se pudieron obtener los movimientos.')
    }

    return datos.movimientos
}

// REGISTRAR MOVIMIENTO

export async function crearMovimiento(movimiento) {
    const respuesta = await fetch(API_URL, {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(movimiento)
    })

    const datos = await respuesta.json()

    if (!respuesta.ok) {
        throw new Error(datos.error || 'No se pudo registrar el movimiento.')
    }

    return datos
}