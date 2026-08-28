<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import DataTable from '@/components/ui/DataTable.vue'
import Pagination from '@/components/ui/Pagination.vue'

const { t } = useI18n()

interface Upgrade {
  id: number
  ticket_id: number
  additional_amount: number
  status: string
  created_at: string
  ticket: any
  old_category: { name: string } | null
  new_category: { name: string } | null
}

const upgrades = ref<Upgrade[]>([])
const isLoading = ref(true)
const error = ref('')
const currentPage = ref(1)
const perPage = ref(10)
const total = ref(0)
const lastPage = ref(1)
const searchQuery = ref('')

const fetch = async () => {
  isLoading.value = true
  try {
    const params = new URLSearchParams()
    params.set('page', String(currentPage.value))
    params.set('per_page', String(perPage.value))
    if (searchQuery.value.trim()) params.set('search', searchQuery.value.trim())
    const res = await api.get('/admin/upgrades?'+params.toString())
    if (res.data.success) if (res.data.meta) {
        upgrades.value = res.data.data
        total.value = res.data.meta.total
        lastPage.value = res.data.meta.last_page
        currentPage.value = res.data.meta.current_page
      } else {
        upgrades.value = res.data.data
        total.value = upgrades.value.length
        lastPage.value = 1
      }
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal memuat upgrade tiket'
  } finally { isLoading.value = false }
}

onMounted(fetch)

let searchTimer: ReturnType<typeof setTimeout> | null = null
watch(searchQuery, () => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { currentPage.value = 1; void fetch() }, 400)
})
function handlePageChange(p: number) { if (p<1||p>lastPage.value) return; currentPage.value=p; void fetch() }

const formatCurrency = (v: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v)
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-black text-[#173B35]">{{ t('upgrade.title') }}</h1>
      <p class="text-sm text-[#66706C] mt-1">{{ t('upgrade.desc') }}</p>
    </div>
    <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">{{ error }}</div>
    <DataTable :headers="['ID','Ticket','Dari','Ke','Tambahan','Status','Tanggal']" :is-loading="isLoading" :is-empty="upgrades.length===0" :empty-message="t('upgrade.empty')">
      <template #toolbar>
        <div class="relative w-full sm:w-72">
          <input v-model="searchQuery" type="text" placeholder="Cari..." class="w-full pl-9 pr-3 py-2 text-sm border border-[#E8E6DE] rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-[#173B35]" />
        </div>
      </template>
      <tr v-for="u in upgrades" :key="u.id" class="hover:bg-[#F7F5EF]/50">
        <td class="px-6 py-3 text-sm">#{{ u.id }}</td>
        <td class="px-6 py-3 text-sm">{{ u.ticket_id }}</td>
        <td class="px-6 py-3 text-sm">{{ u.old_category?.name || '-' }}</td>
        <td class="px-6 py-3 text-sm">{{ u.new_category?.name || '-' }}</td>
        <td class="px-6 py-3 text-sm">{{ formatCurrency(Number(u.additional_amount)) }}</td>
        <td class="px-6 py-3 text-xs capitalize">{{ u.status }}</td>
        <td class="px-6 py-3 text-xs">{{ new Date(u.created_at).toLocaleDateString('id-ID') }}</td>
      </tr>
    </DataTable>
    <Pagination :current-page="currentPage" :last-page="lastPage" :total="total" :per-page="perPage" @page-change="handlePageChange" />
    <div v-if="!isLoading && upgrades.length===0" class="p-6 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800 flex gap-3">
      <span class="text-lg">ℹ️</span>
      <span>{{ t('upgrade.legacy_note') }}</span>
    </div>
  </div>
</template>
