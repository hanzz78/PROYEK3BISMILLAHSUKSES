<template>
  <div class="loading-full-page">
    <p>Memuat Dashboard...</p>
    
  </div>
</template>

<script>
import { useAuthStore } from '../stores/auth'

export default 
{
  name: 'DashboardWrapper',
  setup() 
  {
    const authStore = useAuthStore()
    return { authStore }
  },
  watch: 
  {
    'authStore.user': 
    {
      immediate: true,
      handler(user) 
      {
        if (user) 
        {
          this.redirectToRoleDashboard(user.role)
        }
      }
    }
  },
  methods: 
  {
    redirectToRoleDashboard(role) 
    {
      if (role === 'admin') 
      {
        this.$router.replace({ name: 'admin-dashboard' })
      } else if (role === 'participant') 
      {
        this.$router.replace({ name: 'participant-dashboard' })
      } else 
      {
        // Handle role lain atau error
        console.error('Role tidak dikenal:', role)
        this.authStore.logout()
        this.$router.replace({ name: 'login' })
      }
    }
  }
}
</script>

<style scoped>
.loading-full-page {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  font-size: 1.2rem;
  color: #1B2376;
}
</style>