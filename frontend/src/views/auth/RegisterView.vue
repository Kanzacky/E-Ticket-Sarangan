<script setup lang="ts">
import { LoaderCircle, Eye, EyeOff, ArrowLeft } from 'lucide-vue-next'
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

const errorMsg = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)

async function handleSubmit() {
  if (form.password !== form.password_confirmation) {
    errorMsg.value = 'Kata sandi dan konfirmasi kata sandi tidak cocok.'
    return
  }

  errorMsg.value = ''
  try {
    await authStore.register(form)
    // Wisatawan is default
    void router.push({ name: 'wisatawan.dashboard' })
  } catch (error: unknown) {
    if (axios.isAxiosError(error) && error.response?.data?.message) {
      errorMsg.value = error.response.data.message as string
      
      const errors = error.response.data.errors as Record<string, string[]> | undefined
      if (errors) {
        // Just take the first validation error
        const firstKey = Object.keys(errors)[0]
        if (firstKey && errors[firstKey]) {
          errorMsg.value = errors[firstKey]?.[0] || 'Terjadi kesalahan validasi.'
        }
      }
    } else {
      errorMsg.value = 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.'
    }
  }
}
</script>

<template>
  <main class="min-h-screen bg-white font-sans text-[#1D2724] selection:bg-[#4F7465] selection:text-white flex flex-col lg:flex-row">
    
    <!-- Visual Section (Desktop) - Placed on the left this time for variety -->
    <div class="hidden lg:block lg:w-1/2 relative bg-[#1D2724] order-first">
      <img 
        src="/images/sarangan-hero-2.jpg" 
        alt="Pemandangan Sarangan" 
        class="h-full w-full object-cover opacity-90"
      />
      <div class="absolute inset-0 bg-gradient-to-br from-[#173B35]/70 to-[#1D2724]/30 mix-blend-multiply"></div>
      <div class="absolute inset-0 bg-gradient-to-t from-[#1D2724]/80 via-transparent to-transparent"></div>
    </div>

    <!-- Mobile/Tablet Banner (Hidden on lg) -->
    <div class="lg:hidden w-full h-48 sm:h-64 relative">
      <img src="/images/sarangan-hero-2.jpg" alt="Sarangan" class="w-full h-full object-cover" />
      <div class="absolute inset-0 bg-[#1D2724]/40"></div>
      <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent"></div>
    </div>

    <!-- Form Section -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 lg:p-16 relative">
      <div class="w-full max-w-[440px]">
        
        <!-- Back Navigation -->
        <router-link to="/" class="inline-flex items-center gap-2 text-sm font-medium text-[#66706C] hover:text-[#173B35] transition-colors mb-10">
          <ArrowLeft class="w-4 h-4" />
          Kembali ke Beranda
        </router-link>

        <div>
          <h1 class="text-3xl font-bold text-[#173B35] tracking-tight mb-2">Buat akun</h1>
          <p class="text-[#66706C]">Daftar untuk memesan tiket dan mengelola perjalananmu.</p>
        </div>
        
        <div v-if="errorMsg" class="mt-6 rounded-xl bg-red-50 p-4 text-sm text-red-600 border border-red-100/50 flex items-start gap-3">
          <span class="mt-0.5">⚠️</span>
          {{ errorMsg }}
        </div>

        <form class="mt-8 space-y-4" @submit.prevent="handleSubmit">
          <div class="space-y-1.5">
            <label class="block text-sm font-medium text-[#1D2724]" for="name">Nama Lengkap</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              autocomplete="name"
              placeholder="Masukkan nama lengkap"
              class="w-full rounded-xl border border-[#173B35]/20 bg-white px-4 py-3 text-sm text-[#1D2724] placeholder:text-[#66706C]/60 outline-none transition-all focus:border-[#4F7465] focus:ring-4 focus:ring-[#4F7465]/10 disabled:opacity-60 disabled:bg-[#F7F5EF]"
              :disabled="authStore.isLoading"
            />
          </div>

          <div class="space-y-1.5">
            <label class="block text-sm font-medium text-[#1D2724]" for="email">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              placeholder="Masukkan email"
              class="w-full rounded-xl border border-[#173B35]/20 bg-white px-4 py-3 text-sm text-[#1D2724] placeholder:text-[#66706C]/60 outline-none transition-all focus:border-[#4F7465] focus:ring-4 focus:ring-[#4F7465]/10 disabled:opacity-60 disabled:bg-[#F7F5EF]"
              :disabled="authStore.isLoading"
            />
          </div>

          <div class="space-y-1.5">
            <label class="block text-sm font-medium text-[#1D2724]" for="phone">Nomor HP</label>
            <input
              id="phone"
              v-model="form.phone"
              type="tel"
              autocomplete="tel"
              placeholder="Masukkan nomor handphone"
              class="w-full rounded-xl border border-[#173B35]/20 bg-white px-4 py-3 text-sm text-[#1D2724] placeholder:text-[#66706C]/60 outline-none transition-all focus:border-[#4F7465] focus:ring-4 focus:ring-[#4F7465]/10 disabled:opacity-60 disabled:bg-[#F7F5EF]"
              :disabled="authStore.isLoading"
            />
          </div>
          
          <div class="space-y-1.5">
            <label class="block text-sm font-medium text-[#1D2724]" for="password">Password</label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                minlength="8"
                autocomplete="new-password"
                placeholder="Buat password (min. 8 karakter)"
                class="w-full rounded-xl border border-[#173B35]/20 bg-white pl-4 pr-12 py-3 text-sm text-[#1D2724] placeholder:text-[#66706C]/60 outline-none transition-all focus:border-[#4F7465] focus:ring-4 focus:ring-[#4F7465]/10 disabled:opacity-60 disabled:bg-[#F7F5EF]"
                :disabled="authStore.isLoading"
              />
              <button 
                type="button" 
                class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-[#66706C] hover:text-[#1D2724] focus:outline-none rounded-md"
                @click="showPassword = !showPassword"
                title="Toggle password visibility"
              >
                <EyeOff v-if="showPassword" class="w-4 h-4" />
                <Eye v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div class="space-y-1.5">
            <label class="block text-sm font-medium text-[#1D2724]" for="password_confirmation">Konfirmasi Password</label>
            <div class="relative">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                required
                minlength="8"
                autocomplete="new-password"
                placeholder="Ketik ulang password"
                class="w-full rounded-xl border border-[#173B35]/20 bg-white pl-4 pr-12 py-3 text-sm text-[#1D2724] placeholder:text-[#66706C]/60 outline-none transition-all focus:border-[#4F7465] focus:ring-4 focus:ring-[#4F7465]/10 disabled:opacity-60 disabled:bg-[#F7F5EF]"
                :disabled="authStore.isLoading"
              />
              <button 
                type="button" 
                class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-[#66706C] hover:text-[#1D2724] focus:outline-none rounded-md"
                @click="showConfirmPassword = !showConfirmPassword"
                title="Toggle password visibility"
              >
                <EyeOff v-if="showConfirmPassword" class="w-4 h-4" />
                <Eye v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div class="pt-4">
            <button
              type="submit"
              class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#173B35] px-4 py-3.5 text-sm font-bold text-white transition-all hover:bg-[#1D2724] hover:shadow-lg hover:shadow-[#173B35]/20 active:scale-[0.98] disabled:opacity-70 disabled:pointer-events-none"
              :disabled="authStore.isLoading"
            >
              <template v-if="authStore.isLoading">
                <LoaderCircle class="h-4 w-4 animate-spin" />
                <span>Memproses...</span>
              </template>
              <template v-else>
                <span>Daftar</span>
              </template>
            </button>
          </div>
        </form>

        <div class="mt-8 text-center text-sm text-[#66706C]">
          Sudah punya akun?
          <router-link to="/login" class="font-bold text-[#173B35] hover:text-[#4F7465] transition-colors ml-1">
            Masuk
          </router-link>
        </div>
      </div>
    </div>

  </main>
</template>
