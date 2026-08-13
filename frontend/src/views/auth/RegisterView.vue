<script setup lang="ts">
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<template>
  <main class="flex min-h-screen items-center justify-center bg-gradient-to-b from-sky-50 to-white px-6 py-12">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
      <h1 class="text-2xl font-bold text-slate-900">{{ t('auth.registerTitle') }}</h1>
      <p class="mt-2 text-sm text-slate-500">{{ t('auth.comingSoon') }}</p>

      <form class="mt-6 space-y-4" @submit.prevent>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700" for="name">{{ t('auth.name') }}</label>
          <input
            id="name"
            type="text"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
            disabled
          />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700" for="email">{{ t('auth.email') }}</label>
          <input
            id="email"
            type="email"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
            disabled
          />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700" for="password">{{ t('auth.password') }}</label>
          <input
            id="password"
            type="password"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
            disabled
          />
        </div>
        <button
          type="submit"
          class="w-full rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white opacity-60"
          disabled
        >
          {{ t('auth.registerTitle') }}
        </button>
      </form>

      <div class="mt-4 text-center text-sm">
        <router-link to="/" class="font-medium text-sky-600 hover:text-sky-700">{{ t('common.back') }}</router-link>
      </div>
    </div>
  </main>
</template>
