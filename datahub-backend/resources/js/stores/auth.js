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
                const response = await axios.post('/login', {
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
            const tokenToUse = this.token

            // Clear state and localStorage FIRST
            this.user = null
            this.token = null
            this.isLoggedIn = false
            localStorage.removeItem('token')
            delete axios.defaults.headers.common['Authorization']

            // Then call logout API (in background, don't wait)
            if (tokenToUse) {
                try {
                    await axios.post('/logout', {}, {
                        headers: {
                            Authorization: `Bearer ${tokenToUse}`,
                        },
                    })
                } catch (error) {
                    console.error('Logout API error:', error)
                }
            }
        },

        async fetchUser() {
            if (!this.token) return

            try {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
                const response = await axios.get('/user')
                this.user = response.data
                this.isLoggedIn = true
            } catch (error) {
                // Token invalid, clear state
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
