<script setup lang="ts">
import { useI18n } from 'vue-i18n'

defineProps<{
  title: string
  description: string
}>()

const { t } = useI18n()
</script>

<template>
  <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
    <h2 class="text-xl font-semibold text-slate-800">{{ title }}</h2>
    <p class="mt-2 text-sm text-slate-500">{{ description }}</p>
    <p class="mt-4 text-xs uppercase tracking-wide text-slate-400">{{ t('home.status.frontend') }}</p>
  </div>
</template>
