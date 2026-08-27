<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TicketTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'quota' => ['required', 'integer', 'min:1', 'max:10000'],
            'status' => ['required', 'string', 'in:ACTIVE,INACTIVE'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama jenis tiket wajib diisi.',
            'price.required' => 'Harga wajib diisi.',
            'price.min' => 'Harga minimal 0.',
            'quota.required' => 'Kuota wajib diisi.',
            'quota.min' => 'Kuota minimal 1.',
            'quota.max' => 'Kuota maksimal 10000.',
            'status.in' => 'Status tidak valid. Gunakan ACTIVE atau INACTIVE.',
        ];
    }