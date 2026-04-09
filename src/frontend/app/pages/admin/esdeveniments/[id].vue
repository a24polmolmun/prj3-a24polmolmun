<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const isEdit = route.params.id !== 'nou'

const form = ref({
  nom: '',
  descripcio: '',
  imatge: '',
  recinte: 'Sala Principal',
  data_hora: new Date().toISOString().slice(0, 16),
  aforament_total: 96,
  sessions: [] as any[],
  preus: [] as any[]
})

const selectedFile = ref<File | null>(null)
const imagePreview = ref<string | null>(null)

const { data: movieResponse } = useFetch(() => isEdit ? `http://localhost:8000/api/admin/esdeveniments/${route.params.id}` : null, { 
    server: false,
    immediate: isEdit 
})

watch(movieResponse, (newVal) => {
    if (newVal && (newVal as any).success) {
      const movieData = (newVal as any).data
      form.value = {
        ...form.value,
        ...movieData,
        sessions: movieData.sessions || [],
        preus: movieData.tipus_entrades || []
      }
      imagePreview.value = movieData.imatge
    }
}, { immediate: true })

if (!isEdit) {
  form.value.sessions = [
    { dia: new Date().toISOString().slice(0, 10), hora: '17:00' },
    { dia: new Date().toISOString().slice(0, 10), hora: '19:30' },
    { dia: new Date().toISOString().slice(0, 10), hora: '22:00' }
  ]
  form.value.preus = [
    { nom: 'Adult', preu: 9 },
    { nom: 'Infantil', preu: 6 }
  ]
}

const onFileChange = (e: any) => {
  const file = e.target.files[0]
  if (file) {
    selectedFile.value = file
    imagePreview.value = URL.createObjectURL(file)
  }
}

const addSession = () => form.value.sessions.push({ dia: new Date().toISOString().slice(0, 10), hora: '20:00' })
const removeSession = (index: number) => form.value.sessions.splice(index, 1)

const addPrice = () => form.value.preus.push({ nom: '', preu: 0 })
const removePrice = (index: number) => form.value.preus.splice(index, 1)

const save = async () => {
  if (form.value.aforament_total < 20 || form.value.aforament_total > 120) {
    alert('L\'aforament ha de ser entre 20 i 120 butaques.')
    return
  }

  try {
    const formData = new FormData()
    formData.append('nom', form.value.nom)
    formData.append('descripcio', form.value.descripcio || '')
    formData.append('data_hora', form.value.data_hora)
    formData.append('recinte', form.value.recinte)
    formData.append('aforament_total', String(form.value.aforament_total))
    
    if (selectedFile.value) {
      formData.append('imatge', selectedFile.value)
    }

    form.value.sessions.forEach((s, i) => {
      if (s.id) formData.append(`sessions[${i}][id]`, s.id)
      formData.append(`sessions[${i}][dia]`, s.dia)
      formData.append(`sessions[${i}][hora]`, s.hora)
    })

    form.value.preus.forEach((p, i) => {
      if (p.id) formData.append(`preus[${i}][id]`, p.id)
      formData.append(`preus[${i}][nom]`, p.nom)
      formData.append(`preus[${i}][preu]`, String(p.preu))
    })

    if (isEdit) {
      formData.append('_method', 'PUT')
    }

    const url = isEdit 
      ? `http://localhost:8000/api/admin/esdeveniments/${route.params.id}`
      : 'http://localhost:8000/api/admin/esdeveniments'
    
    await $fetch(url, {
      method: 'POST',
      body: formData
    })
    
    router.push('/admin/esdeveniments')
  } catch (e: any) {
    console.error('Error saving:', e)
    const errorMsg = e.data?.message || 'Error en guardar la pel·lícula'
    alert(errorMsg)
  }
}
</script>

