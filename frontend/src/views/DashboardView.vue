<script setup>
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

// Nombre recibido desde el componente principal
defineProps({
    userName: {
    type: String,
    default: 'Usuario',
    },
})

// Estadísticas de ejemplo
const statistics = [
    {
        title: 'Productos registrados',
        value: 24,
        description: 'En el catálogo',
        icon: Package,
        color: 'blue',
    },
    {
        title: 'Unidades en stock',
        value: 597,
        description: 'Suma de todos los productos',
        icon: Boxes,
        color: 'green',
    },
    {
        title: 'Con stock bajo',
        value: 9,
        description: '3 sin stock',
        icon: AlertTriangle,
        color: 'orange',
    },
    {
        title: 'Movimientos recientes',
        value: 10,
        description: 'Últimos 7 días',
        icon: ArrowLeftRight,
        color: 'slate',
    },
]

// Productos de ejemplo
const lowStockProducts = [
    {
        id: 1,
        name: 'Chicles menta x8',
        category: 'Golosinas',
        stock: 0,
        minimum: 20,
    },
    {
        id: 2,
        name: 'Jugo de naranja 1 L',
        category: 'Bebidas',
        stock: 0,
        minimum: 8,
    },
    {
        id: 3,
        name: 'Chocolate con leche 100 g',
        category: 'Golosinas',
        stock: 9,
        minimum: 15,
    },
    {
        id: 4,
        name: 'Queso cremoso 250 g',
        category: 'Lácteos',
        stock: 0,
        minimum: 5,
    },
    {
        id: 5,
        name: 'Maní salado 120 g',
        category: 'Snacks',
        stock: 6,
        minimum: 10,
    },
]

// Movimientos de ejemplo
const recentMovements = [
    {
        id: 1,
        product: 'Coca-Cola 500 ml',
        type: 'Entrada',
        user: 'Laura Fernández',
        date: '30/10/2026',
        time: '10:30',
        quantity: 48,
    },
    {
        id: 2,
        product: 'Chocolate con leche 100 g',
        type: 'Salida',
        user: 'Martín Gómez',
        date: '30/10/2026',
        time: '09:15',
        quantity: 6,
    },
    {
        id: 3,
        product: 'Maní salado 120 g',
        type: 'Salida',
        user: 'Sofía Ramírez',
        date: '29/10/2026',
        time: '16:40',
        quantity: 4,
    },
    {
        id: 4,
        product: 'Alfajor triple chocolate',
        type: 'Entrada',
        user: 'Laura Fernández',
        date: '29/10/2026',
        time: '14:20',
        quantity: 40,
    },
    {
        id: 5,
        product: 'Agua mineral 1,5 L',
        type: 'Salida',
        user: 'Martín Gómez',
        date: '28/10/2026',
        time: '11:05',
        quantity: 8,
    },
    {
        id: 6,
        product: 'Fideos tirabuzón 500 g',
        type: 'Entrada',
        user: 'Diego Torres',
        date: '28/10/2026',
        time: '09:45',
        quantity: 20,
    },
]
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
</style>
