<script setup lang="ts">

const props = defineProps<{
  title: string
  value: string | number
  trend?: {
    value: number
    label: string
  }
}>()
</script>

<template>
  <div class="bg-white rounded-xl border border-[#E8E6DE] p-5 shadow-sm transition-shadow hover:shadow-md">
    <div class="flex items-center gap-4">
      <div class="w-12 h-12 rounded-full bg-[#F7F5EF] flex items-center justify-center text-[#173B35]">
        <slot name="icon"></slot>
      </div>
      <div>
        <p class="text-xs font-bold text-[#66706C] uppercase tracking-wider mb-1">{{ title }}</p>
        <p class="text-2xl font-black text-[#1D2724] leading-none">{{ value }}</p>
        
        <div v-if="trend" class="mt-2 flex items-center gap-1.5 text-[11px] font-medium">
          <span 
            :class="trend.value >= 0 ? 'text-[#4F7465]' : 'text-red-500'" 
            class="flex items-center gap-0.5"
          >
            <svg v-if="trend.value >= 0" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            <svg v-else class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
            </svg>
            {{ Math.abs(trend.value) }}%
          </span>
          <span class="text-[#66706C]">{{ trend.label }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
