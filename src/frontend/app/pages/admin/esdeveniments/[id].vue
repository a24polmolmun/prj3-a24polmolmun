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

if (isEdit) {
  const { data } = await useFetch(`http://localhost:8000/api/admin/esdeveniments/${route.params.id}`)
  if (data.value) {
    const movieData = (data.value as any).data
    form.value = {
      ...movieData,
      preus: movieData.tipus_entrades || []
    }
  }
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

const addSession = () => form.value.sessions.push({ dia: new Date().toISOString().slice(0, 10), hora: '20:00' })
const removeSession = (index: number) => form.value.sessions.splice(index, 1)

const addPrice = () => form.value.preus.push({ nom: '', preu: 0 })
const removePrice = (index: number) => form.value.preus.splice(index, 1)

const save = async () => {
  try {
    const method = isEdit ? 'PUT' : 'POST'
    const url = isEdit 
      ? `http://localhost:8000/api/admin/esdeveniments/${route.params.id}`
      : 'http://localhost:8000/api/admin/esdeveniments'
      
    await $fetch(url, {
      method,
      body: form.value
    })
    
    router.push('/admin/esdeveniments')
  } catch (e) {
    alert('Error en guardar la pel·lícula')
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
                <label class="text-xs font-black uppercase tracking-widest text-accent ml-2">Aforament</label>
                <input v-model="form.aforament_total" type="number" class="w-full bg-white shadow-sm border border-slate-200 rounded-2xl px-6 py-4 focus:border-accent/50 outline-none font-bold" />
              </div>
              <div class="space-y-2">
                <label class="text-xs font-black uppercase tracking-widest text-accent ml-2">URL Imatge</label>
                <input v-model="form.imatge" type="text" class="w-full bg-white shadow-sm border border-slate-200 rounded-2xl px-6 py-4 focus:border-accent/50 outline-none font-bold" placeholder="https://..." />
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
