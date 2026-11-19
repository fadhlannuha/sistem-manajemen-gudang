<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Requests;

use App\Modules\Product\Models\Category;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $name = trim((string) $value);

                    if ($name === '') {
                        return;
                    }

                    $category = $this->route('category');
                    $ignoreId = $category instanceof Category ? $category->getKey() : $category;

                    $exists = Category::query()
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                        ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
                        ->exists();

                    if ($exists) {
                        $fail('Nama kategori sudah digunakan.');
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'description.max' => 'Deskripsi maksimal 2000 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

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
