<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'unique:suppliers,code'],
            'name' => ['required', 'string', 'max:150'],
            'contact_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'email' => ['nullable', 'string', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:1000'],
            'payment_term_id' => ['required', 'integer', 'exists:payment_terms,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode pemasok wajib diisi.',
            'code.max' => 'Kode pemasok maksimal 20 karakter.',
            'code.unique' => 'Kode pemasok sudah digunakan.',
            'name.required' => 'Nama pemasok wajib diisi.',
            'name.max' => 'Nama pemasok maksimal 150 karakter.',
            'contact_name.max' => 'Nama kontak maksimal 100 karakter.',
            'phone.max' => 'Nomor telepon maksimal 30 karakter.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 150 karakter.',
            'address.max' => 'Alamat maksimal 1000 karakter.',
            'payment_term_id.required' => 'Termin pembayaran wajib dipilih.',
            'payment_term_id.exists' => 'Termin pembayaran yang dipilih tidak ditemukan.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if (is_string($this->input('code'))) {
            $data['code'] = Str::upper(trim($this->input('code')));
        }

        foreach (['name', 'contact_name', 'phone', 'email', 'address'] as $field) {
            if (is_string($this->input($field))) {
                $data[$field] = trim($this->input($field));
            }
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }
}
