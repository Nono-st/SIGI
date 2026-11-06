
<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { obtenerMovimientos, crearMovimiento } from '../services/movimientos'
import { obtenerProductos } from '../services/productos'

// Datos del backend.
const movimientos = ref([])
const productos = ref([])
const cargando = ref(true)
const error = ref('')

// Formulario.
const mostrarFormulario = ref(false)
const guardando = ref(false)
const errorFormulario = ref('')

const nuevoMovimiento = ref({
    productos_id: '',
    tipo: 'entrada',
    cantidad: ''
})

// Búsqueda y filtros.
const busqueda = ref('')
const tipoSeleccionado = ref('')

// Paginación.
const paginaActual = ref(1)
const movimientosPorPagina = 8

// Consultar movimientos y productos.
async function cargarDatos() {
    cargando.value = true
    error.value = ''

    try {
        const [datosMovimientos, datosProductos] = await Promise.all([
            obtenerMovimientos(),
            obtenerProductos()
        ])

        movimientos.value = datosMovimientos
        productos.value = datosProductos
    } catch (err) {
        error.value = 'No se pudieron cargar los movimientos.'
        console.error(err)
    } finally {
        cargando.value = false
    }
}

onMounted(cargarDatos)

// Estadísticas.
const totalMovimientos = computed(() => movimientos.value.length)

const totalEntradas = computed(() =>
    movimientos.value.filter(m => m.tipo === 'entrada').length
)

const totalSalidas = computed(() =>
    movimientos.value.filter(m => m.tipo === 'salida').length
)

// Aplicar búsqueda y filtros.
const movimientosFiltrados = computed(() => {
    return movimientos.value.filter(movimiento => {
        const coincideProducto = movimiento.producto
            .toLowerCase()
            .includes(busqueda.value.toLowerCase().trim())

        const coincideTipo = !tipoSeleccionado.value ||
            movimiento.tipo === tipoSeleccionado.value

        return coincideProducto && coincideTipo
    })
})

// Paginación.
const totalPaginas = computed(() =>
    Math.max(1, Math.ceil(
        movimientosFiltrados.value.length / movimientosPorPagina
    ))
)

const movimientosPaginados = computed(() => {
    const inicio = (paginaActual.value - 1) * movimientosPorPagina

    return movimientosFiltrados.value.slice(
        inicio,
        inicio + movimientosPorPagina
    )
})

watch([busqueda, tipoSeleccionado], () => {
    paginaActual.value = 1
})

watch(totalPaginas, total => {
    if (paginaActual.value > total) {
        paginaActual.value = total
    }
})

// Abrir formulario.
function abrirFormulario() {
    errorFormulario.value = ''

    nuevoMovimiento.value = {
        productos_id: '',
        tipo: 'entrada',
        cantidad: ''
    }

    mostrarFormulario.value = true
}

// Cerrar formulario.
function cerrarFormulario() {
    mostrarFormulario.value = false
    errorFormulario.value = ''
}

// Registrar movimiento.
async function guardarMovimiento() {
    errorFormulario.value = ''

    const productoId = Number(nuevoMovimiento.value.productos_id)
    const cantidad = Number(nuevoMovimiento.value.cantidad)

    if (!Number.isInteger(productoId) || productoId <= 0) {
        errorFormulario.value = 'Seleccioná un producto.'
        return
    }

    if (!['entrada', 'salida'].includes(nuevoMovimiento.value.tipo)) {
        errorFormulario.value = 'Seleccioná un tipo de movimiento válido.'
        return
    }

    if (!Number.isInteger(cantidad) || cantidad <= 0) {
        errorFormulario.value = 'La cantidad debe ser un número entero mayor que cero.'
        return
    }

    guardando.value = true

    try {
        await crearMovimiento({
            productos_id: productoId,
            tipo: nuevoMovimiento.value.tipo,
            cantidad
        })

        cerrarFormulario()
        await cargarDatos()
    } catch (err) {
        errorFormulario.value = err.message ||
            'No se pudo registrar el movimiento.'
    } finally {
        guardando.value = false
    }
}

