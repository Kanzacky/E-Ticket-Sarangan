import axios, { type AxiosInstance } from 'axios'

import { useAuthStore } from '@/stores/auth'

const api: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api/v1',
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

api.interceptors.request.use((config) => {
  const authStore = useAuthStore()

  if (authStore.token) {
    config.headers.Authorization = `Bearer ${authStore.token}`
  }

  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      authStore.logout()
    }

    return Promise.reject(error)
  },
)

export interface ApiResponse<T = unknown> {
  success: boolean
  message: string
  data: T
  meta?: Record<string, unknown>
}

export interface HealthData {
  status: string
  app: string
  version: string
  database: string
}

export const getHealth = () => api.get<ApiResponse<HealthData>>('/health').then((response) => response.data)

// --- Auth Endpoints ---

export interface AuthUser {
  id: number
  name: string
  email: string
  role: 'wisatawan' | 'petugas' | 'admin'
  phone: string | null
}

export interface AuthResponse {
  user: AuthUser
  access_token: string
}

export interface MeResponse {
  user: AuthUser
}

export const loginApi = (data: any) =>
  api.post<ApiResponse<AuthResponse>>('/auth/login', data).then((res) => res.data)

export const registerApi = (data: any) =>
  api.post<ApiResponse<AuthResponse>>('/auth/register', data).then((res) => res.data)

export const logoutApi = () => api.post<ApiResponse<null>>('/auth/logout').then((res) => res.data)

export const getMeApi = () => api.get<ApiResponse<MeResponse>>('/auth/me').then((res) => res.data)

export default api
