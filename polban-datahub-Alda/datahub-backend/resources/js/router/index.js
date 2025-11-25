import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import Login from '../pages/LoginPage.vue'
//import Dashboard from '../pages/Dashboard.vue'
import DashboardWrapper from '../pages/DashboardWrapper.vue' 
import AdminDashboard from '../pages/AdminDashboard.vue' 
import ParticipantDashboard from '../pages/ParticipantDashboard.vue' 
import AdminReview from '../pages/AdminReview.vue'
import AdminLogs from '../pages/AdminLogs.vue'
import ParticipantImport from '../pages/ParticipantImport.vue'
import ParticipantStatus from '../pages/ParticipantStatus.vue'
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
        name: 'dashboard-wrapper',
        component: DashboardWrapper,
        meta: { requiresAuth: true }
    },

    //admin routes
    {
        path: '/admin/dashboard',
        name: 'admin-dashboard',
        component: AdminDashboard,
        meta: { requiresAuth: true, role: 'admin' }
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

    //participant routes
    {
        path: '/participant/dashboard',
        name: 'participant-dashboard',
        component: ParticipantDashboard,
        meta: { requiresAuth: true, role: 'participant' }
    },
    {
        path: '/participant/upload', 
        name: 'participant-upload',
        component: ParticipantImport,
        meta: { requiresAuth: true, role: 'participant' }
    },
    {
        path: '/participant/status',
        name: 'participant-status',
        component: ParticipantStatus,
        meta: { requiresAuth: true, role: 'participant' }
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
    }  else if (guestOnly && authStore.isLoggedIn) {
        // Redirect ke dashboard role jika sudah login
        const redirectName = authStore.isAdmin ? 'admin-dashboard' : 'participant-dashboard';
        next({ name: redirectName })
    } else if (requiredRole && authStore.user?.role !== requiredRole) {
        // Redirect ke dashboard role jika peran tidak cocok
        const redirectName = authStore.isAdmin ? 'admin-dashboard' : 'participant-dashboard';
        next({ name: redirectName })
    } else {
        next()
    }
})

export default router