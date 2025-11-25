
<template>
  <div class="login-page">
    <div class="login-container">
      <div class="login-header">
        <h1>Polban <span>DataVerse</span></h1>
      </div>

      <form @submit.prevent="login" class="login-form">
        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input
            v-model="email"
            type="email"
            id="email"
            placeholder="Masukkan email Anda"
            required
          />
        </div>

        <div class="form-group">
          <label for="password">Kata Sandi</label>
          <input
            v-model="password"
            type="password"
            id="password"
            placeholder="Masukkan kata sandi"
            required
          />
        </div>

        <button type="submit" class="btn-login" :disabled="loading">
          {{ loading ? 'Memproses...' : 'Masuk' }}
        </button>
      </form>

      <p class="footer">
        © 2025 Polban DataVerse All Rights Reserved.
      </p>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '../stores/auth'

export default {
  name: 'Login',
  data() {
    return {
      email: '',
      password: '',
      loading: false,
      errorMessage: '',
    }
  },
  methods: {
    async login() {
      this.loading = true
      this.errorMessage = ''
      
      try {
        const authStore = useAuthStore()
        const result = await authStore.login(this.email, this.password)
        
        if (result.success) {
          // Redirect to dashboard
          this.$router.push({ name: 'dashboard' })
        } else {
          this.errorMessage = result.message
        }
      } catch (err) {
        console.error('Login error:', err)
        this.errorMessage = 'Terjadi kesalahan. Silakan coba lagi.'
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
/* ======= TEMA UTAMA: Biru Polban & Aksen Oranye ======= */
.login-page {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background: linear-gradient(135deg, #ff914d 20%, #1B2376 60%, #1B2376 130%);
  font-family: 'Poppins', sans-serif;
}

/* ======= KARTU LOGIN ======= */
.login-container {
  background: #ffffff;
  padding: 3rem 2.5rem;
  border-radius: 1.5rem;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  width: 100%;
  max-width: 420px;
  text-align: center;
  transition: all 0.3s ease;
}

.login-container:hover {
  transform: translateY(-4px);
}

/* ======= HEADER ======= */
.login-header {
  margin-bottom: 2rem;
}

.login-header .logo {
  width: 80px;
  margin-bottom: 1rem;
}

.login-header h1 {
  font-size: 2rem;
  color: #1e2a8a;
  font-weight: 700;
  margin-bottom: 0.3rem;
}

.login-header span {
  color: #ff914d;
}

.login-header p {
  color: #64748b;
  font-size: 0.95rem;
}

/* ======= FORM ======= */
.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.error-message {
  background-color: #fee2e2;
  border: 1px solid #ef4444;
  color: #dc2626;
  padding: 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.9rem;
  text-align: left;
}

.form-group {
  text-align: left;
}

.form-group label {
  font-weight: 600;
  font-size: 0.9rem;
  color: #1e293b;
  margin-bottom: 0.3rem;
  display: block;
}

.form-group input {
  width: 90%;
  padding: 0.8rem 1rem;
  border: 1.5px solid #cbd5e1;
  border-radius: 0.75rem;
  transition: all 0.3s ease;
  font-size: 0.95rem;
  background-color: #f8fafc;
}

.form-group input:focus {
  outline: none;
  border-color: #1e2a8a;
  box-shadow: 0 0 6px rgba(30, 42, 138, 0.3);
  background-color: #ffffff;
}

/* ======= BUTTON ======= */
.btn-login {
  background: linear-gradient(to right, #ff914d, #f8811a);
  /* Use a darker foreground color to ensure sufficient contrast against the gradient background */
  color: #111827;
  border: none;
  padding: 0.9rem;
  font-size: 1rem;
  font-weight: 600;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: 0.3s ease;
}

.btn-login:hover {
  background: linear-gradient(to right, #f8811a, #ff914d);
  transform: scale(1.03);
}

.btn-login:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* ======= FOOTER ======= */
.footer {
  font-size: 0.8rem;
  color: #94a3b8;
  margin-top: 2rem;
}
</style>
