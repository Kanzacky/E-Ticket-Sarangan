import api, { type ApiResponse } from '@/services/api'
import type { CreateOrderPayload, Order, TicketType } from '@/types/booking.types'

/**
 * GET /ticket-types - Ambil daftar jenis tiket aktif
 */
export const getTicketTypesApi = async (): Promise<TicketType[]> => {
  const response = await api.get<ApiResponse<TicketType[]>>('/ticket-types')
  return response.data.data
}

/**
 * POST /orders - Buat pesanan tiket baru
 */
export const createOrderApi = async (payload: CreateOrderPayload): Promise<Order> => {
  const response = await api.post<ApiResponse<Order>>('/orders', payload)
  return response.data.data
}

/**
 * GET /orders - Ambil riwayat order milik user yang login
 */
export const getMyOrdersApi = async (): Promise<Order[]> => {
  const response = await api.get<ApiResponse<Order[]>>('/orders')
  return response.data.data
}

/**
 * GET /orders/{order_code} - Ambil detail order berdasarkan kode booking
 */
export const getOrderByCodeApi = async (orderCode: string): Promise<Order> => {
  const response = await api.get<ApiResponse<Order>>(`/orders/${orderCode}`)
  return response.data.data
}
