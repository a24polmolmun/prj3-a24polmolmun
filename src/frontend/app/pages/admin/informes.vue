<script setup lang="ts">
import { ref, computed } from 'vue'

const { data } = await useFetch('http://localhost:8000/api/admin/stats')
const stats = computed(() => (data.value as any)?.data || {})

const totalRevenue = computed(() => {
    return stats.value.recaptacio?.reduce((acc: number, item: any) => acc + Number(item.total), 0) || 0
})
</script>

<template>
  <div class="flex flex-1 text-slate-900 font-sans">
    <AdminSidebar />
    
    <main class="flex-1 w-full p-12 overflow-y-auto">
      <header class="mb-12 bg-white shadow-sm p-8 rounded-2xl flex flex-col items-center justify-center text-center">
        <h2 class="text-5xl font-black tracking-tighter uppercase italic text-accent mb-2">Informes</h2>
        <p class="text-slate-500 font-medium italic">Anàlisi detallada de la recaptació i ocupació</p>
      </header>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 font-sans">
        <!-- Revenue Breakdown -->
        <section class="lg:col-span-2 bg-white shadow-sm p-12 rounded-[3.5rem] border border-slate-200 relative overflow-hidden">
           <h3 class="text-2xl font-black text-slate-900 uppercase italic tracking-tighter mb-10">Recaptació per Categoria</h3>
           
           <div class="space-y-8">
              <div v-for="item in stats.recaptacio" :key="item.nom" class="group">
                 <div class="flex justify-between items-end mb-3">
                    <span class="text-xs font-black uppercase tracking-widest text-slate-500">{{ item.nom }}</span>
                    <span class="text-2xl font-black text-slate-900 tabular-nums">{{ item.total }}€</span>
                 </div>
                 <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden border border-slate-200">
                    <div 
                      class="bg-accent h-full transition-all duration-1000 group-hover:bg-white" 
                      :style="{ width: (Number(item.total) / totalRevenue * 100) + '%' }"
                    ></div>
                 </div>
              </div>

              <div class="pt-10 border-t border-slate-200 flex justify-between items-center">
                 <span class="text-sm font-black uppercase tracking-widest text-slate-400">Recaptació Total Acumulada</span>
                 <span class="text-5xl font-black text-accent tabular-nums">{{ totalRevenue }}€</span>
              </div>
           </div>
        </section>

        <!-- Stats sidebar -->
        <div class="space-y-8">
            <!-- Occupancy KPI -->
            <div class="bg-accent p-10 rounded-[3rem] text-black">
               <p class="text-[10px] font-black uppercase tracking-[0.4em] mb-4">Ocupació Global</p>
               <p class="text-7xl font-black mb-2 tabular-nums">{{ stats.ocupacio }}%</p>
               <p class="text-xs font-bold uppercase tracking-widest mt-4 opacity-40 leading-tight">Eficiència d'inventari</p>
            </div>

            <!-- Summary Text -->
            <div class="bg-white shadow-sm p-10 rounded-[3rem] border border-slate-200">
               <h4 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-6">Resum d'Activitat</h4>
               <ul class="space-y-4">
                  <li class="flex justify-between border-b border-slate-200 pb-2">
                     <span class="text-slate-500 text-xs">Vendes últims 7 dies</span>
                     <span class="text-slate-900 font-bold">{{ stats.evolucio?.length || 0 }}</span>
                  </li>
                  <li class="flex justify-between border-b border-slate-200 pb-2">
                     <span class="text-slate-500 text-xs">Entrades Totals</span>
                     <span class="text-slate-900 font-bold">{{ stats.reserves_actives }}</span>
                  </li>
               </ul>
            </div>
        </div>

        <!-- Sales Chart -->
        <section class="lg:col-span-3 bg-white shadow-sm p-12 rounded-[3.5rem] border border-slate-200">
           <div class="flex justify-between items-center mb-10">
              <h3 class="text-2xl font-black text-slate-900 uppercase italic tracking-tighter">Evolució de Vendes</h3>
              <div class="flex gap-2">
                 <div v-for="i in 5" :key="i" class="w-1.5 h-1.5 rounded-full bg-accent/20"></div>
              </div>
           </div>
           
           <!-- Simulated Bar Chart -->
           <div class="flex items-end justify-center h-64 gap-8 px-10 border-b border-slate-200">
              <div 
                v-for="day in stats.evolucio" 
                :key="day.data" 
                class="min-w-[60px] max-w-[100px] w-full bg-accent/20 border-t-4 border-accent rounded-t-xl group relative cursor-crosshair hover:bg-accent/40 transition-all"
                :style="{ height: Math.max(day.total * 30, 8) + 'px' }"
              >
                 <div class="absolute -top-12 left-1/2 -translate-x-1/2 bg-white text-black text-[10px] font-black px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-all">
                    {{ day.total }} VENUTS
                 </div>
                 <div class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-[10px] font-black text-slate-400 whitespace-nowrap">
                    {{ day.data.split('-').slice(1).reverse().join('/') }}
                 </div>
              </div>
           </div>
        </section>
      </div>
    </main>
  </div>
</template>
