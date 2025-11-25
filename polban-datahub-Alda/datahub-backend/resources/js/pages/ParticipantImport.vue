<template>
  <div class="participant-page">
    <Navbar :user="authStore.user" :role-label="'Participant'" @logout="logout" />

    <div class="page-content">
      <div class="page-header">
        <h1>Upload Dokumen Data</h1>
        <p>Silakan upload file CSV atau Excel Anda. Setelah diupload, Admin akan me-review dokumen tersebut.</p>
      </div>

      <div class="upload-card">
        <form @submit.prevent="handleUpload" class="upload-form">
          
          <div class="file-input-group">
            <label for="file-upload" class="file-label">
              {{ file ? file.name : 'Pilih file (CSV, XLSX, XLS, Max: 10MB)' }}
            </label>
            <input 
              type="file" 
              id="file-upload" 
              ref="fileInput" 
              @change="onFileChange"
              accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
              required 
              style="display: none;"
            />
          </div>

          <button type="submit" :disabled="loading || !file" class="btn-upload">
            {{ loading ? 'Mengupload...' : 'Upload Dokumen' }}
          </button>
        </form>

        <div v-if="successMessage" class="message success-message">
          ✅ {{ successMessage }}
          <router-link :to="{ name: 'participant-status' }" class="link-status">Lihat Status</router-link>
        </div>
        
        <div v-if="errorMessage" class="message error-message">
          ❌ {{ errorMessage }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { useAuthStore } from '../stores/auth'
import Navbar from '../components/Navbar.vue'

export default {
  name: 'ParticipantImport',
  components: { Navbar },
  data() {
    return {
      file: null,
      loading: false,
      successMessage: '',
      errorMessage: '',
    }
  },
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
  methods: {
    onFileChange(event) {
      this.file = event.target.files[0]
      this.successMessage = ''
      this.errorMessage = ''
    },
    async handleUpload() {
      if (!this.file) {
        this.errorMessage = 'Pilih file terlebih dahulu.'
        return
      }

      this.loading = true
      this.successMessage = ''
      this.errorMessage = ''

      const formData = new FormData()
      formData.append('file', this.file)

      try {
        const response = await axios.post('/participant/import', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'Authorization': `Bearer ${this.authStore.token}`
          }
        })
        
        this.successMessage = response.data.message
        this.file = null // Clear file input
        this.$refs.fileInput.value = null; // Reset native file input

      } catch (error) {
        console.error('Upload error:', error)
        this.errorMessage = error.response?.data?.message || 'Gagal mengupload dokumen. Cek ukuran atau format file.'
        if (error.response?.data?.errors) {
            this.errorMessage += ': ' + Object.values(error.response.data.errors).flat().join(', ');
        }
      } finally {
        this.loading = false
      }
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
.participant-page {
  min-height: 100vh;
  background: #f1f5f9;
}

.page-content {
  max-width: 800px;
  margin: 0 auto;
  padding: 3rem 2rem;
}

.page-header {
  margin-bottom: 2rem;
  text-align: center;
}

.page-header h1 {
  color: #1e293b;
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.page-header p {
  color: #64748b;
}

.upload-card {
  background: white;
  padding: 3rem;
  border-radius: 1rem;
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.upload-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.file-input-group {
    border: 2px dashed #cbd5e1;
    border-radius: 0.75rem;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}

.file-input-group:hover {
    border-color: #1B2376;
    background-color: #f8fafc;
}

.file-label {
    display: block;
    cursor: pointer;
    font-size: 1rem;
    color: #64748b;
    font-weight: 500;
}

.btn-upload {
  background: linear-gradient(to right, #1B2376, #2d3da6);
  color: white;
  border: none;
  padding: 0.9rem;
  font-size: 1rem;
  font-weight: 600;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: 0.3s ease;
}

.btn-upload:hover:not(:disabled) {
  transform: scale(1.01);
  box-shadow: 0 4px 10px rgba(27, 35, 118, 0.3);
}

.btn-upload:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.message {
    padding: 1rem;
    border-radius: 0.5rem;
    font-weight: 600;
}

.success-message {
    background-color: #d1fae5;
    border: 1px solid #065f46;
    color: #065f46;
}

.link-status {
    color: #1B2376;
    margin-left: 1rem;
    text-decoration: underline;
}

.error-message {
    background-color: #fee2e2;
    border: 1px solid #ef4444;
    color: #dc2626;
}
</style>