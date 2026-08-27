<script setup lang="ts">
import { LoaderCircle, Eye, EyeOff, ArrowLeft } from 'lucide-vue-next'
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: '',
})

const errorMsg = ref('')
const showPassword = ref(false)

async function handleSubmit() {
  errorMsg.value = ''
  try {
    await authStore.login(form)
    
    // Redirect based on role
    if (authStore.role === 'admin') {
      void router.push({ name: 'admin.dashboard' })
    } else if (authStore.role === 'petugas') {
      void router.push({ name: 'petugas.dashboard' })
    } else {
      void router.push({ name: 'wisatawan.dashboard' })
    }
  } catch (error: unknown) {
    if (axios.isAxiosError(error) && error.response?.data?.message) {
      errorMsg.value = error.response.data.message as string
    } else {
      errorMsg.value = 'Email atau password tidak valid.'
    }
  }
}
</script>

<template>
  <main class="min-h-screen bg-white font-sans text-[#1D2724] selection:bg-[#4F7465] selection:text-white flex flex-col lg:flex-row">
    
    <!-- Mobile/Tablet Banner (Hidden on lg) -->
    <div class="lg:hidden w-full h-48 sm:h-64 relative bg-[var(--color-primary)]">
      <img src="/images/sarangan-hero-2.jpg" alt="Sarangan" class="w-full h-full object-cover opacity-80" />
      <div class="absolute inset-0 bg-[var(--color-primary)]/20"></div>
    </div>

    <!-- Form Section -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 lg:p-24 relative">
      <div class="w-full max-w-[440px]">
        
        <!-- Back Navigation -->
        <router-link to="/" class="inline-flex items-center gap-2 text-sm font-medium text-[var(--color-text-secondary)] hover:text-[var(--color-primary)] transition-colors mb-12">
          <ArrowLeft class="w-4 h-4" />
          Kembali ke Beranda
        </router-link>

        <div>
          <h1 class="text-3xl font-bold text-[var(--color-primary)] tracking-tight mb-2">Selamat datang kembali</h1>
          <p class="text-[var(--color-text-secondary)]">Masuk untuk melanjutkan pemesanan tiket Sarangan.</p>
        </div>
        
        <div v-if="errorMsg" class="mt-8 rounded-xl bg-red-50 p-4 text-sm text-red-600 border border-red-100/50 flex items-start gap-3">
          <span class="mt-0.5">⚠️</span>
          {{ errorMsg }}
        </div>

        <form class="mt-8 space-y-5" @submit.prevent="handleSubmit">
          <div class="space-y-1.5">
            <label class="block text-sm font-medium text-[var(--color-text-primary)]" for="email">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              placeholder="Masukkan email"
              class="w-full rounded-[10px] border border-[var(--color-border)] bg-white px-4 py-3 text-sm text-[var(--color-text-primary)] placeholder:text-[var(--color-text-secondary)]/60 outline-none transition-all focus:border-[var(--color-secondary)] focus:ring-4 focus:ring-[var(--color-secondary)]/10 disabled:opacity-60 disabled:bg-[var(--color-background)]"
              :disabled="authStore.isLoading"
            />
          </div>
          
          <div class="space-y-1.5">
            <label class="block text-sm font-medium text-[var(--color-text-primary)]" for="password">Password</label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                autocomplete="current-password"
                placeholder="Masukkan password"
                class="w-full rounded-[10px] border border-[var(--color-border)] bg-white pl-4 pr-12 py-3 text-sm text-[var(--color-text-primary)] placeholder:text-[var(--color-text-secondary)]/60 outline-none transition-all focus:border-[var(--color-secondary)] focus:ring-4 focus:ring-[var(--color-secondary)]/10 disabled:opacity-60 disabled:bg-[var(--color-background)]"
                :disabled="authStore.isLoading"
              />
              <button 
                type="button" 
                class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] focus:outline-none rounded-md"
                @click="showPassword = !showPassword"
                title="Toggle password visibility"
              >
                <EyeOff v-if="showPassword" class="w-4 h-4" />
                <Eye v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div class="pt-2">
            <button
              type="submit"
              class="flex w-full items-center justify-center gap-2 rounded-[10px] bg-[var(--color-primary)] px-4 py-3 text-sm font-bold text-white transition-all hover:bg-[#122c27] active:scale-[0.98] disabled:opacity-70 disabled:pointer-events-none"
              :disabled="authStore.isLoading"
            >
              <template v-if="authStore.isLoading">
                <LoaderCircle class="h-4 w-4 animate-spin" />
                <span>Memproses...</span>
              </template>
              <template v-else>
                <span>Masuk</span>
              </template>
            </button>
          </div>
        </form>

        <div class="mt-8 text-center text-sm text-[var(--color-text-secondary)]">
          Belum punya akun?
          <router-link to="/register" class="font-bold text-[var(--color-primary)] hover:text-[var(--color-secondary)] transition-colors ml-1">
            Daftar sekarang
          </router-link>
        </div>
      </div>
    </div>

    <!-- Visual Section (Desktop) -->
    <div class="hidden lg:block lg:w-1/2 relative bg-[var(--color-primary)]">
      <img 
        src="/images/sarangan-story-2.jpg" 
        alt="Pemandangan Sarangan" 
        class="h-full w-full object-cover opacity-80 mix-blend-overlay"
      />
    </div>

  </main>
</template>
