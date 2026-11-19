<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreProductRequest extends FormRequest
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
            'sku' => ['required', 'string', 'max:30', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'min_stock' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'distinct', 'exists:categories,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sku.required' => 'SKU wajib diisi.',
            'sku.max' => 'SKU maksimal 30 karakter.',
            'sku.unique' => 'SKU sudah digunakan.',
            'name.required' => 'Nama barang wajib diisi.',
            'name.max' => 'Nama barang maksimal 150 karakter.',
            'description.max' => 'Deskripsi maksimal 2000 karakter.',
            'unit_id.required' => 'Satuan wajib dipilih.',
            'unit_id.exists' => 'Satuan yang dipilih tidak ditemukan.',
            'min_stock.required' => 'Stok minimum wajib diisi.',
            'min_stock.numeric' => 'Stok minimum harus berupa angka.',
            'min_stock.min' => 'Stok minimum tidak boleh negatif.',
            'categories.array' => 'Kategori tidak valid.',
            'categories.*.integer' => 'Kategori tidak valid.',
            'categories.*.distinct' => 'Kategori tidak boleh dipilih dua kali.',
            'categories.*.exists' => 'Kategori yang dipilih tidak ditemukan.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('sku')) {
            $data['sku'] = Str::upper(trim((string) $this->input('sku')));
        }

        if ($this->has('name')) {
            $data['name'] = trim((string) $this->input('name'));
        }

        if ($this->has('description')) {
            $data['description'] = trim((string) $this->input('description'));
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }
}
