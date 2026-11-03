<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { obtenerProductos } from '../services/productos'

// Datos provenientes del backend.
const productos = ref([])
const cargando = ref(true)
const error = ref('')

// Controles de búsqueda y filtros.
const busqueda = ref('')
const categoriaSeleccionada = ref('')
const estadoSeleccionado = ref('')

// Paginación.
const paginaActual = ref(1)
const productosPorPagina = 8

// Consulta los productos al backend.
async function cargarProductos() {
    cargando.value = true
    error.value = ''

    try {
        productos.value = await obtenerProductos()
    } catch (err) {
        error.value = 'No se pudieron cargar los productos.'
        console.error(err)
    } finally {
        cargando.value = false
    }
}

onMounted(() => {
    cargarProductos()
})

// Obtiene las categorías existentes sin repetirlas.
const categorias = computed(() => {
    return [...new Set(productos.value.map(producto => producto.categoria))]
        .filter(Boolean)
        .sort()
})

// Calcula el estado de cada producto.
function obtenerEstado(producto) {
    if (Number(producto.stock) === 0) {
        return 'Sin stock'
    }

    if (Number(producto.stock) <= Number(producto.stock_minimo)) {
        return 'Stock bajo'
    }

    return 'Disponible'
}

// Estadísticas generales del inventario.
const totalProductos = computed(() => productos.value.length)

const productosStockBajo = computed(() => {
    return productos.value.filter(producto =>
        Number(producto.stock) > 0 &&
        Number(producto.stock) <= Number(producto.stock_minimo)
    ).length
})

const productosSinStock = computed(() => {
    return productos.value.filter(producto =>
        Number(producto.stock) === 0
    ).length
})

const valorInventario = computed(() => {
    return productos.value.reduce((total, producto) => {
        return total + Number(producto.precio) * Number(producto.stock)
    }, 0)
})

// Aplica búsqueda y filtros.
const productosFiltrados = computed(() => {
    return productos.value.filter(producto => {
        const coincideNombre = producto.nombre
            .toLowerCase()
            .includes(busqueda.value.toLowerCase().trim())

        const coincideCategoria = !categoriaSeleccionada.value ||
            producto.categoria === categoriaSeleccionada.value

        const coincideEstado = !estadoSeleccionado.value ||
            obtenerEstado(producto) === estadoSeleccionado.value

        return coincideNombre && coincideCategoria && coincideEstado
    })
})

// Calcula las páginas necesarias.
const totalPaginas = computed(() => {
    return Math.max(
        1,
        Math.ceil(productosFiltrados.value.length / productosPorPagina)
    )
})

// Productos que se muestran en la página actual.
const productosPaginados = computed(() => {
    const inicio = (paginaActual.value - 1) * productosPorPagina

    return productosFiltrados.value.slice(
        inicio,
        inicio + productosPorPagina
    )
})

// Reinicia la página cuando se modifican los filtros.
watch(
    [busqueda, categoriaSeleccionada, estadoSeleccionado],
    () => {
        paginaActual.value = 1
    }
)

// Limpia todos los filtros.
function limpiarFiltros() {
    busqueda.value = ''
    categoriaSeleccionada.value = ''
    estadoSeleccionado.value = ''
    paginaActual.value = 1
}

// Formatea los precios en pesos argentinos.
function formatearPrecio(precio) {
    return Number(precio).toLocaleString('es-AR', {
        style: 'currency',
        currency: 'ARS',
        maximumFractionDigits: 2
    })
}
</script>

