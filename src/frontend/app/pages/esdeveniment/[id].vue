<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router'
import { onMounted, computed, ref, onUnmounted, watch } from 'vue'
import { useSeatsStore } from '../../stores/seatsStore'
import { useSocket } from '../../composables/useSocket'
import SeatMap from '../../components/SeatMap.vue'

const route = useRoute()
const router = useRouter()
const seatsStore = useSeatsStore()
const { releaseAllSeats } = useSocket()

const movieId = Number(route.params.id)
const hora = route.query.hora as string

// Fetch real movie details and seats from Laravel API
const { data, pending, error } = useFetch(`http://localhost:8000/api/esdeveniments/${movieId}?hora=${hora}`)

const movie = computed(() => (data.value as any)?.data)

// Sync seats with store when data is loaded
watch(data, (newVal: any) => {
  if (newVal?.data?.seients) {
    seatsStore.setSeats(newVal.data.seients)
  }
}, { immediate: true })

// State for steps
const currentStep = ref(1)

// State for tickets (Pas 1)
const ticketCounts = ref({
  jove: 0,
  adult: 0,
  reduida: 0
})

// State for User Data (Pas 3)
const userName = ref('')
const userEmail = ref('')
const isSubmitting = ref(false)
const purchaseLocator = ref('')

const totalTickets = computed(() => 
  ticketCounts.value.jove + ticketCounts.value.adult + ticketCounts.value.reduida
)

const totalPrice = computed(() => {
  if (!movie.value) return 0
  
  // Trobar preus dinàmicament des de l'API o utilitzar fallbacks
  const preus = {
    jove: movie.value.tipus_entrades?.find((t: any) => t.nom === 'Jove')?.preu || 5,
    adult: movie.value.tipus_entrades?.find((t: any) => t.nom === 'Adult')?.preu || 12,
    reduida: movie.value.tipus_entrades?.find((t: any) => t.nom === 'Reduïda')?.preu || 8
  }
  
  return (ticketCounts.value.jove * preus.jove) + 
         (ticketCounts.value.adult * preus.adult) + 
         (ticketCounts.value.reduida * preus.reduida)
})

// Timer state (Pas 2)
const timeLeft = ref(300) // 5 minutes in seconds
let timerInterval: any = null

const formattedTime = computed(() => {
  const minutes = Math.floor(timeLeft.value / 60)
  const seconds = timeLeft.value % 60
  return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
})

const startTimer = () => {
  if (timerInterval) clearInterval(timerInterval)
  timeLeft.value = 300
  timerInterval = setInterval(() => {
    if (timeLeft.value > 0) {
      timeLeft.value--
    } else {
      handleTimeout()
    }
  }, 1000)
}

const handleTimeout = () => {
  if (timerInterval) clearInterval(timerInterval)
  alert('El temps de reserva ha expirat. Torna-ho a intentar.')
  
  // Alliberar tots els seients al servidor
  releaseAllSeats(movieId)
  
  seatsStore.resetSelection()
  currentStep.value = 1
  router.push('/')
}

const nextStep = () => {
  if (totalTickets.value > 0) {
    seatsStore.setMaxSelection(totalTickets.value)
    currentStep.value = 2
    if (!timerInterval) startTimer()
  }
}

const goToPayment = () => {
    if (seatsStore.selectedCount === totalTickets.value) {
        currentStep.value = 3
    }
}

const confirmPurchase = async () => {
  if (!userName.value || !userEmail.value) {
    alert('Si us plau, omple totes les dades.')
    return
  }

  isSubmitting.value = true
  try {
    const response = await $fetch('http://localhost:8000/api/compra', {
      method: 'POST',
      body: {
        esdeveniment_id: movieId,
        seients_ids: selectedSeats.value.map(s => s.id),
        nom: userName.value,
        email: userEmail.value
      }
    }) as any

    if (response.success) {
      if (timerInterval) clearInterval(timerInterval)
      purchaseLocator.value = response.localitzador
      seatsStore.resetSelection()
      currentStep.value = 4
    }
  } catch (error: any) {
    console.error('Error en la compra:', error)
    const message = error.data?.message || 'S\'ha produït un error en processar la compra. Torna-ho a intentar.'
    alert(message)
  } finally {
    isSubmitting.value = false
  }
}

