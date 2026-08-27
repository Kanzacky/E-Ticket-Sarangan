<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useFetch } from '@/composables/useFetch'

const authStore = useAuthStore()
const { data, isLoading, refresh } = useFetch<any>('/api/admin/ticket-types', {
  headers: {
    Authorization: `Bearer ${authStore.token}`
  }
})

const ticketTypes = computed(() => data.value?.data ?? [])

onMounted(() => {
  refresh()
})
</script>

<template>
  <div class="py-8">
    <div v-if="isLoading" class="h-12 w-12 flex items-center justify-center">
      <svg class="h-5 w-5 animate-spin text-[var(--color-primary)]" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="3"/>
        <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z" fill="none" stroke="currentColor" stroke-width="3"/>
      </svg>
      <span>Memuat kategori...</span>
    </div>

    <div v-else-if="ticketTypes.length === 0" class="py-16 text-center text-[var(--color-text-secondary)]">
      <div class="w-16 h-16 rounded-full bg-[var(--color-primary)]/5 flex items-center justify-center mx-auto mb-4">
        <svg class="h-8 w-8 text-[var(--color-primary)]/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M19 13h-6l-2-2L6 18l2 2H2v-4h12v4h-4z" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <p class="font-medium">Belum ada kategori tiket</p>
      <p class="mt-1 text-sm text-[var(--color-text-secondary)]/70">Kategori tiket akan muncul di sini</p>
    </div>

    <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="type in ticketTypes" :key="type.id" class="rounded-[12px] border border-[var(--color-border)] p-6 bg-white transition-all hover:border-[var(--color-secondary)]/50 hover:shadow-lg hover:shadow-[var(--color-primary)]/5">
        <h4 class="text-lg font-bold text-[var(--color-primary)]">{{ type.name }}</h4>
        <p class="mt-2 text-sm text-[var(--color-text-secondary)] leading-relaxed">{{ type.description || 'Tidak ada deskripsi' }}</p>
      </div>
    </div>
  </div>
</template>
