
<script setup>
import { UserRound, Bell, LogOut } from '@lucide/vue'

defineProps({
    title: {
        type: String,
        default: 'Inicio',
    },
    userName: {
        type: String,
        default: 'Usuario de prueba',
    },
    userRole: {
        type: String,
        default: 'Administrador',
    },
})

const emit = defineEmits(['logout'])
</script>

<template>
    <header class="topbar">
        <div class="topbar-title">
            <h2>{{ title }}</h2>
            <p>Sistema de Gestión de Inventario</p>
        </div>

        <div class="topbar-actions">
            <button class="notification-button" title="Notificaciones">
                <Bell :size="20" />
            </button>

            <div class="user-profile">
                <div class="user-avatar">
                    <UserRound :size="21" />
                </div>

                <div class="user-info">
                    <strong>{{ userName }}</strong>
                    <span>{{ userRole }}</span>
                </div>
            </div>

            <button
                class="logout-button"
                title="Cerrar sesión"
                aria-label="Cerrar sesión"
                @click="emit('logout')"
            >
                <LogOut :size="19" />
            </button>
        </div>
    </header>
</template>

<style scoped>
.topbar {
    height: 82px;
    background: white;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 32px;
    gap: 20px;
}

.topbar-title h2 {
    font-size: 17px;
    color: #1e293b;
}

.topbar-title p {
    margin-top: 4px;
    font-size: 11px;
    color: #94a3b8;
}

.topbar-actions {
    display: flex;
    align-items: center;
    gap: 18px;
}

.notification-button,
.logout-button {
    width: 38px;
    height: 38px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    color: #64748b;
    display: grid;
    place-items: center;
    cursor: pointer;
}

.notification-button:hover {
    background: #f8fafc;
}

.logout-button:hover {
    background: #fef2f2;
    color: #dc2626;
    border-color: #fecaca;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: #e0f2fe;
    color: #0284c7;
    border-radius: 50%;
    display: grid;
    place-items: center;
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.user-info strong {
    color: #334155;
    font-size: 12px;
}

.user-info span {
    color: #94a3b8;
    font-size: 11px;
}

@media (max-width: 600px) {
    .topbar {
        padding: 0 14px;
    }

    .topbar-actions {
        gap: 10px;
    }

    .user-info {
        display: none;
    }
}
</style>
