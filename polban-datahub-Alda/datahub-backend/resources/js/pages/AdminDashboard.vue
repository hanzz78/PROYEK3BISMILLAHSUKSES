<template>
  <div class="dashboard">
    <Navbar :user="authStore.user" :role-label="'Admin'" @logout="logout" />

    <div class="dashboard-content">
      <div class="welcome-section">
        <h1>Selamat Datang Admin, {{ authStore.user?.name }}!</h1>
        <p>Anda memiliki kontrol penuh atas sistem data. Kelola data import dan monitor aktivitas.</p>
      </div>

      <!-- Admin Menu -->
      <div class="menu-grid">
        <router-link :to="{ name: 'admin-review' }" class="menu-card menu-review">
          <div class="card-icon">📁</div>
          <h3>Review Dokumen</h3>
          <p>Ubah status dan lihat dokumen import dari participant</p>
        </router-link>

        <router-link :to="{ name: 'admin-logs' }" class="menu-card menu-logs">
          <div class="card-icon">📝</div>
          <h3>Activity Logs</h3>
          <p>Lihat semua aktivitas pengguna sistem</p>
        </router-link>

        <div @click="downloadExport" class="menu-card menu-export">
          <div class="card-icon">📊</div>
          <h3>Export Data Core</h3>
          <p>Download data mahasiswa (Core Data) dalam format Excel/CSV</p>
        </div>
      </div>
      
      <!-- Tambahkan komponen untuk modal atau notifikasi download -->
      <div v-if="downloading" class="status-message">
        <p>Sedang menyiapkan file export, mohon tunggu...</p>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '../stores/auth'
import Navbar from '../components/Navbar.vue' // Gunakan komponen Navbar terpisah
import axios from 'axios'

export default {
  name: 'AdminDashboard',
  components: {
    Navbar,
  },
  data() {
    return {
      downloading: false,
    }
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
    },
    async downloadExport() {
      this.downloading = true
      try {
        const response = await axios.get('/data/export-data', {
          responseType: 'blob', // Penting untuk download file
          headers: {
            'Authorization': `Bearer ${this.authStore.token}`
          }
        })

        // Ambil nama file dari header Content-Disposition
        const contentDisposition = response.headers['content-disposition'];
        let fileName = 'export_data.xlsx';
        if (contentDisposition) {
            const match = contentDisposition.match(/filename="(.+)"/);
            if (match && match[1]) {
                fileName = match[1];
            }
        }
        
        // Buat objek URL untuk blob
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', fileName);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        
        alert('File berhasil didownload!');

      } catch (error) {
        alert('Gagal melakukan export data: ' + (error.response?.data?.message || error.message));
      } finally {
        this.downloading = false;
      }
    }
  }
}
</script>

<style scoped>
/* Style dipindahkan ke App.vue atau file CSS global untuk Navbar */
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

.menu-review .card-icon { color: #ff914d; }
.menu-logs .card-icon { color: #1B2376; }
.menu-export .card-icon { color: #10b981; }

.status-message {
    padding: 1rem;
    margin-top: 2rem;
    background-color: #fef3c7;
    border: 1px solid #f97316;
    color: #9a3412;
    border-radius: 0.5rem;
    text-align: center;
}
</style>