// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  future: {
    compatibilityVersion: 4,
  },
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  css: [
    '~/assets/css/main.css',
  ],
  modules: [
    '@pinia/nuxt',
    '@nuxtjs/tailwindcss',
  ],
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'https://prj-entrades-polmolmun.daw.inspedralbes.cat:8000/api',
      socketUrl: process.env.NUXT_PUBLIC_SOCKET_URL || 'https://prj-entrades-polmolmun.daw.inspedralbes.cat:4000',
    }
  },
  vite: {
    server: {
      allowedHosts: ['prj-entrades-polmolmun.daw.inspedralbes.cat', '77.42.78.251']
    }
  }
})
