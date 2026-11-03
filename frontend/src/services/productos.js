const API_URL  = '/api'

export async function obtenerProductos() {
    try {
        const respuesta = await fetch(`${API_URL}/productos.php`)


        if(!respuesta.ok){
            throw new Error('No se pudieron obtener los productos.')
        }

        const productos = await respuesta.json()

        return productos

    } catch (error) {
        console.error('Error al consultar productos', error)
        throw error
    }
}