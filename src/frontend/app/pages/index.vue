<script setup lang="ts">
import { computed } from 'vue'
import { useEventsStore } from '../stores/eventsStore'

const eventsStore = useEventsStore()
const movies = computed(() => eventsStore.allMovies)
</script>

<template>
  <div>
    <section class="hero">
      <h1>Benvinguts a Cinema Pol</h1>
      <p>La teva plataforma per descobrir les millors pel·lícules i esdeveniments a la teva ciutat. Tria la teva propera experiència avui mateix.</p>
    </section>

    <div class="grid">
      <MovieCard v-for="movie in movies" :key="movie.id" :movie="movie" />
    </div>

    <div v-if="movies.length === 0" style="text-align: center; padding: 2rem;">
      <p style="color: var(--color-accent); font-style: italic;">No hi ha pel·lícules disponibles en aquest moment.</p>
    </div>
  </div>
</template>

<style scoped>
/* Estils específics si calgués, però la majoria són globals */
</style>
