<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api'
import { Search, Edit, Plus, Trash2, X } from 'lucide-vue-next'
import DataTable from '@/components/ui/DataTable.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'

interface TicketType {
  id: number
  name: string
  description: string
  price: number
  quota: number
  status: string
  created_at: string
}

const tickets = ref<TicketType[]>([])
const isLoading = ref(true)
const error = ref('')
const searchQuery = ref('')

const isFormOpen = ref(false)
const isSubmitting = ref(false)
const formMode = ref<'add' | 'edit'>('add')
const selectedTicketId = ref<number | null>(null)

const form = ref({
  name: '',
  description: '',
  price: 0,
  quota: 0,
  status: 'active'
})

const fetchTickets = async () => {
  isLoading.value = true
  error.value = ''
  try {
    const response = await api.get('/admin/ticket-types')
    if (response.data.success) {
      tickets.value = response.data.data
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Gagal memuat data tiket'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchTickets()
})

const filteredTickets = computed(() => {
  return tickets.value.filter(t => 
    t.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
    t.description.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', minimumFractionDigits: 0
  }).format(value)
}

const openForm = (mode: 'add' | 'edit', ticket?: TicketType) => {
  formMode.value = mode
  if (mode === 'edit' && ticket) {
    selectedTicketId.value = ticket.id
    form.value = {
      name: ticket.name,
      description: ticket.description,
      price: ticket.price,
      quota: ticket.quota,
      status: ticket.status
    }
  } else {
    selectedTicketId.value = null
    form.value = {
      name: '',
      description: '',
      price: 0,
      quota: 0,
      status: 'active'
    }
  }
  isFormOpen.value = true
}

const submitForm = async () => {
  isSubmitting.value = true
  try {
    if (formMode.value === 'add') {
      const response = await api.post('/admin/ticket-types', form.value)
      if (response.data.success) {
        tickets.value.unshift(response.data.data)
        isFormOpen.value = false
      }
    } else if (formMode.value === 'edit' && selectedTicketId.value) {
      const response = await api.patch(`/admin/ticket-types/${selectedTicketId.value}`, form.value)
      if (response.data.success) {
        const index = tickets.value.findIndex(t => t.id === selectedTicketId.value)
        if (index !== -1) {
          tickets.value[index] = { ...tickets.value[index], ...response.data.data }
        }
        isFormOpen.value = false
      }
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan tiket')
  } finally {
    isSubmitting.value = false
  }
}

const deleteTicket = async (id: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus tiket ini?')) return
  try {
    await api.delete(`/admin/ticket-types/${id}`)
    tickets.value = tickets.value.filter(t => t.id !== id)
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menghapus tiket')
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-[#173B35]">Tiket Dasar</h1>
        <p class="text-sm font-medium text-[#66706C] mt-1">Kelola tiket masuk utama kawasan wisata.</p>
      </div>
      <button 
        @click="openForm('add')"
        class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-[#173B35] text-white text-sm font-bold rounded-lg hover:bg-[#112a26] transition-colors"
      >
        <Plus class="w-4 h-4" /> Tambah Tiket
      </button>
    </div>

    <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
      {{ error }}
    </div>

    <!-- Data Table -->
    <DataTable
      :headers="['Nama Tiket', 'Harga', 'Kuota', 'Status', 'Aksi']"
      :is-loading="isLoading"
      :is-empty="filteredTickets.length === 0"
      empty-message="Belum ada data tiket."
    >
      <template #toolbar>
        <div class="relative w-full sm:w-72">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <Search class="w-4 h-4 text-[#66706C]" />
          </div>
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Cari nama tiket..." 
            class="w-full pl-9 pr-3 py-2 text-sm border border-[#E8E6DE] rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
          >
        </div>
      </template>

      <tr v-for="ticket in filteredTickets" :key="ticket.id" class="hover:bg-[#F7F5EF]/50 transition-colors">
        <td class="px-6 py-4">
          <div class="text-sm font-bold text-[#1D2724]">{{ ticket.name }}</div>
          <div class="text-xs text-[#66706C] truncate max-w-xs">{{ ticket.description || 'Tidak ada deskripsi' }}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="text-sm font-bold text-[#1D2724]">{{ formatCurrency(ticket.price) }}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="text-sm text-[#1D2724]">{{ ticket.quota }} /hari</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <StatusBadge :tone="ticket.status === 'active' ? 'success' : 'neutral'">
            <span class="capitalize">{{ ticket.status }}</span>
          </StatusBadge>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right">
          <div class="flex items-center justify-end gap-2">
            <button 
              @click="openForm('edit', ticket)"
              class="p-1.5 text-[#173B35] bg-[#173B35]/10 hover:bg-[#173B35]/20 rounded-lg transition-colors"
              title="Edit Tiket"
            >
              <Edit class="w-4 h-4" />
            </button>
            <button 
              @click="deleteTicket(ticket.id)"
              class="p-1.5 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
              title="Hapus Tiket"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </td>
      </tr>
    </DataTable>

    <!-- Modal Form (Drawer approach) -->
    <div v-if="isFormOpen" class="fixed inset-0 z-50 overflow-hidden">
      <div class="absolute inset-0 bg-[#1D2724]/40 backdrop-blur-sm transition-opacity" @click="isFormOpen = false"></div>
      <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-xl transform transition-transform duration-300 ease-in-out flex flex-col">
        
        <div class="px-6 py-4 border-b border-[#E8E6DE] flex items-center justify-between shrink-0 bg-[#F7F5EF]">
          <h3 class="text-base font-bold text-[#1D2724]">{{ formMode === 'add' ? 'Tambah Tiket Baru' : 'Edit Tiket' }}</h3>
          <button @click="isFormOpen = false" class="p-1.5 text-[#66706C] hover:text-[#1D2724] hover:bg-white rounded-lg transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitForm" class="flex-1 overflow-y-auto p-6 space-y-4">
          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Nama Tiket</label>
            <input 
              v-model="form.name" 
              type="text" 
              required
              class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              placeholder="Cth: Tiket Reguler Dewasa"
            >
          </div>

          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Deskripsi Singkat</label>
            <textarea 
              v-model="form.description" 
              rows="3"
              class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              placeholder="Penjelasan singkat tiket..."
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Harga (Rp)</label>
              <input 
                v-model="form.price" 
                type="number" 
                required min="0"
                class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              >
            </div>
            <div>
              <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Kuota / Hari</label>
              <input 
                v-model="form.quota" 
                type="number" 
                required min="0"
                class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              >
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Status</label>
            <select 
              v-model="form.status"
              class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
            >
              <option value="active">Aktif</option>
              <option value="inactive">Nonaktif</option>
            </select>
          </div>

          <div class="pt-6">
            <button 
              type="submit" 
              :disabled="isSubmitting"
              class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-[#173B35] hover:bg-[#112a26] focus:outline-none disabled:opacity-50 transition-colors"
            >
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan Tiket' }}
            </button>
          </div>
        </form>

      </div>
    </div>

  </div>
</template>
