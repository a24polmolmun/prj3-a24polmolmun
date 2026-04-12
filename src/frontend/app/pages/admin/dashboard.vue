<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useSocket } from '../../composables/useSocket'
import { useSeatsStore } from '../../stores/seatsStore'

const { socket } = useSocket()
const seatsStore = useSeatsStore()

const connectedUsers = ref(0)
const stats = ref({
  reserves_actives: 0,
  ocupacio: 0,
  recaptacio: [] as any[]
})

// Estats per als selectors
const esdeveniments = ref<any[]>([])
const selectedEventId = ref<number | null>(null)
const selectedSessionId = ref<number | null>(null)
const isLoadingSeats = ref(false)

// Recuperar estadístiques inicials i llista d'esdeveniments (sense SSR per evitar problemes en recarregar en Docker)
const config = useRuntimeConfig()
const { data: statsData } = await useFetch(`${config.public.apiBase}/admin/stats`, { server: false })
watch(statsData, (newVal) => {
    if (newVal) {
        const d = (newVal as any).data
        stats.value = {
            reserves_actives: d.reserves_actives || 0,
            ocupacio: d.ocupacio || 0,
            recaptacio: d.recaptacio || []
        }
    }
}, { immediate: true })

const { data: eventsData } = await useFetch(`${config.public.apiBase}/admin/esdeveniments`, { server: false })
watch(eventsData, (newVal) => {
    if (newVal) {
        esdeveniments.value = (newVal as any).data || []
    }
}, { immediate: true })

// Sessions de l'esdeveniment seleccionat
const sessionsDisponibles = computed(() => {
    if (!selectedEventId.value) return []
    const event = esdeveniments.value.find(e => e.id === selectedEventId.value)
    return event ? event.sessions : []
})

// Reiniciar sessió si canvia l'esdeveniment
watch(selectedEventId, () => {
    selectedSessionId.value = null
})

