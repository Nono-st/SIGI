<script setup>
import {
    LayoutDashboard,
    Package,
    Tags,
    ArrowLeftRight,
    ChartNoAxesCombined,
    Users,
    Warehouse,
    PanelLeftClose,
    PanelLeftOpen,
} from '@lucide/vue'

import { ref } from 'vue'

const props = defineProps({
    activePage: {
    type: String,
    default: 'dashboard',
    },
})

const emit = defineEmits(['navigate'])

const collapsed = ref(false)

const menuItems = [
    { name: 'Dashboard', path: 'dashboard', icon: LayoutDashboard },
    { name: 'Productos', path: 'productos', icon: Package },
    { name: 'Categorías', path: 'categorias', icon: Tags },
    { name: 'Movimientos', path: 'movimientos', icon: ArrowLeftRight },
    { name: 'Reportes', path: 'reportes', icon: ChartNoAxesCombined },
    { name: 'Usuarios', path: 'usuarios', icon: Users },
]
</script>

<template>
    <aside class="sidebar" :class="{ collapsed }">
        <div class="sidebar-header">
            <div class="brand">
                <div class="brand-icon">
                    <Warehouse :size="22" />
        </div>

        <div v-if="!collapsed" class="brand-text">
            <strong>SIGI</strong>
            <span>Gestión de inventario</span>
        </div>
        </div>

    <button
        class="collapse-button"
        @click="collapsed = !collapsed"
        :title="collapsed ? 'Expandir menú' : 'Contraer menú'"
        >
        <PanelLeftClose v-if="!collapsed" :size="19" />
        <PanelLeftOpen v-else :size="19" />
        </button>
    </div>

    <nav class="sidebar-nav">
        <span v-if="!collapsed" class="nav-label">MENÚ PRINCIPAL</span>

        <button
        v-for="item in menuItems"
        :key="item.path"
        class="nav-item"
        :class="{ active: activePage === item.path }"
        @click="emit('navigate', item.path)"
        :title="collapsed ? item.name : ''"
    >
        <component :is="item.icon" :size="20" />
        <span v-if="!collapsed">{{ item.name }}</span>
    </button>
    </nav>

    <div v-if="!collapsed" class="sidebar-footer">
        <span>Sistema de Gestión</span>
        <small>Proyecto Integrador · 2026</small>
    </div>
    </aside>
</template>

<style scoped>
.sidebar {
    width: 250px;
    min-height: 100vh;
    background: var(--color-sidebar);
    color: #f8fafc;
    display: flex;
    flex-direction: column;
    transition: width 0.2s ease;
    flex-shrink: 0;
}

.sidebar.collapsed {
  width: 76px;
}

.sidebar-header {
    min-height: 82px;
    padding: 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    overflow: hidden;
}

.brand-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: var(--color-primary);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.brand-text {
    display: flex;
    flex-direction: column;
    white-space: nowrap;
    gap: 3px;
}

.brand-text strong {
    font-size: 19px;
}

.brand-text span {
    font-size: 11px;
    color: #94a3b8;
}

.collapse-button {
    border: none;
    background: transparent;
    color: #94a3b8;
    padding: 5px;
    display: grid;
    place-items: center;
    border-radius: 6px;
}

.collapse-button:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.sidebar.collapsed .sidebar-header {
    flex-direction: column;
    gap: 12px;
    padding: 14px 8px;
}

.sidebar-nav {
    padding: 24px 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;  
}

.nav-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1px;
    color: #94a3b8;
    padding: 0 12px;
    margin-bottom: 10px;
}

.nav-item {
    width: 100%;
    min-height: 44px;
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 0 13px;
    border: none;
    border-radius: 7px;
    background: transparent;
    color: #cbd5e1;
    text-align: left;
    font-size: 13px;
    transition: background 0.15s ease;
}

.nav-item:hover {
    background: rgba(255, 255, 255, 0.08);
    color: white;
}

.nav-item.active {
    background: var(--color-primary);
    color: white;
}

.sidebar.collapsed .nav-item {
    justify-content: center;
    padding: 0;
}

.sidebar-footer {
    padding: 18px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    flex-direction: column;
    gap: 5px;
    font-size: 12px;
}

.sidebar-footer small {
    color: #94a3b8;
    font-size: 10px;
}
</style>