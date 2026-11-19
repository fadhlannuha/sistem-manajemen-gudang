<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'issued_at' => ['required', 'date', 'before_or_equal:today'],
            'destination' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'integer', 'distinct'],
            'items.*.qty' => ['required', 'numeric', 'gt:0', 'max:999999999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'issued_at.required' => 'Tanggal pengeluaran wajib diisi.',
            'issued_at.date' => 'Tanggal pengeluaran tidak valid.',
            'issued_at.before_or_equal' => 'Tanggal pengeluaran tidak boleh di masa depan.',
            'destination.max' => 'Tujuan maksimal 100 karakter.',
            'note.max' => 'Catatan maksimal 1000 karakter.',
            'items.required' => 'Tambahkan minimal satu barang.',
            'items.array' => 'Daftar barang tidak valid.',
            'items.min' => 'Tambahkan minimal satu barang.',
            'items.max' => 'Maksimal 50 barang per dokumen.',
            'items.*.product_id.required' => 'Barang wajib dipilih.',
            'items.*.product_id.integer' => 'Barang tidak valid.',
            'items.*.product_id.distinct' => 'Barang tidak boleh dipilih dua kali.',
            'items.*.qty.required' => 'Kuantitas wajib diisi.',
            'items.*.qty.numeric' => 'Kuantitas harus berupa angka.',
            'items.*.qty.gt' => 'Kuantitas harus lebih dari 0.',
            'items.*.qty.max' => 'Kuantitas terlalu besar.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        foreach (['destination', 'note'] as $field) {
            if (is_string($this->input($field))) {
                $data[$field] = trim((string) $this->input($field));
            }
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }
}
