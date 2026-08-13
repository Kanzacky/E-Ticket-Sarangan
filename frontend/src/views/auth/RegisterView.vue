<script setup lang="ts">
import { LoaderCircle } from 'lucide-vue-next'
import { reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  phone: '',
})

const errorMsg = ref('')

async function handleSubmit() {
  if (form.password !== form.password_confirmation) {
    errorMsg.value = 'Kata sandi tidak cocok'
    return
  }

  errorMsg.value = ''
  try {
    await authStore.register(form)
    // Wisatawan is default
    void router.push({ name: 'wisatawan.dashboard' })
  } catch (error: any) {
    if (error.response?.data?.message) {
      errorMsg.value = error.response.data.message
      
      if (error.response.data.errors) {
        // Just take the first validation error
        const firstKey = Object.keys(error.response.data.errors)[0]
        if (firstKey) {
          errorMsg.value = error.response.data.errors[firstKey][0]
        }
      }
    } else {
      errorMsg.value = 'Terjadi kesalahan saat mendaftar'
    }
  }
}
</script>

<template>
  <main class="flex min-h-screen items-center justify-center bg-gradient-to-b from-sky-50 to-white px-6 py-12">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
      <h1 class="text-2xl font-bold text-slate-900">{{ t('auth.registerTitle') }}</h1>
      
      <div v-if="errorMsg" class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-600 border border-red-100">
        {{ errorMsg }}
      </div>

      <form class="mt-6 space-y-4" @submit.prevent="handleSubmit">
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700" for="name">{{ t('auth.name') }}</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
            :disabled="authStore.isLoading"
          />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700" for="email">{{ t('auth.email') }}</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
            :disabled="authStore.isLoading"
          />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700" for="phone">{{ t('auth.phone') }}</label>
          <input
            id="phone"
            v-model="form.phone"
            type="tel"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
            :disabled="authStore.isLoading"
          />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700" for="password">{{ t('auth.password') }}</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            minlength="8"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
            :disabled="authStore.isLoading"
          />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700" for="password_confirmation">{{ t('auth.passwordConfirmation') }}</label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            required
            minlength="8"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
            :disabled="authStore.isLoading"
          />
        </div>
        <button
          type="submit"
          class="flex w-full items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-700 disabled:opacity-70"
          :disabled="authStore.isLoading"
        >
          <LoaderCircle v-if="authStore.isLoading" class="h-4 w-4 animate-spin" />
          {{ t('auth.registerTitle') }}
        </button>
      </form>

      <div class="mt-6 text-center text-sm text-slate-600">
        {{ t('auth.hasAccount') }}
        <router-link to="/login" class="font-medium text-sky-600 hover:text-sky-700">{{ t('auth.loginTitle') }}</router-link>
      </div>

      <div class="mt-4 text-center text-sm">
        <router-link to="/" class="font-medium text-slate-400 hover:text-slate-600">{{ t('common.back') }}</router-link>
      </div>
    </div>
  </main>
</template>
