<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Search } from 'lucide-vue-next'
import api from '@/services/api'
import DataTable from '@/components/ui/DataTable.vue'

const users = ref<any[]>([])
const isLoading = ref(true)
const searchQuery = ref('')

onMounted(async () => {
  try {
    isLoading.value = true
    const response = await api.get('/petugas/users')
    if (response.data.success) {
      users.value = response.data.data
    }
  } catch (error) {
    console.error('Gagal mengambil data wisatawan', error)
  } finally {
    isLoading.value = false
  }
})

const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value
  const q = searchQuery.value.toLowerCase()
  return users.value.filter(u => 
    u.name.toLowerCase().includes(q) || 
    u.email.toLowerCase().includes(q)
  )
})

const formatDate = (dateString: string) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric'
  })
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-[#1D2724]">Daftar Wisatawan</h1>
        <p class="text-sm font-medium text-[#66706C] mt-1">Data wisatawan yang terdaftar dalam sistem.</p>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-[#E8E6DE] flex flex-col sm:flex-row gap-4">
      <div class="relative flex-1">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <Search class="w-4 h-4 text-[#66706C]" />
        </div>
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Cari nama / email..." 
          class="w-full pl-9 pr-4 py-2 bg-[#F7F5EF] border border-[#E8E6DE] rounded-xl text-sm focus:ring-2 focus:ring-[#173B35] focus:border-transparent outline-none transition-all"
        />
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-[#E8E6DE] overflow-hidden">
      <DataTable
        :headers="['Nama', 'Email', 'No. Telepon', 'Terdaftar']"
        :is-loading="isLoading"
        :is-empty="filteredUsers.length === 0"
        empty-message="Tidak ada data wisatawan."
      >
        <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-[#F7F5EF]/50 transition-colors border-b border-[#E8E6DE] last:border-0">
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-sm font-bold text-[#1D2724]">{{ user.name }}</span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-sm font-medium text-[#66706C]">{{ user.email }}</span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-sm font-medium text-[#66706C]">{{ user.phone || '-' }}</span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-sm font-medium text-[#66706C]">{{ formatDate(user.created_at) }}</span>
          </td>
        </tr>
      </DataTable>
    </div>
  </div>
</template>
