<script setup lang="ts">
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<template>
  <main class="flex min-h-screen flex-col items-center justify-center bg-slate-50 px-6 text-center">
    <h1 class="text-6xl font-bold text-sky-600">404</h1>
    <p class="mt-2 text-lg font-medium text-slate-800">Halaman tidak ditemukan</p>
    <router-link to="/" class="mt-6 rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">
      {{ t('common.back') }}
    </router-link>
  </main>
</template>