<template>
    <section class="products-page">

        <!-- Encabezado -->
        <div class="page-header">
            <div>
                <h1>Productos</h1>
                <p>Administrá los productos de tu inventario.</p>
            </div>

            <button class="btn-primary" disabled title="Disponible en la próxima etapa">
                <span>＋</span>
                Nuevo producto
            </button>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">

            <article class="stat-card">
                <div class="stat-icon icon-blue">▦</div>
                <div>
                    <p class="stat-label">Total de productos</p>
                    <h2>{{ totalProductos }}</h2>
                    <span class="stat-description">Productos registrados</span>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon icon-green">✓</div>
                <div>
                    <p class="stat-label">Disponibles</p>
                    <h2>{{ totalProductos - productosStockBajo - productosSinStock }}</h2>
                    <span class="stat-description">Con stock suficiente</span>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon icon-yellow">!</div>
                <div>
                    <p class="stat-label">Stock bajo</p>
                    <h2>{{ productosStockBajo }}</h2>
                    <span class="stat-description">Necesitan reposición</span>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon icon-red">×</div>
                <div>
                    <p class="stat-label">Sin stock</p>
                    <h2>{{ productosSinStock }}</h2>
                    <span class="stat-description">Productos agotados</span>
                </div>
            </article>

        </div>

        <!-- Valor total del inventario -->
        <div class="inventory-value">
            <div>
                <span>Valor del inventario</span>
                <p>Calculado según el precio y las unidades disponibles.</p>
            </div>

            <strong>{{ formatearPrecio(valorInventario) }}</strong>
        </div>

        <!-- Buscador y filtros -->
        <div class="filters-card">
            <div class="filters-heading">
                <div>
                    <h2>Inventario</h2>
                    <p>Consultá y filtrá los productos registrados.</p>
                </div>

                <button class="btn-clear" @click="limpiarFiltros">
                    Limpiar filtros
                </button>
            </div>

            <div class="filters-row">
                <div class="search-box">
                    <span>⌕</span>
                    <input
                        v-model="busqueda"
                        type="text"
                        placeholder="Buscar por nombre de producto..."
                    >
                </div>

                <select v-model="categoriaSeleccionada">
                    <option value="">Todas las categorías</option>
                    <option
                        v-for="categoria in categorias"
                        :key="categoria"
                        :value="categoria"
                    >
                        {{ categoria }}
                    </option>
                </select>

                <select v-model="estadoSeleccionado">
                    <option value="">Todos los estados</option>
                    <option value="Disponible">Disponible</option>
                    <option value="Stock bajo">Stock bajo</option>
                    <option value="Sin stock">Sin stock</option>
                </select>
            </div>
        </div>

        <!-- Cargando -->
        <div v-if="cargando" class="message">
            Cargando productos...
        </div>

        <!-- Error -->
        <div v-else-if="error" class="message error">
            {{ error }}

            <button @click="cargarProductos">
                Reintentar
            </button>
        </div>

        <!-- Tabla -->
        <div v-else class="table-card">

            <div class="table-heading">
                <div>
                    <h2>Listado de productos</h2>
                    <p>{{ productosFiltrados.length }} productos encontrados</p>
                </div>
            </div>

            <div v-if="productosFiltrados.length === 0" class="empty-state">
                <span>⌕</span>
                <h3>No se encontraron productos</h3>
                <p>Probá modificando la búsqueda o los filtros.</p>
                <button class="btn-clear" @click="limpiarFiltros">
                    Limpiar filtros
                </button>
            </div>

            <div v-else class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock actual</th>
                            <th>Stock mínimo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="producto in productosPaginados"
                            :key="producto.id"
                        >
                            <td class="product-id">
                                #{{ String(producto.id).padStart(4, '0') }}
                            </td>

                            <td class="product-name">
                                {{ producto.nombre }}
                            </td>

                            <td>
                                <span class="category-label">
                                    {{ producto.categoria }}
                                </span>
                            </td>

                            <td class="price">
                                {{ formatearPrecio(producto.precio) }}
                            </td>

                            <td class="stock-number">
                                {{ producto.stock }}
                            </td>

                            <td>
                                {{ producto.stock_minimo }}
                            </td>

                            <td>
                                <span
                                    class="status"
                                    :class="{
                                        'status-ok': obtenerEstado(producto) === 'Disponible',
                                        'status-low': obtenerEstado(producto) === 'Stock bajo',
                                        'status-empty': obtenerEstado(producto) === 'Sin stock'
                                    }"
                                >
                                    {{ obtenerEstado(producto) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div
                v-if="productosFiltrados.length > 0"
                class="pagination"
            >
                <span>
                    Mostrando
                    {{ (paginaActual - 1) * productosPorPagina + 1 }}
                    -
                    {{ Math.min(paginaActual * productosPorPagina, productosFiltrados.length) }}
                    de {{ productosFiltrados.length }} productos
                </span>

                <div class="pagination-buttons">
                    <button
                        :disabled="paginaActual === 1"
                        @click="paginaActual--"
                    >
                        Anterior
                    </button>

                    <span class="current-page">
                        {{ paginaActual }} / {{ totalPaginas }}
                    </span>

                    <button
                        :disabled="paginaActual === totalPaginas"
                        @click="paginaActual++"
                    >
                        Siguiente
                    </button>
                </div>
            </div>

        </div>

    </section>
</template>

<style scoped>
.products-page {
    display: flex;
    flex-direction: column;
    gap: 22px;
}

/* Encabezado */

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.page-header h1 {
    font-size: 25px;
    font-weight: 700;
    color: #1e293b;
}

.page-header p {
    margin-top: 6px;
    color: #64748b;
    font-size: 14px;
}

.btn-primary {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 11px 16px;
    border: none;
    border-radius: 8px;
    background: var(--color-primary);
    color: white;
    font-size: 13px;
    font-weight: 600;
}

.btn-primary:disabled {
    cursor: not-allowed;
    opacity: 0.7;
}

/* Estadísticas */

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 19px;
    background: white;
    border: 1px solid var(--color-border);
    border-radius: 11px;
}

.stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 43px;
    height: 43px;
    flex-shrink: 0;
    border-radius: 10px;
    font-size: 22px;
    font-weight: 700;
}

