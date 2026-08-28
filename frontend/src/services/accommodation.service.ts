import type { ApiResponse } from './api'
import api from './api'

export interface Accommodation {
  id: number
  name: string
  description: string | null
  address: string
  phone: string | null
  image_url: string | null
  price_per_night: number
  total_rooms: number
  available_rooms: number
  rating: number
  facilities: string[] | null
  is_active: boolean
}

export interface AccommodationBooking {
  id: number
  booking_code: string
  user_id: number
  accommodation_id: number
  check_in: string
  check_out: string
  rooms: number
  guests: number
  total_price: number
  guest_name: string
  guest_phone: string
  status: 'pending' | 'confirmed' | 'cancelled' | 'completed' | 'paid' | 'expired'
  payment_id?: string | null
  payment_url?: string | null
  payment_expires_at?: string | null
  notes: string | null
  created_at: string
  accommodation?: Accommodation
}

export interface CreateAccommodationBookingPayload {
  accommodation_id: number
  check_in: string
  check_out: string
  rooms: number
  guests: number
  guest_name: string
  guest_phone: string
  notes?: string
}

export async function getAccommodationsApi(): Promise<Accommodation[]> {
  const res = await api.get<ApiResponse<Accommodation[]>>('/accommodations')
  return res.data.data
}

export async function getAccommodationApi(id: number): Promise<Accommodation> {
  const res = await api.get<ApiResponse<Accommodation>>(`/accommodations/${id}`)
  return res.data.data
}

export async function getMyAccommodationBookingsApi(): Promise<AccommodationBooking[]> {
  const res = await api.get<ApiResponse<AccommodationBooking[]>>('/accommodation-bookings')
  return res.data.data
}

export async function createAccommodationBookingApi(
  payload: CreateAccommodationBookingPayload,
): Promise<AccommodationBooking> {
  const res = await api.post<ApiResponse<AccommodationBooking>>('/accommodation-bookings', payload)
  return res.data.data
}
