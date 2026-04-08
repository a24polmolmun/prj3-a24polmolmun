<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useSocket } from '../../composables/useSocket'

const { socket } = useSocket()
const connectedUsers = ref(0)
const stats = ref({
  reserves_actives: 0,
  ocupacio: 0,
  recaptacio: [] as any[]
})

// Fetch initial stats
const { data } = await useFetch('http://localhost:8000/api/admin/stats')
if (data.value) {
    stats.value = (data.value as any).data
}

onMounted(() => {
  if (socket) {
    socket.emit('join-admin')
    socket.on('user-count-update', (count: number) => {
      connectedUsers.value = count
    })
  }
})

const totalRevenue = computed(() => {
    return stats.value.recaptacio?.reduce((acc: number, item: any) => acc + Number(item.total), 0) || 0
})
</script>

<template>
  <div class="flex flex-1 text-slate-900 font-sans">
    <AdminSidebar />
    
    <main class="flex-1 w-full p-12 overflow-y-auto">
      <header class="mb-12 bg-white shadow-sm p-8 rounded-2xl flex flex-col items-center justify-center text-center">
        <h2 class="text-5xl font-black tracking-tighter uppercase italic text-accent mb-2">Dashboard</h2>
        <p class="text-slate-500 font-medium italic">Visió general de l'estat actual del cinema</p>
      </header>

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
        <!-- Live Users -->
        <div class="bg-white shadow-sm p-8 rounded-[2.5rem] border border-slate-200 relative overflow-hidden group hover:border-accent/40 transition-all">
          <div class="absolute top-0 right-0 w-32 h-32 bg-accent/5 blur-3xl rounded-full"></div>
          <p class="text-[10px] font-black uppercase tracking-[0.4em] text-accent mb-6 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-accent animate-ping"></span>
            En Directe
          </p>
          <p class="text-6xl font-black text-slate-900 mb-2 tabular-nums">{{ connectedUsers }}</p>
          <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Usuaris connectats</p>
        </div>

        <!-- Sales -->
        <div class="bg-white shadow-sm p-8 rounded-[2.5rem] border border-slate-200 relative overflow-hidden group hover:border-accent/40 transition-all">
          <p class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-400 mb-6">Recaptació Total</p>
          <p class="text-6xl font-black text-slate-900 mb-2 tabular-nums">
            {{ totalRevenue }}<span class="text-2xl text-accent font-black ml-1">€</span>
          </p>
          <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Compres confirmades</p>
        </div>

        <!-- Reservations -->
        <div class="bg-white shadow-sm p-8 rounded-[2.5rem] border border-slate-200 relative overflow-hidden group hover:border-accent/40 transition-all">
          <p class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-400 mb-6">Reserves</p>
          <p class="text-6xl font-black text-slate-900 mb-2 tabular-nums">{{ stats.reserves_actives }}</p>
          <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Entrades venudes</p>
        </div>

        <!-- Occupancy -->
        <div class="bg-white shadow-sm p-8 rounded-[2.5rem] border border-slate-200 relative overflow-hidden group hover:border-accent/40 transition-all">
          <p class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-400 mb-6">Ocupació Mitjana</p>
          <p class="text-6xl font-black text-slate-900 mb-2 tabular-nums">{{ stats.ocupacio }}<span class="text-2xl text-accent font-black ml-1">%</span></p>
          <div class="w-full bg-slate-100 h-1.5 rounded-full mt-4 overflow-hidden">
             <div class="bg-accent h-full transition-all duration-1000" :style="{ width: stats.ocupacio + '%' }"></div>
          </div>
        </div>
      </div>

      <!-- Live Overview -->
      <section class="bg-white shadow-sm rounded-[3.5rem] p-12 border border-slate-200 relative overflow-hidden">
         <div class="flex items-center justify-between mb-10">
            <div>
              <h3 class="text-2xl font-black text-slate-900 uppercase italic tracking-tighter">Activitat al mapa de seients</h3>
              <p class="text-slate-500 text-sm mt-1">Vista en temps real de les butaques bloquejades pels usuaris</p>
            </div>
            <div class="px-6 py-2 bg-accent/10 border border-accent/20 rounded-full text-accent font-bold text-[10px] uppercase tracking-widest">
                MODO MONITORITZACIÓ
            </div>
         </div>
         
         <div class="aspect-video bg-black/40 rounded-3xl border border-slate-200 flex items-center justify-center text-center">
            <div>
               <p class="text-slate-500 font-medium italic uppercase tracking-widest text-xs">Visualització en temps real</p>
            </div>
         </div>
      </section>
    </main>
  </div>
</template>
