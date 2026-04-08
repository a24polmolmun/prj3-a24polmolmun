<script setup lang="ts">
const { data, refresh } = await useFetch('http://localhost:8000/api/admin/esdeveniments')
const movies = computed(() => (data.value as any)?.data || [])

const deleteMovie = async (id: number) => {
  if (confirm('Estàs segur que vols eliminar aquesta pel·lícula? Totes les sessions i reserves associades s\'eliminaran.')) {
    try {
      await $fetch(`http://localhost:8000/api/admin/esdeveniments/${id}`, { method: 'DELETE' })
      refresh()
    } catch (e) {
      alert('Error en eliminar la pel·lícula')
    }
  }
}
</script>

<template>
  <div class="flex min-h-screen bg-slate-950 text-white font-sans">
    <AdminSidebar />
    
    <main class="flex-1 p-12">
      <header class="flex justify-between items-center mb-12">
        <div>
          <h2 class="text-5xl font-black tracking-tighter uppercase italic text-accent mb-2">Pel·lícules</h2>
          <p class="text-white/40 font-medium italic">Gestiona la cartellera, sessions i preus</p>
        </div>
        <NuxtLink to="/admin/esdeveniments/nou" class="bg-accent text-black px-8 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-white transition-all shadow-xl flex items-center gap-3">
          Afegir Pel·lícula
        </NuxtLink>
      </header>

      <div class="grid grid-cols-1 gap-6">
        <div v-if="movies.length === 0" class="py-32 text-center bg-slate-900 rounded-[3rem] border-2 border-dashed border-white/5">
           <p class="text-white/20 font-black uppercase tracking-[0.3em]">No hi ha dades a la base de dades</p>
        </div>

        <div 
          v-for="movie in movies" 
          :key="movie.id"
          class="bg-slate-900 p-8 rounded-[2.5rem] border border-white/5 flex flex-col md:flex-row items-center justify-between gap-8 group hover:border-accent/30 transition-all font-sans"
        >
          <div class="flex items-center gap-8 flex-1">
             <div class="w-24 h-32 bg-slate-950 rounded-2xl overflow-hidden border border-white/10 flex-shrink-0 flex items-center justify-center relative">
                <img v-if="movie.imatge" :src="movie.imatge" class="w-full h-full object-cover">
                <span v-else class="text-xs text-white/10 font-black uppercase">Sense Imatge</span>
             </div>
             <div>
                <h3 class="text-2xl font-black text-white uppercase italic tracking-tighter group-hover:text-accent transition-colors">{{ movie.nom }}</h3>
                <div class="flex gap-4 mt-2">
                   <div class="flex items-center gap-2 px-3 py-1 bg-white/5 rounded-full">
                      <span class="text-[10px] font-black text-accent uppercase tracking-widest">{{ movie.sessions_count }} Sessions</span>
                   </div>
                   <div class="flex items-center gap-2 px-3 py-1 bg-white/5 rounded-full">
                      <span class="text-[10px] font-black text-white/40 uppercase tracking-widest">{{ movie.aforament_total }} Butaques</span>
                   </div>
                </div>
                <p class="text-white/30 text-xs mt-3 line-clamp-1 italic max-w-xl">{{ movie.descripcio }}</p>
             </div>
          </div>

          <div class="flex items-center gap-4">
             <NuxtLink :to="`/admin/esdeveniments/${movie.id}`" class="px-6 py-3 bg-white/5 text-white/60 hover:text-white hover:bg-white/10 rounded-xl font-bold uppercase tracking-widest text-[10px] border border-white/5">
                Editar
             </NuxtLink>
             <button @click="deleteMovie(movie.id)" class="px-6 py-3 bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white rounded-xl font-bold uppercase tracking-widest text-[10px] border border-red-500/20 transition-all">
                Eliminar
             </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
