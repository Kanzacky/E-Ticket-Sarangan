<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import api from '@/services/api'
import { Search, Edit, Plus, Trash2, X } from 'lucide-vue-next'
import DataTable from '@/components/ui/DataTable.vue'
import Pagination from '@/components/ui/Pagination.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'

interface TicketCategory {
  id: number
  name: string
  description: string
  price: number
  min_age: number | null
  max_age: number | null
  is_active: boolean
  created_at: string
}

const categories = ref<TicketCategory[]>([])
const isLoading = ref(true)
const error = ref('')
const searchQuery = ref('')
const currentPage = ref(1)
const perPage = ref(10)
const total = ref(0)
const lastPage = ref(1)

const isFormOpen = ref(false)
const isSubmitting = ref(false)
const formMode = ref<'add' | 'edit'>('add')
const selectedCategoryId = ref<number | null>(null)

const form = ref({
  name: '',
  description: '',
  price: 0,
  min_age: null as number | null,
  max_age: null as number | null,
  is_active: true
})

const fetchCategories = async () => {
  isLoading.value = true
  error.value = ''
  try {
    const params = new URLSearchParams()
    params.set('page', String(currentPage.value))
    params.set('per_page', String(perPage.value))
    if (searchQuery.value.trim()) params.set('search', searchQuery.value.trim())
    const response = await api.get('/admin/ticket-categories?'+params.toString())
    if (response.data.success) {
      if (response.data.meta) {
        categories.value = response.data.data
        total.value = response.data.meta.total
        lastPage.value = response.data.meta.last_page
        currentPage.value = response.data.meta.current_page
      } else {
        categories.value = response.data.data
        total.value = categories.value.length
        lastPage.value = 1
      }
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Gagal memuat data paket wisata'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchCategories()
})

let searchTimer: ReturnType<typeof setTimeout> | null = null
watch(searchQuery, () => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { currentPage.value = 1; void fetchCategories() }, 400)
})
function handlePageChange(p: number) { if (p<1||p>lastPage.value) return; currentPage.value=p; void fetchCategories() }


const filteredCategories = computed(() => {
  return categories.value.filter(c => 
    c.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
    (c.description && c.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
  )
})

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', minimumFractionDigits: 0
  }).format(value)
}

const formatAge = (min: number | null, max: number | null) => {
  if (min === null && max === null) return 'Semua Umur'
  if (min !== null && max === null) return `>= ${min} tahun`
  if (min === null && max !== null) return `<= ${max} tahun`
  return `${min} - ${max} tahun`
}

const openForm = (mode: 'add' | 'edit', category?: TicketCategory) => {
  formMode.value = mode
  if (mode === 'edit' && category) {
    selectedCategoryId.value = category.id
    form.value = {
      name: category.name,
      description: category.description,
      price: category.price,
      min_age: category.min_age,
      max_age: category.max_age,
      is_active: category.is_active
    }
  } else {
    selectedCategoryId.value = null
    form.value = {
      name: '',
      description: '',
      price: 0,
      min_age: null,
      max_age: null,
      is_active: true
    }
  }
  isFormOpen.value = true
}