// Formatear fecha.
function formatearFecha(fecha) {
    if (!fecha) return '—'

    const fechaUTC = new Date(fecha.replace(' ', 'T') + 'Z')

    return fechaUTC.toLocaleString('es-AR', {
        dateStyle: 'short',
        timeStyle: 'short'
    })
}

// Limpiar filtros.
function limpiarFiltros() {
    busqueda.value = ''
    tipoSeleccionado.value = ''
    paginaActual.value = 1
}
</script>

<template>
    <section class="movements-page">

        <!-- Encabezado -->
        <div class="page-header">
            <div>
                <h1>Movimientos</h1>
                <p>Registrá y consultá las entradas y salidas de stock.</p>
            </div>

            <button class="btn-primary" @click="abrirFormulario">
                <span>＋</span>
                Nuevo movimiento
            </button>
        </div>

        <!-- Formulario -->
        <div
            v-if="mostrarFormulario"
            class="modal-overlay"
            @click.self="cerrarFormulario"
        >
            <form class="movement-form" @submit.prevent="guardarMovimiento">

                <div class="form-heading">
                    <div>
                        <h2>Nuevo movimiento</h2>
                        <p>Completá los datos para actualizar el stock.</p>
                    </div>

                    <button
                        type="button"
                        class="form-close"
                        @click="cerrarFormulario"
                    >
                        ×
                    </button>
                </div>

                <div class="form-grid">

                    <label>
                        Producto

                        <select v-model="nuevoMovimiento.productos_id" required>
                            <option value="" disabled>
                                Seleccioná un producto
                            </option>

                            <option
                                v-for="producto in productos"
                                :key="producto.id"
                                :value="producto.id"
                            >
                                {{ producto.nombre }} — Stock: {{ producto.stock }}
                            </option>
                        </select>
                    </label>

                    <label>
                        Tipo de movimiento

                        <select v-model="nuevoMovimiento.tipo" required>
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                        </select>
                    </label>

                    <label class="full-width">
                        Cantidad

                        <input
                            v-model="nuevoMovimiento.cantidad"
                            type="number"
                            min="1"
                            step="1"
                            placeholder="Ej. 10"
                            required
                        >
                    </label>

                </div>

                <p v-if="errorFormulario" class="form-error">
                    {{ errorFormulario }}
                </p>

                <div class="form-actions">
                    <button
                        type="button"
                        class="btn-cancel"
                        @click="cerrarFormulario"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn-primary"
                        :disabled="guardando"
                    >
                        {{ guardando ? 'Registrando...' : 'Registrar movimiento' }}
                    </button>
                </div>

            </form>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">

            <article class="stat-card">
                <div class="stat-icon icon-blue">▦</div>
                <div>
                    <p class="stat-label">Total de movimientos</p>
                    <h2>{{ totalMovimientos }}</h2>
                    <span class="stat-description">Registros históricos</span>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon icon-green">↓</div>
                <div>
                    <p class="stat-label">Entradas</p>
                    <h2>{{ totalEntradas }}</h2>
                    <span class="stat-description">Ingresos de mercadería</span>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon icon-red">↑</div>
                <div>
                    <p class="stat-label">Salidas</p>
                    <h2>{{ totalSalidas }}</h2>
                    <span class="stat-description">Egresos de mercadería</span>
                </div>
            </article>

        </div>

        <!-- Filtros -->
        <div class="filters-card">
            <div class="filters-heading">
                <div>
                    <h2>Historial</h2>
                    <p>Consultá y filtrá los movimientos registrados.</p>
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
                        placeholder="Buscar por producto..."
                    >
                </div>

                <select v-model="tipoSeleccionado">
                    <option value="">Todos los movimientos</option>
                    <option value="entrada">Entradas</option>
                    <option value="salida">Salidas</option>
                </select>
            </div>
        </div>

        <!-- Cargando -->
        <div v-if="cargando" class="message">
            Cargando movimientos...
        </div>

        <!-- Error -->
        <div v-else-if="error" class="message error">
            {{ error }}

            <button @click="cargarDatos">
                Reintentar
            </button>
        </div>

        <!-- Tabla -->
        <div v-else class="table-card">

            <div class="table-heading">
                <div>
                    <h2>Listado de movimientos</h2>
                    <p>{{ movimientosFiltrados.length }} movimientos encontrados</p>
                </div>
            </div>

            <div v-if="movimientosFiltrados.length === 0" class="empty-state">
                <span>⌕</span>
                <h3>No se encontraron movimientos</h3>
                <p>Probá modificando los filtros o registrando un movimiento.</p>
            </div>

            <div v-else class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="movimiento in movimientosPaginados"
                            :key="movimiento.id"
                        >
                            <td class="movement-id">
                                #{{ String(movimiento.id).padStart(4, '0') }}
                            </td>

                            <td class="product-name">
                                {{ movimiento.producto }}
                            </td>

                            <td>
                                <span
                                    class="status"
                                    :class="movimiento.tipo === 'entrada'
                                        ? 'status-entry'
                                        : 'status-exit'"
                                >
                                    {{ movimiento.tipo === 'entrada' ? 'Entrada' : 'Salida' }}
                                </span>
                            </td>

                            <td class="quantity">
                                {{ movimiento.cantidad }} unidades
                            </td>

                            <td>{{ movimiento.usuario }}</td>

                            <td>{{ formatearFecha(movimiento.fecha) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div
                v-if="movimientosFiltrados.length > 0"
                class="pagination"
            >
                <span>
                    Mostrando
                    {{ (paginaActual - 1) * movimientosPorPagina + 1 }}
                    -
                    {{ Math.min(
                        paginaActual * movimientosPorPagina,
                        movimientosFiltrados.length
                    ) }}
                    de {{ movimientosFiltrados.length }} movimientos
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
.movements-page {
    display: flex;
    flex-direction: column;
    gap: 22px;
}

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
    background: #0284c7;
    color: white;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.btn-primary:hover:not(:disabled) {
    background: #0369a1;
}

.btn-primary:disabled {
    cursor: not-allowed;
    opacity: 0.7;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
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
    cursor: pointer;
}

.filters-row {
    display: grid;
    grid-template-columns: minmax(180px, 1fr) 210px;
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

.filters-row select {
    width: 100%;
    padding: 11px;
    border: 1px solid var(--color-border);
    border-radius: 8px;
    background: white;
    color: #475569;
    font-size: 12px;
}

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

.movement-id {
    color: #94a3b8;
    font-size: 11px;
}

.product-name,
.quantity {
    color: #1e293b;
    font-weight: 600;
}

.status {
    display: inline-block;
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 600;
}

.status-entry {
    background: #dcfce7;
    color: #166534;
}

.status-exit {
    background: #fee2e2;
    color: #991b1b;
}

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
    cursor: pointer;
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
    cursor: pointer;
}

.pagination-buttons button:disabled {
    cursor: not-allowed;
    opacity: 0.4;
}

.current-page {
    color: #1e293b;
    font-weight: 600;
}

/* Formulario */
.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(15, 23, 42, 0.55);
}

.movement-form {
    width: 100%;
    max-width: 560px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 26px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
}

.form-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 24px;
}

.form-heading h2 {
    margin: 0 0 6px;
    color: #1e293b;
    font-size: 20px;
    font-weight: 700;
}

.form-heading p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
}

.form-close {
    border: none;
    background: transparent;
    color: #64748b;
    font-size: 26px;
    cursor: pointer;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.form-grid label {
    display: flex;
    flex-direction: column;
    gap: 7px;
    color: #334155;
    font-size: 14px;
    font-weight: 500;
}

.form-grid input,
.form-grid select {
    box-sizing: border-box;
    width: 100%;
    padding: 11px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: white;
    color: #1e293b;
    font: inherit;
}

.form-grid input:focus,
.form-grid select:focus {
    outline: 2px solid #0284c7;
    outline-offset: 1px;
}

.full-width {
    grid-column: 1 / -1;
}

.form-error {
    margin-top: 16px;
    color: #dc2626;
    font-size: 14px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 24px;
}

.btn-cancel {
    padding: 10px 16px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: white;
    color: #334155;
    cursor: pointer;
}

@media (max-width: 800px) {
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }

    .filters-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .page-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .pagination {
        align-items: flex-start;
        flex-direction: column;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .full-width {
        grid-column: auto;
    }
}
</style>
