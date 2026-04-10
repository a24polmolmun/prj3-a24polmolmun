<script setup lang="ts">
const props = defineProps<{
  reviews: any[]
  averageRating: number
  totalReviews: number
}>();

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('ca-ES', { day: 'numeric', month: 'long', year: 'numeric' }).format(date);
};
</script>

<template>
  <div class="space-y-8">
    <div class="flex items-end gap-4 mb-8">
      <div class="text-5xl font-black text-gray-900 leading-none">{{ averageRating }}</div>
      <div class="flex flex-col">
        <div class="flex mb-1">
          <template v-for="i in 5" :key="i">
            <svg 
              class="w-5 h-5" 
              :class="i <= Math.round(averageRating) ? 'text-yellow-400 fill-current' : 'text-gray-200 fill-current'" 
              viewBox="0 0 20 20"
            >
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
          </template>
        </div>
        <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Basat en {{ totalReviews }} ressenyes</div>
      </div>
    </div>

    <div v-if="reviews.length === 0" class="bg-slate-50 rounded-2xl p-8 text-center border border-dashed border-slate-200">
      <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Encara no hi ha ressenyes. Sigues el primer!</p>
    </div>

    <div v-else class="grid gap-6">
      <div v-for="review in reviews" :key="review.id" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 transition-all hover:shadow-md">
        <div class="flex justify-between items-start mb-4">
          <div class="flex flex-col">
            <span class="text-sm font-black uppercase tracking-tight text-gray-900">{{ review.nom_usuari || 'Usuari Anònim' }}</span>
            <span class="text-[10px] text-slate-400 font-bold">{{ formatDate(review.created_at) }}</span>
          </div>
          <div class="flex">
            <template v-for="i in 5" :key="i">
              <svg 
                class="w-3.5 h-3.5" 
                :class="i <= review.rating ? 'text-yellow-400 fill-current' : 'text-gray-200 fill-current'" 
                viewBox="0 0 20 20"
              >
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </template>
          </div>
        </div>
        <p v-if="review.comment" class="text-sm text-slate-600 leading-relaxed italic">"{{ review.comment }}"</p>
      </div>
    </div>
  </div>
</template>
