<script setup>
import { useRoute } from 'vue-router'; 

const route = useRoute()
</script>

<template>
    <section class="placeholder">
        <div>
            <h1>{{route.meta.title}}</h1>
            <p> Esta sección está en desarrollo.
        Próximamente tendrá sus funcionalidades correspondientes.
            </p>
        </div>
    </section>
</template>

<style scoped>
.placeholder {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 28px;
}

.placeholder h1 {
    font-size: 26px;
    color: #1e293b;
}

.placeholder p {
    margin-top: 8px;
    color: #64748b;
    font-size: 14px;
}
</style>