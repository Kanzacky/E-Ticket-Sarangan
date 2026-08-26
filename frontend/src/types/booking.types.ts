export interface TicketType {
  id: number
  name: string
  description: string | null
  price: number
  quota: number
  status: 'ACTIVE' | 'INACTIVE'
  created_at?: string
  updated_at?: string
}

export interface OrderItemPayload {
  ticket_type_id: number
  quantity: number
}

export interface CreateOrderPayload {
  visit_date: string
  customer_name: string
  customer_email: string
  customer_phone: string
  items: OrderItemPayload[]
}

export interface OrderItem {
  id: number
  order_id: number
  ticket_type_id: number
  quantity: number
  price: number
  subtotal: number
  ticket_type?: TicketType
  created_at?: string
}

export type OrderStatus = 'PENDING' | 'PAID' | 'CANCELLED' | 'EXPIRED'

export interface Order {
  id: number
  user_id: number
  order_code: string
  visit_date: string
  customer_name: string
  customer_email: string
  customer_phone: string
  total_quantity: number
  total_amount: number
  status: OrderStatus
  payment_id?: string | null
  payment_url?: string | null
  qr_expires_at?: string | null
  created_at: string
  updated_at?: string
  items?: OrderItem[]
}
