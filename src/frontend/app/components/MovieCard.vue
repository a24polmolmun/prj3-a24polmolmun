<script setup lang="ts">
interface Movie {
  id: number
  titol: string
  descripcio: string
  imatge: string
  sessions: string[]
}

const props = defineProps<{
  movie: Movie
}>()
</script>

<template>
  <div class="card">
    <div class="relative h-64 overflow-hidden" style="height: 200px; position: relative; overflow: hidden;">
      <img 
        :src="props.movie.imatge" 
        :alt="props.movie.titol" 
        style="width: 100%; h-full: 100%; object-fit: cover;"
      />
    </div>

    <div class="card-content">
      <h3 class="card-title">{{ props.movie.titol }}</h3>
      <p class="card-description">{{ props.movie.descripcio }}</p>

      <div class="sessions" style="margin-top: 1rem;">
        <p style="font-size: 0.8rem; color: var(--color-muted); text-transform: uppercase; margin-bottom: 0.5rem;">Sessions</p>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
          <NuxtLink 
            v-for="session in props.movie.sessions" 
            :key="session"
            :to="`/esdeveniment/${props.movie.id}?hora=${session}`"
            class="btn"
            style="padding: 0.4rem 0.8rem; font-size: 0.9rem;"
          >
            {{ session }}
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Glassmorphism effect if needed */
.glass {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(10px);
}
</style>
