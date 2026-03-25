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
  <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden flex flex-col border border-gray-100 group">
    <!-- Movie Poster -->
    <div class="relative h-64 overflow-hidden">
      <img 
        :src="props.movie.imatge" 
        :alt="props.movie.titol" 
        class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
      <h3 class="absolute bottom-4 left-4 text-white font-bold text-xl drop-shadow-md">
        {{ props.movie.titol }}
      </h3>
    </div>

    <!-- Content -->
    <div class="p-5 flex-grow">
      <p class="text-gray-600 text-sm line-clamp-3 mb-6">
        {{ props.movie.descripcio }}
      </p>

      <!-- Sessions -->
      <div class="space-y-3">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Sessions disponibles</p>
        <div class="flex flex-wrap gap-2">
          <NuxtLink 
            v-for="session in props.movie.sessions" 
            :key="session"
            :to="`/esdeveniment/${props.movie.id}?hora=${session}`"
            class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg font-medium text-sm hover:bg-indigo-600 hover:text-white transition-colors duration-200 border border-indigo-100"
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
