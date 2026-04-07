<template>
  <div>
    <!-- Basic Header restored, but with a solid background to prevent overlaps -->
    <header class="bg-gray-900 relative z-[100] border-b border-white/5 py-4">
      <div class="container mx-auto px-6 flex items-center justify-between">
        <NuxtLink to="/" class="text-xl font-black text-white uppercase italic tracking-tighter">Cinema <span class="text-accent">Pol</span></NuxtLink>
        <nav>
          <ul class="flex gap-8">
            <li><NuxtLink to="/" class="text-xs font-bold uppercase tracking-widest text-white/60 hover:text-accent transition-colors">Inici</NuxtLink></li>
            <li><NuxtLink to="/" class="text-xs font-bold uppercase tracking-widest text-white/60 hover:text-accent transition-colors">Pel·lícules</NuxtLink></li>
            <li><NuxtLink to="#" class="text-xs font-bold uppercase tracking-widest text-white/60 hover:text-accent transition-colors">Contacte</NuxtLink></li>
          </ul>
        </nav>
      </div>
    </header>

    <main>
      <NuxtPage />
    </main>

    <footer class="bg-gray-900 py-12 border-t border-white/5">
      <div class="container mx-auto px-6 text-center">
        <p class="text-white/20 text-[10px] font-black uppercase tracking-[0.5em]">&copy; 2026 Cinema Cat - Tots els drets reservats</p>
      </div>
    </footer>
  </div>
</template>

<style>
body {
  margin: 0;
  padding: 0;
  background-color: #0f172a;
}
.text-accent { color: #ffde00; }
.bg-accent { background-color: #ffde00; }
</style>
