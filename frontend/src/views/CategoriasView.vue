<script setup>
import { ref, computed, onMounted, reactive } from 'vue'

import {
    obtenerCategorias,
    crearCategoria,
    actualizarCategoria,
    eliminarCategoria,
} from '../services/categorias'

// Estado de la pantalla.
const categorias = ref([])
const cargando = ref(false)
const guardando = ref(false)
const error = ref('')
const busqueda = ref('')

// Estado del modal.
const modalAbierto = ref(false)
const modoEdicion = ref(false)
const categoriaEditandoId = ref(null)

// Datos del formulario.
const formulario = reactive({
    nombre: '',
    descripcion: '',
})

// Obtener categorías desde el backend.
async function cargarCategorias() {
    cargando.value = true
    error.value = ''

    try {
        categorias.value = await obtenerCategorias()
    } catch (err) {
        error.value = err.message
    } finally {
        cargando.value = false
    }
}

// Filtrar categorías.
const categoriasFiltradas = computed(() => {
    const termino = busqueda.value.toLowerCase().trim()

    if (!termino) return categorias.value

    return categorias.value.filter((categoria) =>
        [
            categoria.nombre,
            categoria.descripcion || '',
        ].some(valor =>
            String(valor).toLowerCase().includes(termino)
        )
    )
})

// Limpiar formulario.
function limpiarFormulario() {
    formulario.nombre = ''
    formulario.descripcion = ''
    categoriaEditandoId.value = null
}

// Abrir modal para crear.
function abrirCrear() {
    limpiarFormulario()
    error.value = ''
    modoEdicion.value = false
    modalAbierto.value = true
}

// Abrir modal para editar.
function abrirEditar(categoria) {
    limpiarFormulario()

    modoEdicion.value = true
    categoriaEditandoId.value = categoria.id

    formulario.nombre = categoria.nombre
    formulario.descripcion = categoria.descripcion || ''

    error.value = ''
    modalAbierto.value = true
}

// Cerrar modal.
function cerrarModal() {
    modalAbierto.value = false
    limpiarFormulario()
    error.value = ''
}

// Guardar categoría nueva o modificada.
async function guardarCategoria() {
    error.value = ''

    if (!formulario.nombre.trim()) {
        error.value = 'El nombre de la categoría es obligatorio.'
        return
    }

    guardando.value = true

    try {
        const datosCategoria = {
            nombre: formulario.nombre.trim(),
            descripcion: formulario.descripcion.trim(),
        }

        if (modoEdicion.value) {
            await actualizarCategoria(
                categoriaEditandoId.value,
                datosCategoria
            )
        } else {
            await crearCategoria(datosCategoria)
        }

        cerrarModal()
        await cargarCategorias()
    } catch (err) {
        error.value = err.message
    } finally {
        guardando.value = false
    }
}

// Eliminar categoría.
async function confirmarEliminacion(categoria) {
    const confirmado = window.confirm(
        `¿Estás seguro de eliminar la categoría "${categoria.nombre}"?`
    )

    if (!confirmado) return

    error.value = ''

    try {
        await eliminarCategoria(categoria.id)
        await cargarCategorias()
    } catch (err) {
        error.value = err.message
    }
}

// Cargar categorías al ingresar a la pantalla.
onMounted(cargarCategorias)
</script>

