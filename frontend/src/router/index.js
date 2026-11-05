import { createRouter, createWebHistory } from 'vue-router'

import PlaceholderView from '../views/PlaceholderView.vue'

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: () => import('../views/DashboardView.vue'),
    },
    {
        path: '/productos',
        name: 'productos',
        component: () => import('../views/ProductsView.vue'),
        meta: { title: 'Productos' },
    },
    {
        path: '/categorias',
        name: 'categorias',
        component: PlaceholderView,
        meta: { title: 'Categorías' },
    },
    {
        path: '/movimientos',
        name: 'movimientos',
        component: PlaceholderView,
        meta: { title: 'Movimientos' },
    },
    {
        path: '/reportes',
        name: 'reportes',
        component: PlaceholderView,
        meta: { title: 'Reportes' },
    },
    {
        path: '/usuarios',
        name: 'usuarios',
        component: () => import('../views/UsersView.vue'),
        meta: { title: 'Usuarios' },
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

export default router