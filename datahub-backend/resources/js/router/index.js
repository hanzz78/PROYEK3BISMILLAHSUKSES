import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import Login from '../pages/LoginPage.vue'
import Dashboard from '../pages/Dashboard.vue'
import AdminReview from '../pages/AdminReview.vue'
import AdminLogs from '../pages/AdminLogs.vue'
import NotFound from '../pages/NotFound.vue'

const routes = [
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/',
        name: 'dashboard',
        component: Dashboard,
        meta: { requiresAuth: true }
    },
    {
        path: '/admin/review',
        name: 'admin-review',
        component: AdminReview,
        meta: { requiresAuth: true, role: 'admin' }
    },
    {
        path: '/admin/logs',
        name: 'admin-logs',
        component: AdminLogs,
        meta: { requiresAuth: true, role: 'admin' }
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFound
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

// Navigation Guard
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore()

    // Initialize auth on first load (wait for it to complete)
    if (!authStore.user && authStore.token) {
        await authStore.fetchUser()
    }

    const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
    const guestOnly = to.matched.some(record => record.meta.guest)
    const requiredRole = to.meta.role

    if (requiresAuth && !authStore.isLoggedIn) {
        // Redirect to login if not authenticated
        next({ name: 'login' })
    } else if (guestOnly && authStore.isLoggedIn) {
        // Redirect to dashboard if already logged in
        next({ name: 'dashboard' })
    } else if (requiredRole && authStore.user?.role !== requiredRole) {
        // Redirect to dashboard if role doesn't match
        next({ name: 'dashboard' })
    } else {
        next()
    }
})

export default router