.icon-blue {
    background: #e0f2fe;
    color: #0284c7;
}

.icon-green {
    background: #dcfce7;
    color: #16a34a;
}

.icon-yellow {
    background: #fef3c7;
    color: #d97706;
}

.icon-red {
    background: #fee2e2;
    color: #dc2626;
}

.stat-label {
    color: #64748b;
    font-size: 12px;
}

.stat-card h2 {
    margin-top: 5px;
    color: #1e293b;
    font-size: 23px;
    font-weight: 700;
}

.stat-description {
    display: block;
    margin-top: 4px;
    color: #94a3b8;
    font-size: 11px;
}

/* Valor del inventario */

.inventory-value {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 19px 22px;
    background: #eff6ff;
    border: 1px solid #dbeafe;
    border-radius: 10px;
}

.inventory-value span {
    color: #1e40af;
    font-size: 14px;
    font-weight: 600;
}

.inventory-value p {
    margin-top: 5px;
    color: #64748b;
    font-size: 12px;
}

.inventory-value strong {
    color: #1e40af;
    font-size: 22px;
}

/* Filtros */

.filters-card,
.table-card {
    background: white;
    border: 1px solid var(--color-border);
    border-radius: 11px;
}

.filters-card {
    padding: 20px;
}

.filters-heading,
.table-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.filters-heading h2,
.table-heading h2 {
    color: #1e293b;
    font-size: 15px;
    font-weight: 700;
}

.filters-heading p,
.table-heading p {
    margin-top: 5px;
    color: #94a3b8;
    font-size: 12px;
}

.btn-clear {
    padding: 8px 11px;
    border: 1px solid var(--color-border);
    border-radius: 7px;
    background: white;
    color: #475569;
    font-size: 12px;
    font-weight: 500;
}

.btn-clear:hover {
    background: #f8fafc;
}

