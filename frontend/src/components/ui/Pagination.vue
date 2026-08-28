<script setup lang="ts">
defineProps<{
  currentPage: number
  lastPage: number
  total: number
  perPage: number
}>()

const emit = defineEmits<{
  (e: 'page-change', page: number): void
}>()

function pages(current: number, last: number): (number | string)[] {
  const out: (number | string)[] = []
  if (last <= 7) { for (let i=1;i<=last;i++) out.push(i); return out }
  out.push(1)
  if (current > 3) out.push('...')
  for (let i=Math.max(2, current-1); i<=Math.min(last-1, current+1); i++) out.push(i)
  if (current < last-2) out.push('...')
  out.push(last)
  return out
}
</script>

<template>
  <div v-if="lastPage > 1" class="flex items-center justify-between gap-4 py-3">
    <p class="text-xs text-[#66706C] hidden sm:block">Total {{ total }} • Hal {{ currentPage }}/{{ lastPage }}</p>
    <div class="flex items-center gap-1 ml-auto">
      <button @click="emit('page-change', currentPage-1)" :disabled="currentPage<=1" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-[#E8E6DE] bg-white disabled:opacity-40 hover:bg-[#F7F5EF]">‹</button>
      <template v-for="(p,i) in pages(currentPage, lastPage)" :key="i">
        <span v-if="p==='...'" class="px-2 text-xs text-[#66706C]">…</span>
        <button v-else @click="emit('page-change', p as number)" :class="['min-w-[32px] px-2 py-1.5 text-xs font-bold rounded-lg border', p===currentPage ? 'bg-[#173B35] text-white border-[#173B35]' : 'bg-white border-[#E8E6DE] hover:bg-[#F7F5EF]']">{{ p }}</button>
      </template>
      <button @click="emit('page-change', currentPage+1)" :disabled="currentPage>=lastPage" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-[#E8E6DE] bg-white disabled:opacity-40 hover:bg-[#F7F5EF]">›</button>
    </div>
  </div>
</template>
