<template>
  <div>
    <header>
      <div class="container">
        <NuxtLink to="/" class="logo">Cinema Pol</NuxtLink>
        <nav>
          <ul>
            <li><NuxtLink to="/">Inici</NuxtLink></li>
            <li><NuxtLink to="#">Pel·lícules</NuxtLink></li>
            <li><NuxtLink to="#">Contacte</NuxtLink></li>
          </ul>
        </nav>
      </div>
    </header>

    <main class="container">
      <NuxtPage />
    </main>

    <footer>
      <div class="container">
        <p>&copy; 2026 Cinema Cat - Tots els drets reservats</p>
        <p>Desenvolupat amb Nuxt 3 i CSS Personalitzat</p>
      </div>
    </footer>
  </div>
</template>
