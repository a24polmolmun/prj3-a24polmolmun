<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const username = ref('')
const password = ref('')
const error = ref('')

const handleLogin = () => {
    // Autenticació simple hardcoded per petició de l'usuari
    if (username.value === 'admin' && password.value === 'admin123') {
        localStorage.setItem('admin_auth', 'true')
        router.push('/admin/dashboard')
    } else {
        error.value = 'Usuari o contrasenya incorrectes'
    }
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center p-6 font-sans">
    <div class="max-w-md w-full bg-white rounded-[3rem] p-12 shadow-sm border border-slate-100 relative">
        <div class="relative z-10">
            <header class="text-center mb-10">
                <h1 class="text-3xl font-black text-accent uppercase italic tracking-tighter mb-2">
                    Accés Administració
                </h1>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">Identifica't per gestionar el cinema</p>
            </header>

            <form @submit.prevent="handleLogin" class="space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-accent ml-2">Usuari</label>
                    <input 
                        v-model="username" 
                        type="text" 
                        placeholder="admin"
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 focus:border-accent outline-none font-bold"
                    />
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-accent ml-2">Contrasenya</label>
                    <input 
                        v-model="password" 
                        type="password" 
                        placeholder="••••••••"
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 focus:border-accent outline-none font-bold"
                    />
                </div>

                <p v-if="error" class="text-red-500 text-[10px] font-bold uppercase tracking-widest text-center">{{ error }}</p>

                <button 
                    type="submit"
                    class="w-full bg-gray-900 text-white py-5 rounded-2xl font-black uppercase tracking-widest hover:bg-accent hover:text-black transition-all shadow-xl active:scale-95"
                >
                    Entrar
                </button>
            </form>

            <div class="mt-8 text-center">
                <NuxtLink to="/" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-gray-900 transition-colors">
                    ← Tornar a la web
                </NuxtLink>
            </div>
        </div>
    </div>
  </div>
</template>

<style scoped>
.text-accent { color: #ffde00; }
.bg-accent { background-color: #ffde00; }
</style>
