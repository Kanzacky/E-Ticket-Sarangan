<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useFetch } from '@/composables/useFetch'

const authStore = useAuthStore()
const { data, error, isLoading, refresh } = useFetch('/api/admin/ticket-types', {
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
      <svg class="h-5 w-5 animate-spin text-sky-500" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="3"/>
        <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z" fill="none" stroke="currentColor" stroke-width="3"/>
      </svg>
      <span>Memuat kategori...</span>
    </div>

    <div v-else-if="ticketTypes.length === 0" class="py-8 text-center text-sm text-slate-500">
      <svg class="h-6 w-6 mx-auto mb-3 text-slate-300" viewBox="0 0 24 24">
        <path d="M19 13h-6l-2-2L6 18l2 2H2v-4h12v4h-4z" />
      </svg>
      <p>Belum ada kategori tiket</p>
      <p class="mt-1 text-xs text-slate-400">Kategori tiket akan tersedia pada fase berikutnya.</p>
    </div>

    <div v-else class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="type in ticketTypes" :key="type.id" class="rounded-xl border border-slate-200 p-4 hover:bg-[#F7F5EF]">
        <h4 class="font-medium text-slate-900">{{ type.name }}</h4>
        <p class="text-sm text-slate-500">{{ type.description || '' }}</p>
      </div>
    </div>
  </div>
</template>