const selectedSeats = computed(() => {
  return seatsStore.seats.filter(s => s.estat === 'seleccionat')
})

onMounted(() => {
  seatsStore.resetSelection()
  seatsStore.initSocket(movieId) // Inicialitzar la sincronització en temps real
})

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
  // Alliberar tots els seients al servidor en sortir de la pàgina
  releaseAllSeats(movieId)
})

const updateCount = (type: keyof typeof ticketCounts.value, delta: number) => {
  const newVal = ticketCounts.value[type] + delta
  if (newVal >= 0) {
    ticketCounts.value[type] = newVal
  }
}
</script>

<template>
  <div class="event-page-bg min-h-screen py-8 bg-slate-950 text-white">
    <div class="container mx-auto px-4 pb-32">
      <!-- Loading State -->
      <div v-if="pending" class="flex flex-col items-center justify-center py-40 space-y-6">
        <div class="w-16 h-16 border-4 border-accent/20 border-t-accent rounded-full animate-spin"></div>
        <p class="text-accent font-black uppercase tracking-[0.3em] animate-pulse">Carregant dades de la sessió...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error || !movie" class="max-w-xl mx-auto py-20 text-center">
        <p class="text-red-400 font-bold mb-4 italic">S'ha produït un error al carregar l'esdeveniment.</p>
        <NuxtLink to="/" class="px-6 py-2 bg-accent text-black font-black rounded-xl">Tornar a la cartellera</NuxtLink>
      </div>

      <!-- Main Flow -->
      <template v-else>
        <!-- Hero Section with Poster -->
        <div class="relative mb-12 rounded-[3.5rem] overflow-hidden bg-slate-900 border border-white/10 shadow-3xl group">
          <div class="flex flex-col md:flex-row">
            <!-- Poster Placeholder -->
            <div class="w-full md:w-80 lg:w-96 aspect-[2/3] relative overflow-hidden flex-shrink-0">
               <!-- Gradient Poster simulation based on movie name -->
               <div 
                 class="w-full h-full animate-pulse-slow"
                 :style="{
                   background: `linear-gradient(135deg, #111 0%, ${movie.nom ? '#' + Math.abs(movie.nom.split('').reduce((a: number, b: string) => (a << 5) - a + b.charCodeAt(0), 0)).toString(16).slice(0, 6) : '444'} 100%)`
                 }"
               >
                 <div class="absolute inset-0 flex flex-col items-center justify-center p-10 text-center">
                    <span class="text-8xl mb-6 drop-shadow-2xl">🎬</span>
                    <h3 class="text-2xl font-black text-white/20 uppercase tracking-[0.3em] leading-tight">{{ movie.nom }}</h3>
                 </div>
               </div>
               <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent"></div>
               <div class="absolute bottom-6 left-6">
                  <div class="px-4 py-1.5 bg-accent text-black font-black text-[10px] uppercase tracking-widest rounded-full shadow-xl">
                    CARTEL OFICIAL
                  </div>
               </div>
            </div>

            <!-- Movie Info -->
            <div class="flex-1 p-8 md:p-12 flex flex-col justify-between relative">
              <!-- Back button inside hero for better UX -->
              <NuxtLink to="/" class="absolute top-8 right-8 text-white/20 hover:text-accent transition-all group p-2">
                <span class="text-3xl group-hover:-translate-x-1 transition-transform block">←</span>
              </NuxtLink>

              <div>
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-accent/10 border border-accent/20 rounded-full mb-6">
                  <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
                  <span class="text-accent font-black text-[10px] uppercase tracking-[0.2em]">En Cartellera</span>
                </div>
                
                <h1 class="text-5xl md:text-7xl lg:text-8xl font-black text-white tracking-tighter uppercase italic leading-[0.85] mb-6">
                  {{ movie.nom }}
                </h1>
                
                <div class="flex flex-wrap gap-6 mb-8 uppercase font-black text-xs tracking-widest text-white/40 border-l-2 border-accent/30 pl-6">
                   <div class="flex flex-col">
                      <span class="text-accent/60 mb-1">DATA</span>
                      <span class="text-white text-lg">15/04/2026</span>
                   </div>
                   <div class="flex flex-col border-l border-white/10 pl-6">
                      <span class="text-accent/60 mb-1">HORA</span>
                      <span class="text-white text-lg">{{ hora }}h</span>
                   </div>
                   <div class="flex flex-col border-l border-white/10 pl-6">
                      <span class="text-accent/60 mb-1">RECINTE</span>
                      <span class="text-white text-lg">{{ movie.recinte || 'Multisala Pol' }}</span>
                   </div>
                </div>

                <p class="text-white/60 text-lg md:text-xl leading-relaxed max-w-2xl font-medium italic mb-10">
                  {{ movie.descripcio }}
                </p>
              </div>

              <!-- Steps Progress -->
              <div class="mt-auto pt-10 border-t border-white/5 flex items-center gap-4">
                 <div class="flex gap-2">
                    <div v-for="i in 3" :key="i" class="h-1.5 transition-all duration-500 rounded-full" 
                        :class="currentStep === i ? 'w-12 bg-accent' : (currentStep > i ? 'w-6 bg-accent/40' : 'w-6 bg-white/10')">
                    </div>
                 </div>
                 <span class="text-[10px] font-black uppercase tracking-widest text-white/20 ml-4">
                    Pas {{ currentStep > 3 ? 3 : currentStep }} de 3: {{ currentStep === 1 ? 'Selecció' : (currentStep === 2 ? 'Localització' : 'Pagament') }}
                 </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 1: Ticket Selection -->
        <div v-if="currentStep === 1" class="max-w-2xl mx-auto bg-slate-900/80 rounded-[2.5rem] p-10 border border-white/10 shadow-[0_25px_50px_-12px_rgba(0,0,0,0.5)] backdrop-blur-2xl">
          <div class="text-center mb-10">
            <h2 class="text-3xl font-black text-white mb-2">Tria les teves entrades</h2>
            <div class="h-1 w-20 bg-accent mx-auto rounded-full"></div>
          </div>
          
          <div class="space-y-6">
            <!-- Ticket Type Item -->
            <div class="flex items-center justify-between p-6 bg-white/[0.03] rounded-3xl border border-white/5 hover:bg-white/[0.06] hover:border-accent/40 hover:scale-[1.02] transition-all duration-300 group">
              <div>
                <h3 class="text-xl font-bold text-white group-hover:text-accent transition-colors">Menors de 16 o majors de 65 anys</h3>
                <p class="text-accent/80 font-bold mt-1 text-lg">5.00 €</p>
              </div>
              <div class="flex items-center gap-6">
                <button @click="updateCount('jove', -1)" class="w-12 h-12 rounded-2xl bg-white/5 text-white hover:bg-red-500 hover:text-white transition-all flex items-center justify-center text-2xl font-black border border-white/10">-</button>
                <span class="text-3xl font-black text-white w-10 text-center">{{ ticketCounts.jove }}</span>
                <button @click="updateCount('jove', 1)" class="w-12 h-12 rounded-2xl bg-white/5 text-white hover:bg-accent hover:text-black transition-all flex items-center justify-center text-2xl font-black border border-white/10">+</button>
              </div>
            </div>

            <!-- Ticket Type Item -->
            <div class="flex items-center justify-between p-6 bg-white/[0.03] rounded-3xl border border-white/5 hover:bg-white/[0.06] hover:border-accent/40 hover:scale-[1.02] transition-all duration-300 group">
              <div>
                <h3 class="text-xl font-bold text-white group-hover:text-accent transition-colors">Adults</h3>
                <p class="text-accent/80 font-bold mt-1 text-lg">12.00 €</p>
              </div>
              <div class="flex items-center gap-6">
                <button @click="updateCount('adult', -1)" class="w-12 h-12 rounded-2xl bg-white/5 text-white hover:bg-red-500 hover:text-white transition-all flex items-center justify-center text-2xl font-black border border-white/10">-</button>
                <span class="text-3xl font-black text-white w-10 text-center">{{ ticketCounts.adult }}</span>
                <button @click="updateCount('adult', 1)" class="w-12 h-12 rounded-2xl bg-white/5 text-white hover:bg-accent hover:text-black transition-all flex items-center justify-center text-2xl font-black border border-white/10">+</button>
              </div>
            </div>

            <!-- Ticket Type Item -->
            <div class="flex items-center justify-between p-6 bg-white/[0.03] rounded-3xl border border-white/5 hover:bg-white/[0.06] hover:border-accent/40 hover:scale-[1.02] transition-all duration-300 group">
              <div class="flex-1 mr-6">
                <h3 class="text-xl font-bold text-white group-hover:text-accent transition-colors">Reduïda</h3>
                <p class="text-accent/80 font-bold mt-1 text-lg">8.00 €</p>
                <p class="text-white/30 text-xs mt-3 italic leading-relaxed font-medium">Persones amb una discapacitat del 33% o superior i persones amb el carnet Jove d'entre 17 a 30 anys</p>
              </div>
              <div class="flex items-center gap-6">
                <button @click="updateCount('reduida', -1)" class="w-12 h-12 rounded-2xl bg-white/5 text-white hover:bg-red-500 hover:text-white transition-all flex items-center justify-center text-2xl font-black border border-white/10">-</button>
                <span class="text-3xl font-black text-white w-10 text-center">{{ ticketCounts.reduida }}</span>
                <button @click="updateCount('reduida', 1)" class="w-12 h-12 rounded-2xl bg-white/5 text-white hover:bg-accent hover:text-black transition-all flex items-center justify-center text-2xl font-black border border-white/10">+</button>
              </div>
            </div>
          </div>

          <div class="mt-12 pt-10 border-t border-white/5 flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="text-center md:text-left">
              <p class="text-white/40 text-xs font-black uppercase tracking-[0.2em] mb-1">Inversió total</p>
              <p class="text-5xl font-black text-white tabular-nums drop-shadow-[0_0_15px_rgba(255,255,255,0.1)]">{{ totalPrice.toFixed(2) }}<span class="text-2xl ml-1 text-accent">€</span></p>
              <div class="flex items-center gap-2 mt-2 justify-center md:justify-start">
                <div class="w-2 h-2 rounded-full bg-accent animate-pulse"></div>
                <p class="text-accent text-sm font-black uppercase tracking-widest">{{ totalTickets }} entrades</p>
              </div>
            </div>
            <button 
              @click="nextStep"
              :disabled="totalTickets === 0"
              class="group relative w-full md:w-auto px-16 py-5 rounded-2xl font-black text-xl transition-all shadow-[0_20px_40px_-10px_rgba(255,222,0,0.3)] disabled:shadow-none active:scale-95 disabled:opacity-20 disabled:cursor-not-allowed overflow-hidden mt-4 md:mt-0"
              :class="totalTickets > 0 ? 'bg-accent text-black hover:bg-white' : 'bg-white/10 text-white/40'"
            >
              <span class="relative z-10 flex items-center gap-3">
                Següent pas
                <span class="text-2xl group-hover:translate-x-1 transition-transform">→</span>
              </span>
            </button>
          </div>
        </div>

        <!-- Step 2: Seat Map -->
        <div v-else-if="currentStep === 2" class="animate-fade-in">
          <!-- Timer Header (Simplified) -->
          <div class="max-w-md mx-auto mb-10 bg-slate-900 border-2 border-accent/20 rounded-[2rem] p-6 flex items-center justify-between shadow-2xl backdrop-blur-xl group">
            <div class="flex items-center gap-5">
              <div>
                <p class="text-accent/60 text-[10px] font-black uppercase tracking-[0.2em] mb-0.5">Finalitza la reserva en</p>
                <p class="text-4xl font-mono font-black text-accent tabular-nums drop-shadow-[0_0_15px_rgba(255,222,0,0.3)]">{{ formattedTime }}</p>
              </div>
            </div>
            <div class="text-right border-l border-white/10 pl-6">
              <p class="text-white/20 text-[10px] uppercase font-bold tracking-widest mb-1">Entrades</p>
              <p class="text-2xl font-black text-white">{{ totalTickets }}</p>
            </div>
          </div>

          <div class="flex justify-center">
            <SeatMap />
          </div>
        </div>

        <!-- Step 3: Personal Data Form -->
        <div v-else-if="currentStep === 3" class="max-w-xl mx-auto animate-fade-in pb-20">
          <div class="flex items-center justify-center gap-4 mb-8 text-accent font-black uppercase tracking-widest text-sm bg-slate-900/50 py-3 rounded-2xl border border-accent/20">
            <span class="animate-pulse">●</span>
            TEMPS RESTANT PER FINALITZAR: {{ formattedTime }}
          </div>

          <div class="bg-slate-900/80 rounded-[2.5rem] p-10 border border-white/10 shadow-2xl backdrop-blur-2xl">
            <div class="text-center mb-10">
              <h2 class="text-3xl font-black text-white mb-2">Dades de contacte</h2>
              <p class="text-white/40 text-sm italic font-medium">Necessitem aquestes dades per enviar-te les entrades</p>
            </div>

            <div class="space-y-6">
              <div class="space-y-2">
                <label class="text-xs font-black uppercase tracking-widest text-accent ml-2">Nom complet</label>
                <input 
                  v-model="userName"
                  type="text" 
                  placeholder="Ex: Joan Garcia"
                  class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-accent/50 focus:bg-white/10 transition-all font-bold text-lg"
                />
              </div>

              <div class="space-y-2">
                <label class="text-xs font-black uppercase tracking-widest text-accent ml-2">Correu electrònic</label>
                <input 
                  v-model="userEmail"
                  type="email" 
                  placeholder="joan@exemple.com"
                  class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-accent/50 focus:bg-white/10 transition-all font-bold text-lg"
                />
              </div>
            </div>

            <div class="mt-12 flex flex-col gap-4">
              <button 
                @click="confirmPurchase"
                :disabled="isSubmitting || !userName || !userEmail"
                class="w-full py-5 bg-accent text-black font-black rounded-2xl hover:bg-white transition-all active:scale-95 disabled:opacity-20 disabled:cursor-not-allowed shadow-[0_20px_40px_-10px_rgba(255,222,0,0.3)] text-xl flex items-center justify-center gap-3"
              >
                <template v-if="isSubmitting">
                  <span class="w-6 h-6 border-4 border-black/20 border-t-black rounded-full animate-spin"></span>
                  Processant...
                </template>
                <template v-else>
                  FINALITZAR COMPRA 🛒
                </template>
              </button>
              <button @click="currentStep = 2" class="w-full py-4 text-white/40 font-black uppercase tracking-widest hover:text-white transition-all text-sm">
                ← Tornar a la selecció de butaques
              </button>
            </div>
          </div>
        </div>

        <!-- Step 4: Purchase Success -->
        <div v-else-if="currentStep === 4" class="max-w-2xl mx-auto animate-fade-in text-center pb-20">
          <div class="bg-slate-900/80 rounded-[3.5rem] p-12 border border-white/10 shadow-2xl backdrop-blur-2xl relative overflow-hidden">
            <!-- Decorative Glow -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-64 h-64 bg-accent/20 blur-[100px] rounded-full -z-10"></div>
            
            <div class="w-24 h-24 bg-accent text-black rounded-3xl flex items-center justify-center mx-auto mb-8 text-5xl shadow-[0_0_50px_rgba(255,222,0,0.3)]">✓</div>
            
            <h2 class="text-4xl font-black text-white mb-4 tracking-tighter uppercase italic">Compra realitzada!</h2>
            <p class="text-white/40 text-lg font-medium italic mb-12">Hem enviat les teves entrades al correu <span class="text-accent">{{ userEmail }}</span></p>

            <div class="bg-white/5 border-2 border-dashed border-accent/30 rounded-3xl p-8 mb-12 group hover:bg-white/10 transition-all">
              <p class="text-[10px] font-black uppercase tracking-[0.4em] text-accent mb-4">Codi Localitzador</p>
              <p class="text-6xl font-mono font-black text-white tracking-widest">{{ purchaseLocator }}</p>
              <div class="mt-6 flex items-center justify-center gap-2">
                <span class="text-accent animate-pulse">●</span>
                <p class="text-accent font-bold text-xs uppercase tracking-widest">Guarda aquest codi per consultar les teves entrades</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <NuxtLink to="/meves-entrades" class="py-5 bg-white/5 text-white font-black rounded-2xl border border-white/10 hover:bg-white/10 transition-all flex items-center justify-center gap-3">
                CONSULTAR ENTRADES
              </NuxtLink>
              <NuxtLink to="/" class="py-5 bg-accent text-black font-black rounded-2xl hover:bg-white transition-all shadow-xl flex items-center justify-center gap-3">
                TORNAR A L'INICI
              </NuxtLink>
            </div>
          </div>
        </div>
      </template>

      <!-- Confirmation Bar (Step 2 Only) -->
      <div v-if="currentStep === 2" class="confirmation-bar fixed bottom-0 left-0 right-0 h-28 bg-black/90 backdrop-blur-2xl border-t border-white/10 z-50">
        <div class="container mx-auto h-full flex justify-between items-center px-8">
          <div class="hidden md:flex flex-col">
            <span class="text-accent text-[10px] uppercase font-black tracking-[0.3em] mb-1">Selecció actual</span>
            <div class="flex items-center gap-3">
              <span class="text-4xl font-black text-white">{{ seatsStore.selectedCount }}</span>
              <div class="flex flex-col text-white/40 leading-none">
                <span class="text-[10px] font-bold uppercase tracking-widest">de {{ totalTickets }}</span>
                <span class="text-[10px] font-bold uppercase tracking-widest">butaques</span>
              </div>
            </div>
          </div>

          <div class="flex-1 mx-12">
            <div v-if="selectedSeats.length > 0" class="flex flex-wrap gap-2 animate-fade-in">
              <span v-for="seat in selectedSeats" :key="seat.id" class="px-4 py-2 bg-accent/20 rounded-xl text-accent font-black text-xs border border-accent/20 shadow-xl">
                F {{ seat.fila }} - B.{{ seat.numero }}
              </span>
            </div>
            <div v-else class="flex items-center gap-3 text-white/20">
              <div class="w-8 h-8 rounded-full border border-dashed border-white/20 flex items-center justify-center text-sm">!</div>
              <span class="italic text-sm font-medium">Tria les teves butaques al mapa superior per continuar...</span>
            </div>
          </div>
          
          <button 
            @click="goToPayment"
            class="group relative px-12 py-5 bg-accent text-black font-black rounded-2xl hover:bg-white transition-all active:scale-95 disabled:opacity-10 disabled:grayscale disabled:cursor-not-allowed shadow-[0_15px_30px_-5px_rgba(255,222,0,0.4)]"
            :disabled="seatsStore.selectedCount !== totalTickets"
          >
            <span class="relative z-10 flex items-center gap-3 text-lg">
              CONTINUAR AL PAGAMENT
              <span class="text-2xl group-hover:translate-x-1 transition-transform">💳</span>
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.text-accent { color: #ffde00; }
.bg-accent { background-color: #ffde00; }
.border-accent { border-color: #ffde00; }
.animate-fade-in { animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
