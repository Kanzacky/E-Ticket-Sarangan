<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { Search } from 'lucide-vue-next'
import api from '@/services/api'
import DataTable from '@/components/ui/DataTable.vue'
import Pagination from '@/components/ui/Pagination.vue'

const users = ref<any[]>([])
const isLoading = ref(true)
const searchQuery = ref('')

const currentPage = ref(1)
const perPage = ref(10)
const total = ref(0)
const lastPage = ref(1)

const fetchUsers = async () => {
  try {
    isLoading.value = true
    const params = new URLSearchParams()
    params.set('page', String(currentPage.value))
    params.set('per_page', String(perPage.value))
    if (searchQuery.value.trim()) params.set('search', searchQuery.value.trim())

    const response = await api.get(`/petugas/users?${params.toString()}`)
    if (response.data.success) {
      if (response.data.meta) {
        users.value = response.data.data
        total.value = response.data.meta.total
        lastPage.value = response.data.meta.last_page
        currentPage.value = response.data.meta.current_page
      } else {
        users.value = response.data.data
        total.value = users.value.length
        lastPage.value = 1
      }
    }
  } catch (error) {
    console.error('Gagal mengambil data wisatawan', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => fetchUsers())

const handlePageChange = (page: number) => {
  currentPage.value = page
  fetchUsers()
}

watch(searchQuery, () => {
  currentPage.value = 1
  fetchUsers()
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
        :is-empty="users.length === 0"
        empty-message="Tidak ada data wisatawan."
      >
        <tr v-for="user in users" :key="user.id" class="hover:bg-[#F7F5EF]/50 transition-colors border-b border-[#E8E6DE] last:border-0">
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
        <template #pagination>
          <Pagination :current-page="currentPage" :last-page="lastPage" :total="total" :per-page="perPage" @page-change="handlePageChange" @update:perPage="v => { perPage = v; currentPage = 1; fetchUsers() }" />
        </template>
      </DataTable>
    </div>
  </div>
</template>
