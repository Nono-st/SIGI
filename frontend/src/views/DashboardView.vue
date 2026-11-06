<script setup>
import { ref, computed, onMounted } from 'vue'

import {
    Package,
    Boxes,
    AlertTriangle,
    ArrowLeftRight,
    PackagePlus,
    Plus,
    ArrowRight,
} from '@lucide/vue'

import { RouterLink } from 'vue-router'

import { obtenerProductos } from '../services/productos'
import { obtenerMovimientos } from '../services/movimientos'

// Nombre recibido desde el componente principal
defineProps({
    userName: {
        type: String,
        default: 'Usuario',
    },
})

// Datos reales de la base de datos
const productos = ref([])
const movimientos = ref([])

const cargando = ref(true)
const error = ref('')

// Cargar información del sistema
async function cargarDatos() {
    cargando.value = true
    error.value = ''

    try {
        const [datosProductos, datosMovimientos] = await Promise.all([
            obtenerProductos(),
            obtenerMovimientos(),
        ])

        productos.value = datosProductos
        movimientos.value = datosMovimientos

    } catch (err) {
        error.value = err.message || 'No se pudieron cargar los datos.'
    } finally {
        cargando.value = false
    }
}

// Productos con stock igual o inferior al mínimo
const lowStockProducts = computed(() => {
    return productos.value
        .filter(producto =>
            Number(producto.stock) <= Number(producto.stock_minimo)
        )
        .sort((a, b) => Number(a.stock) - Number(b.stock))
        .map(producto => ({
            id: producto.id,
            name: producto.nombre,
            category: producto.categoria,
            stock: Number(producto.stock),
            minimum: Number(producto.stock_minimo),
        }))
})

// Últimos movimientos
const recentMovements = computed(() => {
    return movimientos.value.slice(0, 6).map(movimiento => {
        const fecha = new Date(
            movimiento.fecha.replace(' ', 'T') + 'Z'
        )

        return {
            id: movimiento.id,
            product: movimiento.producto,
            type: movimiento.tipo === 'entrada' ? 'Entrada' : 'Salida',
            user: movimiento.usuario,
            date: fecha.toLocaleDateString('es-AR'),
            time: fecha.toLocaleTimeString('es-AR', {
                hour: '2-digit',
                minute: '2-digit',
            }),
            quantity: Number(movimiento.cantidad),
        }
    })
})

// Movimientos de los últimos 7 días
const movimientosSemana = computed(() => {
    const ahora = new Date()
    const haceSieteDias = new Date(
        ahora.getTime() - 7 * 24 * 60 * 60 * 1000
    )

    return movimientos.value.filter(movimiento => {
        const fecha = new Date(
            movimiento.fecha.replace(' ', 'T') + 'Z'
        )

        return fecha >= haceSieteDias && fecha <= ahora
    }).length
})

// Estadísticas del inventario
const statistics = computed(() => {
    const sinStock = productos.value.filter(
        producto => Number(producto.stock) === 0
    ).length

    return [
        {
            title: 'Productos registrados',
            value: productos.value.length,
            description: 'En el catálogo',
            icon: Package,
            color: 'blue',
        },
        {
            title: 'Unidades en stock',
            value: productos.value.reduce(
                (total, producto) => total + Number(producto.stock),
                0
            ),
            description: 'Suma de todos los productos',
            icon: Boxes,
            color: 'green',
        },
        {
            title: 'Con stock bajo',
            value: lowStockProducts.value.length,
            description: `${sinStock} sin stock`,
            icon: AlertTriangle,
            color: 'orange',
        },
        {
            title: 'Movimientos recientes',
            value: movimientosSemana.value,
            description: 'Últimos 7 días',
            icon: ArrowLeftRight,
            color: 'slate',
        },
    ]
})

// Cargar datos al entrar al Dashboard
onMounted(cargarDatos)
</script>

