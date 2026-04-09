<script setup lang="ts">
import { computed } from 'vue'
import { useSeatsStore, type Seient } from '../stores/seatsStore'
import SeatItem from './SeatItem.vue'

const props = withDefaults(defineProps<{
  readonly?: boolean
}>(), {
  readonly: false
})

const seatsStore = useSeatsStore()

// Obtenir files úniques de forma dinàmica des de l'store
const rows = computed(() => {
  const rowSet = new Set<string>()
  seatsStore.seats.forEach(s => rowSet.add(s.fila))
  return Array.from(rowSet).sort()
})

// Funció helper per obtenir un seient concret per fila i número
const getSeat = (row: string, num: number): Seient | undefined => {
  return seatsStore.seats.find(s => s.fila === row && s.numero === num)
}
</script>

<template>
  <div class="hall-container">
    <!-- Cinema Screen -->
    <div class="screen-wide">
      <div class="screen-text">PANTALLA</div>
    </div>

    <!-- Seats Grid with Row Labels -->
    <!-- Utilitzem 13 columnes: 1 per l'ID de fila + 12 per les butaques -->
    <div class="seats-layout grid grid-cols-[3rem_repeat(12,1fr)] gap-2 md:gap-4 items-center justify-items-center w-full max-w-4xl mx-auto">
      <template v-for="row in rows" :key="row">
        <!-- Label de la fila -->
        <div class="row-id font-black text-slate-400">{{ row }}</div>
        
        <!-- Butaques de la fila (fins a 12) -->
        <div v-for="n in 12" :key="`${row}${n}`" class="seat-wrapper w-full flex justify-center">
            <SeatItem 
              v-if="getSeat(row, n)"
              :seient="getSeat(row, n)!" 
              :readonly="props.readonly"
              @toggle="seatsStore.toggleSeatSelection" 
            />
            <!-- Espai buit si no hi ha seient en aquesta posició (per mantenir la graella) -->
            <div v-else class="w-8 h-8 md:w-12 md:h-12 opacity-0"></div>
        </div>
      </template>
    </div>

    <!-- Legend -->
    <div class="legend-centered">
      <div class="legend-box">
        <div class="legend-color-sample seat-disponible border border-gray-200"></div>
        <span>Disponible</span>
      </div>
      <div class="legend-box">
        <div class="legend-color-sample seat-reservat"></div>
        <span>Reservat</span>
      </div>
      <div class="legend-box">
        <div class="legend-color-sample seat-venut"></div>
        <span>Venut</span>
      </div>
      <div class="legend-box">
        <div class="legend-color-sample seat-seleccionat"></div>
        <span>La teva selecció</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Estils específics de disposició local si cal */
</style>
