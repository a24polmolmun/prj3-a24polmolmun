<script setup lang="ts">
import { useRoute } from 'vue-router'
import { computed } from 'vue'

const route = useRoute()
const isAdminRoute = computed(() => route.path.startsWith('/admin'))
</script>

<template>
  <div class="min-h-screen font-sans">
    <!-- Header públic -->
    <header v-if="!isAdminRoute" class="bg-gray-900 relative z-[100] border-b border-white/5 py-4">
      <div class="container mx-auto px-6 flex items-center justify-between">
        <NuxtLink to="/" class="text-xl font-black text-white uppercase italic tracking-tighter">Cinema <span class="text-accent">Pol</span></NuxtLink>
        <nav>
          <ul class="flex gap-8">
            <li><NuxtLink to="/" class="text-xs font-bold uppercase tracking-widest text-white/60 hover:text-accent transition-colors">Inici</NuxtLink></li>
            <li><NuxtLink to="/meves-entrades" class="text-xs font-bold uppercase tracking-widest text-white/60 hover:text-accent transition-colors">Les meves entrades</NuxtLink></li>
            <li><NuxtLink to="#" class="text-xs font-bold uppercase tracking-widest text-white/60 hover:text-accent transition-colors">Contacte</NuxtLink></li>
          </ul>
        </nav>
      </div>
    </header>

    <main :class="{ 'bg-slate-950': !isAdminRoute || isAdminRoute }">
      <NuxtPage />
    </main>

    <!-- Footer públic -->
    <footer v-if="!isAdminRoute" class="bg-gray-900 py-12 border-t border-white/5">
      <div class="container mx-auto px-6 text-center">
        <p class="text-white/20 text-[10px] font-black uppercase tracking-[0.5em]">&copy; 2026 Cinema Cat - Tots els drets reservats</p>
      </div>
    </footer>
  </div>
</template>

<style>
body {
  margin: 0;
  padding: 0;
  background-color: #020617; /* Slate 950 base */
}
.text-accent { color: #ffde00; }
.bg-accent { background-color: #ffde00; }
</style>