<template>
    <main class="categorias-page">

        <!-- Encabezado -->
        <section class="page-header">
            <div>
                <span class="page-eyebrow">Inventario</span>
                <h1>Categorías</h1>
                <p>Organizá los productos de tu inventario.</p>
            </div>

            <button class="btn-primary" @click="abrirCrear">
                <span class="btn-icon">+</span>
                Nueva categoría
            </button>
        </section>

        <!-- Estadísticas -->
        <section class="stats-grid">
            <article class="stat-card">
                <div class="stat-icon stat-icon-blue">C</div>

                <div>
                    <span class="stat-label">Total de categorías</span>
                    <strong class="stat-value">{{ categorias.length }}</strong>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon stat-icon-green">✓</div>

                <div>
                    <span class="stat-label">Categorías disponibles</span>
                    <strong class="stat-value">{{ categorias.length }}</strong>
                </div>
            </article>
        </section>

        <!-- Listado -->
        <section class="content-card">

            <div class="content-header">
                <div>
                    <h2>Listado de categorías</h2>
                    <p>Categorías registradas en SIGI.</p>
                </div>

                <div class="search-box">
                    <span class="search-icon">⌕</span>

                    <input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar categoría..."
                    />
                </div>
            </div>

            <!-- Error -->
            <div v-if="error && !modalAbierto" class="alert-error">
                {{ error }}

                <button @click="cargarCategorias">
                    Reintentar
                </button>
            </div>

            <!-- Cargando -->
            <div v-if="cargando" class="empty-state">
                <span class="loader"></span>
                <p>Cargando categorías...</p>
            </div>

            <!-- Tabla -->
            <div v-else class="table-container">

                <table v-if="categoriasFiltradas.length > 0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th class="actions-heading">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="categoria in categoriasFiltradas"
                            :key="categoria.id"
                        >
                            <td>#{{ String(categoria.id).padStart(4, '0') }}</td>

                            <td>
                                <strong>{{ categoria.nombre }}</strong>
                            </td>

                            <td>
                                {{ categoria.descripcion || 'Sin descripción' }}
                            </td>

                            <td>
                                <div class="action-buttons">
                                    <button
                                        class="btn-action btn-edit"
                                        title="Editar categoría"
                                        @click="abrirEditar(categoria)"
                                    >
                                        Editar
                                    </button>

                                    <button
                                        class="btn-action btn-delete"
                                        title="Eliminar categoría"
                                        @click="confirmarEliminacion(categoria)"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Estado vacío -->
                <div v-else class="empty-state">
                    <div class="empty-icon">C</div>

                    <h3>No se encontraron categorías</h3>

                    <p>
                        {{
                            busqueda
                                ? 'Probá con otro término de búsqueda.'
                                : 'Todavía no hay categorías registradas.'
                        }}
                    </p>
                </div>
            </div>

            <footer class="table-footer">
                Mostrando {{ categoriasFiltradas.length }}
                de {{ categorias.length }} categorías
            </footer>
        </section>

        <!-- Modal de creación y edición -->
        <div
            v-if="modalAbierto"
            class="modal-overlay"
            @click.self="cerrarModal"
        >
            <section class="modal-card">

                <header class="modal-header">
                    <div>
                        <span class="page-eyebrow">
                            {{ modoEdicion ? 'Modificar registro' : 'Nuevo registro' }}
                        </span>

                        <h2>
                            {{ modoEdicion ? 'Editar categoría' : 'Crear categoría' }}
                        </h2>
                    </div>

                    <button
                        class="btn-close"
                        type="button"
                        @click="cerrarModal"
                    >
                        ×
                    </button>
                </header>

                <form @submit.prevent="guardarCategoria">

                    <div class="form-group">
                        <label for="nombre">Nombre de la categoría</label>

                        <input
                            id="nombre"
                            v-model="formulario.nombre"
                            type="text"
                            maxlength="100"
                            placeholder="Ej. Bebidas"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción</label>

                        <textarea
                            id="descripcion"
                            v-model="formulario.descripcion"
                            rows="4"
                            placeholder="Descripción de la categoría..."
                        ></textarea>
                    </div>

                    <div v-if="error" class="alert-error modal-error">
                        {{ error }}
                    </div>

                    <footer class="modal-actions">
                        <button
                            class="btn-secondary"
                            type="button"
                            @click="cerrarModal"
                        >
                            Cancelar
                        </button>

                        <button
                            class="btn-primary"
                            type="submit"
                            :disabled="guardando"
                        >
                            {{
                                guardando
                                    ? 'Guardando...'
                                    : modoEdicion
                                        ? 'Guardar cambios'
                                        : 'Crear categoría'
                            }}
                        </button>
                    </footer>

                </form>
            </section>
        </div>

    </main>
</template>

<style scoped>
.categorias-page {
    display: flex;
    flex-direction: column;
    gap: 24px;
    color: #1e293b;
}

.page-header,
.content-header,
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.page-eyebrow {
    display: block;
    margin-bottom: 6px;
    color: #0284c7;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.09em;
    text-transform: uppercase;
}

.page-header h1,
.modal-header h2 {
    margin: 0;
    font-size: 26px;
    font-weight: 700;
}

.page-header p,
.content-header p {
    margin: 6px 0 0;
    color: #64748b;
    font-size: 13px;
}

.btn-primary,
.btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 40px;
    padding: 0 16px;
    border: 0;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-primary {
    background: #0284c7;
    color: white;
}

