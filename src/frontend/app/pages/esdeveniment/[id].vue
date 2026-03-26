<script setup lang="ts">
import { useRoute } from 'vue-router'
import { onMounted, computed } from 'vue'
import { useEventsStore } from '../../stores/eventsStore'
import { useSeatsStore } from '../../stores/seatsStore'
import SeatMap from '../../components/SeatMap.vue'

const route = useRoute()
const eventsStore = useEventsStore()
const seatsStore = useSeatsStore()

const movieId = Number(route.params.id)
const hora = route.query.hora as string
const movie = eventsStore.getMovieById(movieId)

const selectedSeats = computed(() => {
  return seatsStore.seats.filter(s => s.estat === 'seleccionat')
})

const displayedSeats = computed(() => {
  return selectedSeats.value.slice(0, 6)
})

const hasMoreSeats = computed(() => {
  return selectedSeats.value.length > 6
})

onMounted(() => {
  seatsStore.resetSelection()
})
</script>

<template>
  <div class="event-page-bg py-12">
    <div class="container pb-32">
      <!-- 3-Part Header Bar -->
      <div class="header-bar">
        <div class="header-left">
          <NuxtLink to="/" class="btn-back text-lg font-bold">← Tornar a la cartellera</NuxtLink>
        </div>
        
        <div class="header-center">
          <h1 v-if="movie" class="movie-title-header">
            {{ movie.titol }}
          </h1>
        </div>

        <div class="header-right">
          <div v-if="movie" class="session-time-header">
            {{ hora }}
          </div>
        </div>
      </div>

      <!-- Main Hall Container (White) -->
      <div class="flex justify-center mt-10">
        <SeatMap />
      </div>
    </div>

    <!-- Reverted Confirmation Bar (Original Style) -->
    <div class="confirmation-bar">
      <div class="container flex justify-between items-center h-full px-6">
        <div class="flex-1">
          <div v-if="selectedSeats.length > 0" class="flex flex-wrap gap-x-4 gap-y-1">
            <span v-for="seat in selectedSeats" :key="seat.id" class="text-white font-bold text-sm">
              Fila {{ seat.fila }} - B.{{ seat.numero }}
            </span>
          </div>
          <span v-else class="text-white/40 italic text-sm">Cap seient seleccionat</span>
        </div>
        
        <div class="flex items-center gap-6">
          <div class="hidden md:block text-right">
             <div class="text-white font-bold leading-tight">{{ movie?.titol }}</div>
             <div class="text-white/60 text-xs tracking-wider uppercase">{{ hora }}</div>
          </div>
          <button 
            class="btn-confirm px-10 py-3"
            :disabled="selectedSeats.length === 0"
          >
            Confirmar Reserva
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.event-page {
  padding-bottom: 8rem;
}

.btn-back {
  color: var(--color-accent);
  text-decoration: none;
  font-weight: 500;
}

.text-accent {
  color: var(--color-accent);
}

.text-muted {
  color: var(--color-muted);
}
</style>