<template>
  <div class="flex flex-1 text-slate-900 font-sans min-h-screen bg-slate-50">
    <AdminSidebar />
    
    <main class="flex-1 w-full p-8 lg:p-12 overflow-y-auto">
      <!-- Header -->
      <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
        <div>
          <h2 class="text-4xl font-black tracking-tighter uppercase italic text-slate-900 mb-1">
            {{ isEdit ? 'Editar Pel·lícula' : 'Nova Pel·lícula' }}
          </h2>
          <p class="text-slate-500 font-medium text-sm">Gestiona els detalls, sessions i preus de la cartellera</p>
        </div>
        <div class="flex items-center gap-4 w-full md:w-auto">
           <NuxtLink to="/admin/esdeveniments" class="px-6 py-3 text-slate-500 font-bold uppercase tracking-widest text-xs hover:text-slate-900 transition-all">
             Cancel·lar
           </NuxtLink>
           <button @click="save" class="bg-accent text-black px-10 py-3.5 rounded-xl font-black uppercase tracking-widest hover:brightness-110 transition-all shadow-md active:scale-95">
             Guardar Canvis
           </button>
        </div>
      </header>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 font-sans pb-20">
        <!-- Detalls Generals (Esquerra - 8 columnes) -->
        <section class="lg:col-span-8 bg-white shadow-md p-8 lg:p-10 rounded-2xl border border-slate-100 space-y-8">
          <div class="flex items-center gap-3 border-b border-slate-100 pb-6 mb-2">
            <div class="w-2 h-6 bg-accent rounded-full"></div>
            <h3 class="text-lg font-black uppercase tracking-tight text-slate-800">Detalls Generals</h3>
          </div>
          
          <div class="grid grid-cols-1 gap-8">
            <div class="space-y-2">
              <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Títol de la pel·lícula</label>
              <input 
                v-model="form.nom" 
                type="text" 
                placeholder="Ex: Inception" 
                class="w-full bg-white border border-slate-200 rounded-lg px-5 py-3.5 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none font-bold placeholder:text-slate-300 transition-all" 
              />
            </div>
            
            <div class="space-y-2">
              <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Descripció / Sinopsi</label>
              <textarea 
                v-model="form.descripcio" 
                rows="5" 
                placeholder="Escriu una breu descripció..."
                class="w-full bg-white border border-slate-200 rounded-lg px-5 py-3.5 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none font-medium text-slate-600 transition-all"
              ></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Aforament (20-120)</label>
                <input 
                  v-model="form.aforament_total" 
                  type="number" 
                  min="20" 
                  max="120" 
                  class="w-full bg-white border border-slate-200 rounded-lg px-5 py-3.5 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none font-bold" 
                />
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Pòster de la pel·lícula</label>
                <div class="relative group">
                    <input type="file" @change="onFileChange" accept="image/*" class="hidden" id="image-upload" />
                    <label for="image-upload" class="flex items-center justify-between w-full bg-slate-50 border border-slate-200 rounded-lg px-5 py-3.5 cursor-pointer hover:bg-slate-100 transition-all">
                        <span class="text-xs font-bold text-slate-500 truncate max-w-[200px]">{{ selectedFile ? selectedFile.name : 'Selecciona una imatge...' }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </label>
                </div>
              </div>
            </div>

            <!-- Previsualització de la Imatge -->
            <div v-if="imagePreview" class="pt-2">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-3 ml-1">Vista prèvia del pòster</p>
                <div class="relative w-40 aspect-[2/3] rounded-xl overflow-hidden border border-slate-200 shadow-sm transition-transform hover:scale-105">
                    <img :src="imagePreview.startsWith('blob:') ? imagePreview : (imagePreview.startsWith('/storage') ? 'http://localhost:8000' + imagePreview : imagePreview)" class="w-full h-full object-cover" />
                </div>
            </div>
          </div>
        </section>

        <!-- Sessions i Preus (Dreta - 4 columnes) -->
        <div class="lg:col-span-4 space-y-8">
          <!-- Secció de Sessions -->
          <section class="bg-white shadow-md p-8 rounded-2xl border border-slate-100">
            <div class="flex justify-between items-center mb-8 pb-4 border-b border-slate-50">
               <h3 class="text-md font-black uppercase tracking-tight text-slate-800">Sessions</h3>
               <button @click="addSession" class="bg-slate-50 hover:bg-slate-100 text-slate-600 px-3 py-1.5 rounded-lg font-bold text-[10px] uppercase tracking-wider transition-all border border-slate-200">
                 + Afegir
               </button>
            </div>
            <div class="space-y-3">
               <div v-for="(s, index) in form.sessions" :key="index" class="flex items-center justify-between gap-3 bg-slate-50 p-3.5 rounded-xl border border-slate-100 group transition-all hover:bg-white hover:shadow-sm">
                  <div class="flex flex-1 items-center gap-3">
                    <input v-model="s.dia" type="date" class="bg-transparent border-none outline-none font-bold text-[11px] text-slate-700 w-full" />
                    <input v-model="s.hora" type="time" class="bg-transparent border-none outline-none font-black text-[11px] text-yellow-600 w-20" />
                  </div>
                  <button @click="removeSession(index)" class="text-slate-300 hover:text-red-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
               </div>
            </div>
          </section>

          <!-- Secció de Preus -->
          <section class="bg-white shadow-md p-8 rounded-2xl border border-slate-100">
            <div class="flex justify-between items-center mb-8 pb-4 border-b border-slate-50">
               <h3 class="text-md font-black uppercase tracking-tight text-slate-800">Preus</h3>
               <button @click="addPrice" class="bg-slate-50 hover:bg-slate-100 text-slate-600 px-3 py-1.5 rounded-lg font-bold text-[10px] uppercase tracking-wider transition-all border border-slate-200">
                 + Afegir
               </button>
            </div>
            <div class="space-y-3">
               <div v-for="(p, index) in form.preus" :key="index" class="flex items-center justify-between gap-3 bg-slate-50 p-3.5 rounded-xl border border-slate-100 group transition-all hover:bg-white hover:shadow-sm">
                  <input 
                    v-model="p.nom" 
                    type="text" 
                    placeholder="Nom entrada" 
                    class="flex-1 bg-transparent border-none outline-none font-bold text-[11px] text-slate-700 placeholder:text-slate-300"
                  />
                  <div class="flex items-center gap-1.5">
                    <input v-model="p.preu" type="number" step="0.5" class="w-12 bg-transparent border-none outline-none font-black text-[11px] text-yellow-600 text-right" />
                    <span class="text-[10px] font-black text-slate-400">€</span>
                  </div>
                  <button @click="removePrice(index)" class="text-slate-300 hover:text-red-500 transition-colors ml-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
               </div>
            </div>
          </section>
        </div>
      </div>
    </main>
  </div>
</template>
