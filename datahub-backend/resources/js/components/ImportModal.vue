<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Import Data Mahasiswa</h2>
        <button @click="$emit('close')" class="btn-close">✕</button>
      </div>

      <div class="modal-body">
        <div v-if="!file" class="upload-area" @dragover.prevent @drop.prevent="handleDrop">
          <input 
            ref="fileInput" 
            type="file" 
            accept=".csv,.xlsx,.xls" 
            @change="handleFileSelect"
            style="display: none"
          />
          <div class="upload-icon">📁</div>
          <p>Drag & drop file CSV/Excel di sini</p>
          <p class="upload-hint">atau</p>
          <button @click="$refs.fileInput.click()" class="btn-browse">
            Browse File
          </button>
          <p class="file-info">Format: CSV, XLSX, XLS (Max: 10MB)</p>
        </div>

        <div v-else class="file-selected">
          <div class="file-icon">📄</div>
          <div class="file-details">
            <p class="file-name">{{ file.name }}</p>
            <p class="file-size">{{ formatFileSize(file.size) }}</p>
          </div>
          <button @click="removeFile" class="btn-remove">✕</button>
        </div>

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <div v-if="successMessage" class="success-message">
          {{ successMessage }}
        </div>
      </div>

      <div class="modal-footer">
        <button @click="$emit('close')" class="btn-cancel">Batal</button>
        <button 
          @click="uploadFile" 
          class="btn-upload" 
          :disabled="!file || uploading"
        >
          {{ uploading ? 'Uploading...' : 'Upload' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { useAuthStore } from '../stores/auth'

export default {
  name: 'ImportModal',
  emits: ['close'],
  data() {
    return {
      file: null,
      uploading: false,
      errorMessage: '',
      successMessage: '',
    }
  },
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
  methods: {
    handleFileSelect(event) {
      const selectedFile = event.target.files[0]
      this.validateFile(selectedFile)
    },
    handleDrop(event) {
      const droppedFile = event.dataTransfer.files[0]
      this.validateFile(droppedFile)
    },
    validateFile(file) {
      this.errorMessage = ''
      
      if (!file) return

      // Check file type
      const allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
      if (!allowedTypes.includes(file.type) && !file.name.match(/\.(csv|xlsx|xls)$/)) {
        this.errorMessage = 'Format file tidak valid. Gunakan CSV atau Excel.'
        return
      }

      // Check file size (10MB)
      if (file.size > 10 * 1024 * 1024) {
        this.errorMessage = 'Ukuran file terlalu besar. Maksimal 10MB.'
        return
      }

      this.file = file
    },
    removeFile() {
      this.file = null
      this.errorMessage = ''
      this.successMessage = ''
    },
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes'
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
    },
    async uploadFile() {
      if (!this.file) return

      this.uploading = true
      this.errorMessage = ''
      this.successMessage = ''

      try {
        const formData = new FormData()
        formData.append('file', this.file)

        const response = await axios.post('/import-data', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'Authorization': `Bearer ${this.authStore.token}`
          }
        })

        this.successMessage = response.data.message + ` (${response.data.rows_imported} rows)`
        this.file = null
        
        setTimeout(() => {
          this.$emit('close')
        }, 2000)

      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Upload gagal. Silakan coba lagi.'
      } finally {
        this.uploading = false
      }
    }
  }
}
</script>

<style scoped>
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
  max-width: 500px;
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
  font-size: 1.3rem;
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

.upload-area {
  border: 2px dashed #cbd5e1;
  border-radius: 0.75rem;
  padding: 3rem 2rem;
  text-align: center;
  transition: all 0.3s;
}

.upload-area:hover {
  border-color: #1B2376;
  background: #f8fafc;
}

.upload-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.upload-hint {
  color: #94a3b8;
  margin: 0.5rem 0;
}

.btn-browse {
  background: #1B2376;
  color: white;
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 0.5rem;
  cursor: pointer;
  font-weight: 600;
  margin: 1rem 0;
}

.btn-browse:hover {
  background: #2d3da6;
}

.file-info {
  font-size: 0.85rem;
  color: #94a3b8;
  margin-top: 1rem;
}

.file-selected {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f1f5f9;
  border-radius: 0.5rem;
}

.file-icon {
  font-size: 2rem;
}

.file-details {
  flex: 1;
}

.file-name {
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.file-size {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0.25rem 0 0 0;
}

.btn-remove {
  background: #ef4444;
  color: white;
  border: none;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 1.2rem;
}

.error-message {
  background: #fee2e2;
  border: 1px solid #ef4444;
  color: #dc2626;
  padding: 0.75rem;
  border-radius: 0.5rem;
  margin-top: 1rem;
}

.success-message {
  background: #d1fae5;
  border: 1px solid #10b981;
  color: #059669;
  padding: 0.75rem;
  border-radius: 0.5rem;
  margin-top: 1rem;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.btn-cancel {
  background: #f1f5f9;
  color: #64748b;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  cursor: pointer;
  font-weight: 600;
}

.btn-upload {
  background: #ff914d;
  color: #1B2376;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  cursor: pointer;
  font-weight: 600;
}

.btn-upload:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-upload:hover:not(:disabled) {
  background: #f8811a;
}
</style>
