import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// =====================================================================
// PERBAIKAN UTAMA: Base URL
// =====================================================================
// Ini akan membuat semua request axios otomatis diawali dengan "/api"
// Contoh: axios.post('login') -> menjadi -> axios.post('/api/login')
window.axios.defaults.baseURL = '/api';

// =====================================================================
// INTERCEPTOR: Auto Logout jika Token Hangus
// =====================================================================
window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        // Jika server menolak token (Error 401 Unauthorized)
        if (error.response && error.response.status === 401) {
            console.warn('Sesi habis atau token tidak valid. Melakukan logout otomatis...');
            
            // 1. Hapus token dari penyimpanan lokal
            localStorage.removeItem('token');
            
            // 2. Redirect paksa ke halaman login
            // Cek dulu apakah kita sudah di halaman login agar tidak refresh berulang
            if (window.location.pathname !== '/login') {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);