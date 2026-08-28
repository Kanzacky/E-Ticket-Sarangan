<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import DataTable from '@/components/ui/DataTable.vue'

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

const fetch = async () => {
  isLoading.value = true
  try {
    const res = await api.get('/admin/upgrades')
    if (res.data.success) upgrades.value = res.data.data
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal memuat upgrade tiket'
  } finally { isLoading.value = false }
}

onMounted(fetch)
const formatCurrency = (v: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v)
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-black text-[#173B35]">Upgrade Tiket</h1>
      <p class="text-sm text-[#66706C] mt-1">Monitoring upgrade kategori tiket (dari ticket_upgrades)</p>
    </div>
    <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">{{ error }}</div>
    <DataTable :headers="['ID','Ticket','Dari','Ke','Tambahan','Status','Tanggal']" :is-loading="isLoading" :is-empty="upgrades.length===0" empty-message="Belum ada upgrade">
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
    <div v-if="!isLoading && upgrades.length===0" class="p-6 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
      Fitur upgrade tiket belum digunakan pada alur orders saat ini (tabel legacy ticket_upgrades). Data akan muncul jika ada upgrade kategori.
    </div>
  </div>
</template>
