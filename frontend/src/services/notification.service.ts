import api, { type ApiResponse } from '@/services/api'

export interface Notification {
  id: number
  user_id: number
  title: string
  message: string
  type: string | null
  data: Record<string, unknown> | null
  read_at: string | null
  created_at: string
  updated_at: string
}

export const getNotificationsApi = async (): Promise<Notification[]> => {
  const res = await api.get<ApiResponse<Notification[]>>('/notifications')
  return res.data.data
}

export const getUnreadCountApi = async (): Promise<number> => {
  const res = await api.get<ApiResponse<{ unread_count: number }>>('/notifications/unread-count')
  return res.data.data.unread_count
}

export const markAsReadApi = async (id: number): Promise<void> => {
  await api.patch(`/notifications/${id}/read`)
}

export const markAllAsReadApi = async (): Promise<void> => {
  await api.patch('/notifications/read-all')
}

export const deleteNotificationApi = async (id: number): Promise<void> => {
  await api.delete(`/notifications/${id}`)
}
