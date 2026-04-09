<script setup lang="ts">
import type { Seient } from '../stores/seatsStore'

/**
 * Nota: L'estat 'reservat' es visualitza en color groc/taronja.
 * Aquest estat se sincronitzarà via WebSockets per mostrar bloquejos
 * en temps real d'altres usuaris.
 */

const props = withDefaults(defineProps<{
  seient: Seient,
  readonly?: boolean
}>(), {
  readonly: false
})

const emit = defineEmits(['toggle'])

const statusClasses = {
  disponible: 'bg-gray-200 border-gray-400 hover:bg-gray-300 cursor-pointer',
  reservat: 'bg-orange-400 border-orange-600 cursor-not-allowed opacity-80',
  venut: 'bg-gray-600 border-gray-800 cursor-not-allowed opacity-50',
  seleccionat: 'bg-blue-600 border-blue-800 hover:bg-blue-700 cursor-pointer text-white shadow-lg'
}
</script>

<template>
  <button
    :class="[
      'seat-btn',
      `seat-${props.seient.estat}`,
      { 'cursor-default pointer-events-none hover:scale-100': props.readonly }
    ]"
    :disabled="props.readonly || props.seient.estat === 'reservat' || props.seient.estat === 'venut'"
    @click="!props.readonly && emit('toggle', props.seient.id)"
    :title="`Fila ${props.seient.fila}, Seient ${props.seient.numero} - ${props.seient.estat}`"
  >
    {{ props.seient.numero }}
  </button>
</template>

<style scoped>
/* Estils extres per simular la forma d'una butaca si calgués */
button {
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}
button:active:not(:disabled) {
  transform: scale(0.95);
}
</style>
