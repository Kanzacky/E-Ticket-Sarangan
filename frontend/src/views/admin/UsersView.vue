<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'
import { Search, Eye, Filter, Trash2, X, Plus, Edit } from 'lucide-vue-next'
import DataTable from '@/components/ui/DataTable.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import Pagination from '@/components/ui/Pagination.vue'

interface User {
  id: number
  name: string
  email: string
  role: string
  phone: string
  created_at: string
}

const users = ref<User[]>([])
const isLoading = ref(true)
const error = ref('')

const route = useRoute()
const isPetugasPage = computed(() => route.name === 'admin.petugas')

const searchQuery = ref('')
const filterRole = ref('all')
const currentPage = ref(1)
const perPage = ref(10)
const total = ref(0)
const lastPage = ref(1)

const pageTitle = computed(() => isPetugasPage.value ? 'Petugas & Admin' : 'Wisatawan')
const pageDescription = computed(() => isPetugasPage.value ? 'Kelola data petugas dan administrator sistem.' : 'Kelola data wisatawan terdaftar pada sistem e-Ticket Sarangan.')

const isDetailOpen = ref(false)
const isFormOpen = ref(false)
const formMode = ref<'add' | 'edit'>('add')
const selectedUser = ref<User | null>(null)
const isSubmitting = ref(false)

const form = ref({
  id: 0,
  name: '',
  email: '',
  password: '',
  role: 'petugas'
})

const fetchUsers = async () => {
  isLoading.value = true
  error.value = ''
  try {
    const params = new URLSearchParams()
    params.set('page', String(currentPage.value))
    params.set('per_page', String(perPage.value))
    if (searchQuery.value.trim()) params.set('search', searchQuery.value.trim())
    const response = await api.get(`/admin/users?${params.toString()}`)
    if (response.data.success) {
      // support both paginated (data + meta) and legacy (data array)
      if (response.data.meta && Array.isArray(response.data.data)) {
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
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Gagal memuat data pengguna'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => { fetchUsers() })

let searchTimer: ReturnType<typeof setTimeout> | null = null
watch(searchQuery, () => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { currentPage.value = 1; void fetchUsers() }, 400)
})

function handlePageChange(page: number) {
  if (page < 1 || page > lastPage.value) return
  currentPage.value = page
  void fetchUsers()
}

const filteredUsers = computed(() => {
  return users.value.filter(user => {
    if (isPetugasPage.value) {
      if (user.role === 'wisatawan') return false
    } else {
      if (user.role !== 'wisatawan') return false
    }
    const matchesRole = filterRole.value === 'all' || user.role === filterRole.value
    return matchesRole
  })
})

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'long', year: 'numeric'
  })
}

const openDetail = (user: User) => {
  selectedUser.value = user
  isDetailOpen.value = true
}

const deleteUser = async (id: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) return
  try {
    await api.delete(`/admin/users/${id}`)
    if (selectedUser.value?.id === id) { isDetailOpen.value = false; selectedUser.value = null }
    await fetchUsers()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menghapus pengguna')
  }
}

const openForm = (mode: 'add' | 'edit', user?: User) => {
  formMode.value = mode
  if (mode === 'edit' && user) {
    form.value = {
      id: user.id,
      name: user.name,
      email: user.email,
      password: '', // Leave blank for edit unless changing
      role: user.role
    }
  } else {
    form.value = { id: 0, name: '', email: '', password: '', role: 'petugas' }
  }
  isFormOpen.value = true
}

