<template>
  <div class="dashboard">
    <!-- Navbar -->
    <nav class="navbar">
      <div class="nav-brand">
        <h2>Polban <span>DataVerse</span></h2>
      </div>
      <div class="nav-user">
        <span class="user-name">{{ authStore.user?.name }}</span>
        <span class="user-role" :class="roleClass">{{ roleLabel }}</span>
        <button @click="logout" class="btn-logout">Logout</button>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="dashboard-content">
      <div class="welcome-section">
        <h1>Selamat Datang, {{ authStore.user?.name }}!</h1>
        <p>{{ welcomeMessage }}</p>
      </div>

      <!-- Admin Menu -->
      <div v-if="authStore.isAdmin" class="menu-grid">
        <router-link to="/admin/review" class="menu-card">
          <div class="card-icon">📋</div>
          <h3>Review Data</h3>
          <p>Approve atau reject data import dari participant</p>
        </router-link>

        <router-link to="/admin/logs" class="menu-card">
          <div class="card-icon">📊</div>
          <h3>Activity Logs</h3>
          <p>Lihat semua aktivitas pengguna sistem</p>
        </router-link>

        <div @click="showExportModal = true" class="menu-card">
          <div class="card-icon">📥</div>
          <h3>Export Data</h3>
          <p>Download data mahasiswa dalam format Excel/CSV</p>
        </div>
      </div>

      <!-- Participant Menu -->
      <div v-if="authStore.isParticipant" class="menu-grid">
        <div @click="showImportModal = true" class="menu-card">
          <div class="card-icon">📤</div>
          <h3>Import Data</h3>
          <p>Upload file CSV/Excel untuk import data mahasiswa</p>
        </div>

        <div @click="showExportModal = true" class="menu-card">
          <div class="card-icon">📥</div>
          <h3>Export Data</h3>
          <p>Download data mahasiswa dalam format Excel/CSV</p>
        </div>
      </div>
    </div>

    <!-- Import Modal (Participant) -->
    <ImportModal 
      v-if="showImportModal" 
      @close="showImportModal = false"
    />

    <!-- Export Modal (Both) -->
    <ExportModal 
      v-if="showExportModal" 
      @close="showExportModal = false"
    />
  </div>
</template>

<script>
import { useAuthStore } from '../stores/auth'
import ImportModal from '../components/ImportModal.vue'
import ExportModal from '../components/ExportModal.vue'

export default {
  name: 'Dashboard',
  components: {
    ImportModal,
    ExportModal,
  },
  data() {
    return {
      showImportModal: false,
      showExportModal: false,
    }
  },
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
  mounted() {
    console.log('Dashboard mounted')
    console.log('User:', this.authStore.user)
    console.log('Role:', this.authStore.user?.role)
    console.log('isAdmin:', this.authStore.isAdmin)
    console.log('isParticipant:', this.authStore.isParticipant)
  },
  computed: {
    roleLabel() {
      return this.authStore.isAdmin ? 'Admin' : 'Participant'
    },
    roleClass() {
      return this.authStore.isAdmin ? 'role-admin' : 'role-participant'
    },
    welcomeMessage() {
      if (this.authStore.isAdmin) {
        return 'Kelola data import dan monitor aktivitas sistem'
      }
      return 'Import dan export data mahasiswa dengan mudah'
    }
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

/* Navbar */
.navbar {
  background: linear-gradient(to right, #1B2376, #2d3da6);
  color: white;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.nav-brand h2 {
  margin: 0;
  font-size: 1.5rem;
}

.nav-brand span {
  color: #ff914d;
}

.nav-user {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-name {
  font-weight: 600;
}

.user-role {
  padding: 0.3rem 0.8rem;
  border-radius: 0.5rem;
  font-size: 0.85rem;
  font-weight: 600;
}

.role-admin {
  background: #ff914d;
  color: #1B2376;
}

.role-participant {
  background: #10b981;
  color: white;
}

.btn-logout {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-logout:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Content */
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

/* Menu Grid */
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
  font-size: 3rem;
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
</style>
