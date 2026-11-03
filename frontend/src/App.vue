
<script setup>
import { ref } from 'vue'
import { RouterView, useRoute, useRouter } from 'vue-router'

import AppSidebar from './components/layout/AppSidebar.vue'
import AppTopbar from './components/layout/AppTopbar.vue'

// Por ahora es un usuario de prueba.
// Más adelante estos datos vendrán del inicio de sesión.
const usuario = ref({
  nombre: 'Usuario de prueba',
  rol: 'Administrador',
})

const route = useRoute()
const router = useRouter()

function navigateTo(page) {
  if (page === 'dashboard') {
    router.push('/')
  } else {
    router.push(`/${page}`)
  }
}
</script>

<template>
  <div class="app-layout">

    <AppSidebar
      :active-page="route.name"
      @navigate="navigateTo"
    />

    <main class="main-content">

      <AppTopbar
        :title="route.meta.title || 'Dashboard'"
        :user-name="usuario.nombre"
        :user-role="usuario.rol"
      />

      <div class="page-content">
        <RouterView v-slot="{ Component }">
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

@media (max-width: 600px) {
  .page-content {
    padding: 20px 14px;
  }
}
</style>
