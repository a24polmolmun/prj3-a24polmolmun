<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const navItems = [
  { name: 'Taulell de Control', path: '/admin/dashboard' },
  { name: 'Pel·lícules', path: '/admin/esdeveniments' },
  { name: 'Informes', path: '/admin/informes' }
]

onMounted(() => {
    // Comprovar si l'usuari està autenticat (només al client)
    if (import.meta.client) {
        const auth = localStorage.getItem('admin_auth')
        if (!auth && !window.location.pathname.includes('/admin/login')) {
            router.push('/admin/login')
        }
    }
})

const handleLogout = () => {
    localStorage.removeItem('admin_auth')
    router.push('/admin/login')
}
</script>

<template>
  <aside class="w-72 bg-white border-r border-slate-200 flex flex-col h-screen sticky top-0">
    <div class="p-8 border-b border-slate-200">
      <h1 class="text-2xl font-black text-accent tracking-tighter uppercase italic">
        Admin <span class="text-slate-900">Cinema Pol</span>
      </h1>
      <p class="text-[10px] font-bold text-slate-500 tracking-[0.3em] uppercase mt-2">Gestió Premium</p>
    </div>

    <nav class="flex-1 p-6 space-y-4">
      <NuxtLink 
        v-for="item in navItems" 
        :key="item.path"
        :to="item.path"
        class="flex items-center px-6 py-4 rounded-2xl font-bold transition-all group"
        :class="$route.path.startsWith(item.path) ? 'bg-slate-100 text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100'"
      >
        <span class="uppercase tracking-widest text-xs">{{ item.name }}</span>
      </NuxtLink>
    </nav>

    <div class="p-8 border-t border-slate-200 space-y-4">
      <div class="bg-slate-100 rounded-2xl p-4 border border-slate-200">
        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Usuari actiu</p>
        <p class="text-sm font-bold text-slate-900">Administrador</p>
      </div>
      
      <button 
        @click="handleLogout"
        class="w-full py-4 text-red-500 font-black uppercase tracking-widest text-[10px] hover:bg-red-50 rounded-2xl transition-all"
      >
        Tancar Sessió
      </button>
    </div>
  </aside>
</template>
