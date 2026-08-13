<script setup lang="ts">
import { LogOut, Menu, Ticket } from 'lucide-vue-next'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const { t } = useI18n()
const authStore = useAuthStore()
const sidebarOpen = ref(false)

function handleLogout(): void {
  authStore.logout()
  void router.push({ name: 'login' })
}
</script>

<template>
  <div class="flex min-h-screen bg-slate-50">
    <aside
      class="fixed inset-y-0 left-0 z-40 w-64 transform bg-slate-900 text-white transition-transform lg:static lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="flex items-center gap-2 px-6 py-5">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-600">
          <Ticket class="h-5 w-5" />
        </div>
        <span class="text-sm font-semibold">{{ t('app.name') }}</span>
      </div>
      <nav class="mt-2 space-y-1 px-3">
        <router-link
          to="/"
          class="block rounded-lg px-3 py-2 text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white"
        >
          {{ t('nav.home') }}
        </router-link>

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
          <span class="hidden text-sm text-slate-600 sm:block">{{ authStore.user?.name }}</span>
          <button
            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100"
            type="button"
            @click="handleLogout"
          >
            <LogOut class="h-4 w-4" />
            {{ t('nav.logout') }}
          </button>
        </div>
      </header>

      <main class="flex-1 p-4 lg:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>
