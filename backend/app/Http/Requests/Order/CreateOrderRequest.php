<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ticket_type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'visit_date.required' => 'Tanggal kunjungan wajib diisi.',
            'visit_date.date' => 'Format tanggal kunjungan tidak valid.',
            'visit_date.after_or_equal' => 'Tanggal kunjungan tidak boleh sebelum hari ini.',
            'customer_name.required' => 'Nama pengunjung wajib diisi.',
            'customer_email.required' => 'Email pengunjung wajib diisi.',
            'customer_email.email' => 'Format email tidak valid.',
            'customer_phone.required' => 'Nomor handphone wajib diisi.',
            'customer_phone.regex' => 'Format nomor handphone tidak valid.',
            'items.required' => 'Pilih minimal satu tiket.',
            'items.min' => 'Pilih minimal satu tiket.',
            'items.*.ticket_type_id.required' => 'Jenis tiket wajib dipilih.',
            'items.*.ticket_type_id.exists' => 'Jenis tiket tidak ditemukan.',
            'items.*.quantity.required' => 'Jumlah tiket wajib diisi.',
            'items.*.quantity.min' => 'Jumlah tiket minimal 1.',
            'items.*.quantity.max' => 'Jumlah tiket per jenis maksimal 100.',
        ];
    }
}
