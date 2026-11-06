import { createRouter, createWebHistory } from 'vue-router'


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
        component: () => import('../views/CategoriasView.vue'),
        meta: { title: 'Categorías' },
    },
    {
        path: '/movimientos',
        name: 'movimientos',
        component:() => import('../views/MovimientosView.vue'),
        meta: { title: 'Movimientos' },
    },
    {
        path: '/reportes',
        name: 'reportes',
        component: () => import('../views/ReportesView.vue'),
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