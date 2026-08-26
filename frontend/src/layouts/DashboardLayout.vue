<script setup lang="ts">
import {
  CalendarCheck,
  History,
  LayoutDashboard,
  LogOut,
  Menu,
  PlusCircle,
  QrCode,
  Ticket,
  Users,
} from 'lucide-vue-next'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { RouterView, useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const { t } = useI18n()
const authStore = useAuthStore()
const sidebarOpen = ref(false)

async function handleLogout() {
  await authStore.logout()
  void router.push({ name: 'login' })
}
</script>

<template>
  <div class="flex min-h-screen bg-slate-50">
    <aside
      class="fixed inset-y-0 left-0 z-40 w-64 transform bg-slate-900 text-white transition-transform lg:static lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="flex items-center gap-2 px-6 py-5 border-b border-slate-800">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-600">
          <Ticket class="h-5 w-5" />
        </div>
        <span class="text-sm font-semibold">{{ t('app.name') }}</span>
      </div>

      <nav class="mt-4 space-y-1.5 px-3">
        <router-link
          to="/"
          class="flex items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-xs font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
        >
          {{ t('nav.home') }}
        </router-link>

        <!-- Wisatawan Menus -->
        <template v-if="authStore.role === 'wisatawan'">
          <router-link
            to="/booking"
            class="flex items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-xs font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
            active-class="bg-sky-600 text-white font-semibold"
          >
            <PlusCircle class="h-4 w-4" />
            Pesan Tiket
          </router-link>

          <router-link
            to="/my-bookings"
            class="flex items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-xs font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
            active-class="bg-sky-600 text-white font-semibold"
          >
            <History class="h-4 w-4" />
            Riwayat Booking
          </router-link>
        </template>

        <!-- Petugas Menus -->
        <template v-if="authStore.role === 'petugas'">
          <router-link
            to="/petugas/scanner"
            class="flex items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-xs font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
            active-class="bg-sky-600 text-white font-semibold"
          >
            <QrCode class="h-4 w-4" />
            Scanner QR
          </router-link>

          <router-link
            to="/petugas/checkins"
            class="flex items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-xs font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
            active-class="bg-sky-600 text-white font-semibold"
          >
            <CalendarCheck class="h-4 w-4" />
            Data Check-in
          </router-link>
        </template>

        <!-- Admin Menus -->
        <template v-if="authStore.role === 'admin'">
          <router-link
            to="/admin/dashboard"
            class="flex items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-xs font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
            active-class="bg-sky-600 text-white font-semibold"
          >
            <LayoutDashboard class="h-4 w-4" />
            Dashboard
          </router-link>

          <router-link
            to="/admin/users"
            class="flex items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-xs font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
            active-class="bg-sky-600 text-white font-semibold"
          >
            <Users class="h-4 w-4" />
            Manajemen User
          </router-link>
        </template>

        <slot name="menu" />
      </nav>
    </aside>

    <div v-if="sidebarOpen" class="fixed inset-0 z-30 bg-black/50 lg:hidden" @click="sidebarOpen = false" />

    <div class="flex min-w-0 flex-1 flex-col">
      <header
        class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 lg:px-8"
      >
        <button
          class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden"
          type="button"
          @click="sidebarOpen = true"
        >
          <Menu class="h-5 w-5" />
        </button>
        <div class="flex-1" />
        <div class="flex items-center gap-3">
          <span class="hidden text-sm font-medium text-slate-700 sm:block">{{ authStore.user?.name }}</span>
          <span class="hidden rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 sm:block uppercase">
            {{ authStore.role }}
          </span>
          <button
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3.5 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-100"
            type="button"
            @click="handleLogout"
          >
            <LogOut class="h-3.5 w-3.5" />
            {{ t('nav.logout') }}
          </button>
        </div>
      </header>

      <main class="flex-1 p-4 lg:p-8">
        <slot>
          <RouterView />
        </slot>
      </main>
    </div>
  </div>
</template>