<template>
    <section class="dashboard">

    <!-- Encabezado de bienvenida -->
        <div class="welcome-header">
            <div>
                <h1>Hola, {{ userName }}</h1>
                <p>Este es el resumen del estado de tu inventario.</p>
            </div>

            <div class="header-actions">
                <RouterLink to="/productos" class="button-secondary">
                <Plus :size="16" />
                Nuevo producto
                </RouterLink>

                <RouterLink to="/movimientos" class="button-primary">
                <ArrowLeftRight :size="16" />
                Registrar movimiento
                </RouterLink>
            </div>
        </div>

    <p v-if="cargando" class="dashboard-message">
        Cargando información del inventario...
    </p>

    <p v-else-if="error" class="dashboard-message error">
        {{ error }}
    </p>

    <!-- Tarjetas estadísticas -->
    <div class="statistics">
        <article
        v-for="stat in statistics"
        :key="stat.title"
        class="stat-card"
        >
        <div>
            <p>{{ stat.title }}</p>
            <h2>{{ stat.value }}</h2>
            <small>{{ stat.description }}</small>
        </div>

        <div class="stat-icon" :class="stat.color">
            <component :is="stat.icon" :size="21" />
        </div>
        </article>
    </div>

    <!-- Productos con stock bajo -->
    <article class="dashboard-card">
        <div class="card-header">
            <div>
                <h2>Productos con stock bajo</h2>
                <p>Requieren reposición.</p>
        </div>

            <RouterLink to="/productos" class="text-link">
            Ver todos <ArrowRight :size="14" />
            </RouterLink>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Stock</th>
                        <th>Mínimo</th>
                        <th>Estado</th>
                    </tr>
                </thead>

            <tbody>
                <tr
                v-for="product in lowStockProducts"
                :key="product.id"
                >
                    <td class="product-name">{{ product.name }}</td>
                    <td class="category-name">{{ product.category }}</td>
                    <td>{{ product.stock }}</td>
                    <td>{{ product.minimum }}</td>
                    <td>
                <span
                    class="status-badge"
                    :class="product.stock === 0 ? 'out' : 'low'"
                    >
                <span class="status-dot"></span>
                    {{ product.stock === 0 ? 'Sin stock' : 'Stock bajo' }}
                </span>
                </td>
                </tr>
                <tr v-if="!cargando && !error && lowStockProducts.length === 0">
                <td colspan="5" class="empty-state">
                    No hay productos con stock bajo. ¡Todo en orden!
                </td>
</tr>
            </tbody>
        </table>
    </div>
    </article>

    <!-- Últimos movimientos -->
    <article class="dashboard-card">
        <div class="card-header">
            <div>
            <h2>Últimos movimientos</h2>
            <p>Entradas y salidas recientes.</p>
        </div>

            <RouterLink to="/movimientos" class="text-link">
            Historial <ArrowRight :size="14" />
        </RouterLink>
        </div>

        <div class="movement-list">
            <div
            v-for="movement in recentMovements"
            :key="movement.id"
            class="movement-row"
        >
            <span
                class="movement-type"
                :class="movement.type.toLowerCase()"
            >
            <ArrowRight
                :size="13"
                :class="{ 'arrow-entry': movement.type === 'Entrada' }"
            />
            {{ movement.type }}
            </span>

            <div class="movement-info">
                <strong>{{ movement.product }}</strong>
                <small>
                {{ movement.user }} · {{ movement.date }} · {{ movement.time }}
            </small>
            </div>


            <strong
            class="movement-quantity"
            :class="movement.type.toLowerCase()"
            >
            {{ movement.type === 'Entrada' ? '+' : '-' }}{{ movement.quantity }}
            </strong>
            </div>
        </div>
        <p
        v-if="!cargando && !error && recentMovements.length === 0"
        class="empty-state">
        Todavía no hay movimientos registrados.
        </p>
    </article>

    <!-- Accesos rápidos -->
    <div class="quick-actions">
        <RouterLink to="/productos" class="quick-action">
            <div class="quick-icon">
            <PackagePlus :size="21" />
            </div>

        <div>
            <strong>Registrar producto</strong>
            <p>Agregá un nuevo artículo al catálogo con su precio y stock inicial.</p>
        </div>

        <ArrowRight :size="17" />
        </RouterLink>

        <RouterLink to="/movimientos" class="quick-action">
        <div class="quick-icon">
            <ArrowLeftRight :size="21" />
        </div>

        <div>
            <strong>Registrar movimiento</strong>
            <p>Cargá una entrada de mercadería o una salida por venta.</p>
        </div>

        <ArrowRight :size="17" />
        </RouterLink>
    </div>

    </section>
</template>

<style scoped>
.dashboard {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Encabezado */

.welcome-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
}

.welcome-header h1 {
    color: #1e293b;
    font-size: 22px;
    font-weight: 700;
}

