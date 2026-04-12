<script setup lang="ts">
// Recuperar llista de pel·lícules de l'API (només al client)
const config = useRuntimeConfig()
const { data, refresh } = await useFetch(`${config.public.apiBase}/admin/esdeveniments`, { server: false })
const movies = computed(() => (data.value as any)?.data || [])

const deleteMovie = async (id: number) => {
  if (confirm('Estàs segur que vols eliminar aquesta pel·lícula? Totes les sessions i reserves associades s\'eliminaran.')) {
    try {
      await $fetch(`${config.public.apiBase}/admin/esdeveniments/${id}`, { method: 'DELETE' })
      refresh()
    } catch (e) {
      alert('Error en eliminar la pel·lícula')
    }
  }
}
</script>

<template>
  <div class="flex flex-1 text-slate-900 font-sans">
    <AdminSidebar />
    
    <main class="flex-1 w-full p-12">
      <header class="flex flex-col md:flex-row justify-between items-center mb-12 bg-white shadow-sm p-8 rounded-2xl text-center md:text-left gap-6">
        <div class="flex flex-col items-center md:items-start">
          <h2 class="text-5xl font-black tracking-tighter uppercase italic text-accent mb-2">Pel·lícules</h2>
          <p class="text-slate-500 font-medium italic">Gestiona la cartellera, sessions i preus</p>
        </div>
        <NuxtLink to="/admin/esdeveniments/nou" class="bg-accent text-black px-8 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-slate-100 transition-all shadow-xl flex items-center gap-3">
          Afegir Pel·lícula
        </NuxtLink>
      </header>

      <div class="flex flex-col gap-4 w-full">
        <div v-if="movies.length === 0" class="py-32 text-center bg-white shadow-sm rounded-[3rem] border-2 border-dashed border-slate-200">
           <p class="text-slate-400 font-black uppercase tracking-[0.3em]">No hi ha dades a la base de dades</p>
        </div>

        <div 
          v-for="movie in movies" 
          :key="movie.id"
          class="w-full bg-white shadow-md p-6 rounded-[2rem] border border-slate-100 flex flex-col lg:flex-row items-center justify-between gap-6 group hover:translate-x-2 transition-all duration-300"
        >
          <div class="flex items-center gap-8 flex-1 w-full lg:w-auto">
             <div class="w-16 h-20 bg-slate-50 rounded-xl overflow-hidden border border-slate-200 shrink-0 flex items-center justify-center relative">
                <img v-if="movie.imatge" :src="movie.imatge.startsWith('/storage') ? config.public.apiBase.replace('/api', '') + movie.imatge : movie.imatge" class="w-full h-full object-cover">
                <span v-else class="text-[8px] text-slate-300 font-bold uppercase">Sense Imatge</span>
             </div>
             <div class="flex-1 min-w-0">
                <div class="flex items-center gap-4 mb-2">
                    <h3 class="text-xl font-black text-slate-900 uppercase italic tracking-tighter truncate">{{ movie.nom }}</h3>
                    <span class="px-2 py-0.5 bg-accent/10 text-accent text-[8px] font-black uppercase tracking-widest rounded-md border border-accent/20">PEL·LÍCULA</span>
                </div>
                <div class="flex flex-wrap gap-4">
                   <div class="flex items-center gap-1">
                      <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Sessions:</span>
                       <span class="text-[10px] font-black text-slate-700">{{ movie.sessions_count }}</span>
                   </div>
                   <div class="flex items-center gap-1">
                      <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Butaques:</span>
                      <span class="text-[10px] font-black text-slate-700">{{ movie.aforament_total }}</span>
                   </div>
                   <div class="hidden md:flex items-center gap-1">
                      <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">ID:</span>
                      <span class="text-[10px] font-black text-slate-700">#{{ movie.id }}</span>
                   </div>
                </div>
             </div>
          </div>

          <div class="flex items-center gap-3 w-full lg:w-auto justify-end">
             <NuxtLink :to="`/admin/esdeveniments/${movie.id}`" class="px-5 py-3 bg-slate-900 text-white hover:bg-accent hover:text-black rounded-xl font-black uppercase tracking-widest text-[9px] transition-all shadow-sm">
                Gestionar pel·lícula
             </NuxtLink>
             <button @click="deleteMovie(movie.id)" class="p-3 bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-xl transition-all border border-red-100" title="Eliminar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
             </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
