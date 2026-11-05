
<script setup>
import { ref, computed, onMounted, reactive } from 'vue'

import {
    obtenerUsuarios,
    crearUsuario,
    actualizarUsuario,
    eliminarUsuario,
} from '../services/usuarios'

// Estado de la pantalla

const usuarios = ref([])
const cargando = ref(false)
const guardando = ref(false)
const error = ref('')
const busqueda = ref('')

// Estado del modal

const modalAbierto = ref(false)
const modoEdicion = ref(false)
const usuarioEditandoId = ref(null)

// Estado del formulario

const formulario = reactive({
    nombre: '',
    usuario: '',
    password: '',
    rol: 'empleado',
})

// Obtener usuarios desde el backend

async function cargarUsuarios() {
    cargando.value = true
    error.value = ''

    try {
        usuarios.value = await obtenerUsuarios()
    } catch (err) {
        error.value = err.message
    } finally {
        cargando.value = false
    }
}

// Filtrar usuarios

const usuariosFiltrados = computed(() => {
    const termino = busqueda.value.toLowerCase().trim()

    if (!termino) return usuarios.value

    return usuarios.value.filter((usuario) =>
        [
            usuario.nombre,
            usuario.usuario,
            usuario.rol,
        ].some((valor) =>
            String(valor).toLowerCase().includes(termino)
        )
    )
})

// Limpiar formulario

function limpiarFormulario() {
    formulario.nombre = ''
    formulario.usuario = ''
    formulario.password = ''
    formulario.rol = 'empleado'

    usuarioEditandoId.value = null
}

// Abrir modal para crear

function abrirCrear() {
    limpiarFormulario()
    modoEdicion.value = false
    modalAbierto.value = true
}

// Abrir modal para editar

function abrirEditar(usuario) {
    limpiarFormulario()

    modoEdicion.value = true
    usuarioEditandoId.value = usuario.id

    formulario.nombre = usuario.nombre
    formulario.usuario = usuario.usuario
    formulario.rol = usuario.rol

    modalAbierto.value = true
}

// Cerrar modal

function cerrarModal() {
    modalAbierto.value = false
    limpiarFormulario()
    error.value = ''
}

// Guardar usuario nuevo o modificado

async function guardarUsuario() {
    error.value = ''

    if (
        !formulario.nombre.trim() ||
        !formulario.usuario.trim()
    ) {
        error.value = 'Completa el nombre y el usuario.'
        return
    }

    if (
        !modoEdicion.value &&
        !formulario.password.trim()
    ) {
        error.value = 'La contraseña es obligatoria para crear un usuario.'
        return
    }

    guardando.value = true

    try {
        if (modoEdicion.value) {
            const datos = {
                nombre: formulario.nombre.trim(),
                usuario: formulario.usuario.trim(),
                rol: formulario.rol,
            }

            // Si se escribió una contraseña, también se actualiza.

            if (formulario.password.trim()) {
                datos.password = formulario.password
            }

            await actualizarUsuario(
                usuarioEditandoId.value,
                datos
            )
        } else {
            await crearUsuario({
                nombre: formulario.nombre.trim(),
                usuario: formulario.usuario.trim(),
                password: formulario.password.trim(),
                rol: formulario.rol,
            })
        }

        cerrarModal()
        await cargarUsuarios()

    } catch (err) {
        error.value = err.message
    } finally {
        guardando.value = false
    }
}

// Eliminar usuario

async function confirmarEliminacion(usuario) {
    const confirmado = window.confirm(
        `¿Estás seguro de que querés eliminar al usuario "${usuario.nombre}"?`
    )

    if (!confirmado) return

    error.value = ''

    try {
        await eliminarUsuario(usuario.id)
        await cargarUsuarios()
    } catch (err) {
        error.value = err.message
    }
}

// Cargar usuarios al ingresar a la pantalla

onMounted(cargarUsuarios)
</script>

