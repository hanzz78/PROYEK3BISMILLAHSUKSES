<template>
  <div class="admin-page">
    <nav class="navbar">
      <div class="nav-brand">
        <h2>Polban <span>DataVerse</span></h2>
      </div>
      <div class="nav-user">
        <router-link to="/" class="nav-link">← Dashboard</router-link>
        <span class="user-name">{{ authStore.user?.name }}</span>
        <button @click="logout" class="btn-logout">Logout</button>
      </div>
    </nav>

    <div class="page-content">
      <div class="page-header">
        <h1>Activity Logs</h1>
        <p>Monitor semua aktivitas pengguna di sistem</p>
      </div>

      <div class="filters">
        <select v-model="filterAction" @change="fetchLogs">
          <option value="">Semua Action</option>
          <option value="login">Login</option>
          <option value="logout">Logout</option>
          <option value="import_data">Import Data</option>
          <option value="approve_data">Approve Data</option>
          <option value="reject_data">Reject Data</option>
          <option value="export_data">Export Data</option>
        </select>
      </div>

      <div v-if="loading" class="loading">Loading...</div>

      <div v-else-if="logs.length === 0" class="empty-state">
        <div class="empty-icon">📝</div>
        <p>Belum ada activity logs</p>
      </div>

      <div v-else class="logs-table">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>User</th>
              <th>Action</th>
              <th>Description</th>
              <th>IP Address</th>
              <th>Timestamp</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in logs" :key="log.id">
              <td>{{ log.id }}</td>
              <td>{{ log.user?.name || '-' }}</td>
              <td>
                <span :class="['badge', getActionClass(log.action)]">
                  {{ log.action }}
                </span>
              </td>
              <td>{{ log.description }}</td>
              <td class="ip-address">{{ log.ip_address }}</td>
              <td>{{ formatDate(log.created_at) }}</td>
            </tr>
          </tbody>
        </table>

        <div v-if="pagination" class="pagination">
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

export default {
  name: 'AdminLogs',
  data() {
    return {
      loading: false,
      logs: [],
      pagination: null,
      filterAction: '',
      currentPage: 1,
    }
  },
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
  mounted() {
    this.fetchLogs()
  },
  methods: {
    async fetchLogs() {
      this.loading = true
      try {
        const params = { page: this.currentPage }
        if (this.filterAction) {
          params.action = this.filterAction
        }

        // PERBAIKAN: URL diganti jadi '/admin/logs' sesuai api.php
        const response = await axios.get('/admin/logs', {
          params,
          headers: {
            'Authorization': `Bearer ${this.authStore.token}`
          }
        })

        // PERBAIKAN: Tambahkan '|| []' untuk mencegah error undefined
        this.logs = response.data.data || []
        
        // PERBAIKAN: Cek keberadaan response.data sebelum akses pagination
        if (response.data) {
            this.pagination = {
              current_page: response.data.current_page || 1,
              last_page: response.data.last_page || 1,
              per_page: response.data.per_page || 10,
              total: response.data.total || 0,
            }
        }
      } catch (error) {
        console.error('Fetch logs error:', error)
        this.logs = [] // Kosongkan logs jika error agar tidak crash
        // Opsional: alert jika ingin notifikasi
        // alert('Gagal mengambil logs: ' + (error.response?.data?.message || error.message))
      } finally {
        this.loading = false
      }
    },
    changePage(page) {
      if (!this.pagination || page < 1 || page > this.pagination.last_page) return
      this.currentPage = page
      this.fetchLogs()
    },
    getActionClass(action) {
      const classes = {
        'login': 'badge-info',
        'logout': 'badge-secondary',
        'import_data': 'badge-primary',
        'approve_data': 'badge-success',
        'reject_data': 'badge-danger',
        'export_data': 'badge-warning',
      }
      return classes[action] || 'badge-default'
    },
    formatDate(dateString) {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleString('id-ID')
    },
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
.admin-page {
  min-height: 100vh;
  background: #f1f5f9;
}

.navbar {
  background: linear-gradient(to right, #1B2376, #2d3da6);
  color: white;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
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

.nav-link {
  color: white;
  text-decoration: none;
  font-weight: 600;
}

.btn-logout {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  cursor: pointer;
}

.page-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 3rem 2rem;
}

.page-header h1 {
  color: #1e293b;
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.filters {
  margin-bottom: 2rem;
}

.filters select {
  padding: 0.75rem 1rem;
  border: 1.5px solid #cbd5e1;
  border-radius: 0.5rem;
  font-size: 1rem;
  background: white;
}

.loading {
  text-align: center;
  padding: 3rem;
  color: #64748b;
}

.empty-state {
  text-align: center;
  padding: 4rem;
  background: white;
  border-radius: 1rem;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.logs-table {
  background: white;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead {
  background: #f8fafc;
}

th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #1e293b;
  border-bottom: 2px solid #e2e8f0;
}

td {
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
  font-size: 0.9rem;
}

.ip-address {
  font-family: monospace;
  color: #64748b;
}

.badge {
  display: inline-block;
  padding: 0.3rem 0.7rem;
  border-radius: 0.4rem;
  font-size: 0.8rem;
  font-weight: 600;
}

.badge-info { background: #dbeafe; color: #1e40af; }
.badge-secondary { background: #e5e7eb; color: #4b5563; }
.badge-primary { background: #e0e7ff; color: #4338ca; }
.badge-success { background: #d1fae5; color: #065f46; }
.badge-danger { background: #fee2e2; color: #991b1b; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-default { background: #f1f5f9; color: #475569; }

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