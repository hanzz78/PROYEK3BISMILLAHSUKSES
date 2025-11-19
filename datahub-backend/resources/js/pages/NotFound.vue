<template>
  <div class="not-found-page">
    <div class="not-found-content">
      <div class="error-code">404</div>
      <h1>Halaman Tidak Ditemukan</h1>
      <p>Maaf, halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
      <router-link to="/" class="btn-home">Kembali ke Dashboard</router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotFound'
}
</script>

<style scoped>
.not-found-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #ff914d 20%, #1B2376 60%, #1B2376 130%);
}

.not-found-content {
  text-align: center;
  color: white;
}

.error-code {
  font-size: 8rem;
  font-weight: 900;
  line-height: 1;
  opacity: 0.8;
}

h1 {
  font-size: 2rem;
  margin: 1rem 0;
}

p {
  font-size: 1.1rem;
  margin: 1rem 0 2rem 0;
  opacity: 0.9;
}

.btn-home {
  display: inline-block;
  padding: 1rem 2rem;
  background: white;
  color: #1B2376;
  text-decoration: none;
  border-radius: 0.75rem;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-home:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}
</style>
