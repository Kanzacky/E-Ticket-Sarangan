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
  const response = await api.post<ScanResponse>('/scan', { order_code: orderCode })
  return response.data
}

export const getScanHistoryApi = async (): Promise<any[]> => {
  const response = await api.get('/scan/history')
  return response.data.data
}
