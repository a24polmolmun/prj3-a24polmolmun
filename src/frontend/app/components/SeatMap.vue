<script setup lang="ts">
import { useSeatsStore } from '../stores/seatsStore'
import SeatItem from './SeatItem.vue'

const seatsStore = useSeatsStore()
</script>

<template>
  <div class="hall-container">
    <!-- Cinema Screen -->
    <div class="screen-wide">
      <div class="screen-text">PANTALLA</div>
    </div>

    <!-- Seats Grid with Row Labels -->
    <div class="seats-layout">
      <template v-for="row in ['A', 'B', 'C', 'D', 'E']" :key="row">
        <div class="row-id">{{ row }}</div>
        <div v-for="n in 8" :key="`${row}${n}`" class="seat-wrapper">
          <SeatItem 
            :seient="seatsStore.seats.find(s => s.fila === row && s.numero === n)!" 
            @toggle="seatsStore.toggleSeatSelection" 
          />
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
