<script setup lang="ts">
definePageMeta({
  middleware: [
    function (to, from) {
      return navigateTo('/admin/dashboard')
    },
  ],
})
</script>

<template>
  <div class="min-h-screen bg-white"></div>
</template>
