import axios from 'axios';
window.axios = axios;
// Mengatur agar semua request data mengarah ke route API (bukan route Web)
window.axios.defaults.baseURL = '/api';

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';