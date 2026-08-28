<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import api from '@/services/api'
import { Search, Edit, Plus, Trash2, X } from 'lucide-vue-next'
import DataTable from '@/components/ui/DataTable.vue'
import Pagination from '@/components/ui/Pagination.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'

interface Accommodation {
  id: number
  name: string
  address: string
  price_per_night: number
  total_rooms: number
  available_rooms: number
  is_active: boolean
}

const accommodations = ref<Accommodation[]>([])
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
const selectedId = ref<number | null>(null)

const form = ref({
  name: '',
  description: '',
  address: '',
  phone: '',
  image_url: '',
  price_per_night: 0,
  total_rooms: 1,
  available_rooms: 1,
  is_active: true
})
const selectedImageFile = ref<File | null>(null)
const imagePreview = ref<string | null>(null)

function handleImageSelect(e: Event) {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0] || null
  selectedImageFile.value = file
  if (file) {
    imagePreview.value = URL.createObjectURL(file)
  } else {
    imagePreview.value = null
  }
}

const fetchAccommodations = async () => {
  isLoading.value = true
  error.value = ''
  try {
    const params = new URLSearchParams()
    params.set('page', String(currentPage.value))
    params.set('per_page', String(perPage.value))
    if (searchQuery.value.trim()) params.set('search', searchQuery.value.trim())
    const response = await api.get('/admin/accommodations?'+params.toString())
    if (response.data.success) {
      if (response.data.meta) {
        accommodations.value = response.data.data
        total.value = response.data.meta.total
        lastPage.value = response.data.meta.last_page
        currentPage.value = response.data.meta.current_page
      } else {
        accommodations.value = response.data.data
        total.value = accommodations.value.length
        lastPage.value = 1
      }
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Gagal memuat data penginapan'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchAccommodations()
})

let searchTimer: ReturnType<typeof setTimeout> | null = null
watch(searchQuery, () => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { currentPage.value = 1; void fetchAccommodations() }, 400)
})
function handlePageChange(p: number) { if (p<1||p>lastPage.value) return; currentPage.value=p; void fetchAccommodations() }


const filteredAccommodations = computed(() => {
  return accommodations.value.filter(a => 
    a.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
    a.address.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', minimumFractionDigits: 0
  }).format(value)
}

const openForm = async (mode: 'add' | 'edit', item?: Accommodation) => {
  formMode.value = mode
  selectedImageFile.value = null
  imagePreview.value = null
  if (mode === 'edit' && item) {
    selectedId.value = item.id
    try {
      const response = await api.get(`/admin/accommodations/${item.id}`)
      if (response.data.success) {
        const data = response.data.data
        form.value = {
          name: data.name,
          description: data.description || '',
          address: data.address || '',
          phone: data.phone || '',
          image_url: data.image_url || '',
          price_per_night: data.price_per_night,
          total_rooms: data.total_rooms,
          available_rooms: data.available_rooms,
          is_active: Boolean(data.is_active)
        }
        imagePreview.value = data.image_url || null
      }
    } catch (err) {
      alert('Gagal mengambil detail penginapan')
      return
    }
  } else {
    selectedId.value = null
    form.value = {
      name: '',
      description: '',
      address: '',
      phone: '',
      image_url: '',
      price_per_night: 0,
      total_rooms: 1,
      available_rooms: 1,
      is_active: true
    }
  }
  isFormOpen.value = true
}

