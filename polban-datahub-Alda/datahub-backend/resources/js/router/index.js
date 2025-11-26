import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

// Import Halaman
import Login from '../pages/LoginPage.vue'
// DashboardWrapper tidak dipakai jika kita redirect langsung, tapi biarkan jika ada sisa kode
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
        // Route '/' kita biarkan kosong atau redirect manual
        path: '/',
        name: 'root',
        redirect: to => {
            // Biarkan beforeEach yang menangani arahnya
            return { name: 'login' }
        }
    },

    // --- ADMIN ROUTES ---
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

    // --- PARTICIPANT ROUTES ---
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

    // 404 Not Found
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

// --- NAVIGATION GUARD (PENJAGA PINTU) ---
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore()

    // 1. Cek User Login (Load dari token jika ada tapi user belum dimuat)
    if (!authStore.user && authStore.token) {
        await authStore.fetchUser()
    }

    const isAuthenticated = authStore.isLoggedIn
    const userRole = authStore.user?.role

    // =========================================================
    // KASUS 1: Mengakses Halaman Perlu Login tapi Belum Login
    // =========================================================
    if (to.meta.requiresAuth && !isAuthenticated) {
        return next({ name: 'login' })
    }

    // =========================================================
    // KASUS 2: Sudah Login, tapi buka halaman Login atau Root
    // =========================================================
    if ((to.meta.guest || to.name === 'root') && isAuthenticated) {
        const targetRoute = userRole === 'admin' ? 'admin-dashboard' : 'participant-dashboard';
        
        // FIX INFINITE REDIRECT: Cek dulu apakah kita sudah di sana?
        if (to.name !== targetRoute) {
            return next({ name: targetRoute })
        }
    }

    // =========================================================
    // KASUS 3: Cek Hak Akses Role (Salah Kamar)
    // =========================================================
    if (to.meta.role && to.meta.role !== userRole) {
        // Tentukan harusnya ke mana
        const targetRoute = userRole === 'admin' ? 'admin-dashboard' : 'participant-dashboard';

        // FIX INFINITE REDIRECT:
        // Jangan redirect jika kita sudah berada di halaman target!
        // (Ini mencegah loop jika userRole undefined/salah baca tapi router maksa ke sana)
        if (to.name !== targetRoute) {
            return next({ name: targetRoute })
        }
    }

    // Jika lolos semua cek, silakan masuk
    next()
})

export default router