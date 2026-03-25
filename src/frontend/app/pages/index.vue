<script setup lang="ts">
import { computed } from 'vue'
import { useEventsStore } from '../stores/eventsStore'

const eventsStore = useEventsStore()
const movies = computed(() => eventsStore.allMovies)
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <header class="mb-12 text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">
          Cartellera del <span class="text-indigo-600">Cinema</span>
        </h1>
        <p class="text-lg text-gray-500 max-w-2xl mx-auto">
          Tria el teu esdeveniment preferit i consulta les sessions disponibles. Gaudeix de la millor experiència cinematogràfica.
        </p>
        <div class="mt-6 w-24 h-1.5 bg-indigo-600 mx-auto rounded-full"></div>
      </header>

      <!-- Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <MovieCard 
          v-for="movie in movies" 
          :key="movie.id" 
          :movie="movie" 
        />
      </div>

      <!-- Empty State (Optional but good practice) -->
      <div v-if="movies.length === 0" class="text-center py-20">
        <p class="text-gray-400 italic">No hi ha pel·lícules disponibles en aquest moment.</p>
      </div>
    </div>
  </div>
</template>

<style>
/* Global fade-in animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.grid > * {
  animation: fadeIn 0.5s ease-out forwards;
}
</style>
