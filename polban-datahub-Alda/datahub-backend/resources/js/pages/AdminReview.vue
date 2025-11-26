<template>
  <div class="admin-page">
    <Navbar :user="authStore.user" :role-label="'Admin'" @logout="logout" />

    <div class="page-content">
      <div class="page-header">
        <h1>Review Dokumen Import</h1>
        <p>Tinjau dokumen yang diupload participant dan tentukan statusnya.</p>
      </div>

      <div v-if="loading" class="loading-state">Memuat data...</div>

      <div v-else-if="imports.length === 0" class="empty-state">
        <div class="empty-icon">📂</div>
        <p>Tidak ada dokumen yang perlu direview saat ini.</p>
      </div>

      <div v-else class="table-container">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Participant</th>
              <th>File</th>
              <th>Baris</th>
              <th>Status</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in imports" :key="item.import_id">
              <td>#{{ item.import_id }}</td>
              <td>
                <div class="user-info">
                  <span class="user-name">{{ item.user?.name || 'Unknown' }}</span>
                  <span class="user-email">{{ item.user?.email }}</span>
                </div>
              </td>
              <td>
                <div class="file-info">
                   <a href="#" @click.prevent="downloadFile(item)" class="file-link">
                      <span v-if="item.isDownloading">⏳ Mengunduh...</span>
                      <span v-else>📄 {{ item.file_name }}</span>
                   </a>
                </div>
              </td>
              <td>{{ item.total_rows }}</td>
              <td>
                <span :class="['badge', getStatusClass(item.status)]">
                  {{ formatStatus(item.status) }}
                </span>
              </td>
              <td>{{ formatDate(item.created_at) }}</td>
              <td>
                <button 
                  @click="openReviewModal(item)" 
                  class="btn-review" 
                  :disabled="isFinal(item.status)"
                  :class="{ 'btn-disabled': isFinal(item.status) }"
                >
                  {{ isFinal(item.status) ? 'Selesai' : 'Review' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="pagination.last_page > 1" class="pagination">
          <button 
            @click="changePage(pagination.current_page - 1)" 
            :disabled="pagination.current_page === 1"
            class="btn-page"
          >
            Previous
          </button>
          <span>Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
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

    <div v-if="showModal" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Review Dokumen #{{ selectedItem?.import_id }}</h3>
          <button @click="closeModal" class="btn-close">×</button>
        </div>
        
        <form @submit.prevent="submitStatus">
          <div class="form-group">
            <label>Status Dokumen</label>
            <select v-model="form.status" required>
              <option value="approved">✅ Approve (Setujui)</option>
              <option value="rejected">❌ Reject (Tolak)</option>
              <option value="reviewed">👀 Reviewed (Tandai Dilihat)</option>
            </select>
          </div>

          <div class="form-group">
            <label>Catatan Admin <span v-if="form.status === 'rejected'" class="text-danger">*</span></label>
            <textarea 
              v-model="form.notes" 
              rows="4" 
              placeholder="Berikan alasan penolakan atau catatan tambahan..."
              :required="form.status === 'rejected'"
            ></textarea>
          </div>

          <div class="modal-actions">
            <button type="button" @click="closeModal" class="btn-cancel">Batal</button>
            <button type="submit" class="btn-save" :disabled="submitting">
              {{ submitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { useAuthStore } from '../stores/auth'
import Navbar from '../components/Navbar.vue'

export default {
  name: 'AdminReview',
  components: { Navbar },
  data() {
    return {
      imports: [],
      loading: false,
      pagination: { current_page: 1, last_page: 1 },
      showModal: false,
      selectedItem: null,
      submitting: false,
      form: {
        status: 'reviewed',
        notes: ''
      }
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
    async fetchImports(page = 1) {
      this.loading = true
      try {
        const response = await axios.get('/admin/dokumen', {
          params: { page },
          headers: { Authorization: `Bearer ${this.authStore.token}` }
        })
        
        // Map isDownloading untuk loading file
        this.imports = response.data.data.map(item => ({...item, isDownloading: false}))
        
        this.pagination = {
            current_page: response.data.current_page,
            last_page: response.data.last_page
        }
      } catch (error) {
        console.error('Fetch error:', error)
      } finally {
        this.loading = false
      }
    },
    
    changePage(page) {
        this.fetchImports(page)
    },

    // --- FITUR DOWNLOAD SECURE ---
    async downloadFile(item) {
      if (item.isDownloading) return
      item.isDownloading = true
      
      try {
        // Request Blob dengan Auth Token
        const response = await axios.get(`/admin/dokumen/${item.import_id}/download`, {
          responseType: 'blob', 
          headers: { Authorization: `Bearer ${this.authStore.token}` }
        })

        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', item.file_name)
        document.body.appendChild(link)
        link.click()
        
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)

      } catch (error) {
        console.error('Download error:', error)
        alert('Gagal mendownload file. ' + (error.response?.data?.message || ''))
      } finally {
        item.isDownloading = false
      }
    },

    // --- CEK STATUS FINAL ---
    isFinal(status) {
        return ['approved', 'rejected', 'completed', 'in_process', 'visualizing'].includes(status);
    },

    openReviewModal(item) {
      this.selectedItem = item
      this.form.status = item.status === 'uploaded' ? 'reviewed' : item.status
      this.form.notes = item.admin_notes || ''
      this.showModal = true
    },

    closeModal() {
      this.showModal = false
      this.selectedItem = null
      this.form.notes = ''
    },

    async submitStatus() {
      if (!this.selectedItem) return

      this.submitting = true
      try {
        // Ambil ID yang benar (import_id)
        const id = this.selectedItem.import_id 
        
        await axios.post(`/admin/dokumen/${id}/status`, {
            new_status: this.form.status,
            notes: this.form.notes
        }, {
            headers: { Authorization: `Bearer ${this.authStore.token}` }
        })

        alert('Status berhasil diperbarui!')
        this.closeModal()
        this.fetchImports(this.pagination.current_page)

      } catch (error) {
        // Tampilkan pesan error (misal: 403 Forbidden jika sudah final)
        const msg = error.response?.data?.message || error.message
        alert('Gagal update status: ' + msg)
      } finally {
        this.submitting = false
      }
    },

    // Helper Functions
    getStatusClass(status) {
      const classes = {
        'uploaded': 'badge-primary',
        'reviewed': 'badge-info',
        'rejected': 'badge-danger',
        'approved': 'badge-success',
        'in_process': 'badge-warning',
        'visualizing': 'badge-secondary',
        'completed': 'badge-complete',
      }
      return classes[status] || 'badge-default'
    },
    formatStatus(status) {
       if(!status) return '-'
       return status.charAt(0).toUpperCase() + status.slice(1);
    },
    formatDate(date) {
      if(!date) return '-'
      return new Date(date).toLocaleDateString('id-ID', {
          day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute:'2-digit'
      })
    },
    logout() {
      if(confirm('Yakin ingin logout?')) {
          this.authStore.logout()
      }
    }
  }
}
</script>

<style scoped>
.admin-page { min-height: 100vh; background: #f1f5f9; }
.page-content { max-width: 1200px; margin: 0 auto; padding: 2rem; }
.page-header h1 { font-size: 1.8rem; color: #1e293b; margin-bottom: 0.5rem; }
.page-header p { color: #64748b; margin-bottom: 2rem; }

.table-container { background: white; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); overflow: hidden; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
th { background: #f8fafc; font-weight: 600; color: #475569; }
.user-info { display: flex; flex-direction: column; }
.user-name { font-weight: 600; color: #1e293b; }
.user-email { font-size: 0.85rem; color: #64748b; }
.file-link { color: #2563eb; text-decoration: none; font-weight: 500; cursor: pointer; }
.file-link:hover { text-decoration: underline; }

.badge { padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;}
.badge-primary { background: #dbeafe; color: #1e40af; }
.badge-info { background: #e0f2fe; color: #0369a1; }
.badge-success { background: #dcfce7; color: #15803d; }
.badge-danger { background: #fee2e2; color: #991b1b; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-default { background: #f1f5f9; color: #475569; }

.btn-review { background: #1B2376; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; cursor: pointer; transition: 0.2s; }
.btn-review:hover { background: #2d3da6; }

/* Style tombol disabled */
.btn-disabled {
    background: #94a3b8 !important; 
    cursor: not-allowed;
    opacity: 0.7;
}

.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 50; }
.modal-content { background: white; width: 90%; max-width: 500px; border-radius: 1rem; padding: 2rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.modal-header h3 { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0; }
.btn-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b; }
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #1e293b; }
.form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; font-size: 1rem; }
.text-danger { color: #ef4444; }
.modal-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; }
.btn-cancel { background: white; border: 1px solid #cbd5e1; padding: 0.75rem 1.5rem; border-radius: 0.5rem; cursor: pointer; }
.btn-save { background: #1B2376; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 0.5rem; cursor: pointer; }
.btn-save:disabled { opacity: 0.7; cursor: not-allowed; }

.pagination { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; }
.btn-page { padding: 0.5rem 1rem; border: 1px solid #cbd5e1; background: white; border-radius: 0.5rem; cursor: pointer; }
.btn-page:disabled { opacity: 0.5; }
</style>