<script setup>
import{ ref } from 'vue'
import { iniciarSesion } from '../services/auth';


const emit = defineEmits(['login-success'])

const usuario = ref('')
const password = ref('')
const cargando = ref(false)
const error = ref('')


async function ingresar() {
    error.value = ''

    if(!usuario.value.trim() || !password.value){
        error.value = 'Completa el usuario y la contraseña.'
        return
    }
    cargando.value = true
    
    try {
        const datosUsuario = await iniciarSesion(
            usuario.value.trim(),
            password.value
        )
        emit('login-success', datosUsuario)
    } catch (err) {
        error.value = err.message || 'No se pudo inciar sesion'
    }finally{
        cargando.value = false
    }
}
</script>

<template>
    <main class="login-page">
        <section class="login-card">
            <div class="login-brand">
                <div>
                    <h1>SIGI</h1>
                    <p>Sistema de Gesition de Inevntario</p>
                </div>
            </div>

            <div class="login-heading">
                <h2>Bienvenido</h2>
                <p>Ingresa tus credenciales para continuar</p>
            </div>

            <form  @submit.prevent="ingresar">
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input 
                    id="usuario"
                    v-model="usuario"
                    type="text"
                    placeholder="Ingresa tu usuario"
                    autocomplete="username"
                    :disabled="cargando"
                    />
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input 
                    id="password"
                    v-model="password"
                    type="password"
                    placeholder="Ingresa la contraseña"
                    autocomplete="current-password"
                    :disabled="cargando"
                    />
                </div>

                <p v-if="error" class="error-message">
                    {{ error }}
                </p>

                <button type="sumbit" :disabled="cargando">
                    {{ cargando ? 'Ingresando...' : 'Iniciar sesion' }}
                </button>
            </form>

            <p class="login-footer"> SIGI - Gesition de Inevntario</p>
        </section>
    </main>
</template>

<style scoped>
.login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: #f1f5f9;
}

.login-card {
    width: 100%;
    max-width: 420px;
    padding: 36px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 12px 35px rgba(15, 23, 42, 0.08);
}

.login-brand {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 38px;
}

.brand-icon {
    width: 48px;
    height: 48px;
    display: grid;
    place-items: center;
    border-radius: 12px;
    background: #0284c7;
    color: white;
    font-size: 24px;
    font-weight: 700;
}

.login-brand h1 {
    margin: 0;
    color: #1e293b;
    font-size: 22px;
}

.login-brand p {
    margin: 3px 0 0;
    color: #64748b;
    font-size: 12px;
}

.login-heading {
    margin-bottom: 28px;
}

.login-heading h2 {
    margin: 0 0 8px;
    color: #1e293b;
    font-size: 25px;
}

.login-heading p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 20px;
}

.form-group label {
    color: #334155;
    font-size: 14px;
    font-weight: 600;
}

.form-group input {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    outline: none;
    color: #1e293b;
    background: #ffffff;
    font-size: 14px;
}

.form-group input:focus {
    border-color: #0284c7;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
}

.form-group input:disabled {
    background: #f1f5f9;
}

.error-message {
    margin: 0 0 16px;
    padding: 10px 12px;
    border-radius: 8px;
    background: #fef2f2;
    color: #b91c1c;
    font-size: 13px;
}

form button {
    width: 100%;
    padding: 13px;
    border: none;
    border-radius: 8px;
    background: #0284c7;
    color: white;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

form button:hover:not(:disabled) {
    background: #0369a1;
}

form button:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.login-footer {
    margin: 28px 0 0;
    color: #94a3b8;
    font-size: 12px;
    text-align: center;
}
</style>