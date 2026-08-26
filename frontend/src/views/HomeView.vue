<script setup lang="ts">
import { History, LoaderCircle, MountainSnow, PlusCircle, Ticket } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'

import StatusBadge from '@/components/ui/StatusBadge.vue'
import { useApiHealth } from '@/composables/useApiHealth'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const authStore = useAuthStore()
const { status, data, message } = useApiHealth()
</script>

<template>
  <main class="flex min-h-screen flex-col bg-gradient-to-b from-sky-50 via-white to-emerald-50">
    <header class="flex items-center justify-between px-6 py-4">
      <div class="flex items-center gap-2">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-600 text-white">
          <Ticket class="h-5 w-5" />
        </div>
        <span class="text-lg font-semibold text-slate-800">{{ t('app.name') }}</span>
      </div>

      <nav class="flex items-center gap-3">
        <template v-if="authStore.isAuthenticated">
          <router-link
            to="/my-bookings"
            class="flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:bg-sky-50 hover:text-sky-700"
          >
            <History class="h-4 w-4" />
            Tiket Saya
          </router-link>
          <router-link
            to="/booking"
            class="flex items-center gap-1.5 rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-sky-700"
          >
            <PlusCircle class="h-4 w-4" />
            Pesan Tiket
          </router-link>
        </template>
        <template v-else>
          <router-link
            to="/login"
            class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-sky-100 hover:text-sky-700"
          >
            {{ t('nav.login') }}
          </router-link>
          <router-link
            to="/register"
            class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-sky-700"
          >
            {{ t('nav.register') }}
          </router-link>
        </template>
      </nav>
    </header>

    <section class="mx-auto flex w-full max-w-3xl flex-1 flex-col items-center justify-center px-6 py-12 text-center">
      <div
        class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-emerald-500 text-white shadow-lg shadow-sky-200"
      >
        <MountainSnow class="h-8 w-8" />
      </div>
      <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-5xl">{{ t('home.title') }}</h1>
      <p class="mt-3 max-w-xl text-base text-slate-600 sm:text-lg">{{ t('home.subtitle') }}</p>
      <span class="mt-4 rounded-full bg-sky-100 px-4 py-1.5 text-sm font-semibold text-sky-700">
        {{ t('app.inotek') }}
      </span>

      <!-- Action Buttons CTA -->
      <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
        <router-link
          to="/booking"
          class="flex items-center gap-2 rounded-xl bg-sky-600 px-6 py-3 text-base font-semibold text-white shadow-md shadow-sky-200 transition hover:bg-sky-700"
        >
          <PlusCircle class="h-5 w-5" />
          Pesan Tiket Sekarang
        </router-link>
        <router-link
          to="/my-bookings"
          class="flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-6 py-3 text-base font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
        >
          <Ticket class="h-5 w-5 text-sky-600" />
          Riwayat Booking
        </router-link>
      </div>

      <!-- System Status Card -->
      <div class="mt-10 w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 text-left shadow-sm">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ t('home.status.title') }}</h2>

        <div class="mt-4 flex items-center justify-between">
          <span class="text-sm text-slate-600">{{ t('home.status.frontend') }}</span>
          <StatusBadge tone="success">{{ t('home.status.connected') }}</StatusBadge>
        </div>

        <div class="mt-3 flex items-center justify-between">
          <span class="text-sm text-slate-600">{{ t('home.status.api') }}</span>
          <StatusBadge v-if="status === 'checking'" tone="info">
            <LoaderCircle class="h-3.5 w-3.5 animate-spin" /> {{ t('common.loading') }}
          </StatusBadge>
          <StatusBadge v-else-if="status === 'connected'" tone="success">{{ t('home.status.connected') }}</StatusBadge>
          <StatusBadge v-else tone="danger">{{ t('home.status.disconnected') }}</StatusBadge>
        </div>

        <div v-if="status === 'connected'" class="mt-3 flex items-center justify-between">
          <span class="text-sm text-slate-600">{{ t('home.status.database') }}</span>
          <StatusBadge :tone="data?.database === 'connected' ? 'success' : 'danger'">
            {{ data?.database === 'connected' ? t('home.status.connected') : t('home.status.disconnected') }}
          </StatusBadge>
        </div>

        <p v-if="message" class="mt-4 break-all text-xs text-slate-400">{{ message }}</p>
      </div>
    </section>

    <footer class="py-6 text-center text-sm text-slate-400">{{ t('app.name') }} &middot; {{ t('app.inotek') }}</footer>
  </main>
</template>
