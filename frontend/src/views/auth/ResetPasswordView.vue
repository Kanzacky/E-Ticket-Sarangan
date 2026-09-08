<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { LoaderCircle, ArrowLeft } from 'lucide-vue-next'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()

const form = ref({
  email: (route.query.email as string) || '',
  token: (route.query.token as string) || '',
  password: '',
  password_confirmation: '',
})

const isLoading = ref(false)
const error = ref('')
const message = ref('')

onMounted(() => {
  // support /reset-password/:token?email=...
  if (route.params.token) form.value.token = route.params.token as string
})

async function handleSubmit() {
  error.value = ''; message.value = ''; isLoading.value = true
  try {
    const res = await api.post('/auth/reset-password', form.value)
    message.value = res.data.message || 'Password berhasil direset'
    setTimeout(() => router.push('/login'), 2000)
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal reset password'
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
        <h1 class="text-3xl font-bold text-[#173B35] mb-2">Reset Password</h1>
        <p class="text-sm text-[#66706C] mb-6">Masukkan token dari email dan password baru.</p>

        <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl">{{ error }}</div>
        <div v-if="message" class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl">{{ message }}</div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1.5">Email</label>
            <input v-model="form.email" type="email" required class="w-full px-4 py-3 rounded-[10px] border border-[#E8E6DE] text-sm focus:border-[#173B35] focus:ring-4 focus:ring-[#173B35]/10 outline-none" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1.5">Token</label>
            <input v-model="form.token" required placeholder="Token dari email / log" class="w-full px-4 py-3 rounded-[10px] border border-[#E8E6DE] text-sm focus:border-[#173B35] focus:ring-4 focus:ring-[#173B35]/10 outline-none" />
            <p class="text-xs text-[#66706C] mt-1">Jika mail=log, copy token dari log backend (storage/logs).</p>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1.5">Password Baru</label>
            <input v-model="form.password" type="password" required minlength="8" class="w-full px-4 py-3 rounded-[10px] border border-[#E8E6DE] text-sm focus:border-[#173B35] focus:ring-4 focus:ring-[#173B35]/10 outline-none" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1.5">Konfirmasi Password</label>
            <input v-model="form.password_confirmation" type="password" required class="w-full px-4 py-3 rounded-[10px] border border-[#E8E6DE] text-sm focus:border-[#173B35] focus:ring-4 focus:ring-[#173B35]/10 outline-none" />
          </div>
          <button type="submit" :disabled="isLoading" class="w-full flex items-center justify-center gap-2 rounded-[10px] bg-[#173B35] text-white py-3 text-sm font-bold hover:bg-[#112a26] disabled:opacity-60">
            <LoaderCircle v-if="isLoading" class="w-4 h-4 animate-spin" /> Reset Password
          </button>
        </form>
      </div>
    </div>
    <div class="hidden lg:block lg:w-1/2 relative bg-[#173B35]">
      <img src="/images/sarangan-story-2.jpg" alt="Sarangan" class="h-full w-full object-cover opacity-70 mix-blend-overlay" />
    </div>
  </main>
</template>
