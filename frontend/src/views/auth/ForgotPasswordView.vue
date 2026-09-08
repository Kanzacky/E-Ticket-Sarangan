<script setup lang="ts">
import { ref } from 'vue'
import { LoaderCircle, ArrowLeft, Mail } from 'lucide-vue-next'
import api from '@/services/api'

const email = ref('')
const isLoading = ref(false)
const message = ref('')
const error = ref('')

async function handleSubmit() {
  error.value = ''
  message.value = ''
  isLoading.value = true
  try {
    const res = await api.post('/auth/forgot-password', { email: email.value })
    message.value = res.data.message || 'Link reset telah dikirim ke email Anda (cek log jika mail=log).'
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal mengirim link reset'
  } finally { isLoading.value = false }
}
</script>

<template>
  <main class="min-h-screen bg-white flex flex-col lg:flex-row">
    <div class="lg:hidden w-full h-48 relative bg-[#173B35]">
      <img src="/images/sarangan-hero-2.jpg" alt="Sarangan" class="w-full h-full object-cover opacity-70" />
    </div>
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 lg:p-24">
      <div class="w-full max-w-[440px]">
        <router-link to="/login" class="inline-flex items-center gap-2 text-sm text-[#66706C] hover:text-[#173B35] mb-8">
          <ArrowLeft class="w-4 h-4" /> Kembali ke Login
        </router-link>
        <h1 class="text-3xl font-bold text-[#173B35] mb-2">Lupa Password</h1>
        <p class="text-sm text-[#66706C] mb-6">Masukkan email Anda, kami kirim link reset.</p>

        <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl">{{ error }}</div>
        <div v-if="message" class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl">{{ message }}</div>

        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div>
            <label class="block text-sm font-medium mb-1.5">Email</label>
            <div class="relative">
              <Mail class="w-4 h-4 absolute left-3 top-3.5 text-[#66706C]" />
              <input v-model="email" type="email" required placeholder="email@example.com" class="w-full pl-9 pr-4 py-3 rounded-[10px] border border-[#E8E6DE] bg-white text-sm focus:border-[#173B35] focus:ring-4 focus:ring-[#173B35]/10 outline-none" :disabled="isLoading" />
            </div>
          </div>
          <button type="submit" :disabled="isLoading" class="w-full flex items-center justify-center gap-2 rounded-[10px] bg-[#173B35] text-white py-3 text-sm font-bold hover:bg-[#112a26] disabled:opacity-60">
            <LoaderCircle v-if="isLoading" class="w-4 h-4 animate-spin" /> Kirim Link Reset
          </button>
        </form>
      </div>
    </div>
    <div class="hidden lg:block lg:w-1/2 relative bg-[#173B35]">
      <img src="/images/sarangan-story-2.jpg" alt="Sarangan" class="h-full w-full object-cover opacity-70 mix-blend-overlay" />
    </div>
  </main>
</template>
