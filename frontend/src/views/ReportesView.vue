
<script setup>
import { ref, computed, onMounted } from 'vue'
import { obtenerProductos } from '../services/productos'
import { obtenerMovimientos } from '../services/movimientos'

const productos = ref([])
const movimientos = ref([])
const cargando = ref(true)
const error = ref('')
const periodo = ref('30')

async function cargarDatos() {
    cargando.value = true
    error.value = ''

    try {
        const [datosProductos, datosMovimientos] = await Promise.all([
            obtenerProductos(),
            obtenerMovimientos()
        ])

        productos.value = datosProductos
        movimientos.value = datosMovimientos
    } catch (err) {
        console.error(err)
        error.value = err.message || 'No se pudieron cargar los reportes.'
    } finally {
        cargando.value = false
    }
}

onMounted(cargarDatos)

// Convierte la fecha de SQLite a una fecha de JavaScript.
function convertirFecha(fecha) {
    if (!fecha) return null

    const resultado = new Date(fecha.replace(' ', 'T') + 'Z')

    return Number.isNaN(resultado.getTime()) ? null : resultado
}

// Filtra los movimientos según el período elegido.
const movimientosFiltrados = computed(() => {
    if (periodo.value === 'todos') return movimientos.value

    const dias = Number(periodo.value)
    const fechaLimite = Date.now() - dias * 24 * 60 * 60 * 1000

    return movimientos.value.filter(movimiento => {
        const fecha = convertirFecha(movimiento.fecha)

        return fecha && fecha.getTime() >= fechaLimite
    })
})

// Estadísticas del inventario actual.
const totalProductos = computed(() => productos.value.length)

const totalUnidades = computed(() =>
    productos.value.reduce((total, producto) =>
        total + Number(producto.stock), 0
    )
)

const productosSinStock = computed(() =>
    productos.value.filter(producto => Number(producto.stock) === 0)
)

const productosStockBajo = computed(() =>
    productos.value.filter(producto =>
        Number(producto.stock) > 0 &&
        Number(producto.stock) <= Number(producto.stock_minimo)
    )
)

const valorInventario = computed(() =>
    productos.value.reduce((total, producto) =>
        total + Number(producto.precio) * Number(producto.stock), 0
    )
)

// Cantidad de entradas y salidas del período.
const totalEntradas = computed(() =>
    movimientosFiltrados.value
        .filter(m => m.tipo === 'entrada')
        .reduce((total, m) => total + Number(m.cantidad), 0)
)

const totalSalidas = computed(() =>
    movimientosFiltrados.value
        .filter(m => m.tipo === 'salida')
        .reduce((total, m) => total + Number(m.cantidad), 0)
)

// Agrupa los movimientos por categoría.
const movimientosPorCategoria = computed(() => {
    const grupos = {}

    movimientosFiltrados.value.forEach(movimiento => {
        const producto = productos.value.find(
            p => Number(p.id) === Number(movimiento.productos_id)
        )

        const categoria = producto?.categoria || 'Sin categoría'

        if (!grupos[categoria]) {
            grupos[categoria] = {
                nombre: categoria,
                entradas: 0,
                salidas: 0
            }
        }

        if (movimiento.tipo === 'entrada') {
            grupos[categoria].entradas += Number(movimiento.cantidad)
        } else {
            grupos[categoria].salidas += Number(movimiento.cantidad)
        }
    })

    return Object.values(grupos).sort((a, b) =>
        (b.entradas + b.salidas) - (a.entradas + a.salidas)
    )
})

// Productos con mayor cantidad de unidades vendidas/retiradas.
const productosMasSalidas = computed(() => {
    const grupos = {}

    movimientosFiltrados.value
        .filter(m => m.tipo === 'salida')
        .forEach(movimiento => {
            const id = Number(movimiento.productos_id)
            const producto = productos.value.find(p => Number(p.id) === id)

            if (!producto) return

            if (!grupos[id]) {
                grupos[id] = {
                    id,
                    nombre: producto.nombre,
                    categoria: producto.categoria || 'Sin categoría',
                    cantidad: 0
                }
            }

            grupos[id].cantidad += Number(movimiento.cantidad)
        })

    return Object.values(grupos)
        .sort((a, b) => b.cantidad - a.cantidad)
        .slice(0, 6)
})

function formatearDinero(valor) {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        maximumFractionDigits: 2
    }).format(valor)
}

function exportarCSV() {
    const encabezados = [
        'ID',
        'Producto',
        'Categoria',
        'Tipo',
        'Cantidad',
        'Usuario',
        'Fecha'
    ]

    const filas = movimientosFiltrados.value.map(movimiento => {
        const producto = productos.value.find(
            p => Number(p.id) === Number(movimiento.productos_id)
        )

        return [
            movimiento.id,
            producto?.nombre || movimiento.producto,
            producto?.categoria || 'Sin categoría',
            movimiento.tipo,
            movimiento.cantidad,
            movimiento.usuario,
            movimiento.fecha
        ]
    })

    const escapar = valor =>
        `"${String(valor ?? '').replace(/"/g, '""')}"`

    const contenido = [
        encabezados,
        ...filas
    ].map(fila => fila.map(escapar).join(';')).join('\n')

    const archivo = new Blob(
        ['\uFEFF' + contenido],
        { type: 'text/csv;charset=utf-8;' }
    )

    const url = URL.createObjectURL(archivo)
    const enlace = document.createElement('a')

    enlace.href = url
    enlace.download = `reporte-movimientos-${periodo.value}.csv`
    enlace.click()

    URL.revokeObjectURL(url)
}
</script>

