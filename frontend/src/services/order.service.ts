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
 * POST /orders - Buat pesanan tiket baru.
 * Timeout diperluas ke 45s karena backend memanggil Xendit API
 * secara sinkron setelah menyimpan pesanan.
 */
export const createOrderApi = async (payload: CreateOrderPayload): Promise<Order> => {
  const response = await api.post<ApiResponse<Order>>('/orders', payload, {
    timeout: 45_000,
  })
  return response.data.data
}

/**
 * POST /orders/{order_code}/pay - Buat / ambil URL pembayaran Xendit.
 * Digunakan sebagai fallback jika createOrderApi berhasil buat pesanan
 * tapi gagal mendapatkan payment_url (mis. Xendit timeout di server).
 * Juga digunakan oleh tombol "Bayar" di halaman Pesanan Saya.
 */
export const payOrderApi = async (orderCode: string): Promise<string> => {
  const response = await api.post<ApiResponse<{ payment_url: string }>>(
    `/orders/${orderCode}/pay`,
    {},
    { timeout: 45_000 },
  )
  return response.data.data.payment_url
}

export interface PaginatedMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface PaginatedOrders {
  data: Order[]
  meta: PaginatedMeta
}

/**
 * GET /orders - Ambil riwayat order milik user yang login.
 * Mendukung parameter paginasi dan pencarian opsional.
 */
export const getMyOrdersApi = async (params?: {
  page?: number
  per_page?: number
  search?: string
}): Promise<PaginatedOrders> => {
  const response = await api.get<ApiResponse<Order[]>>('/orders', { params })
  const rawData = response.data.data
  // Support both paginated (object with data/meta) and flat array responses
  if (Array.isArray(rawData)) {
    return {
      data: rawData,
      meta: { current_page: 1, last_page: 1, per_page: rawData.length, total: rawData.length },
    }
  }
  const paginated = rawData as unknown as { data: Order[]; meta: PaginatedMeta }
  return paginated
}

/**
 * GET /orders/{order_code} - Ambil detail order berdasarkan kode booking
 */
export const getOrderByCodeApi = async (orderCode: string): Promise<Order> => {
  const response = await api.get<ApiResponse<Order>>(`/orders/${orderCode}`)
  return response.data.data
}