.btn-primary:hover {
    background: #0369a1;
}

.btn-primary:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

.btn-icon {
    font-size: 20px;
    line-height: 1;
}

.btn-secondary {
    border: 1px solid #e2e8f0;
    background: white;
    color: #475569;
}

.btn-secondary:hover {
    background: #f8fafc;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: white;
}

.stat-icon {
    display: grid;
    width: 42px;
    height: 42px;
    flex-shrink: 0;
    place-items: center;
    border-radius: 10px;
    font-size: 17px;
    font-weight: 700;
}

.stat-icon-blue {
    background: #e0f2fe;
    color: #0284c7;
}

.stat-icon-green {
    background: #dcfce7;
    color: #16a34a;
}

.stat-label {
    display: block;
    margin-bottom: 5px;
    color: #64748b;
    font-size: 12px;
}

.stat-value {
    font-size: 23px;
    font-weight: 700;
}

.content-card {
    overflow: hidden;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: white;
}

.content-header {
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
}

.content-header h2 {
    margin: 0;
    font-size: 16px;
}

.content-header p {
    font-size: 12px;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 8px;
    width: min(260px, 100%);
    padding: 0 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: white;
}

.search-icon {
    color: #94a3b8;
    font-size: 22px;
}

.search-box input {
    width: 100%;
    min-width: 0;
    height: 38px;
    border: 0;
    outline: 0;
    color: #1e293b;
    font-size: 12px;
}

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

th {
    padding: 13px 20px;
    background: #f8fafc;
    color: #64748b;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

td {
    padding: 15px 20px;
    border-top: 1px solid #f1f5f9;
    color: #475569;
    font-size: 12px;
}

.action-buttons {
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-action {
    padding: 7px 10px;
    border: 1px solid transparent;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
}

.btn-edit {
    border-color: #bae6fd;
    background: #f0f9ff;
    color: #0369a1;
}

.btn-delete {
    border-color: #fecaca;
    background: #fef2f2;
    color: #dc2626;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 45px 20px;
    text-align: center;
}

.empty-state h3 {
    margin: 0;
    color: #334155;
    font-size: 15px;
}

.empty-state p {
    margin: 0;
    color: #94a3b8;
    font-size: 12px;
}

.empty-icon {
    display: grid;
    width: 42px;
    height: 42px;
    place-items: center;
    border-radius: 10px;
    background: #f1f5f9;
    color: #64748b;
    font-weight: 700;
}

.table-footer {
    padding: 14px 20px;
    border-top: 1px solid #e2e8f0;
    color: #64748b;
    font-size: 11px;
}

.alert-error {
    margin: 16px 20px;
    padding: 12px 14px;
    border: 1px solid #fecaca;
    border-radius: 8px;
    background: #fef2f2;
    color: #b91c1c;
    font-size: 12px;
}

.alert-error button {
    margin-left: 10px;
    border: 0;
    background: transparent;
    color: #991b1b;
    font-weight: 700;
    cursor: pointer;
}

.modal-overlay {
    position: fixed;
    z-index: 100;
    inset: 0;
    display: grid;
    place-items: center;
    padding: 20px;
    background: rgb(15 23 42 / 45%);
}

.modal-card {
    width: min(460px, 100%);
    padding: 24px;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: white;
    box-shadow: 0 20px 50px rgb(15 23 42 / 15%);
}

.modal-header {
    margin-bottom: 24px;
}

.btn-close {
    width: 32px;
    height: 32px;
    border: 0;
    border-radius: 7px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 22px;
    cursor: pointer;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 7px;
    margin-bottom: 18px;
}

.form-group label {
    color: #475569;
    font-size: 12px;
    font-weight: 600;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 11px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    outline: none;
    color: #1e293b;
    font-family: inherit;
    font-size: 13px;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #0284c7;
    box-shadow: 0 0 0 3px rgb(2 132 199 / 10%);
}

.form-group textarea {
    resize: vertical;
}

.modal-error {
    margin: 0 0 16px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 24px;
}

.loader {
    width: 24px;
    height: 24px;
    border: 3px solid #e2e8f0;
    border-top-color: #0284c7;
    border-radius: 50%;
    animation: girar 0.8s linear infinite;
}

@keyframes girar {
    to {
        transform: rotate(360deg);
    }
}

@media (max-width: 650px) {
    .page-header,
    .content-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .search-box {
        width: 100%;
    }
}
</style>