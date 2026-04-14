// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  future: {
    compatibilityVersion: 4,
  },
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  app: {
    head: {
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }
      ]
    }
  },
  css: [
    '~/assets/css/main.css',
  ],
  modules: [
    '@pinia/nuxt',
    '@nuxtjs/tailwindcss',
  ],
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'https://prj-entrades-polmolmun.daw.inspedralbes.cat/api',
      socketUrl: process.env.NUXT_PUBLIC_SOCKET_URL || 'https://prj-entrades-polmolmun.daw.inspedralbes.cat',
    }
  },
  vite: {
    server: {
      allowedHosts: ['prj-entrades-polmolmun.daw.inspedralbes.cat', '77.42.78.251']
    }
  }
})
