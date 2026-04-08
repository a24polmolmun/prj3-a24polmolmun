<script setup lang="ts">
const navItems = [
  { name: 'Dashboard', path: '/admin/dashboard' },
  { name: 'Pel·lícules', path: '/admin/esdeveniments' },
  { name: 'Informes', path: '/admin/informes' }
]
</script>

<template>
  <aside class="w-72 bg-white border-r border-slate-200 flex flex-col h-screen">
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

    <div class="p-8 border-t border-slate-200">
      <div class="bg-slate-100 rounded-2xl p-4 border border-slate-200">
        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Usuari actiu</p>
        <p class="text-sm font-bold text-slate-900">Administrador</p>
      </div>
    </div>
  </aside>
</template>
