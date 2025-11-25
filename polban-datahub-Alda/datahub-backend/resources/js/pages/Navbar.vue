<template>
  <nav class="navbar">
    <div class="nav-brand">
      <h2>Polban <span>DataVerse</span></h2>
    </div>
    <div class="nav-user">
      <router-link 
        :to="{ name: user.role === 'admin' ? 'admin-dashboard' : 'participant-dashboard' }" 
        class="nav-link"
      >
        ← Dashboard
      </router-link>
      <span class="user-name">{{ user?.name }}</span>
      <span class="user-role" :class="roleClass">{{ roleLabel }}</span>
      <button @click="$emit('logout')" class="btn-logout">Logout</button>
    </div>
  </nav>
</template>

<script>
export default {
  name: 'Navbar',
  props: {
    user: {
      type: Object,
      required: true,
    },
    roleLabel: {
      type: String,
      required: true,
    }
  },
  computed: {
    roleClass() {
      return this.user?.role === 'admin' ? 'role-admin' : 'role-participant'
    },
  }
}
</script>

<style scoped>
/* Style dari Dashboard.vue lama, dipindahkan di sini */
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
  font-weight: 700;
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
  padding: 0.5rem;
  border-radius: 0.5rem;
  transition: background-color 0.3s;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.1);
}

.user-name {
  font-weight: 600;
}

.user-role {
  padding: 0.3rem 0.8rem;
  border-radius: 0.5rem;
  font-size: 0.85rem;
  font-weight: 600;
}

.role-admin {
  background: #ff914d;
  color: #1B2376;
}

.role-participant {
  background: #10b981;
  color: white;
}

.btn-logout {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.3s;
  font-weight: 600;
}

.btn-logout:hover {
  background: rgba(255, 255, 255, 0.3);
}
</style>