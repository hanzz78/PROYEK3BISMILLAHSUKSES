<template>
  <div class="dashboard">
    <Navbar :user="authStore.user" :role-label="'Participant'" @logout="logout" />

    <div class="dashboard-content">
      <div class="welcome-section">
        <h1>Halo {{ authStore.user?.name }}!</h1>
        <p>Silakan upload dokumen data baru atau lihat status dokumen yang sudah Anda kirim.</p>
      </div>

      <!-- Participant Menu -->
      <div class="menu-grid">
        <router-link :to="{ name: 'participant-import' }" class="menu-card menu-import">
          <div class="card-icon">⬆️</div>
          <h3>Upload Dokumen</h3>
          <p>Kirim file CSV/Excel baru untuk diproses oleh Admin.</p>
        </router-link>

        <router-link :to="{ name: 'participant-status' }" class="menu-card menu-status">
          <div class="card-icon">📜</div>
          <h3>Status Dokumen</h3>
          <p>Lihat riwayat dan status proses dokumen yang telah Anda upload.</p>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '../stores/auth'
import Navbar from '../components/Navbar.vue'

export default {
  name: 'ParticipantDashboard',
  components: {
    Navbar,
  },
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
  methods: {
    async logout() {
      if (confirm('Yakin ingin logout?')) {
        await this.authStore.logout()
        this.$router.push({ name: 'login' })
      }
    }
  }
}
</script>

<style scoped>
.dashboard {
  min-height: 100vh;
  background: #f1f5f9;
}

.dashboard-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 3rem 2rem;
}

.welcome-section {
  margin-bottom: 3rem;
}

.welcome-section h1 {
  color: #1e293b;
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.welcome-section p {
  color: #64748b;
  font-size: 1.1rem;
}

.menu-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.menu-card {
  background: white;
  padding: 2rem;
  border-radius: 1rem;
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
  color: inherit;
}

.menu-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.card-icon {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}

.menu-card h3 {
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.menu-card p {
  color: #64748b;
  font-size: 0.9rem;
}

.menu-import .card-icon { color: #1B2376; }
.menu-status .card-icon { color: #ff914d; }
</style>