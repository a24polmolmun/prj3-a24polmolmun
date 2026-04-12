<script setup lang="ts">
const config = useRuntimeConfig()
// Fetch real movies from Laravel API with robust error handling
const { data, pending, error, refresh } = useFetch(`${config.public.apiBase}/esdeveniments`, {
  lazy: true,
  // Retry 3 times with exponential backoff behavior (default Nuxt behavior if true/number)
  retry: 3,
  retryDelay: 1000, // 1 second between retries
})

// Extract the array of events from the API Response
const movies = computed(() => {
  const response = data.value as any
  return response?.data || []
})
</script>

<template>
  <div class="min-h-screen w-full bg-gray-50 flex flex-col selection:bg-accent selection:text-black font-sans">
    
    <!-- Hero / Header Section (DARK) -->
    <header class="relative pt-32 pb-32 px-6 text-center overflow-hidden bg-gray-900 border-b border-white/5 isolate">
      <!-- Background Glow Decoration -->
      <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1200px] h-[600px] bg-accent/[0.03] blur-[160px] rounded-full -z-10 pointer-events-none"></div>
      
      <div class="max-w-4xl mx-auto relative z-10">
        <h1 class="text-5xl md:text-7xl font-black tracking-tighter uppercase italic text-accent mb-6 drop-shadow-[0_10px_30px_rgba(255,222,0,0.15)]">
          Cartellera <br class="md:hidden" /> Cinema Pol
        </h1>
        <p class="text-white/40 text-lg md:text-xl font-medium tracking-wide max-w-2xl mx-auto leading-relaxed italic">
          BENVINGUTS A CINEMA POL. La millor selecció de cinema en versió original i qualitat premium.
        </p>
      </div>
    </header>

    <!-- Separation Spacer -->
    <div class="h-20 bg-gray-50"></div>

    <!-- Main Section (LIGHT) -->
    <main class="flex-1 container mx-auto px-6 py-12 pb-48">
      
      <!-- Loading State -->
      <div v-if="pending" class="flex flex-col items-center justify-center py-40 space-y-8">
        <div class="relative w-20 h-20">
          <div class="absolute inset-0 border-8 border-gray-200 rounded-full"></div>
          <div class="absolute inset-0 border-8 border-t-accent rounded-full animate-spin"></div>
        </div>
        <p class="text-gray-400 font-black uppercase tracking-[0.5em] text-xs animate-pulse text-center">Sincronitzant amb la cartellera...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="max-w-xl mx-auto bg-white rounded-[3rem] p-16 text-center shadow-2xl border border-red-50/50">
        <div class="w-20 h-20 bg-red-50 text-red-500 rounded-3xl flex items-center justify-center mx-auto mb-8 text-4xl font-black">!</div>
        <h2 class="text-3xl font-black text-gray-900 mb-4 tracking-tighter uppercase italic text-center">Error de xarxa</h2>
        <p class="text-gray-400 font-medium italic mb-10 px-8 text-center">No hem pogut obtenir la cartellera. Comprova la teva connexió.</p>
        <button 
          @click="() => refresh()" 
          class="w-full py-5 bg-gray-900 text-white font-black rounded-2xl hover:bg-accent hover:text-black transition-all active:scale-95 shadow-xl"
        >
          REINTENTAR CONNEXIÓ
        </button>
      </div>

      <!-- Movie Grid -->
      <div v-else-if="movies.length > 0" class="animate-fade-in container mx-auto">
        <div class="flex flex-col md:flex-row items-center justify-between mb-20 border-b-2 border-gray-100 pb-10 gap-6">
          <div class="text-center md:text-left">
            <h2 class="text-4xl md:text-5xl font-black text-gray-900 tracking-tighter uppercase italic">Pel·lícules en cartellera</h2>
            <p class="text-gray-400 font-bold uppercase tracking-widest text-[10px] mt-3">Sessions i horaris disponibles per avui</p>
          </div>
          <div class="px-6 py-3 bg-gray-900 text-white text-[10px] font-black uppercase tracking-[0.3em] rounded-full whitespace-nowrap">
            {{ movies.length }} TÍTOLS DISPONIBLES
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-12">
          <MovieCard 
            v-for="movie in movies" 
            :key="movie.id" 
            :movie="movie" 
          />
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="max-w-3xl mx-auto py-32 bg-white rounded-[3.5rem] border-2 border-dashed border-gray-100 flex flex-col items-center justify-center text-center shadow-sm">
        <div class="text-8xl mb-10 opacity-20 filter grayscale">🎞️</div>
        <h2 class="text-3xl font-black text-gray-300 uppercase tracking-[0.2em] mb-4">Cartellera buida</h2>
        <p class="text-gray-400/60 font-medium italic max-w-md px-8">Estem preparant la nova programació. Torna a visitar-nos aviat!</p>
      </div>
    </main>
  </div>
</template>

<style>
.animate-fade-in {
  animation: fadeIn 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(40px); }
  to { opacity: 1; transform: translateY(0); }
}

.text-accent { color: #ffde00; }
.bg-accent { background-color: #ffde00; }
.border-accent { border-color: #ffde00; }
</style>
