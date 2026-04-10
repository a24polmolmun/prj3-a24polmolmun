<script setup lang="ts">
const email = ref('')
const localitzador = ref('')
const reserves = ref<any[]>([])
const loading = ref(false)
const searched = ref(false)
const errorMsg = ref('')

const buscarEntrades = async () => {
  if (!email.value || !localitzador.value) return
  
  loading.value = true
  searched.value = true
  errorMsg.value = ''
  reserves.value = []
  
  try {
    const data = await $fetch(`http://localhost:8000/api/entrades?email=${email.value}&localitzador=${localitzador.value}`)
    reserves.value = data as any[]
    if (reserves.value.length === 0) {
      errorMsg.value = 'No hem trobat cap reserva amb aquestes dades.'
    }
  } catch (e: any) {
    console.error(e)
    errorMsg.value = e.data?.message || 'S\'ha produït un error en cercar les teves entrades.'
  } finally {
    loading.value = false
  }
}

const formatDate = (dateStr: string) => {
  if (!dateStr) return '—'
  const [year, month, day] = dateStr.split('-')
  return `${day}/${month}/${year}`
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col selection:bg-accent selection:text-black font-sans">
    
    <!-- Header Section -->
    <header class="relative pt-24 pb-16 px-6 text-center overflow-hidden bg-gray-900 border-b border-white/5 isolate">
      <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1200px] h-[400px] bg-accent/[0.03] blur-[160px] rounded-full -z-10 pointer-events-none"></div>
      
      <div class="max-w-4xl mx-auto relative z-10">
        <h1 class="text-4xl md:text-6xl font-black tracking-tighter uppercase italic text-accent mb-4">
          Les meves entrades
        </h1>
        <p class="text-white/40 text-lg font-medium tracking-wide max-w-2xl mx-auto leading-relaxed italic">
          Consulta les teves reserves i prepara't per a la funció.
        </p>
      </div>
    </header>

    <main class="flex-1 container mx-auto px-6 py-12 max-w-4xl">
      
      <!-- Search Box -->
      <div class="bg-white rounded-[2rem] p-8 shadow-xl border border-gray-100 mb-12 mt-16">
        <div class="flex flex-col md:flex-row gap-6">
          <div class="flex-1 space-y-4">
            <div>
              <label for="email" class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2 ml-4">Correu electrònic</label>
              <input 
                id="email"
                v-model="email"
                type="email" 
                placeholder="ex: pol@example.com"
                class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent focus:border-accent focus:bg-white rounded-2xl outline-none transition-all font-bold text-gray-900"
              >
            </div>
            <div>
              <label for="locator" class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2 ml-4">Codi Localitzador</label>
              <input 
                id="locator"
                v-model="localitzador"
                type="text" 
                placeholder="Ex: DB392Z"
                maxlength="6"
                class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent focus:border-accent focus:bg-white rounded-2xl outline-none transition-all font-mono font-black text-gray-900 tracking-widest uppercase"
                @keyup.enter="buscarEntrades"
              >
            </div>
          </div>
          <div class="flex items-end">
            <button 
              @click="buscarEntrades"
              :disabled="loading || !email || !localitzador"
              class="w-full md:w-auto h-[60px] px-8 bg-gray-900 text-white font-black rounded-2xl hover:bg-accent hover:text-black transition-all active:scale-95 shadow-xl disabled:opacity-50 disabled:cursor-not-allowed uppercase italic tracking-wider whitespace-nowrap"
            >
              {{ loading ? 'Buscant...' : 'Buscar les meves entrades' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Results -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-20 space-y-6">
        <div class="w-12 h-12 border-4 border-gray-200 border-t-accent rounded-full animate-spin"></div>
        <p class="text-gray-400 font-black uppercase tracking-widest text-xs">Cercant a la base de dades...</p>
      </div>

      <div v-else-if="searched && reserves.length > 0" class="space-y-6 animate-fade-in">
        <!-- Ticket Card -->
        <div 
          v-for="reserva in reserves" 
          :key="reserva.id"
          class="bg-white rounded-[2.5rem] p-0 shadow-lg border border-gray-100 overflow-hidden flex flex-col md:flex-row hover:shadow-2xl transition-all duration-500 group"
        >
          <!-- Movie Poster -->
          <!-- Movie Poster Placeholder -->
          <div class="w-full md:w-48 h-64 md:h-auto bg-slate-900 border-r border-gray-100 overflow-hidden flex-shrink-0 relative group">
            <div 
              class="w-full h-full"
              :style="{
                background: `linear-gradient(135deg, #111 0%, ${reserva.seient?.esdeveniment?.nom ? '#' + Math.abs(reserva.seient.esdeveniment.nom.split('').reduce((a: number, b: string) => (a << 5) - a + b.charCodeAt(0), 0)).toString(16).slice(0, 6) : '444'} 100%)`
              }"
            >
              <div class="absolute inset-0 flex items-center justify-center text-5xl group-hover:scale-110 transition-transform duration-700">🎬</div>
            </div>
            
            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-transparent to-transparent opacity-60"></div>
            
            <!-- Overlay for locator -->
            <div class="absolute bottom-4 left-4 right-4 bg-white/10 backdrop-blur-md px-3 py-2 rounded-xl border border-white/20">
               <p class="text-[8px] font-black text-white/60 uppercase tracking-widest mb-0.5">Localitzador</p>
               <p class="text-sm font-mono font-black text-accent tracking-widest">{{ localitzador.toUpperCase() }}</p>
            </div>
          </div>

          <!-- Ticket Info -->
          <div class="flex-1 p-8 flex flex-col justify-between">
            <div>
              <div class="flex justify-between items-start mb-4">
                <h3 class="text-3xl font-black text-gray-900 uppercase italic tracking-tighter leading-none">
                  {{ reserva.seient?.esdeveniment?.nom }}
                </h3>
              </div>
              
              <div class="flex flex-wrap gap-4 mb-8">
                <div class="px-4 py-1.5 bg-gray-100 border border-gray-200 rounded-full">
                  <span class="text-gray-600 font-black text-[10px] uppercase tracking-widest">Data: {{ formatDate(reserva.seient?.sessio?.dia) }}</span>
                </div>
                <div class="px-4 py-1.5 bg-accent/10 border border-accent/20 rounded-full">
                  <span class="text-accent font-black text-[10px] uppercase tracking-widest">Sessió: {{ reserva.seient?.sessio?.hora }}h</span>
                </div>
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                  <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Fila</p>
                  <p class="text-2xl font-black text-gray-900">{{ reserva.seient?.fila }}</p>
                </div>
                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                  <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Butaca</p>
                  <p class="text-2xl font-black text-gray-900">{{ reserva.seient?.numero }}</p>
                </div>
              </div>
            </div>
            
            <div class="mt-8 flex items-center justify-between pt-6 border-t border-gray-50">
              <div class="flex flex-col">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-300">Reserva #{{ reserva.id }}</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                <span class="text-green-600 text-[10px] font-black uppercase tracking-widest">Entrada Confirmada</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State / Error -->
      <div v-else-if="searched" class="bg-white rounded-[3rem] p-16 text-center border-2 border-dashed border-gray-100 shadow-sm animate-fade-in">
        <div class="text-6xl mb-6 opacity-30">🎟️</div>
        <h2 class="text-2xl font-black text-gray-900 uppercase italic mb-2">No hem trobat res</h2>
        <p class="text-gray-400 font-medium italic mb-0">
          {{ errorMsg || 'No hem trobat cap reserva associada a aquest correu i localitzador.' }}
        </p>
      </div>

    </main>
  </div>
</template>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
