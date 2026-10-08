import api from './api'

export interface ScanResponseData {
  code: string
  name: string
  date: string
  type: string
  qty: number
}

export interface ScanResponse {
  message: string
  data?: ScanResponseData
}

export const scanTicketApi = async (orderCode: string): Promise<ScanResponse> => {
  // Scan butuh response cepat — timeout 10s, bukan 30s global
  // Jika server butuh lebih dari 10s untuk scan, ada masalah di backend
  const response = await api.post<ScanResponse>('/scan', { order_code: orderCode }, { timeout: 10_000 })
  return response.data
}

export const getScanHistoryApi = async (): Promise<any[]> => {
  const response = await api.get('/scan/history')
  return response.data.data
}