const submitForm = async () => {
  isSubmitting.value = true
  try {
    if (formMode.value === 'add') {
      const response = await api.post('/admin/users', form.value)
      if (response.data.success) { isFormOpen.value = false; await fetchUsers() }
    } else {
      const payload: any = { name: form.value.name, email: form.value.email, role: form.value.role }
      if (form.value.password) payload.password = form.value.password
      const response = await api.patch(`/admin/users/${form.value.id}`, payload)
      if (response.data.success) { isFormOpen.value = false; await fetchUsers() }
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan data pengguna')
  } finally { isSubmitting.value = false }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-[#173B35]">{{ pageTitle }}</h1>
        <p class="text-sm font-medium text-[#66706C] mt-1">{{ pageDescription }}</p>
      </div>
      <button 
        v-if="isPetugasPage"
        @click="openForm('add')"
        class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-[#173B35] text-white text-sm font-bold rounded-lg hover:bg-[#112a26] transition-colors"
      >
        <Plus class="w-4 h-4" /> Tambah Petugas
      </button>
    </div>

    <!-- Error State -->
    <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
      {{ error }}
    </div>

    <!-- Main Content -->
    <DataTable
      :headers="isPetugasPage 
        ? ['Nama & Email', 'Role', 'Terdaftar', 'Aksi'] 
        : ['Nama & Email', 'Role', 'No. Telepon', 'Terdaftar', 'Aksi']"
      :is-loading="isLoading"
      :is-empty="filteredUsers.length === 0"
      empty-message="Belum ada data pengguna yang sesuai dengan pencarian."
    >
      <template #toolbar>
        <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
          <div class="relative w-full sm:w-72">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Search class="w-4 h-4 text-[#66706C]" />
            </div>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Cari nama atau email..." 
              class="w-full pl-9 pr-3 py-2 text-sm border border-[#E8E6DE] rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
            >
          </div>
          
          <div v-if="isPetugasPage" class="flex items-center gap-2 w-full sm:w-auto">
            <Filter class="w-4 h-4 text-[#66706C]" />
            <select 
              v-model="filterRole"
              class="w-full sm:w-auto text-sm border border-[#E8E6DE] rounded-lg bg-white px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
            >
              <option value="all">Semua Role</option>
              <option value="petugas">Petugas</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>
      </template>

      <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-[#F7F5EF]/50 transition-colors">
        <td class="px-6 py-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-[#173B35]/10 flex items-center justify-center text-[#173B35] font-bold text-sm uppercase shrink-0">
              {{ user.name.charAt(0) }}
            </div>
            <div>
              <div class="text-sm font-bold text-[#1D2724]">{{ user.name }}</div>
              <div class="text-xs text-[#66706C]">{{ user.email }}</div>
            </div>
          </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <StatusBadge :tone="user.role === 'admin' ? 'danger' : user.role === 'petugas' ? 'info' : 'success'">
            <span class="capitalize">{{ user.role }}</span>
          </StatusBadge>
        </td>
        <td v-if="!isPetugasPage" class="px-6 py-4 whitespace-nowrap text-sm text-[#1D2724]">
          {{ user.phone || '-' }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="text-sm text-[#1D2724]">{{ formatDate(user.created_at) }}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right">
          <div class="flex items-center justify-end gap-2">
            <button 
              @click="openDetail(user)"
              class="p-1.5 text-[#173B35] bg-[#173B35]/10 hover:bg-[#173B35]/20 rounded-lg transition-colors"
              title="Detail Pengguna"
            >
              <Eye class="w-4 h-4" />
            </button>
            <button 
              v-if="isPetugasPage"
              @click="openForm('edit', user)"
              class="p-1.5 text-[#173B35] bg-[#173B35]/10 hover:bg-[#173B35]/20 rounded-lg transition-colors"
              title="Edit Petugas"
            >
              <Edit class="w-4 h-4" />
            </button>
            <button 
              v-if="isPetugasPage && user.role !== 'admin'"
              @click="deleteUser(user.id)"
              class="p-1.5 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
              title="Hapus Petugas"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </td>
      </tr>
    </DataTable>
    <Pagination :current-page="currentPage" :last-page="lastPage" :total="total" :per-page="perPage" @page-change="handlePageChange" />

    <!-- Detail Drawer -->
    <div v-if="isDetailOpen && selectedUser" class="fixed inset-0 z-50 overflow-hidden">
      <div class="absolute inset-0 bg-[#1D2724]/40 backdrop-blur-sm transition-opacity" @click="isDetailOpen = false"></div>
      <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-xl transform transition-transform duration-300 ease-in-out flex flex-col">
        
        <!-- Drawer Header -->
        <div class="px-6 py-4 border-b border-[#E8E6DE] flex items-center justify-between shrink-0 bg-[#F7F5EF]">
          <div>
            <h3 class="text-base font-bold text-[#1D2724]">Detail Pengguna</h3>
          </div>
          <button @click="isDetailOpen = false" class="p-1.5 text-[#66706C] hover:text-[#1D2724] hover:bg-white rounded-lg transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Drawer Content -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
          <div class="flex items-center gap-4 p-4 bg-[#F7F5EF] rounded-xl border border-[#E8E6DE]">
            <div class="w-14 h-14 rounded-full bg-[#173B35] flex items-center justify-center text-white font-bold text-xl uppercase shadow-inner">
              {{ selectedUser.name.charAt(0) }}
            </div>
            <div>
              <p class="text-lg font-black text-[#173B35] leading-tight">{{ selectedUser.name }}</p>
              <p class="text-sm text-[#66706C] mt-0.5">{{ selectedUser.email }}</p>
            </div>
          </div>

          <div class="space-y-3">
            <div class="grid grid-cols-3 gap-2 py-3 border-b border-[#E8E6DE]">
              <div class="col-span-1 text-xs font-bold text-[#66706C] uppercase tracking-wider">Role</div>
              <div class="col-span-2">
                <StatusBadge :tone="selectedUser.role === 'admin' ? 'danger' : selectedUser.role === 'petugas' ? 'info' : 'success'">
                  <span class="capitalize">{{ selectedUser.role }}</span>
                </StatusBadge>
              </div>
            </div>
            <div class="grid grid-cols-3 gap-2 py-3 border-b border-[#E8E6DE]">
              <div class="col-span-1 text-xs font-bold text-[#66706C] uppercase tracking-wider">No. Telepon</div>
              <div class="col-span-2 text-sm font-medium text-[#1D2724]">{{ selectedUser.phone || '-' }}</div>
            </div>
            <div class="grid grid-cols-3 gap-2 py-3 border-b border-[#E8E6DE]">
              <div class="col-span-1 text-xs font-bold text-[#66706C] uppercase tracking-wider">Terdaftar</div>
              <div class="col-span-2 text-sm font-medium text-[#1D2724]">{{ formatDate(selectedUser.created_at) }}</div>
            </div>
          </div>
          
          <!-- Actions -->
          <div v-if="selectedUser.role !== 'admin'" class="pt-4 border-t border-[#E8E6DE]">
            <p class="text-xs font-bold text-[#1D2724] uppercase tracking-wider mb-3">Zona Berbahaya</p>
            <button 
              @click="deleteUser(selectedUser.id)"
              class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-bold text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 rounded-lg transition-colors"
            >
              <Trash2 class="w-4 h-4" /> Hapus Pengguna
            </button>
          </div>

        </div>
      </div>
    </div>

    <!-- Add/Edit Form Drawer -->
    <div v-if="isFormOpen" class="fixed inset-0 z-50 overflow-hidden">
      <div class="absolute inset-0 bg-[#1D2724]/40 backdrop-blur-sm transition-opacity" @click="isFormOpen = false"></div>
      <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-xl transform transition-transform duration-300 ease-in-out flex flex-col">
        
        <div class="px-6 py-4 border-b border-[#E8E6DE] flex items-center justify-between shrink-0 bg-[#F7F5EF]">
          <h3 class="text-base font-bold text-[#1D2724]">{{ formMode === 'add' ? 'Tambah Petugas Baru' : 'Edit Petugas' }}</h3>
          <button @click="isFormOpen = false" class="p-1.5 text-[#66706C] hover:text-[#1D2724] hover:bg-white rounded-lg transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitForm" class="flex-1 overflow-y-auto p-6 space-y-4">
          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Nama Lengkap</label>
            <input 
              v-model="form.name" 
              type="text" 
              required
              class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              placeholder="Nama Petugas"
            >
          </div>

          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Email Akses</label>
            <input 
              v-model="form.email" 
              type="email" 
              required
              class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              placeholder="petugas@sarangan.test"
            >
          </div>

          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Role (Peran)</label>
            <select 
              v-model="form.role" 
              required
              class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
            >
              <option value="petugas">Petugas Loket</option>
              <option value="admin">Administrator</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Kata Sandi {{ formMode === 'edit' ? '(Opsional)' : '' }}</label>
            <input 
              v-model="form.password" 
              type="password" 
              :required="formMode === 'add'" minlength="6"
              class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              :placeholder="formMode === 'edit' ? 'Biarkan kosong jika tidak diubah' : 'Minimal 6 karakter'"
            >
          </div>

          <div class="pt-6">
            <button 
              type="submit" 
              :disabled="isSubmitting"
              class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-[#173B35] hover:bg-[#112a26] focus:outline-none disabled:opacity-50 transition-colors"
            >
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan Petugas' }}
            </button>
          </div>
        </form>

      </div>
    </div>

  </div>
</template>