<script setup lang="ts">
const props = defineProps<{
  headers: string[]
  isLoading?: boolean
  isEmpty?: boolean
  emptyMessage?: string
}>()
</script>

<template>
  <div class="bg-white rounded-xl border border-[#E8E6DE] shadow-sm overflow-hidden flex flex-col">
    <!-- Optional Toolbar Slot -->
    <div v-if="$slots.toolbar" class="p-4 border-b border-[#E8E6DE] bg-[#F7F5EF]/50">
      <slot name="toolbar"></slot>
    </div>

    <!-- Table Container -->
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-[#F7F5EF] text-[#66706C] text-[11px] font-bold uppercase tracking-wider border-b border-[#E8E6DE]">
            <th v-for="header in headers" :key="header" class="px-6 py-4 whitespace-nowrap">
              {{ header }}
            </th>
          </tr>
        </thead>
        
        <tbody class="divide-y divide-[#E8E6DE]">
          <!-- Loading State (Skeleton) -->
          <template v-if="isLoading">
            <tr v-for="i in 5" :key="i" class="animate-pulse border-b border-[#E8E6DE] last:border-0">
              <td v-for="(_, index) in headers" :key="index" class="px-6 py-5">
                <div class="h-4 bg-[#E8E6DE] rounded-md w-full max-w-[80%]"></div>
              </td>
            </tr>
          </template>

          <!-- Empty State -->
          <tr v-else-if="isEmpty">
            <td :colspan="headers.length" class="px-6 py-12 text-center text-[#66706C]">
              <p class="text-sm font-medium">{{ emptyMessage || 'Tidak ada data.' }}</p>
            </td>
          </tr>

          <!-- Data Rows (Slot) -->
          <slot v-else></slot>
        </tbody>
      </table>
    </div>
    
    <!-- Optional Pagination Slot -->
    <div v-if="$slots.pagination" class="p-4 border-t border-[#E8E6DE] bg-[#F7F5EF]/30">
      <slot name="pagination"></slot>
    </div>
  </div>
</template>
