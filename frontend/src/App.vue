<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterView, useRoute, useRouter } from 'vue-router'

import AppSidebar from './components/layout/AppSidebar.vue'
import AppTopbar from './components/layout/AppTopbar.vue'
import LoginView from './views/LoginView.vue'

import {
    obtenerSesion,
    cerrarSesion,
} from './services/auth.js'

// Usuario autenticado
const usuario = ref(null)

// Estado de la aplicación
const cargando = ref(true)
const error = ref('')

const route = useRoute()
const router = useRouter()

// Mostrar el rol recibido del backend
const rolVisible = computed(() => {
    if (!usuario.value) return ''

    const roles = {
        administrador: 'Administrador',
        empleado: 'Empleado',
    }

    return roles[usuario.value.rol] || usuario.value.rol
})

// Comprobar si ya existe una sesión al abrir la aplicación
async function comprobarSesion() {
    cargando.value = true
    error.value = ''

    try {
        usuario.value = await obtenerSesion()
    } catch (err) {
        error.value = err.message || 'No se pudo conectar con el servidor.'
    } finally {
        cargando.value = false
    }
}

// Recibir los datos después del login
function iniciarSesionCorrectamente(datosUsuario) {
    usuario.value = datosUsuario
    error.value = ''
    router.push('/')
}

// Cerrar la sesión en el backend
async function salir() {
    error.value = ''

    try {
        await cerrarSesion()
        usuario.value = null
        router.push('/')
    } catch (err) {
        error.value = err.message || 'No se pudo cerrar la sesión.'
    }
}

// Navegación del menú lateral
function navigateTo(page) {
    if (page === 'dashboard') {
        router.push('/')
    } else {
        router.push(`/${page}`)
    }
}

onMounted(() => {
    comprobarSesion()
})
</script>
<template>
    <!-- Comprobando la sesión -->
    <div v-if="cargando" class="loading-screen">
        <p>Comprobando sesión...</p>
    </div>

    <!-- Error de conexión -->
    <div v-else-if="error && !usuario" class="loading-screen">
        <div class="connection-error">
            <p>{{ error }}</p>
            <button @click="comprobarSesion">
                Reintentar
            </button>
        </div>
    </div>

    <!-- Pantalla de inicio de sesión -->
    <LoginView
        v-else-if="!usuario"
        @login-success="iniciarSesionCorrectamente"
    />

    <!-- Aplicación principal -->
    <div v-else class="app-layout">
        <AppSidebar
            :active-page="route.name"
            @navigate="navigateTo"
        />

        <main class="main-content">
            <AppTopbar
                :title="route.meta.title || 'Inicio'"
                :user-name="usuario.nombre"
                :user-role="rolVisible"
                @logout="salir"
            />

            <p v-if="error" class="global-error">
                {{ error }}
            </p>

            <div class="page-content">
                <RouterView
                    v-slot="{ Component }"
                >
                    <component
                        :is="Component"
                        :user-name="usuario.nombre"
                    />
                </RouterView>
            </div>
        </main>
    </div>
</template>

<style scoped>
.app-layout {
    display: flex;
    min-height: 100vh;
    width: 100%;
}

.main-content {
    flex: 1;
    min-width: 0;
    overflow-x: auto;
}

.page-content {
    padding: 32px;
}

.loading-screen {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: #f1f5f9;
    color: #475569;
}

.connection-error {
    text-align: center;
}

.connection-error button {
    margin-top: 16px;
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    background: #0284c7;
    color: white;
    cursor: pointer;
}

.global-error {
    margin: 16px 32px 0;
    padding: 12px;
    border-radius: 8px;
    background: #fef2f2;
    color: #b91c1c;
    font-size: 13px;
}

@media (max-width: 600px) {
    .page-content {
        padding: 20px 14px;
    }
}
</style>