.filters-row {
    display: grid;
    grid-template-columns: minmax(180px, 1fr) 210px 190px;
    gap: 12px;
    margin-top: 19px;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 0 12px;
    border: 1px solid var(--color-border);
    border-radius: 8px;
    color: #94a3b8;
}

.search-box span {
    font-size: 22px;
}

.search-box input {
    width: 100%;
    min-width: 0;
    padding: 11px 0;
    border: none;
    outline: none;
    color: #1e293b;
    font-size: 12px;
}

.search-box:focus-within {
    border-color: var(--color-primary);
}

.filters-row select {
    width: 100%;
    padding: 11px;
    border: 1px solid var(--color-border);
    border-radius: 8px;
    outline: none;
    background: white;
    color: #475569;
    font-size: 12px;
}

.filters-row select:focus {
    border-color: var(--color-primary);
}

/* Tabla */

.table-card {
    overflow: hidden;
}

.table-heading {
    padding: 19px 20px;
    border-bottom: 1px solid var(--color-border);
}

.table-container {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 12px;
    white-space: nowrap;
}

th {
    background: #f8fafc;
    color: #64748b;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

th,
td {
    padding: 15px 17px;
    border-bottom: 1px solid #f1f5f9;
}

tbody tr:hover {
    background: #f8fafc;
}

tbody tr:last-child td {
    border-bottom: none;
}

.product-id {
    color: #94a3b8;
    font-size: 11px;
}

.product-name {
    color: #1e293b;
    font-weight: 600;
}

.category-label {
    display: inline-block;
    padding: 5px 9px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #475569;
    font-size: 11px;
}

.price {
    color: #1e293b;
    font-weight: 600;
}

.stock-number {
    color: #1e293b;
    font-weight: 600;
}

/* Estados */

.status {
    display: inline-block;
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 600;
}

.status-ok {
    background: #dcfce7;
    color: #166534;
}

.status-low {
    background: #fef3c7;
    color: #92400e;
}

.status-empty {
    background: #fee2e2;
    color: #991b1b;
}

/* Mensajes */

.message,
.empty-state {
    padding: 32px;
    text-align: center;
    color: #64748b;
}

.message {
    background: white;
    border: 1px solid var(--color-border);
    border-radius: 10px;
}

.message.error {
    color: #b91c1c;
}

.message button {
    display: block;
    margin: 14px auto 0;
    padding: 9px 15px;
    border: none;
    border-radius: 7px;
    background: var(--color-primary);
    color: white;
}

.empty-state span {
    font-size: 30px;
    color: #94a3b8;
}

.empty-state h3 {
    margin-top: 10px;
    color: #334155;
    font-size: 15px;
}

.empty-state p {
    margin: 7px 0 15px;
    font-size: 12px;
}

/* Paginación */

.pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    border-top: 1px solid var(--color-border);
    color: #64748b;
    font-size: 11px;
}

.pagination-buttons {
    display: flex;
    align-items: center;
    gap: 9px;
}

.pagination-buttons button {
    padding: 7px 10px;
    border: 1px solid var(--color-border);
    border-radius: 6px;
    background: white;
    color: #475569;
    font-size: 11px;
}

.pagination-buttons button:disabled {
    cursor: not-allowed;
    opacity: 0.4;
}

.current-page {
    color: #1e293b;
    font-weight: 600;
}

/* Adaptación a pantallas pequeñas */

@media (max-width: 1050px) {
    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .filters-row {
        grid-template-columns: 1fr 1fr;
    }

    .search-box {
        grid-column: 1 / -1;
    }
}

@media (max-width: 600px) {
    .page-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .stat-card {
        padding: 13px;
        gap: 9px;
    }

    .stat-icon {
        width: 34px;
        height: 34px;
        font-size: 17px;
    }

    .stat-card h2 {
        font-size: 19px;
    }

    .inventory-value {
        align-items: flex-start;
        flex-direction: column;
    }

    .filters-row {
        grid-template-columns: 1fr;
    }

    .search-box {
        grid-column: auto;
    }

    .pagination {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>