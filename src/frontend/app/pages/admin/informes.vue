<script setup lang="ts">
import { ref, computed } from 'vue'

const { data } = await useFetch('http://localhost:8000/api/admin/stats')
const stats = computed(() => (data.value as any)?.data || {})

const totalRevenue = computed(() => {
    return stats.value.recaptacio?.reduce((acc: number, item: any) => acc + Number(item.total), 0) || 0
})
</script>

<template>
  <div class="flex min-h-screen bg-slate-950 text-white font-sans">
    <AdminSidebar />
    
    <main class="flex-1 p-12 overflow-y-auto">
      <header class="mb-12">
        <h2 class="text-5xl font-black tracking-tighter uppercase italic text-accent mb-2">Informes</h2>
        <p class="text-white/40 font-medium italic">Anàlisi detallada de la recaptació i ocupació</p>
      </header>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 font-sans">
        <!-- Revenue Breakdown -->
        <section class="lg:col-span-2 bg-slate-900 p-12 rounded-[3.5rem] border border-white/10 relative overflow-hidden">
           <h3 class="text-2xl font-black text-white uppercase italic tracking-tighter mb-10">Recaptació per Categoria</h3>
           
           <div class="space-y-8">
              <div v-for="item in stats.recaptacio" :key="item.nom" class="group">
                 <div class="flex justify-between items-end mb-3">
                    <span class="text-xs font-black uppercase tracking-widest text-white/40">{{ item.nom }}</span>
                    <span class="text-2xl font-black text-white tabular-nums">{{ item.total }}€</span>
                 </div>
                 <div class="w-full bg-white/5 h-3 rounded-full overflow-hidden border border-white/5">
                    <div 
                      class="bg-accent h-full transition-all duration-1000 group-hover:bg-white" 
                      :style="{ width: (Number(item.total) / totalRevenue * 100) + '%' }"
                    ></div>
                 </div>
              </div>

              <div class="pt-10 border-t border-white/5 flex justify-between items-center">
                 <span class="text-sm font-black uppercase tracking-widest text-white/20">Recaptació Total Acumulada</span>
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
            <div class="bg-slate-900 p-10 rounded-[3rem] border border-white/5">
               <h4 class="text-xs font-black uppercase tracking-widest text-white/40 mb-6">Resum d'Activitat</h4>
               <ul class="space-y-4">
                  <li class="flex justify-between border-b border-white/5 pb-2">
                     <span class="text-white/40 text-xs">Vendes últims 7 dies</span>
                     <span class="text-white font-bold">{{ stats.evolucio?.length || 0 }}</span>
                  </li>
                  <li class="flex justify-between border-b border-white/5 pb-2">
                     <span class="text-white/40 text-xs">Entrades Totals</span>
                     <span class="text-white font-bold">{{ stats.reserves_actives }}</span>
                  </li>
               </ul>
            </div>
        </div>

        <!-- Sales Chart -->
        <section class="lg:col-span-3 bg-slate-900 p-12 rounded-[3.5rem] border border-white/10">
           <div class="flex justify-between items-center mb-10">
              <h3 class="text-2xl font-black text-white uppercase italic tracking-tighter">Evolució de Vendes</h3>
              <div class="flex gap-2">
                 <div v-for="i in 5" :key="i" class="w-1.5 h-1.5 rounded-full bg-accent/20"></div>
              </div>
           </div>
           
           <!-- Simulated Bar Chart -->
           <div class="flex items-end justify-between h-64 gap-4 px-10 border-b border-white/5">
              <div 
                v-for="day in stats.evolucio" 
                :key="day.data" 
                class="flex-1 bg-accent/20 border-t-4 border-accent rounded-t-xl group relative cursor-crosshair hover:bg-accent/40 transition-all"
                :style="{ height: (day.total * 30) + 'px' }"
              >
                 <div class="absolute -top-12 left-1/2 -translate-x-1/2 bg-white text-black text-[10px] font-black px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-all">
                    {{ day.total }} VENUTS
                 </div>
                 <div class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-[10px] font-black text-white/20 whitespace-nowrap">
                    {{ day.data.split('-').slice(1).reverse().join('/') }}
                 </div>
              </div>
           </div>
        </section>
      </div>
    </main>
  </div>
</template>
