<script setup lang="ts">
import { ref } from 'vue'

const isLoading = ref(false)
const users = ref<any[]>([])

const userRoleClass = 'bg-slate-100 text-slate-800 px-2 py-1 rounded text-xs'
</script>

<template>
  <PlaceholderCard
    title="Manajemen Pengguna"
    description="Daftar lengkap pengguna sistem"
  >
    <template #default>
      <div v-if="isLoading" class="flex items-center justify-center h-64">
        <div class="animate-spin h-8 w-8"></div>
      </div>

      <template v-if="users.length > 0">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-500">
            <thead class="bg-[#F7F5EF] text-xs uppercase text-slate-600">
              <tr>
                <th scope="col" class="px-6 py-4 font-bold">Nama</th>
                <th scope="col" class="px-6 py-4 font-bold">Email</th>
                <th scope="col" class="px-6 py-4 font-bold">Role</th>
                <th scope="col" class="px-6 py-4 font-bold">Telepon</th>
                <th scope="col" class="px-6 py-4 font-bold">Dibuat</th>
                <th scope="col" class="px-6 py-4 font-bold">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id" class="border-b border-slate-100">
                <td class="px-6 py-4">{{ user.name }}</td>
                <td class="px-6 py-4">{{ user.email }}</td>
                <td class="px-6 py-4">
                  <span 
                    :class="userRoleClass"
                  >
                    {{ user.role }}
                  </span>
                </td>
                <td class="px-6 py-4">{{ user.phone || '-' }}</td>
                <td class="px-6 py-4">{{ user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID') : '-' }}</td>
                <td class="px-6 py-4">
                  <button class="text-blue-600 hover:text-blue-800 text-sm">Detail</button>
                  <button class="text-red-600 hover:text-red-800 text-sm mr-2">Hapus</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>

      <template v-if="users.length === 0 && !isLoading">
        <div class="text-center py-12 text-slate-400">
          <p>Belum ada pengguna terdaftar</p>
          <p class="mt-2 text-xs">Tambah pengguna pertama melalui register</p>
        </div>
      </template>
    </template>
  </PlaceholderCard>
</template>