<template>
    <section class="reports-page">
        <div class="page-header">
            <div>
                <h1>Reportes</h1>
                <p>Analizá el estado y el comportamiento de tu inventario.</p>
            </div>

            <div class="header-actions">
                <select v-model="periodo" class="period-select">
                    <option value="7">Últimos 7 días</option>
                    <option value="30">Últimos 30 días</option>
                    <option value="90">Últimos 90 días</option>
                    <option value="todos">Todo el historial</option>
                </select>

                <button
                    class="btn-primary"
                    @click="exportarCSV"
                    :disabled="movimientosFiltrados.length === 0"
                >
                    ↓ Exportar CSV
                </button>
            </div>
        </div>

        <div v-if="cargando" class="message">
            Cargando reportes...
        </div>

        <div v-else-if="error" class="message error">
            {{ error }}
            <button @click="cargarDatos">Reintentar</button>
        </div>

        <template v-else>
            <!-- INVENTARIO -->
            <div class="section-title">
                <h2>Estado del inventario</h2>
                <p>Datos actuales de tus productos.</p>
            </div>

            <div class="stats-grid">
                <article class="stat-card">
                    <span class="stat-icon icon-blue">▦</span>
                    <div>
                        <p>Productos registrados</p>
                        <h3>{{ totalProductos }}</h3>
                    </div>
                </article>

                <article class="stat-card">
                    <span class="stat-icon icon-green">▣</span>
                    <div>
                        <p>Unidades en stock</p>
                        <h3>{{ totalUnidades }}</h3>
                    </div>
                </article>

                <article class="stat-card">
                    <span class="stat-icon icon-yellow">⚠</span>
                    <div>
                        <p>Stock bajo</p>
                        <h3>{{ productosStockBajo.length }}</h3>
                    </div>
                </article>

                <article class="stat-card">
                    <span class="stat-icon icon-red">!</span>
                    <div>
                        <p>Sin stock</p>
                        <h3>{{ productosSinStock.length }}</h3>
                    </div>
                </article>
            </div>

            <article class="value-card">
                <div>
                    <p>Valorización del inventario</p>
                    <h2>{{ formatearDinero(valorInventario) }}</h2>
                    <small>Valor calculado según precio × stock actual.</small>
                </div>
                <span class="value-icon">$</span>
            </article>

            <!-- MOVIMIENTOS -->
            <div class="section-title">
                <h2>Actividad del período</h2>
                <p>Unidades ingresadas y retiradas.</p>
            </div>

            <div class="stats-grid movement-stats">
                <article class="stat-card">
                    <span class="stat-icon icon-green">↓</span>
                    <div>
                        <p>Unidades ingresadas</p>
                        <h3>{{ totalEntradas }}</h3>
                    </div>
                </article>

                <article class="stat-card">
                    <span class="stat-icon icon-red">↑</span>
                    <div>
                        <p>Unidades retiradas</p>
                        <h3>{{ totalSalidas }}</h3>
                    </div>
                </article>

                <article class="stat-card">
                    <span class="stat-icon icon-blue">↔</span>
                    <div>
                        <p>Movimientos registrados</p>
                        <h3>{{ movimientosFiltrados.length }}</h3>
                    </div>
                </article>
            </div>

            <!-- CATEGORÍAS -->
            <article class="report-card">
                <div class="card-heading">
                    <div>
                        <h2>Movimientos por categoría</h2>
                        <p>Comparación de entradas y salidas por unidades.</p>
                    </div>
                </div>

                <div v-if="movimientosPorCategoria.length === 0" class="empty-state">
                    No hay movimientos para este período.
                </div>

                <div v-else class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Entradas</th>
                                <th>Salidas</th>
                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="categoria in movimientosPorCategoria" :key="categoria.nombre">
                                <td class="strong">{{ categoria.nombre }}</td>
                                <td class="green-text">{{ categoria.entradas }}</td>
                                <td class="red-text">{{ categoria.salidas }}</td>
                                <td>{{ categoria.entradas + categoria.salidas }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>

            <!-- PRODUCTOS CON MÁS SALIDAS -->
            <article class="report-card">
                <div class="card-heading">
                    <div>
                        <h2>Productos con más salidas</h2>
                        <p>Los 6 productos con mayor cantidad de unidades retiradas.</p>
                    </div>
                </div>

                <div v-if="productosMasSalidas.length === 0" class="empty-state">
                    No hay salidas registradas para este período.
                </div>

                <div v-else class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Unidades retiradas</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(producto, index) in productosMasSalidas" :key="producto.id">
                                <td class="rank">{{ index + 1 }}</td>
                                <td class="strong">{{ producto.nombre }}</td>
                                <td>{{ producto.categoria }}</td>
                                <td class="red-text strong">{{ producto.cantidad }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>

            <!-- ALERTAS DE STOCK -->
            <article class="report-card">
                <div class="card-heading">
                    <div>
                        <h2>Productos que requieren atención</h2>
                        <p>Productos sin stock o por debajo del mínimo establecido.</p>
                    </div>
                </div>

                <div v-if="productosSinStock.length + productosStockBajo.length === 0" class="empty-state">
                    Todos los productos tienen stock suficiente.
                </div>

                <div v-else class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Stock actual</th>
                                <th>Stock mínimo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="producto in [...productosSinStock, ...productosStockBajo]"
                                :key="producto.id"
                            >
                                <td class="strong">{{ producto.nombre }}</td>
                                <td>{{ producto.categoria }}</td>
                                <td>{{ producto.stock }}</td>
                                <td>{{ producto.stock_minimo }}</td>
                                <td>
                                    <span
                                        class="status"
                                        :class="Number(producto.stock) === 0 ? 'status-red' : 'status-yellow'"
                                    >
                                        {{ Number(producto.stock) === 0 ? 'Sin stock' : 'Stock bajo' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>
        </template>
    </section>
</template>

<style scoped>
.reports-page {
    display: flex;
    flex-direction: column;
    gap: 22px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
}

.page-header h1 {
    color: #1e293b;
    font-size: 25px;
    font-weight: 700;
}

.page-header p, .section-title p {
    margin-top: 6px;
    color: #64748b;
    font-size: 13px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.period-select {
    padding: 10px 12px;
    border: 1px solid var(--color-border);
    border-radius: 8px;
    background: white;
    color: #334155;
    font-size: 12px;
}

.btn-primary {
    padding: 11px 15px;
    border: none;
    border-radius: 8px;
    background: #0284c7;
    color: white;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.section-title h2 {
    color: #1e293b;
    font-size: 17px;
    font-weight: 700;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 15px;
}

.movement-stats {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 18px;
    background: white;
    border: 1px solid var(--color-border);
    border-radius: 11px;
}

.stat-card p {
    color: #64748b;
    font-size: 12px;
}

.stat-card h3 {
    margin-top: 7px;
    color: #1e293b;
    font-size: 24px;
    font-weight: 700;
}

.stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 42px;
    height: 42px;
    border-radius: 10px;
    font-size: 21px;
    font-weight: 700;
}

.icon-blue { background: #e0f2fe; color: #0284c7; }
.icon-green { background: #dcfce7; color: #16a34a; }
.icon-yellow { background: #fef3c7; color: #d97706; }
.icon-red { background: #fee2e2; color: #dc2626; }

.value-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 22px;
    background: white;
    border: 1px solid var(--color-border);
    border-radius: 11px;
}

.value-card p {
    color: #64748b;
    font-size: 13px;
}

.value-card h2 {
    margin-top: 8px;
    color: #1e293b;
    font-size: 27px;
    font-weight: 700;
}

.value-card small {
    display: block;
    margin-top: 6px;
    color: #94a3b8;
    font-size: 11px;
}

.value-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #dcfce7;
    color: #16a34a;
    font-size: 25px;
    font-weight: 700;
}

.report-card {
    overflow: hidden;
    background: white;
    border: 1px solid var(--color-border);
    border-radius: 11px;
}

.card-heading {
    padding: 19px 20px;
    border-bottom: 1px solid var(--color-border);
}

.card-heading h2 {
    color: #1e293b;
    font-size: 15px;
    font-weight: 700;
}

.card-heading p {
    margin-top: 5px;
    color: #94a3b8;
    font-size: 12px;
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
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
}

th, td {
    padding: 14px 18px;
    border-bottom: 1px solid #f1f5f9;
}

tbody tr:last-child td {
    border-bottom: none;
}

tbody tr:hover {
    background: #f8fafc;
}

.strong { color: #1e293b; font-weight: 600; }
.green-text { color: #16a34a; font-weight: 600; }
.red-text { color: #dc2626; font-weight: 600; }
.rank { color: #0284c7; font-weight: 700; }

.status {
    display: inline-block;
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 600;
}

.status-red { background: #fee2e2; color: #991b1b; }
.status-yellow { background: #fef3c7; color: #92400e; }

.empty-state, .message {
    padding: 28px;
    text-align: center;
    color: #64748b;
    font-size: 13px;
}

.message {
    background: white;
    border: 1px solid var(--color-border);
    border-radius: 10px;
}

.message.error { color: #b91c1c; }

.message button {
    display: block;
    margin: 12px auto 0;
    padding: 8px 13px;
    border: none;
    border-radius: 7px;
    background: var(--color-primary);
    color: white;
    cursor: pointer;
}

@media (max-width: 1000px) {
    .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 650px) {
    .page-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .header-actions {
        width: 100%;
        flex-wrap: wrap;
    }

    .stats-grid, .movement-stats {
        grid-template-columns: 1fr;
    }

    .value-card h2 { font-size: 22px; }
}
</style>