.welcome-header p {
    margin-top: 5px;
    color: #64748b;
    font-size: 12px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.button-primary,
.button-secondary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 9px 12px;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.button-primary {
    background: #0284c7;
    color: white;
}

.button-primary:hover {
    background: #0369a1;
}

.button-secondary {
    background: white;
    color: #334155;
    border: 1px solid #e2e8f0;
}

.button-secondary:hover {
    background: #f8fafc;
}

/* Estadísticas */

.statistics {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

.stat-card {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    padding: 16px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}

.stat-card p {
    color: #64748b;
    font-size: 11px;
}

.stat-card h2 {
    margin-top: 8px;
    color: #1e293b;
    font-size: 22px;
}

.stat-card small {
    display: block;
    margin-top: 5px;
    color: #64748b;
    font-size: 10px;
}

.stat-icon {
    width: 36px;
    height: 36px;
    display: grid;
    place-items: center;
    border-radius: 8px;
    flex-shrink: 0;
}

.stat-icon.blue {
    color: #0284c7;
    background: #f0f9ff;
}

.stat-icon.green {
    color: #16a34a;
    background: #f0fdf4;
}

.stat-icon.orange {
    color: #ea580c;
    background: #fff7ed;
}

.stat-icon.slate {
    color: #334155;
    background: #f1f5f9;
}

/* Tarjetas y encabezados */

.dashboard-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}

.card-header {
    min-height: 58px;
    padding: 13px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid #e2e8f0;
}

.card-header h2 {
    color: #1e293b;
    font-size: 13px;
}

.card-header p {
    margin-top: 4px;
    color: #64748b;
    font-size: 10px;
}

.text-link {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #334155;
    font-size: 10px;
    white-space: nowrap;
}

.text-link:hover {
    color: #0284c7;
}

/* Tabla de productos */

.table-container {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 11px;
}

th {
    padding: 10px 15px;
    color: #334155;
    font-weight: 500;
    background: #fff;
}

td {
    padding: 9px 15px;
    color: #334155;
    border-top: 1px solid #e2e8f0;
    white-space: nowrap;
}

.product-name {
    color: #0f172a;
    font-weight: 500;
}

.category-name {
    color: #64748b;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 7px;
    border-radius: 20px;
    font-size: 9px;
}

.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

.status-badge.out {
    color: #dc2626;
    background: #fef2f2;
    border: 1px solid #fecaca;
}

.status-badge.low {
    color: #c2410c;
    background: #fff7ed;
    border: 1px solid #fed7aa;
}

/* Últimos movimientos */

.movement-row {
    min-height: 46px;
    padding: 9px 15px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid #e2e8f0;
}

.movement-row:last-child {
    border-bottom: none;
}

.movement-type {
    width: 65px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 4px;
    padding: 4px 5px;
    border-radius: 20px;
    font-size: 9px;
    flex-shrink: 0;
}

.movement-type.entrada {
    color: #16a34a;
     background: #f0fdf4;
    border: 1px solid #bbf7d0;
}

.movement-type.salida {
    color: #dc2626;
    background: #fef2f2;
    border: 1px solid #fecaca;
}

.arrow-entry {
    transform: rotate(180deg);
}

.movement-info {
    flex: 1;
    min-width: 0;
}

.movement-info strong {
    display: block;
    color: #0f172a;
    font-size: 11px;
    font-weight: 500;
}

.movement-info small {
    display: block;
    margin-top: 3px;
    color: #64748b;
    font-size: 9px;
}

.movement-quantity {
    font-size: 11px;
    flex-shrink: 0;
}

.movement-quantity.entrada {
    color: #16a34a;
}

.movement-quantity.salida {
    color: #dc2626;
}

/* Accesos rápidos */

.quick-actions {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.quick-action {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: white;
    border: 1px dashed #cbd5e1;
    border-radius: 9px;
    color: #64748b;
}

.quick-action:hover {
    border-color: #0284c7;
    background: #f8fcff;
}

.quick-icon {
    width: 34px;
    height: 34px;
    display: grid;
    place-items: center;
    border-radius: 7px;
    background: #0284c7;
    color: white;
    flex-shrink: 0;
}

.quick-action div:nth-child(2) {
    flex: 1;
    min-width: 0;
}

.quick-action strong {
    display: block;
    color: #1e293b;
    font-size: 11px;
}

.quick-action p {
    margin-top: 4px;
    color: #64748b;
    font-size: 10px;
    line-height: 1.5;
}

/* Adaptación a pantallas pequeñas */

@media (max-width: 950px) {
    .statistics {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 650px) {
    .welcome-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .header-actions {
        flex-wrap: wrap;
    }

    .quick-actions {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 450px) {
    .statistics {
    grid-template-columns: 1fr;
    }

    .movement-row {
        gap: 8px;
        padding: 9px;
    }

    .movement-type {
        width: 59px;
    }
}

/* Estados vacíos y mensajes */

.empty-state {
    padding: 24px 15px;
    color: #64748b;
    text-align: center;
    font-size: 11px;
}

.dashboard-message {
    padding: 12px 15px;
    border-radius: 8px;
    background: #f0f9ff;
    color: #0369a1;
    font-size: 12px;
}

.dashboard-message.error {
    background: #fef2f2;
    color: #dc2626;
}
</style>
