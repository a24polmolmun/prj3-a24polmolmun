<script setup lang="ts">
import { ref } from 'vue'
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

if (isEdit) {
  // Recuperar dades de la pel·lícula per editar (només al client)
  const { data } = await useFetch(`http://localhost:8000/api/admin/esdeveniments/${route.params.id}`, { server: false })
  watch(data, (newVal) => {
    if (newVal) {
      const movieData = (newVal as any).data
      form.value = {
        ...movieData,
        sessions: movieData.sessions || [],
        preus: movieData.tipus_entrades || []
      }
      imagePreview.value = movieData.imatge
    }
  }, { immediate: true })
} else {
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
  <div class="flex flex-1 text-slate-900 font-sans">
    <AdminSidebar />
    
    <main class="flex-1 w-full p-12 overflow-y-auto">
      <header class="flex flex-col md:flex-row justify-between items-center mb-12 bg-white shadow-sm p-8 rounded-2xl text-center md:text-left gap-6">
        <div class="flex flex-col items-center md:items-start">
          <h2 class="text-5xl font-black tracking-tighter uppercase italic text-accent mb-2">
            {{ isEdit ? 'Editar Pel·lícula' : 'Nova Pel·lícula' }}
          </h2>
          <p class="text-slate-500 font-medium italic">Configura els detalls generals, sessions i preus</p>
        </div>
        <div class="flex gap-4">
           <NuxtLink to="/admin/esdeveniments" class="px-8 py-4 text-slate-500 font-black uppercase tracking-widest text-xs hover:text-slate-900 transition-all">Cancel·lar</NuxtLink>
           <button @click="save" class="bg-accent text-black px-12 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-slate-100 transition-all shadow-xl">
             Guardar
           </button>
        </div>
      </header>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 font-sans">
        <!-- Detalls Generals -->
        <section class="bg-white shadow-sm p-10 rounded-[3rem] border border-slate-200 space-y-8">
          <h3 class="text-xl font-black uppercase italic tracking-tighter text-slate-400 mb-6">Detalls Generals</h3>
          
          <div class="grid grid-cols-1 gap-6">
            <div class="space-y-2">
              <label class="text-xs font-black uppercase tracking-widest text-accent ml-2">Títol</label>
              <input v-model="form.nom" type="text" class="w-full bg-white shadow-sm border border-slate-200 rounded-2xl px-6 py-4 focus:border-accent/50 outline-none font-bold" />
            </div>
            
            <div class="space-y-2">
              <label class="text-xs font-black uppercase tracking-widest text-accent ml-2">Descripció</label>
              <textarea v-model="form.descripcio" rows="4" class="w-full bg-white shadow-sm border border-slate-200 rounded-2xl px-6 py-4 focus:border-accent/50 outline-none font-medium italic"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-6">
              <div class="space-y-2">
                <label class="text-xs font-black uppercase tracking-widest text-accent ml-2">Aforament (20-120)</label>
                <input v-model="form.aforament_total" type="number" min="20" max="120" class="w-full bg-white shadow-sm border border-slate-200 rounded-2xl px-6 py-4 focus:border-accent/50 outline-none font-bold" />
              </div>
              <div class="space-y-2">
                <label class="text-xs font-black uppercase tracking-widest text-accent ml-2">Pujar Imatge (PNG/JPG)</label>
                <div class="relative group">
                    <input type="file" @change="onFileChange" accept="image/*" class="hidden" id="image-upload" />
                    <label for="image-upload" class="flex items-center justify-between w-full bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl px-6 py-4 cursor-pointer hover:border-accent/50 transition-all">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ selectedFile ? selectedFile.name : 'Triar fitxer...' }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 group-hover:text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </label>
                </div>
              </div>
            </div>

            <!-- Previsualització -->
            <div v-if="imagePreview" class="pt-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-2">Previsualització</p>
                <div class="relative w-32 aspect-[2/3] rounded-2xl overflow-hidden border-4 border-white shadow-lg">
                    <img :src="imagePreview.startsWith('blob:') ? imagePreview : (imagePreview.startsWith('/storage') ? 'http://localhost:8000' + imagePreview : imagePreview)" class="w-full h-full object-cover" />
                </div>
            </div>
          </div>
        </section>

        <!-- Sessions i Preus -->
        <div class="space-y-12">
          <!-- Sessions -->
          <section class="bg-white shadow-sm p-10 rounded-[3rem] border border-slate-200">
            <div class="flex justify-between items-center mb-8">
               <h3 class="text-xl font-black uppercase italic tracking-tighter text-slate-400">Sessions</h3>
               <button @click="addSession" class="text-accent font-black uppercase tracking-widest text-[10px] hover:text-slate-900 transition-all">+ Afegir Sessió</button>
            </div>
            <div class="space-y-4">
               <div v-for="(s, index) in form.sessions" :key="index" class="flex items-center gap-4 bg-white shadow-sm p-4 rounded-2xl border border-slate-200">
                  <input v-model="s.dia" type="date" class="flex-1 bg-transparent border-none outline-none font-bold uppercase tracking-widest text-slate-700" />
                  <input v-model="s.hora" type="time" class="w-24 bg-transparent border-none outline-none font-bold text-accent" />
                  <button @click="removeSession(index)" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-500 transition-all">✕</button>
               </div>
            </div>
          </section>

          <!-- Preus -->
          <section class="bg-white shadow-sm p-10 rounded-[3rem] border border-slate-200">
            <div class="flex justify-between items-center mb-8">
               <h3 class="text-xl font-black uppercase italic tracking-tighter text-slate-400">Preus</h3>
               <button @click="addPrice" class="text-accent font-black uppercase tracking-widest text-[10px] hover:text-slate-900 transition-all">+ Afegir Preu</button>
            </div>
            <div class="space-y-4">
               <div v-for="(p, index) in form.preus" :key="index" class="flex items-center gap-4 bg-white shadow-sm p-4 rounded-2xl border border-slate-200">
                  <input v-model="p.nom" type="text" placeholder="Categoria" class="flex-1 bg-transparent border-none outline-none font-bold uppercase tracking-widest text-slate-700" />
                  <div class="flex items-center gap-2">
                    <input v-model="p.preu" type="number" step="0.5" class="w-16 bg-transparent border-none outline-none font-bold text-accent text-right" />
                    <input type="hidden" v-model="p.id" />
                    <span class="text-accent font-black">€</span>
                  </div>
                  <button @click="removePrice(index)" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-500 transition-all">✕</button>
               </div>
            </div>
          </section>
        </div>
      </div>
    </main>
  </div>
</template>
