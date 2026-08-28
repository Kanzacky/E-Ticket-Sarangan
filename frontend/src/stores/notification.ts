import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { getNotificationsApi, getUnreadCountApi, markAsReadApi, markAllAsReadApi } from '@/services/notification.service'
import type { Notification } from '@/services/notification.service'

export const useNotificationStore = defineStore('notification', () => {
  const notifications = ref<Notification[]>([])
  const unreadCount = ref(0)
  const isLoading = ref(false)
  let pollTimer: ReturnType<typeof setInterval> | null = null

  const unread = computed(() => notifications.value.filter(n => !n.read_at))

  async function fetchAll() {
    isLoading.value = true
    try {
      const [list, count] = await Promise.all([getNotificationsApi(), getUnreadCountApi()])
      notifications.value = list
      unreadCount.value = count
    } finally {
      isLoading.value = false
    }
  }

  async function fetchUnreadCount() {
    try {
      unreadCount.value = await getUnreadCountApi()
    } catch {}
  }

  async function markAsRead(id: number) {
    await markAsReadApi(id)
    const n = notifications.value.find(x => x.id === id)
    if (n) n.read_at = new Date().toISOString()
    unreadCount.value = Math.max(0, unreadCount.value - 1)
  }

  async function markAllAsRead() {
    await markAllAsReadApi()
    notifications.value.forEach(n => { n.read_at = new Date().toISOString() })
    unreadCount.value = 0
  }

  function startPolling(intervalMs = 30000) {
    if (pollTimer) return
    void fetchUnreadCount()
    pollTimer = setInterval(() => { void fetchUnreadCount() }, intervalMs)
  }

  function stopPolling() {
    if (pollTimer) {
      clearInterval(pollTimer)
      pollTimer = null
    }
  }

  return { notifications, unreadCount, unread, isLoading, fetchAll, fetchUnreadCount, markAsRead, markAllAsRead, startPolling, stopPolling }
})
