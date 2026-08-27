<script setup lang="ts">
import { useI18n } from 'vue-i18n'

defineProps<{
  title: string
  description: string
}>()

const { t } = useI18n()
</script>

<template>
  <div class="rounded-[12px] border border-[var(--color-border)] bg-white p-10 flex flex-col items-center justify-center text-center min-h-[300px]">
    <div class="w-16 h-16 rounded-full bg-[var(--color-primary)]/5 flex items-center justify-center mb-6">
      <svg class="w-8 h-8 text-[var(--color-primary)]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
      </svg>
    </div>
    <h2 class="text-xl font-bold text-[var(--color-primary)]">{{ title }}</h2>
    <p class="mt-3 text-sm text-[var(--color-text-secondary)] max-w-md mx-auto leading-relaxed">{{ description }}</p>
    <div class="mt-8 px-4 py-1.5 rounded-full bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20">
      <p class="text-xs font-semibold uppercase tracking-wider text-[var(--color-accent)]">{{ t('home.status.frontend') || 'Segera Hadir' }}</p>
    </div>
  </div>
</template>
