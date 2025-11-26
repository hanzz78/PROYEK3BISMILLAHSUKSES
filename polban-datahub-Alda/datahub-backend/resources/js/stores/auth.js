import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
        isLoggedIn: false,
    }),

    getters: {
        isAdmin: (state) => state.user?.role === 'admin',
        isParticipant: (state) => state.user?.role === 'participant',
    },

    actions: {
        async login(email, password) {
            try {
                const response = await axios.post('login', {
                    email,
                    password,
                })

                this.token = response.data.token
                this.user = response.data.user
                this.isLoggedIn = true

                // Save token to localStorage
                localStorage.setItem('token', response.data.token)

                // Set default axios header
                axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`

                return { success: true }
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Login failed',
                }
            }
        },

        async logout() {
            // 1. Ambil token saat ini sebelum dihapus
            const tokenToUse = this.token

            // 2. Hapus token dari penyimpanan lokal browser (Local Storage)
            // Ini langkah paling penting agar user tidak otomatis login lagi saat halaman direfresh.
            localStorage.removeItem('token')
            
            // 3. Panggil API Logout di server (Background process)
            // Kita gunakan try-catch agar jika server error/offline, logout di sisi klien tetap jalan.
            try {
                if (tokenToUse) {
                    await axios.post('logout', {}, {
                        headers: {
                            Authorization: `Bearer ${tokenToUse}`,
                        },
                    })
                }
            } catch (error) {
                console.error('Logout API error:', error)
            }

            // 4. HARD REDIRECT (Solusi Anti-Crash)
            // Daripada mereset state Vue manual (yang bikin error "Cannot read properties of null"),
            // kita paksa browser memuat ulang halaman dan pindah ke /login.
            // Ini akan membersihkan memori aplikasi secara total dan aman.
            window.location.href = '/login'
        },

        async fetchUser() {
            if (!this.token) return

            try {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
                const response = await axios.get('user')
                this.user = response.data
                this.isLoggedIn = true
            } catch (error) {
                // Jika token tidak valid atau expired, lakukan logout paksa
                this.logout()
            }
        },

        initAuth() {
            if (this.token) {
                this.fetchUser()
            }
        },
    },
})