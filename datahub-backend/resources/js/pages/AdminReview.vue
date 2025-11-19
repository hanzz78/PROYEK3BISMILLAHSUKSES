<template>
  <div class="admin-page">
    <!-- Navbar -->
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

    <!-- Content -->
    <div class="page-content">
      <div class="page-header">
        <h1>Review Data Import</h1>
        <p>Approve atau reject data import dari participant</p>
      </div>

      <div v-if="loading" class="loading">Loading...</div>

      <div v-else-if="pendingImports.length === 0" class="empty-state">
        <div class="empty-icon">📭</div>
        <p>Tidak ada data pending untuk direview</p>
      </div>

      <div v-else class="imports-table">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Importer</th>
              <th>Data Preview</th>
              <th>Tanggal Import</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in pendingImports" :key="item.id">
              <td>{{ item.id }}</td>
              <td>{{ item.user?.name }}</td>
              <td>
                <div class="data-preview">
                  <strong>{{ item.kelas || '-' }}</strong> | 
                  Angkatan: {{ item.angkatan || '-' }}
                </div>
              </td>
              <td>{{ formatDate(item.created_at) }}</td>
              <td>
                <div class="action-buttons">
                  <button @click="showDetail(item)" class="btn-detail">Detail</button>
                  <button @click="approveImport(item.id)" class="btn-approve">✓</button>
                  <button @click="showRejectModal(item)" class="btn-reject">✕</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="detailModal" class="modal-overlay" @click="detailModal = null">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Detail Import Data</h2>
          <button @click="detailModal = null" class="btn-close">✕</button>
        </div>
        <div class="modal-body">
          <div class="detail-grid">
            <div class="detail-item">
              <strong>Kelas:</strong> {{ detailModal.kelas || '-' }}
            </div>
            <div class="detail-item">
              <strong>Angkatan:</strong> {{ detailModal.angkatan || '-' }}
            </div>
            <div class="detail-item">
              <strong>Tanggal Lahir:</strong> {{ detailModal.tgl_lahir || '-' }}
            </div>
            <div class="detail-item">
              <strong>Jenis Kelamin:</strong> {{ detailModal.jenis_kelamin || '-' }}
            </div>
            <div class="detail-item">
              <strong>Agama:</strong> {{ detailModal.agama || '-' }}
            </div>
            <div class="detail-item">
              <strong>Kode Pos:</strong> {{ detailModal.kode_pos || '-' }}
            </div>
            <div class="detail-item">
              <strong>SLTA (Raw):</strong> {{ detailModal.nama_slta_raw || '-' }}
            </div>
            <div class="detail-item">
              <strong>Jalur Daftar (Raw):</strong> {{ detailModal.nama_jalur_daftar_raw || '-' }}
            </div>
            <div class="detail-item">
              <strong>Kabupaten/Kota (Raw):</strong> {{ detailModal.nama_wilayah_raw || '-' }}
            </div>
            <div class="detail-item">
              <strong>Provinsi (Raw):</strong> {{ detailModal.provinsi_raw || '-' }}
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="detailModal = null" class="btn-cancel">Tutup</button>
          <button @click="approveImport(detailModal.id)" class="btn-approve-modal">
            Approve
          </button>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="rejectModal" class="modal-overlay" @click="rejectModal = null">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Reject Import</h2>
          <button @click="rejectModal = null" class="btn-close">✕</button>
        </div>
        <div class="modal-body">
          <p>Berikan alasan penolakan:</p>
          <textarea 
            v-model="rejectNotes" 
            rows="4" 
            placeholder="Contoh: Data tidak lengkap, SLTA tidak sesuai..."
            required
          ></textarea>
        </div>
        <div class="modal-footer">
          <button @click="rejectModal = null" class="btn-cancel">Batal</button>
          <button @click="confirmReject" class="btn-reject-modal">
            Reject
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
  name: 'AdminReview',
  data() {
    return {
      loading: false,
      pendingImports: [],
      detailModal: null,
      rejectModal: null,
      rejectNotes: '',
    }
  },
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
  mounted() {
    this.fetchPendingImports()
  },
  methods: {
    async fetchPendingImports() {
      this.loading = true
      try {
        const response = await axios.get('/admin/pending-imports', {
          headers: {
            'Authorization': `Bearer ${this.authStore.token}`
          }
        })
        this.pendingImports = response.data.data
      } catch (error) {
        alert('Gagal mengambil data: ' + (error.response?.data?.message || error.message))
      } finally {
        this.loading = false
      }
    },
    showDetail(item) {
      this.detailModal = item
    },
    showRejectModal(item) {
      this.rejectModal = item
      this.rejectNotes = ''
    },
    async approveImport(id) {
      if (!confirm('Yakin ingin approve data ini?')) return

      try {
        await axios.post(`/admin/approve/${id}`, {}, {
          headers: {
            'Authorization': `Bearer ${this.authStore.token}`
          }
        })
        alert('Data berhasil di-approve!')
        this.detailModal = null
        this.fetchPendingImports()
      } catch (error) {
        alert('Approve gagal: ' + (error.response?.data?.message || error.message))
      }
    },
    async confirmReject() {
      if (!this.rejectNotes.trim()) {
        alert('Alasan penolakan harus diisi!')
        return
      }

      try {
        await axios.post(`/admin/reject/${this.rejectModal.id}`, {
          notes: this.rejectNotes
        }, {
          headers: {
            'Authorization': `Bearer ${this.authStore.token}`
          }
        })
        alert('Data berhasil di-reject!')
        this.rejectModal = null
        this.fetchPendingImports()
      } catch (error) {
        alert('Reject gagal: ' + (error.response?.data?.message || error.message))
      }
    },
    formatDate(dateString) {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
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

.nav-link {
  color: white;
  text-decoration: none;
  font-weight: 600;
}

.nav-link:hover {
  color: #ff914d;
}

.user-name {
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

.imports-table {
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
}

.data-preview {
  font-size: 0.9rem;
  color: #64748b;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-detail {
  padding: 0.4rem 0.8rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.4rem;
  cursor: pointer;
  font-size: 0.85rem;
}

.btn-approve {
  padding: 0.4rem 0.8rem;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 0.4rem;
  cursor: pointer;
  font-weight: bold;
}

.btn-reject {
  padding: 0.4rem 0.8rem;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 0.4rem;
  cursor: pointer;
  font-weight: bold;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 1rem;
  width: 90%;
  max-width: 600px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  color: #1e293b;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #64748b;
}

.modal-body {
  padding: 2rem;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

.detail-item {
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 0.5rem;
}

.detail-item strong {
  color: #1e293b;
  display: block;
  margin-bottom: 0.25rem;
}

textarea {
  width: 95%;
  padding: 0.75rem;
  border: 1.5px solid #cbd5e1;
  border-radius: 0.5rem;
  font-family: inherit;
  resize: vertical;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.btn-cancel {
  padding: 0.75rem 1.5rem;
  background: #f1f5f9;
  color: #64748b;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-weight: 600;
}

.btn-approve-modal {
  padding: 0.75rem 1.5rem;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-weight: 600;
}

.btn-reject-modal {
  padding: 0.75rem 1.5rem;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-weight: 600;
}
</style>
