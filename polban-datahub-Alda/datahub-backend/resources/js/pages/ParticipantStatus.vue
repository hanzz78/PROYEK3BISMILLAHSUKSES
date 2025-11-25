<template>
  <div class="participant-page">
    <!-- Asumsi Anda memiliki komponen Navbar yang diimport dan dipanggil di sini -->
    <Navbar :user="authStore.user" :role-label="'Participant'" @logout="logout" />

    <div class="page-content">
      <div class="page-header">
        <h1>Status Dokumen Anda</h1>
        <p>Riwayat upload dan status pemrosesan dokumen Anda.</p>
      </div>

      <div v-if="loading" class="loading-state">Memuat data...</div>

      <div v-else-if="imports.length === 0" class="empty-state">
        <div class="empty-icon">📂</div>
        <p>Anda belum pernah mengupload dokumen.</p>
        <router-link :to="{ name: 'participant-upload' }" class="btn-primary">
            Upload Dokumen Pertama Anda
        </router-link>
      </div>

      <div v-else class="status-table">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama File</th>
              <th>Baris Data</th>
              <th>Status</th>
              <th>Catatan Admin</th>
              <th>Diupload Pada</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in imports" :key="item.id">
              <td>{{ item.id }}</td>
              <td>{{ item.file_name }}</td>
              <td>{{ item.total_rows }}</td>
              <td>
                <span :class="['badge', getStatusClass(item.status)]">
                  {{ formatStatus(item.status) }}
                </span>
              </td>
              <td :title="item.admin_notes">{{ item.admin_notes || '-' }}</td>
              <td>{{ formatDate(item.created_at) }}</td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="pagination">
          <button 
            @click="changePage(pagination.current_page - 1)" 
            :disabled="pagination.current_page === 1"
            class="btn-page"
          >
            Previous
          </button>
          <span class="page-info">
            Page {{ pagination.current_page }} of {{ pagination.last_page }}
          </span>
          <button 
            @click="changePage(pagination.current_page + 1)" 
            :disabled="pagination.current_page === pagination.last_page"
            class="btn-page"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { useAuthStore } from '../stores/auth'
import Navbar from '../components/Navbar.vue' // Pastikan ini ada

export default {
  name: 'ParticipantStatus',
  components: { Navbar },
  data() {
    return {
      loading: false,
      imports: [],
      pagination: {
        current_page: 1,
        last_page: 1,
      },
    }
  },
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
  mounted() {
    this.fetchImports()
  },
  methods: {
    async fetchImports() {
      this.loading = true
      try {
        const response = await axios.get('/participant/dokumen', {
          params: { page: this.pagination.current_page },
          headers: {
            'Authorization': `Bearer ${this.authStore.token}`
          }
        })

        this.imports = response.data.data
        this.pagination.current_page = response.data.current_page
        this.pagination.last_page = response.data.last_page
        
      } catch (error) {
        alert('Gagal mengambil data status: ' + (error.response?.data?.message || error.message))
      } finally {
        this.loading = false
      }
    },
    changePage(page) {
        this.pagination.current_page = page;
        this.fetchImports();
    },
    getStatusClass(status) {
      const classes = {
        'uploaded': 'badge-primary', // Baru diupload
        'reviewed': 'badge-info',    // Admin sudah melihat
        'rejected': 'badge-danger',  // Ditolak
        'approved': 'badge-success', // Disetujui
        'in_process': 'badge-warning', // Sedang diproses
        'visualizing': 'badge-secondary', // Sedang divisualisasi
        'completed': 'badge-complete', // Selesai
      }
      return classes[status] || 'badge-default'
    },
    formatStatus(status) {
      const labels = {
        'uploaded': 'Menunggu Review',
        'reviewed': 'Direview Admin',
        'rejected': 'Ditolak',
        'approved': 'Disetujui',
        'in_process': 'Diproses Database',
        'visualizing': 'Visualisasi',
        'completed': 'Selesai',
      }
      return labels[status] || status
    },
    formatDate(dateString) {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleString('id-ID')
    },
    logout() {
      if (confirm('Yakin ingin logout?')) {
        this.authStore.logout()
        this.$router.push({ name: 'login' })
      }
    }
  }
}
</script>

<style scoped>
/*
   Catatan: Style ini mengasumsikan komponen Navbar menggunakan style yang Anda kirimkan sebelumnya,
   dan style untuk badge/tabel/pagination mirip dengan AdminLogs.vue
*/
.participant-page {
  min-height: 100vh;
  background: #f1f5f9;
}

.page-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 3rem 2rem;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  color: #1e293b;
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.page-header p {
  color: #64748b;
}

.loading-state {
    text-align: center;
    padding: 3rem;
    color: #64748b;
}

.empty-state {
  text-align: center;
  padding: 4rem;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.btn-primary {
    background: #ff914d;
    color: #1B2376;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    text-decoration: none;
    font-weight: 600;
    margin-top: 1rem;
    display: inline-block;
    transition: all 0.3s;
}

.status-table {
  background: white;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

thead {
  background: #f8fafc;
}

th {
  font-weight: 600;
  color: #1e293b;
}

td:nth-child(5) {
    max-width: 250px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.9rem;
    color: #64748b;
}

.badge {
  display: inline-block;
  padding: 0.3rem 0.7rem;
  border-radius: 0.4rem;
  font-size: 0.8rem;
  font-weight: 600;
}

/* Warna Badge */
.badge-primary { background: #dbeafe; color: #1e40af; } 
.badge-info { background: #e0e7ff; color: #4338ca; } 
.badge-danger { background: #fee2e2; color: #991b1b; } 
.badge-success { background: #d1fae5; color: #065f46; } 
.badge-warning { background: #fef3c7; color: #92400e; } 
.badge-secondary { background: #e5e7eb; color: #4b5563; } 
.badge-complete { background: #bbf7d0; color: #15803d; } 

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-top: 1px solid #e2e8f0;
}

.btn-page {
  padding: 0.5rem 1rem;
  background: #1B2376;
  color: white;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-weight: 600;
}

.btn-page:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: #64748b;
  font-weight: 600;
}
</style>