const submitForm = async () => {
  isSubmitting.value = true
  try {
    if (formMode.value === 'add') {
      const response = await api.post('/admin/ticket-categories', form.value)
      if (response.data.success) {
        categories.value.unshift(response.data.data)
        isFormOpen.value = false
      }
    } else if (formMode.value === 'edit' && selectedCategoryId.value) {
      const response = await api.patch(`/admin/ticket-categories/${selectedCategoryId.value}`, form.value)
      if (response.data.success) {
        const index = categories.value.findIndex(c => c.id === selectedCategoryId.value)
        if (index !== -1) {
          categories.value[index] = { ...categories.value[index], ...response.data.data }
        }
        isFormOpen.value = false
      }
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan paket wisata')
  } finally {
    isSubmitting.value = false
  }
}

const deleteCategory = async (id: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus paket wisata ini?')) return
  try {
    await api.delete(`/admin/ticket-categories/${id}`)
    categories.value = categories.value.filter(c => c.id !== id)
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menghapus paket wisata')
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-[#173B35]">Paket Wisata</h1>
        <p class="text-sm font-medium text-[#66706C] mt-1">Kelola penawaran paket wisata terpadu.</p>
      </div>
      <button 
        @click="openForm('add')"
        class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-[#173B35] text-white text-sm font-bold rounded-lg hover:bg-[#112a26] transition-colors"
      >
        <Plus class="w-4 h-4" /> Tambah Paket
      </button>
    </div>

    <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
      {{ error }}
    </div>

    <!-- Data Table -->
    <DataTable
      :headers="['Nama Paket', 'Harga', 'Kategori Umur', 'Status', 'Aksi']"
      :is-loading="isLoading"
      :is-empty="filteredCategories.length === 0"
      empty-message="Belum ada data paket wisata."
    >
      <template #toolbar>
        <div class="relative w-full sm:w-72">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <Search class="w-4 h-4 text-[#66706C]" />
          </div>
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Cari nama paket..." 
            class="w-full pl-9 pr-3 py-2 text-sm border border-[#E8E6DE] rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
          >
        </div>
      </template>

      <tr v-for="category in filteredCategories" :key="category.id" class="hover:bg-[#F7F5EF]/50 transition-colors">
        <td class="px-6 py-4">
          <div class="text-sm font-bold text-[#1D2724]">{{ category.name }}</div>
          <div class="text-xs text-[#66706C] truncate max-w-xs">{{ category.description || 'Tidak ada deskripsi' }}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="text-sm font-bold text-[#1D2724]">{{ formatCurrency(category.price) }}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="text-sm text-[#1D2724]">{{ formatAge(category.min_age, category.max_age) }}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <StatusBadge :tone="category.is_active ? 'success' : 'neutral'">
            <span class="capitalize">{{ category.is_active ? 'Aktif' : 'Nonaktif' }}</span>
          </StatusBadge>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right">
          <div class="flex items-center justify-end gap-2">
            <button 
              @click="openForm('edit', category)"
              class="p-1.5 text-[#173B35] bg-[#173B35]/10 hover:bg-[#173B35]/20 rounded-lg transition-colors"
              title="Edit Paket"
            >
              <Edit class="w-4 h-4" />
            </button>
            <button 
              @click="deleteCategory(category.id)"
              class="p-1.5 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
              title="Hapus Paket"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </td>
      </tr>
    </DataTable>
    <Pagination :current-page="currentPage" :last-page="lastPage" :total="total" :per-page="perPage" @page-change="handlePageChange" />

    <!-- Modal Form (Drawer approach) -->
    <div v-if="isFormOpen" class="fixed inset-0 z-50 overflow-hidden">
      <div class="absolute inset-0 bg-[#1D2724]/40 backdrop-blur-sm transition-opacity" @click="isFormOpen = false"></div>
      <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-xl transform transition-transform duration-300 ease-in-out flex flex-col">
        
        <div class="px-6 py-4 border-b border-[#E8E6DE] flex items-center justify-between shrink-0 bg-[#F7F5EF]">
          <h3 class="text-base font-bold text-[#1D2724]">{{ formMode === 'add' ? 'Tambah Paket Baru' : 'Edit Paket' }}</h3>
          <button @click="isFormOpen = false" class="p-1.5 text-[#66706C] hover:text-[#1D2724] hover:bg-white rounded-lg transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitForm" class="flex-1 overflow-y-auto p-6 space-y-4">
          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Nama Paket</label>
            <input 
              v-model="form.name" 
              type="text" 
              required
              class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              placeholder="Cth: Paket Keluarga Besar"
            >
          </div>

          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Deskripsi</label>
            <textarea 
              v-model="form.description" 
              rows="3" required
              class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              placeholder="Penjelasan lengkap tentang paket ini..."
            ></textarea>
          </div>

          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Harga (Rp)</label>
            <input 
              v-model="form.price" 
              type="number" 
              required min="0"
              class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
            >
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Umur Min. (Opsional)</label>
              <input 
                v-model="form.min_age" 
                type="number" min="0"
                class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
                placeholder="Kosongkan jika tidak ada"
              >
            </div>
            <div>
              <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Umur Max. (Opsional)</label>
              <input 
                v-model="form.max_age" 
                type="number" min="0"
                class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
                placeholder="Kosongkan jika tidak ada"
              >
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Status</label>
            <select 
              v-model="form.is_active"
              class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
            >
              <option :value="true">Aktif</option>
              <option :value="false">Nonaktif</option>
            </select>
          </div>

          <div class="pt-6">
            <button 
              type="submit" 
              :disabled="isSubmitting"
              class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-[#173B35] hover:bg-[#112a26] focus:outline-none disabled:opacity-50 transition-colors"
            >
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan Paket' }}
            </button>
          </div>
        </form>

      </div>
    </div>

  </div>
</template>