// Vigilant el canvi de sessió per carregar el mapa
watch(selectedSessionId, async (newId) => {
    if (newId) {
        const session = sessionsDisponibles.value.find((s: any) => s.id === newId)
        if (!session) return

        isLoadingSeats.value = true
        try {
            // Utilitzem $fetch per a peticions imperatives dins de watchers
            const response = await $fetch(`${config.public.apiBase}/esdeveniments/${selectedEventId.value}?hora=${session.hora}`) as any
            
            if (response.success && response.data) {
                seatsStore.setSeats(response.data.seients || [])
                // Inicialitzem el socket amb l'ID de l'esdeveniment
                seatsStore.initSocket(selectedEventId.value!)
            }
        } catch (e) {
            console.error('Error carregant seients:', e)
        } finally {
            isLoadingSeats.value = false
        }
    } else {
        seatsStore.setSeats([])
    }
})

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
      <header class="flex flex-col md:flex-row justify-between items-center mb-12 bg-white shadow-sm p-8 rounded-2xl text-center md:text-left gap-6">
        <div class="flex flex-col items-center md:items-start">
          <h2 class="text-5xl font-black tracking-tighter uppercase italic text-accent mb-2">Taulell de Control</h2>
          <p class="text-slate-500 font-medium italic">Monitorització de l'activitat de vendes en temps real</p>
        </div>
      </header>

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
        <!-- Live Users -->
        <div class="bg-gradient-to-br from-white to-slate-50 shadow-md p-8 rounded-[2.5rem] border border-slate-200 relative overflow-hidden group hover:shadow-xl hover:border-accent/40 transition-all">
          <div class="absolute top-0 right-0 w-32 h-32 bg-accent/10 blur-3xl rounded-full translate-x-12 -translate-y-12"></div>
          <p class="text-[10px] font-black uppercase tracking-[0.4em] text-accent mb-6 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-accent animate-ping"></span>
            En Directe / Live
          </p>
          <div class="flex items-baseline gap-2">
            <p class="text-6xl font-black text-slate-900 mb-2 tabular-nums">{{ connectedUsers }}</p>
            <p class="text-slate-400 font-bold text-xs uppercase italic">Actius</p>
          </div>
          <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest mt-2">Usuaris navegant ara</p>
        </div>

        <!-- Sales -->
        <div class="bg-gradient-to-br from-white to-slate-50 shadow-md p-8 rounded-[2.5rem] border border-slate-200 relative overflow-hidden group hover:shadow-xl hover:border-accent/40 transition-all">
          <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-slate-200/20 blur-2xl rounded-full"></div>
          <p class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-400 mb-6 italic">Ingressos Totals</p>
          <p class="text-6xl font-black text-slate-900 mb-2 tabular-nums">
            {{ totalRevenue }}<span class="text-2xl text-accent font-black ml-1">€</span>
          </p>
          <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest mt-2">Vendes confirmades</p>
        </div>

        <!-- Reservations -->
        <div class="bg-gradient-to-br from-white to-slate-50 shadow-md p-8 rounded-[2.5rem] border border-slate-200 relative overflow-hidden group hover:shadow-xl hover:border-accent/40 transition-all">
          <p class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-400 mb-6 italic">Entrades</p>
          <p class="text-6xl font-black text-slate-900 mb-2 tabular-nums">{{ stats.reserves_actives }}</p>
          <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest mt-2">Reserves totals</p>
        </div>

        <!-- Occupancy -->
        <div class="bg-gradient-to-br from-white to-slate-50 shadow-md p-8 rounded-[2.5rem] border border-slate-200 relative overflow-hidden group hover:shadow-xl hover:border-accent/40 transition-all flex flex-col justify-between">
          <div>
            <p class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-400 mb-6 italic">Ocupació Mitjana</p>
            <p class="text-6xl font-black text-slate-900 mb-2 tabular-nums">{{ stats.ocupacio }}<span class="text-2xl text-accent font-black ml-1">%</span></p>
          </div>
          <div class="w-full bg-slate-100 h-2 rounded-full mt-4 overflow-hidden border border-slate-200">
             <div class="bg-accent h-full transition-all duration-1000 shadow-[0_0_15px_rgba(255,222,0,0.5)]" :style="{ width: stats.ocupacio + '%' }"></div>
          </div>
        </div>
      </div>

      <!-- Live Overview -->
      <section class="bg-white shadow-sm rounded-[3.5rem] p-12 border border-slate-200 relative overflow-hidden">
         <div class="flex flex-col md:flex-row items-center justify-between mb-10 gap-6">
            <div>
              <h3 class="text-2xl font-black text-slate-900 uppercase italic tracking-tighter text-center md:text-left">Activitat al mapa de seients</h3>
              <p class="text-slate-500 text-sm mt-1 text-center md:text-left">Vista en temps real de les butaques bloquejades pels usuaris</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4 justify-center">
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Pel·lícula</label>
                    <select v-model="selectedEventId" class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm font-bold outline-none focus:border-accent">
                        <option :value="null" disabled>Selecciona una pel·lícula</option>
                        <option v-for="e in esdeveniments" :key="e.id" :value="e.id">{{ e.nom }}</option>
                    </select>
                </div>
                
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2">Sessió</label>
                    <select v-model="selectedSessionId" :disabled="!selectedEventId" class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm font-bold outline-none focus:border-accent disabled:opacity-50">
                        <option :value="null" disabled>Selecciona sessió</option>
                        <option v-for="s in sessionsDisponibles" :key="s.id" :value="s.id">{{ s.dia }} - {{ s.hora }}</option>
                    </select>
                </div>
                
                <div class="px-6 py-3 bg-accent/10 border border-accent/20 rounded-2xl text-accent font-bold text-[10px] uppercase tracking-widest hidden lg:block">
                    MODO MONITORITZACIÓ
                </div>
            </div>
         </div>
         
         <div v-if="selectedSessionId" class="bg-slate-50 rounded-[2.5rem] p-8 md:p-12 border border-slate-100 min-h-[400px]">
             <div v-if="isLoadingSeats" class="flex flex-col items-center justify-center p-20 gap-4">
                 <div class="w-12 h-12 border-4 border-accent border-t-transparent rounded-full animate-spin"></div>
                 <p class="text-slate-400 font-bold uppercase tracking-widest text-xs italic">Carregant mapa...</p>
             </div>
             <div v-else class="flex justify-center overflow-x-auto py-6">
                <!-- Mapa de Seients Mode Readonly -->
                <SeatMap :readonly="true" />
             </div>
         </div>
         
         <div v-else class="aspect-video bg-slate-50/50 rounded-3xl border border-dashed border-slate-200 flex flex-col items-center justify-center text-center p-12">
            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="text-slate-400 font-bold uppercase tracking-widest text-xs italic">Selecciona una pel·lícula i sessió per monitoritzar</p>
         </div>
      </section>
    </main>
  </div>
</template>
