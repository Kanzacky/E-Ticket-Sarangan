<script setup lang="ts">
import { ref } from 'vue'

const isLoading = ref(false)
const users = ref<any[]>([])

const userRoleClass = 'bg-[var(--color-primary)]/10 text-[var(--color-primary)] px-2 py-1 rounded-[6px] text-xs font-semibold uppercase tracking-wider'
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-[var(--color-primary)]">Manajemen Pengguna</h1>
        <p class="text-sm text-[var(--color-text-secondary)] mt-1">Daftar lengkap pengguna sistem</p>
      </div>
    </div>
    
    <div class="rounded-[12px] border border-[var(--color-border)] bg-white overflow-hidden">
    <div v-if="isLoading" class="flex items-center justify-center h-64">
        <div class="animate-spin h-8 w-8"></div>
      </div>

      <template v-if="users.length > 0">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-[var(--color-text-secondary)]">
            <thead class="bg-[#F7F5EF] text-xs uppercase text-[#1D2724] border-b border-[var(--color-border)]">
              <tr>
                <th scope="col" class="px-6 py-4 font-bold">Nama</th>
                <th scope="col" class="px-6 py-4 font-bold">Email</th>
                <th scope="col" class="px-6 py-4 font-bold">Role</th>
                <th scope="col" class="px-6 py-4 font-bold">Telepon</th>
                <th scope="col" class="px-6 py-4 font-bold">Dibuat</th>
                <th scope="col" class="px-6 py-4 font-bold text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id" class="border-b border-[var(--color-border)] last:border-0 hover:bg-[#F7F5EF]/50 transition-colors">
                <td class="px-6 py-4 font-medium text-[var(--color-primary)]">{{ user.name }}</td>
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
                <td class="px-6 py-4 text-right">
                  <button class="font-semibold text-[var(--color-accent)] hover:text-[#b38550] transition-colors text-sm mr-4">Detail</button>
                  <button class="font-semibold text-red-600 hover:text-red-700 transition-colors text-sm">Hapus</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>

      <template v-if="users.length === 0 && !isLoading">
        <div class="text-center py-16">
          <p class="text-[var(--color-text-secondary)] font-medium">Belum ada pengguna terdaftar</p>
          <p class="mt-2 text-sm text-[var(--color-text-secondary)]/70">Pengguna akan muncul di sini setelah mendaftar</p>
        </div>
      </template>
    </div>
  </div>
</template>