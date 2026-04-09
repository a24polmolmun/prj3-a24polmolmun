<script setup lang="ts">
import { computed } from 'vue'

interface Session {
  id: number
  hora: string
  dia: string
}

interface Movie {
  id: number
  nom: string
  descripcio: string
  imatge: string
  data_hora: string
  recinte: string
  sessions: Session[]
}

const props = defineProps<{
  movie: Movie
}>()

// Hores des de l'API amb fallback per si no n'hi ha cap
const sessions = computed(() => {
  if (props.movie.sessions && props.movie.sessions.length > 0) {
    return props.movie.sessions.map(s => s.hora)
  }
  return ['17:00', '19:30', '22:00']
})

const formattedHora = computed(() => {
  if (!props.movie.data_hora) return ''
  const date = new Date(props.movie.data_hora)
  return date.toLocaleTimeString('ca-ES', { hour: '2-digit', minute: '2-digit' })
})
</script>

<template>
  <div class="movie-card bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 border border-gray-100 group">
    <!-- Image Section -->
    <div class="relative h-64 overflow-hidden">
      <img 
        :src="movie.imatge?.startsWith('/storage') ? 'http://localhost:8000' + movie.imatge : (movie.imatge || 'https://images.unsplash.com/photo-1485846234645-a62644ffb1e7?q=80&w=1000&auto=format&fit=crop')" 
        :alt="movie.nom" 
        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
    </div>

    <!-- Content Section -->
    <div class="p-6">
      <div class="mb-3">
        <span class="text-[10px] font-bold text-accent uppercase tracking-widest mb-1 block">{{ movie.recinte || 'Multisala Pol' }}</span>
        <h3 class="text-xl font-black text-gray-900 leading-tight group-hover:text-accent transition-colors">{{ movie.nom }}</h3>
      </div>
      
      <p class="text-gray-500 text-sm line-clamp-2 mb-6 italic">
        {{ movie.descripcio }}
      </p>

      <!-- Sessions Section -->
      <div class="space-y-3 pt-4 border-t border-gray-100">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Tria una sessió</p>
        <div class="flex flex-wrap gap-2">
          <NuxtLink 
            v-for="hora in sessions" 
            :key="hora"
            :to="`/esdeveniment/${movie.id}?hora=${hora}`"
            class="px-4 py-2 bg-gray-50 hover:bg-accent text-gray-900 hover:text-black rounded-xl text-xs font-black transition-all border border-gray-100 hover:border-accent"
          >
            {{ hora }}
          </NuxtLink>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
.movie-card {
  /* Fix build issues by adding some CSS */
  display: flex;
  flex-direction: column;
}

.text-accent {
  color: #ffde00;
}
.bg-accent {
  background-color: #ffde00;
}
.border-accent {
  border-color: #ffde00;
}
</style>
