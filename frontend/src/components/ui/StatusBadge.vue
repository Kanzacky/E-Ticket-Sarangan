<script setup lang="ts">
import { CheckCircle2, CircleAlert, LoaderCircle } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{
  tone: 'success' | 'danger' | 'info' | 'neutral'
}>()

const config = computed(() => {
  switch (props.tone) {
    case 'success':
      return {
        classes: 'bg-green-50 text-green-700 border-green-200',
        icon: CheckCircle2,
      }
    case 'danger':
      return {
        classes: 'bg-red-50 text-red-700 border-red-200',
        icon: CircleAlert,
      }
    case 'info':
      return {
        classes: 'bg-sky-50 text-sky-700 border-sky-200',
        icon: LoaderCircle,
      }
    default:
      return {
        classes: 'bg-slate-50 text-slate-600 border-slate-200',
        icon: LoaderCircle,
      }
  }
})
</script>

<template>
  <span
    class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-sm font-medium"
    :class="config.classes"
  >
    <component :is="config.icon" class="h-4 w-4" />
    <slot />
  </span>
</template>
