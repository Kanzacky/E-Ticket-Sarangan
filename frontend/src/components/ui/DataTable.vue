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
          <!-- Loading State -->
          <tr v-if="isLoading">
            <td :colspan="headers.length" class="px-6 py-12 text-center text-[#66706C]">
              <div class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-5 w-5 text-[#173B35]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-medium">Memuat data...</span>
              </div>
            </td>
          </tr>

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