<template>
    <main class="usuarios-page">
        <!-- Encabezado -->
        <section class="page-header">
            <div>
                <span class="page-eyebrow">Administración</span>
                <h1>Usuarios</h1>
                <p>Gestioná los usuarios y sus permisos de acceso al sistema.</p>
            </div>

            <button class="btn-primary" @click="abrirCrear">
                <span class="btn-icon">+</span>
                Nuevo usuario
            </button>
        </section>

        <!-- Estadísticas -->
        <section class="stats-grid">
            <article class="stat-card">
                <div class="stat-icon stat-icon-blue">U</div>
                <div>
                    <span class="stat-label">Total de usuarios</span>
                    <strong class="stat-value">{{ usuarios.length }}</strong>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon stat-icon-purple">A</div>
                <div>
                    <span class="stat-label">Administradores</span>
                    <strong class="stat-value">
                        {{ usuarios.filter(u => u.rol === 'administrador').length }}
                    </strong>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon stat-icon-green">E</div>
                <div>
                    <span class="stat-label">Empleados</span>
                    <strong class="stat-value">
                        {{ usuarios.filter(u => u.rol === 'empleado').length }}
                    </strong>
                </div>
            </article>
        </section>

        <!-- Listado -->
        <section class="content-card">
            <div class="content-header">
                <div>
                    <h2>Listado de usuarios</h2>
                    <p>Usuarios registrados en SIGI.</p>
                </div>

                <div class="search-box">
                    <span class="search-icon">⌕</span>
                    <input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar usuario..."
                    />
                </div>
            </div>

            <!-- Error -->
            <div v-if="error && !modalAbierto" class="alert-error">
                {{ error }}
                <button @click="cargarUsuarios">Reintentar</button>
            </div>

            <!-- Cargando -->
            <div v-if="cargando" class="empty-state">
                <span class="loader"></span>
                <p>Cargando usuarios...</p>
            </div>

            <!-- Tabla -->
            <div v-else class="table-container">
                <table v-if="usuariosFiltrados.length > 0">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Nombre de usuario</th>
                            <th>Rol</th>
                            <th class="actions-heading">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="usuario in usuariosFiltrados"
                            :key="usuario.id"
                        >
                            <td>
                                <div class="user-cell">
                                    <div class="avatar">
                                        {{ usuario.nombre.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <strong>{{ usuario.nombre }}</strong>
                                        <small>ID: {{ usuario.id }}</small>
                                    </div>
                                </div>
                            </td>

                            <td>{{ usuario.usuario }}</td>

                            <td>
                                <span
                                    class="role-badge"
                                    :class="usuario.rol === 'administrador'
                                        ? 'role-admin'
                                        : 'role-employee'"
                                >
                                    {{ usuario.rol === 'administrador'
                                        ? 'Administrador'
                                        : 'Empleado' }}
                                </span>
                            </td>

                            <td>
                                <div class="action-buttons">
                                    <button
                                        class="btn-action btn-edit"
                                        title="Editar usuario"
                                        @click="abrirEditar(usuario)"
                                    >
                                        Editar
                                    </button>

                                    <button
                                        class="btn-action btn-delete"
                                        title="Eliminar usuario"
                                        @click="confirmarEliminacion(usuario)"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-else class="empty-state">
                    <div class="empty-icon">U</div>
                    <h3>No se encontraron usuarios</h3>
                    <p>
                        {{
                            busqueda
                                ? 'Probá con otro término de búsqueda.'
                                : 'Todavía no hay usuarios registrados.'
                        }}
                    </p>
                </div>
            </div>

            <footer class="table-footer">
                Mostrando {{ usuariosFiltrados.length }}
                de {{ usuarios.length }} usuarios
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
                            {{ modoEdicion ? 'Editar usuario' : 'Crear usuario' }}
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

                <form @submit.prevent="guardarUsuario">
                    <div class="form-group">
                        <label for="nombre">Nombre completo</label>
                        <input
                            id="nombre"
                            v-model="formulario.nombre"
                            type="text"
                            placeholder="Ej. Juan Pérez"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label for="usuario">Nombre de usuario</label>
                        <input
                            id="usuario"
                            v-model="formulario.usuario"
                            type="text"
                            placeholder="Ej. juanperez"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label for="password">
                            Contraseña
                            <span v-if="modoEdicion" class="optional-label">
                                (opcional)
                            </span>
                        </label>
                        <input
                            id="password"
                            v-model="formulario.password"
                            type="password"
                            :placeholder="modoEdicion
                                ? 'Dejar vacío para mantener la actual'
                                : 'Ingresá una contraseña'"
                            :required="!modoEdicion"
                        />
                    </div>

                    <div class="form-group">
                        <label for="rol">Rol del usuario</label>
                        <select id="rol" v-model="formulario.rol">
                            <option value="empleado">Empleado</option>
                            <option value="administrador">Administrador</option>
                        </select>
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
                            {{ guardando
                                ? 'Guardando...'
                                : modoEdicion
                                    ? 'Guardar cambios'
                                    : 'Crear usuario' }}
                        </button>
                    </footer>
                </form>
            </section>
        </div>
    </main>
</template>

<style scoped>
.usuarios-page {
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
    grid-template-columns: repeat(3, minmax(0, 1fr));
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

.stat-icon-purple {
    background: #f3e8ff;
    color: #9333ea;
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
    background: #fff;
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
    white-space: nowrap;
}

tbody tr:hover {
    background: #f8fafc;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 11px;
}

.user-cell strong {
    display: block;
    color: #1e293b;
    font-size: 12px;
}

.user-cell small {
    display: block;
    margin-top: 3px;
    color: #94a3b8;
    font-size: 10px;
}

.avatar {
    display: grid;
    width: 34px;
    height: 34px;
    flex-shrink: 0;
    place-items: center;
    border-radius: 50%;
    background: #e0f2fe;
    color: #0369a1;
    font-size: 13px;
    font-weight: 700;
}

.role-badge {
    display: inline-flex;
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 600;
}

.role-admin {
    background: #f3e8ff;
    color: #7e22ce;
}

.role-employee {
    background: #dcfce7;
    color: #15803d;
}

.actions-heading {
    text-align: right;
}

.action-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 7px;
}

.btn-action {
    padding: 6px 9px;
    border: 1px solid transparent;
    border-radius: 6px;
    background: transparent;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
}

.btn-edit {
    border-color: #bae6fd;
    color: #0369a1;
}

.btn-edit:hover {
    background: #e0f2fe;
}

.btn-delete {
    border-color: #fecaca;
    color: #dc2626;
}

.btn-delete:hover {
    background: #fef2f2;
}

.table-footer {
    padding: 13px 20px;
    border-top: 1px solid #e2e8f0;
    color: #94a3b8;
    font-size: 11px;
}

.empty-state {
    display: flex;
    min-height: 190px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 25px;
    color: #64748b;
    text-align: center;
}

.empty-state h3 {
    margin: 12px 0 5px;
    color: #334155;
    font-size: 14px;
}

.empty-state p {
    margin: 0;
    font-size: 12px;
}

.empty-icon {
    display: grid;
    width: 42px;
    height: 42px;
    place-items: center;
    border-radius: 50%;
    background: #f1f5f9;
    color: #64748b;
    font-weight: 700;
}

.loader {
    width: 25px;
    height: 25px;
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

.alert-error {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin: 15px 20px;
    padding: 12px;
    border: 1px solid #fecaca;
    border-radius: 8px;
    background: #fef2f2;
    color: #b91c1c;
    font-size: 12px;
}

.alert-error button {
    border: 0;
    background: transparent;
    color: #991b1b;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}

.modal-overlay {
    position: fixed;
    z-index: 1000;
    inset: 0;
    display: grid;
    place-items: center;
    padding: 20px;
    background: rgb(15 23 42 / 45%);
}

.modal-card {
    width: min(100%, 470px);
    max-height: 90vh;
    overflow-y: auto;
    padding: 24px;
    border-radius: 14px;
    background: white;
    box-shadow: 0 20px 50px rgb(15 23 42 / 18%);
}

.modal-header {
    margin-bottom: 22px;
}

.modal-header h2 {
    font-size: 20px;
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
    margin-bottom: 16px;
}

.form-group label {
    color: #334155;
    font-size: 12px;
    font-weight: 600;
}

.optional-label {
    color: #94a3b8;
    font-weight: 400;
}

.form-group input,
.form-group select {
    width: 100%;
    height: 41px;
    box-sizing: border-box;
    padding: 0 11px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    outline: none;
    background: white;
    color: #1e293b;
    font-size: 12px;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #0284c7;
    box-shadow: 0 0 0 3px rgb(2 132 199 / 10%);
}

.modal-error {
    margin: 5px 0 15px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 9px;
    margin-top: 22px;
}

@media (max-width: 760px) {
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
        box-sizing: border-box;
    }

    .page-header h1 {
        font-size: 23px;
    }
}
</style>