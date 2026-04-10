<script setup lang="ts">
import { ref } from 'vue';
import { useReviewStore } from '@/stores/reviewStore';

const props = defineProps<{
  esdeveniment_id: number | string
}>();

const reviewStore = useReviewStore();
const nomUsuari = ref('');
const rating = ref(0);
const hoverRating = ref(0);
const comment = ref('');
const isSubmitting = ref(false);
const message = ref('');
const messageType = ref<'success' | 'error' | ''>('');

const setRating = (val: number) => {
  rating.value = val;
};

const handleSubmit = async () => {
  if (!nomUsuari.value.trim()) {
    message.value = "Si us plau, escriu el teu nom.";
    messageType.value = 'error';
    return;
  }
  
  if (rating.value === 0) {
    message.value = "Si us plau, tria una puntuació.";
    messageType.value = 'error';
    return;
  }

  isSubmitting.value = true;
  message.value = '';
  
  const result = await reviewStore.submitReview({
    esdeveniment_id: props.esdeveniment_id,
    nom_usuari: nomUsuari.value,
    rating: rating.value,
    comment: comment.value
  });

  if (result && result.success) {
    message.value = "Gràcies per la teva valoració!";
    messageType.value = 'success';
    rating.value = 0;
    comment.value = '';
    nomUsuari.value = '';
  } else {
    message.value = result?.message || "Error al enviar la ressenya.";
    messageType.value = 'error';
  }
  
  isSubmitting.value = false;
};
</script>

<template>
  <div class="bg-slate-900 rounded-[2rem] p-8 md:p-10 shadow-xl border border-white/5 isolate overflow-hidden relative">
    <!-- Background Decor -->
    <div class="absolute -top-20 -right-20 w-64 h-64 bg-accent/10 rounded-full blur-3xl -z-10"></div>
    
    <div class="relative z-10">
      <h3 class="text-2xl font-black text-white uppercase italic tracking-tighter mb-2">Com ha anat la funció?</h3>
      <p class="text-white/40 text-[10px] font-black uppercase tracking-widest mb-8">La teva opinió ens ajuda a millorar</p>

      <div v-if="message" :class="messageType === 'success' ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20'" class="mb-6 p-4 rounded-xl border text-xs font-bold uppercase tracking-widest text-center">
        {{ message }}
      </div>

      <div class="space-y-6">
        <!-- Name Input -->
        <div class="space-y-3">
          <label class="text-[10px] font-black uppercase tracking-widest text-accent ml-2">El teu nom</label>
          <input 
            v-model="nomUsuari" 
            type="text"
            placeholder="Ex: Joan Garcia"
            class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 focus:border-accent outline-none font-bold text-white placeholder-white/20 transition-all font-bold"
          />
        </div>

        <!-- Stars -->
        <div class="space-y-3">
          <label class="text-[10px] font-black uppercase tracking-widest text-accent ml-2">Puntuació</label>
          <div class="flex gap-2 bg-white/5 w-fit p-3 rounded-2xl border border-white/10">
            <template v-for="i in 5" :key="i">
              <button 
                @click="setRating(i)" 
                @mouseenter="hoverRating = i"
                @mouseleave="hoverRating = 0"
                class="transition-all transform active:scale-90"
              >
                <svg 
                  class="w-8 h-8 cursor-pointer" 
                  :class="(hoverRating || rating) >= i ? 'text-accent fill-current' : 'text-white/10 fill-current'" 
                  viewBox="0 0 20 20"
                >
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </button>
            </template>
          </div>
        </div>

        <!-- Comment -->
        <div class="space-y-3">
          <label class="text-[10px] font-black uppercase tracking-widest text-accent ml-2">Comentari (Opcional)</label>
          <textarea 
            v-model="comment" 
            placeholder="Comparteix la teva experiència amb nosaltres..."
            rows="4"
            class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 focus:border-accent outline-none font-bold text-white placeholder-white/20 transition-all resize-none"
          ></textarea>
        </div>

        <button 
          @click="handleSubmit"
          :disabled="isSubmitting || rating === 0"
          class="w-full bg-accent text-gray-900 py-5 rounded-2xl font-black uppercase tracking-widest hover:brightness-110 disabled:opacity-50 disabled:grayscale transition-all shadow-xl active:scale-95"
        >
          {{ isSubmitting ? 'Enviant...' : 'Publicar Ressenya' }}
        </button>
      </div>
    </div>
  </div>
</template>
