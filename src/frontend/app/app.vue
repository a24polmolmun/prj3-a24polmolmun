<script setup lang="ts">
import { useRoute } from 'vue-router'
import { computed } from 'vue'

const route = useRoute()
const isAdminRoute = computed(() => route.path.startsWith('/admin'))
const isSocketConnected = useState('isSocketConnected', () => true)
</script>

<template>
  <div class="min-h-screen font-sans">
    <!-- Banner de Reconnexió -->
    <transition name="slide-down">
      <div v-if="isSocketConnected === false" class="bg-red-600 text-white text-[10px] font-black uppercase tracking-[0.2em] py-2 text-center sticky top-0 z-[200] shadow-lg">
        ⚠️ Re-connectant al servidor en temps real...
      </div>
    </transition>

    <!-- Header públic -->
    <header :class="{ 'top-0': isSocketConnected !== false, 'top-[28px]': isSocketConnected === false }" class="bg-gray-900 relative z-[100] border-b border-white/5 py-4 transition-all duration-300">
      <div class="container mx-auto px-6 flex items-center justify-between">
        <NuxtLink to="/" class="text-xl font-black text-white uppercase italic tracking-tighter">Cinema <span class="text-accent">Pol</span></NuxtLink>
        <nav>
          <ul class="flex gap-8">
            <li><NuxtLink to="/" class="text-xs font-bold uppercase tracking-widest text-white/60 hover:text-accent transition-colors">Inici</NuxtLink></li>
            <li><NuxtLink to="/meves-entrades" class="text-xs font-bold uppercase tracking-widest text-white/60 hover:text-accent transition-colors">Les meves entrades</NuxtLink></li>
            <li><NuxtLink to="/admin/login" class="text-xs font-bold uppercase tracking-widest text-white/60 hover:text-accent transition-colors border-l border-white/10 pl-8">Accés Administració</NuxtLink></li>
          </ul>
        </nav>
      </div>
    </header>

    <main :class="{ 'bg-slate-950': !isAdminRoute, 'bg-gray-50': isAdminRoute }" class="flex-1 flex flex-col">
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
  @apply bg-slate-950;
}
.text-accent { color: #ffde00; }
.bg-accent { background-color: #ffde00; }

.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}
</style>
