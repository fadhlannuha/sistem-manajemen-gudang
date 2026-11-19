<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockReceiptRequest extends FormRequest
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
            'supplier_id' => ['required', 'integer'],
            'received_at' => ['required', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'integer', 'distinct'],
            'items.*.qty' => ['required', 'numeric', 'gt:0', 'max:999999999'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Pemasok wajib dipilih.',
            'supplier_id.integer' => 'Pemasok tidak valid.',
            'received_at.required' => 'Tanggal penerimaan wajib diisi.',
            'received_at.date' => 'Tanggal penerimaan tidak valid.',
            'received_at.before_or_equal' => 'Tanggal penerimaan tidak boleh di masa depan.',
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
            'items.*.unit_cost.required' => 'Harga satuan wajib diisi.',
            'items.*.unit_cost.numeric' => 'Harga satuan harus berupa angka.',
            'items.*.unit_cost.min' => 'Harga satuan tidak boleh negatif.',
            'items.*.unit_cost.max' => 'Harga satuan terlalu besar.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('note'))) {
            $this->merge(['note' => trim((string) $this->input('note'))]);
        }
    }
}