const submitForm = async () => {
  isSubmitting.value = true
  try {
    const hasFile = !!selectedImageFile.value
    let response
    if (hasFile) {
      const fd = new FormData()
      fd.append('name', form.value.name)
      fd.append('description', form.value.description || '')
      fd.append('address', form.value.address)
      if (form.value.phone) fd.append('phone', form.value.phone)
      fd.append('price_per_night', String(form.value.price_per_night))
      fd.append('total_rooms', String(form.value.total_rooms))
      fd.append('available_rooms', String(form.value.available_rooms))
      fd.append('is_active', form.value.is_active ? '1' : '0')
      if (form.value.image_url && !selectedImageFile.value) fd.append('image_url', form.value.image_url)
      if (selectedImageFile.value) fd.append('image', selectedImageFile.value)
      const headers = { 'Content-Type': 'multipart/form-data' }
      if (formMode.value === 'add') {
        response = await api.post('/admin/accommodations', fd, { headers })
      } else {
        fd.append('_method', 'PATCH')
        response = await api.post(`/admin/accommodations/${selectedId.value}`, fd, { headers })
      }
    } else {
      const payload: any = { ...form.value, is_active: form.value.is_active ? 1 : 0 }
      if (formMode.value === 'add') {
        response = await api.post('/admin/accommodations', payload)
      } else {
        response = await api.patch(`/admin/accommodations/${selectedId.value}`, payload)
      }
    }
    if (response.data.success) {
      isFormOpen.value = false
      selectedImageFile.value = null
      imagePreview.value = null
      await fetchAccommodations()
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan penginapan')
  } finally {
    isSubmitting.value = false
  }
}

const deleteAccommodation = async (id: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus penginapan ini?')) return
  try {
    await api.delete(`/admin/accommodations/${id}`)
    accommodations.value = accommodations.value.filter(a => a.id !== id)
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menghapus penginapan')
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-[#173B35]">Penginapan</h1>
        <p class="text-sm font-medium text-[#66706C] mt-1">Kelola data akomodasi dan penginapan di sekitar kawasan.</p>
      </div>
      <button 
        @click="openForm('add')"
        class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-[#173B35] text-white text-sm font-bold rounded-lg hover:bg-[#112a26] transition-colors"
      >
        <Plus class="w-4 h-4" /> Tambah Penginapan
      </button>
    </div>

    <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
      {{ error }}
    </div>

    <!-- Data Table -->
    <DataTable
      :headers="['Nama Penginapan', 'Alamat', 'Harga / Malam', 'Kamar', 'Status', 'Aksi']"
      :is-loading="isLoading"
      :is-empty="filteredAccommodations.length === 0"
      empty-message="Belum ada data penginapan."
    >
      <template #toolbar>
        <div class="relative w-full sm:w-72">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <Search class="w-4 h-4 text-[#66706C]" />
          </div>
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Cari nama atau alamat..." 
            class="w-full pl-9 pr-3 py-2 text-sm border border-[#E8E6DE] rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
          >
        </div>
      </template>

      <tr v-for="item in filteredAccommodations" :key="item.id" class="hover:bg-[#F7F5EF]/50 transition-colors">
        <td class="px-6 py-4">
          <div class="text-sm font-bold text-[#1D2724]">{{ item.name }}</div>
        </td>
        <td class="px-6 py-4">
          <div class="text-sm text-[#1D2724] truncate max-w-[200px]">{{ item.address }}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="text-sm font-bold text-[#1D2724]">{{ formatCurrency(item.price_per_night) }}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="text-sm text-[#1D2724]">{{ item.available_rooms }} <span class="text-[#66706C]">/ {{ item.total_rooms }}</span></div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <StatusBadge :tone="item.is_active ? 'success' : 'neutral'">
            <span class="capitalize">{{ item.is_active ? 'Aktif' : 'Nonaktif' }}</span>
          </StatusBadge>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right">
          <div class="flex items-center justify-end gap-2">
            <button 
              @click="openForm('edit', item)"
              class="p-1.5 text-[#173B35] bg-[#173B35]/10 hover:bg-[#173B35]/20 rounded-lg transition-colors"
              title="Edit Penginapan"
            >
              <Edit class="w-4 h-4" />
            </button>
            <button 
              @click="deleteAccommodation(item.id)"
              class="p-1.5 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
              title="Hapus Penginapan"
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
          <h3 class="text-base font-bold text-[#1D2724]">{{ formMode === 'add' ? 'Tambah Penginapan' : 'Edit Penginapan' }}</h3>
          <button @click="isFormOpen = false" class="p-1.5 text-[#66706C] hover:text-[#1D2724] hover:bg-white rounded-lg transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitForm" class="flex-1 overflow-y-auto p-6 space-y-4">
          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Nama Penginapan</label>
            <input 
              v-model="form.name" 
              type="text" 
              required
              class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              placeholder="Cth: Villa Indah Sarangan"
            >
          </div>

          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Alamat Lengkap</label>
            <textarea 
              v-model="form.address" 
              rows="2" required
              class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              placeholder="Jl. Raya Telaga Sarangan No..."
            ></textarea>
          </div>

          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Deskripsi</label>
            <textarea 
              v-model="form.description" 
              rows="3"
              class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              placeholder="Deskripsi fasilitas dan keunggulan..."
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-[#1D2724] mb-1.5">No. Telepon</label>
              <input 
                v-model="form.phone" 
                type="text" 
                class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
                placeholder="08123..."
              >
            </div>
            <div>
              <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Harga / Malam (Rp)</label>
              <input 
                v-model="form.price_per_night" 
                type="number" 
                required min="0"
                class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              >
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Total Kamar</label>
              <input 
                v-model="form.total_rooms" 
                type="number" 
                required min="1"
                class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              >
            </div>
            <div>
              <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Kamar Tersedia</label>
              <input 
                v-model="form.available_rooms" 
                type="number" 
                required min="0"
                class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              >
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5">URL Gambar Sampul</label>
            <input 
              v-model="form.image_url" 
              type="url" 
              class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              placeholder="https://..."
            >
          </div>

          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Upload Gambar (opsional)</label>
            <input type="file" accept="image/jpeg,image/png,image/webp,image/jpg" @change="handleImageSelect" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#173B35] file:text-white hover:file:bg-[#112a26] file:text-xs file:font-bold" />
            <div v-if="imagePreview" class="mt-2">
              <img :src="imagePreview" alt="Preview" class="h-24 w-auto rounded-lg border border-[#E8E6DE] object-cover" />
              <p class="text-xs text-[#66706C] mt-1">Preview — akan diupload ke Storage (s3/public)</p>
            </div>
            <p class="text-xs text-[#66706C] mt-1">Jika memilih file, URL akan diabaikan.</p>
          </div>

          <div class="flex items-center gap-3 pt-2">
            <input 
              v-model="form.is_active" 
              type="checkbox" 
              id="isActive"
              class="w-4 h-4 text-[#173B35] border-[#E8E6DE] rounded focus:ring-[#173B35]"
            >
            <label for="isActive" class="text-sm font-bold text-[#1D2724] cursor-pointer">Penginapan Aktif</label>
          </div>

          <div class="pt-6">
            <button 
              type="submit" 
              :disabled="isSubmitting"
              class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-[#173B35] hover:bg-[#112a26] focus:outline-none disabled:opacity-50 transition-colors"
            >
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan Penginapan' }}
            </button>
          </div>
        </form>

      </div>
    </div>

  </div>
</template>
