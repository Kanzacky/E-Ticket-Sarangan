<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { Bell, CheckCheck, Trash2, X } from 'lucide-vue-next'
import { useNotificationStore } from '@/stores/notification'
import { deleteNotificationApi } from '@/services/notification.service'
import { useAuthStore } from '@/stores/auth'

const notificationStore = useNotificationStore()
const authStore = useAuthStore()
const isOpen = ref(false)

async function toggle() {
  isOpen.value = !isOpen.value
  if (isOpen.value) await notificationStore.fetchAll()
}

async function handleMarkAll() {
  await notificationStore.markAllAsRead()
}

async function handleMarkOne(id: number) {
  await notificationStore.markAsRead(id)
}

async function handleDelete(id: number) {
  await deleteNotificationApi(id)
  notificationStore.notifications = notificationStore.notifications.filter(n => n.id !== id)
}

function formatTime(dateStr: string) {
  return new Date(dateStr).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' })
}

onMounted(() => {
  if (authStore.isAuthenticated) notificationStore.startPolling(30000)
})
onUnmounted(() => notificationStore.stopPolling())
watch(() => authStore.isAuthenticated, (v) => {
  if (v) notificationStore.startPolling(30000)
  else notificationStore.stopPolling()
})
</script>

<template>
  <div class="relative">
    <button @click="toggle" class="relative p-2 rounded-full text-[#66706C] hover:text-[#173B35] hover:bg-[#F7F5EF] transition-colors">
      <Bell class="w-5 h-5" />
      <span v-if="notificationStore.unreadCount > 0" class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-[#C9965B] text-white text-[10px] font-bold rounded-full flex items-center justify-center border border-white">
        {{ notificationStore.unreadCount > 99 ? '99+' : notificationStore.unreadCount }}
      </span>
    </button>

    <div v-if="isOpen" class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-lg border border-[#E8E6DE] z-50 overflow-hidden">
      <div class="px-4 py-3 border-b border-[#E8E6DE] flex items-center justify-between bg-[#F7F5EF]/50">
        <h3 class="text-sm font-bold text-[#1D2724]">Notifikasi</h3>
        <div class="flex items-center gap-1">
          <button v-if="notificationStore.unreadCount > 0" @click="handleMarkAll" class="p-1.5 text-[#66706C] hover:text-[#173B35] hover:bg-white rounded-lg" title="Tandai semua dibaca">
            <CheckCheck class="w-4 h-4" />
          </button>
          <button @click="isOpen = false" class="p-1.5 text-[#66706C] hover:text-[#173B35]">
            <X class="w-4 h-4" />
          </button>
        </div>
      </div>

      <div class="max-h-96 overflow-y-auto">
        <div v-if="notificationStore.isLoading" class="p-6 text-center text-sm text-[#66706C]">Memuat...</div>
        <div v-else-if="notificationStore.notifications.length === 0" class="p-8 text-center">
          <p class="text-sm text-[#66706C]">Tidak ada notifikasi</p>
        </div>
        <div v-else class="divide-y divide-[#E8E6DE]">
          <div v-for="n in notificationStore.notifications" :key="n.id" class="px-4 py-3 hover:bg-[#F7F5EF]/50 flex gap-3" :class="{ 'bg-emerald-50/30': !n.read_at }">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-[#1D2724] truncate">{{ n.title }}</p>
              <p class="text-xs text-[#66706C] mt-0.5 line-clamp-2">{{ n.message }}</p>
              <p class="text-[11px] text-[#66706C]/70 mt-1">{{ formatTime(n.created_at) }} <span v-if="n.type" class="ml-1 px-1.5 py-0.5 bg-[#E8E6DE] rounded text-[10px]">{{ n.type }}</span></p>
            </div>
            <div class="flex flex-col gap-1 shrink-0">
              <button v-if="!n.read_at" @click="handleMarkOne(n.id)" class="p-1 text-emerald-600 hover:bg-emerald-50 rounded" title="Tandai dibaca"><CheckCheck class="w-3.5 h-3.5" /></button>
              <button @click="handleDelete(n.id)" class="p-1 text-red-500 hover:bg-red-50 rounded" title="Hapus"><Trash2 class="w-3.5 h-3.5" /></button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="isOpen" @click="isOpen = false" class="fixed inset-0 z-40"></div>
  </div>
</template>
