<script setup lang="ts">
const navItems = [
  { name: 'Dashboard', path: '/admin/dashboard' },
  { name: 'Pel·lícules', path: '/admin/esdeveniments' },
  { name: 'Informes', path: '/admin/informes' },
  { name: 'Tornar a la Web', path: '/' },
]
</script>

<template>
  <aside class="w-72 bg-slate-900 border-r border-white/10 flex flex-col h-screen sticky top-0">
    <div class="p-8 border-b border-white/10">
      <h1 class="text-2xl font-black text-accent tracking-tighter uppercase italic">
        Admin <span class="text-white">Cinema Pol</span>
      </h1>
      <p class="text-[10px] font-bold text-white/30 tracking-[0.3em] uppercase mt-2">Gestió Premium</p>
    </div>

    <nav class="flex-1 p-6 space-y-4">
      <NuxtLink 
        v-for="item in navItems" 
        :key="item.path"
        :to="item.path"
        class="flex items-center px-6 py-4 rounded-2xl font-bold transition-all group"
        :class="$route.path.startsWith(item.path) ? 'bg-accent text-black shadow-[0_10px_20px_-5px_rgba(255,222,0,0.3)]' : 'text-white/40 hover:text-white hover:bg-white/5'"
      >
        <span class="uppercase tracking-widest text-xs">{{ item.name }}</span>
      </NuxtLink>
    </nav>

    <div class="p-8 border-t border-white/10">
      <div class="bg-white/5 rounded-2xl p-4 border border-white/5">
        <p class="text-[8px] font-black text-white/20 uppercase tracking-widest mb-1">Usuari actiu</p>
        <p class="text-sm font-bold text-white">Administrador</p>
      </div>
    </div>
  </aside>
</template>
