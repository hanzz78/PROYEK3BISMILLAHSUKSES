<template>
  <div class="admin-page">
    <!-- Asumsi Anda memiliki komponen Navbar yang diimport dan dipanggil di sini -->
    <Navbar :user="authStore.user" :role-label="'Admin'" @logout="logout" />

    <!-- Content -->
    <div class="page-content">
      <div class="page-header">
        <h1>Review Dokumen Import</h1>
        <p>Ubah status dokumen yang diupload oleh participant dan kelola progres pemrosesan.</p>
      </div>

      <!-- Filter Status -->
      <div class="filters">
        <label for="filter-status">Filter Status:</label>
        <select v-model="filterStatus" @change="fetchImports" id="filter-status">
          <option value="">Semua Status (Aksi Aktif)</option>
          <option value="uploaded">Menunggu Review</option>
          <option value="reviewed">Direview Admin</option>
          <option value="approved">Disetujui</option>
          <option value="rejected">Ditolak</option>
          <option value="in_process">Diproses Database</option>
          <option value="visualizing">Visualisasi</option>
          <option value="completed">Selesai</option>
        </select>
      </div>

      <div v-if="loading" class="loading">Loading...</div>

      <div v-else-if="imports.length === 0" class="empty-state">
        <div class="empty-icon">📭</div>
        <p>Tidak ada dokumen untuk direview dengan status ini.</p>
      </div>

      <div v-else class="imports-table">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Importer</th>
              <th>Nama File</th>
              <th>Baris</th>
              <th>Status</th>
              <th>Tanggal Upload</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in imports" :key="item.id">
              <td>{{ item.id }}</td>
              <td>{{ item.user?.name }}</td>
              <td>{{ item.file_name }}</td>
              <td>{{ item.total_rows }}</td>
              <td>
                <span :class="['badge', getStatusClass(item.status)]">
                  {{ formatStatus(item.status) }}
                </span>
              </td>
              <td>{{ formatDate(item.created_at) }}</td>
              <td>
                <div class="action-buttons">
                  <button @click="downloadFile(item)" class="btn-download">Download File</button>
                  <button @click="showDetail(item)" class="btn-detail">Detail & Status</button>
                </div>
              </td>
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

    <!-- Detail Modal (untuk mengubah status) -->
    <div v-if="detailModal" class="modal-overlay" @click="detailModal = null">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Detail & Update Status Dokumen #{{ detailModal.id }}</h2>
          <button @click="detailModal = null" class="btn-close">✕</button>
        </div>
        <div class="modal-body">
          <div class="detail-info">
            <p><strong>Nama File:</strong> {{ detailModal.file_name }}</p>
            <p><strong>Uploader:</strong> {{ detailModal.user?.name }}</p>
            <p><strong>Baris Data:</strong> {{ detailModal.total_rows }}</p>
            <p><strong>Status Saat Ini:</strong> 
              <span :class="['badge', getStatusClass(detailModal.status)]">
                {{ formatStatus(detailModal.status) }}
              </span>
            </p>
          </div>
          
          <h3 class="status-title">Ubah Status Dokumen</h3>
          <div class="form-group">
              <label for="new_status">Pilih Status Baru:</label>
              <select v-model="selectedNewStatus" id="new_status">
                  <option disabled value="">Pilih Status Berikutnya</option>
                  <option v-for="s in filteredStatuses" :key="s" :value="s">
                    {{ formatStatus(s) }}
                  </option>
              </select>
          </div>

          <div v-if="isRejection" class="form-group">
              <label for="reject_notes">Catatan Penolakan (Wajib):</label>
              <textarea v-model="rejectNotes" id="reject_notes" rows="3" placeholder="Sebutkan alasan penolakan..."></textarea>
          </div>
          
          <div v-if="detailModal.admin_notes" class="notes-box">
              <strong>Catatan Admin Sebelumnya:</strong>
              <p>{{ detailModal.admin_notes }}</p>
          </div>

        </div>
        <div class="modal-footer">
          <button @click="detailModal = null" class="btn-cancel">Tutup</button>
          <button 
            @click="updateStatus" 
            :disabled="!selectedNewStatus || updateLoading || (isRejection && !rejectNotes.trim())"
            class="btn-update"
          >
            {{ updateLoading ? 'Memperbarui...' : 'Perbarui Status' }}
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
  name: 'AdminReview',
  components: { Navbar },
  data() {
    return {
      loading: false,
      updateLoading: false,
      imports: [],
      detailModal: null,
      filterStatus: '', // Default: kosong (AdminController akan menampilkan yang perlu aksi)
      selectedNewStatus: '',
      rejectNotes: '',
      allStatuses: [
        'uploaded',
        'reviewed',
        'rejected',
        'approved',
        'in_process',
        'visualizing',
        'completed'
      ],
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
  computed: {
    isRejection() {
        return this.selectedNewStatus === 'rejected';
    },
    filteredStatuses() {
        if (!this.detailModal) return [];
        const currentStatus = this.detailModal.status;
        const currentIndex = this.allStatuses.indexOf(currentStatus);
        
        // Aturan sederhana: Admin bisa mengubah ke status berikutnya dalam alur, atau reject
        return this.allStatuses.filter((status, index) => {
            if (status === 'rejected') return true; // Selalu bisa reject
            
            // Logika alur status normal
            if (currentStatus === 'uploaded' && index === this.allStatuses.indexOf('reviewed')) return true;
            if (currentStatus === 'reviewed' && index === this.allStatuses.indexOf('approved')) return true;
            if (currentStatus === 'approved' && index === this.allStatuses.indexOf('in_process')) return true;
            if (currentStatus === 'in_process' && index === this.allStatuses.indexOf('visualizing')) return true;
            if (currentStatus === 'visualizing' && index === this.allStatuses.indexOf('completed')) return true;
            
            return false;
        });
    }
  },
  watch: {
    detailModal(newVal) {
      if (newVal) {
        this.selectedNewStatus = ''; // Reset saat modal dibuka
        this.rejectNotes = '';
      }
    }
  },
  methods: {
    async fetchImports() {
      this.loading = true
      try {
        const params = { page: this.pagination.current_page };
        if (this.filterStatus) {
            params.status = this.filterStatus;
        }

        const response = await axios.get('/admin/dokumen', { // Rute baru: /admin/dokumen
          params,
          headers: {
            'Authorization': `Bearer ${this.authStore.token}`
          }
        })
        this.imports = response.data.data
        this.pagination.current_page = response.data.current_page
        this.pagination.last_page = response.data.last_page

      } catch (error) {
        alert('Gagal mengambil data: ' + (error.response?.data?.message || error.message))
      } finally {
        this.loading = false
      }
    },
    changePage(page) {
        this.pagination.current_page = page;
        this.fetchImports();
    },
    showDetail(item) {
      this.detailModal = item
    },
    async updateStatus() {
        if (this.isRejection && !this.rejectNotes.trim()) {
            alert('Catatan penolakan wajib diisi!');
            return;
        }
        
        if (!confirm(`Yakin ingin mengubah status dokumen #${this.detailModal.id} menjadi "${this.formatStatus(this.selectedNewStatus)}"?`)) return;

        this.updateLoading = true;

        try {
            await axios.post(`/admin/dokumen/${this.detailModal.id}/status`, { // Rute baru: /admin/dokumen/{id}/status
                new_status: this.selectedNewStatus,
                notes: this.rejectNotes.trim() || null
            }, {
                headers: {
                    'Authorization': `Bearer ${this.authStore.token}`
                }
            })
            alert('Status berhasil diperbarui!');
            this.detailModal = null;
            this.fetchImports(); // Refresh data
        } catch (error) {
            alert('Update status gagal: ' + (error.response?.data?.message || error.message));
        } finally {
            this.updateLoading = false;
        }
    },
    async downloadFile(item) {
        if (!confirm(`Yakin ingin mendownload file asli: ${item.file_name}?`)) return;
        
        try {
            const response = await axios.get(`/admin/dokumen/${item.id}/download`, { // Rute baru: /admin/dokumen/{id}/download
                responseType: 'blob',
                headers: {
                    'Authorization': `Bearer ${this.authStore.token}`
                }
            })

            const contentDisposition = response.headers['content-disposition'];
            let fileName = item.file_name || `document_${item.id}.xlsx`;
            if (contentDisposition) {
                const match = contentDisposition.match(/filename="(.+)"/);
                if (match && match[1]) {
                    fileName = match[1];
                }
            }
            
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', fileName);
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);
            
        } catch (error) {
            alert('Gagal mendownload file: ' + (error.response?.data?.message || error.message));
        }
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
/* Style disederhanakan dan mirip dengan ParticipantStatus.vue */
.admin-page { min-height: 100vh; background: #f1f5f9; }
.page-content { max-width: 1400px; margin: 0 auto; padding: 3rem 2rem; }
.page-header h1 { color: #1e293b; font-size: 2rem; margin-bottom: 0.5rem; }

.filters { margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem; }
.filters label { font-weight: 600; color: #1e293b; }
.filters select { padding: 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 0.5rem; font-size: 1rem; background: white; }

.loading, .empty-state { text-align: center; padding: 3rem; color: #64748b; }
.empty-state { background: white; border-radius: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }

.imports-table { background: white; border-radius: 1rem; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
table { width: 100%; border-collapse: collapse; }
thead { background: #f8fafc; }
th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
th { font-weight: 600; color: #1e293b; }

.action-buttons { display: flex; gap: 0.5rem; }
.btn-detail, .btn-download { padding: 0.5rem 1rem; border: none; border-radius: 0.4rem; cursor: pointer; font-size: 0.85rem; font-weight: 600; }
.btn-detail { background: #ff914d; color: #1B2376; }
.btn-download { background: #3b82f6; color: white; }

/* Modal Styles */
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 1000; }
.modal-content { background: white; border-radius: 1rem; width: 90%; max-width: 650px; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
.modal-header { padding: 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
.btn-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b; }
.modal-body { padding: 2rem; }
.modal-footer { padding: 1.5rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 1rem; }
.btn-cancel { padding: 0.75rem 1.5rem; background: #f1f5f9; color: #64748b; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; }
.btn-update { padding: 0.75rem 1.5rem; background: #10b981; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; }

.detail-info { margin-bottom: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: 0.5rem; }
.detail-info p { margin-bottom: 0.5rem; }
.status-title { margin-top: 1.5rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem; color: #1B2376; }

.form-group { margin-bottom: 1rem; }
.form-group label { display: block; font-weight: 600; margin-bottom: 0.4rem; color: #1e293b; }
.form-group select, .form-group textarea { width: 98%; padding: 0.75rem; border: 1.5px solid #cbd5e1; border-radius: 0.5rem; font-family: inherit; resize: vertical; background-color: #ffffff; }

.notes-box { margin-top: 1.5rem; padding: 1rem; background-color: #f0f4f8; border-radius: 0.5rem; }
.notes-box strong { color: #1B2376; }
</style>