<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Requests;

use App\Modules\Product\Models\Unit;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
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
                'max:50',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $name = trim((string) $value);

                    if ($name === '') {
                        return;
                    }

                    $unit = $this->route('unit');
                    $ignoreId = $unit instanceof Unit ? $unit->getKey() : $unit;

                    $exists = Unit::query()
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                        ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
                        ->exists();

                    if ($exists) {
                        $fail('Nama satuan sudah digunakan.');
                    }
                },
            ],
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('units', 'code')->ignore($this->route('unit')),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama satuan wajib diisi.',
            'name.max' => 'Nama satuan maksimal 50 karakter.',
            'name.unique' => 'Nama satuan sudah digunakan.',
            'code.required' => 'Kode satuan wajib diisi.',
            'code.max' => 'Kode satuan maksimal 10 karakter.',
            'code.unique' => 'Kode satuan sudah digunakan.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('name')) {
            $data['name'] = trim((string) $this->input('name'));
        }

        if ($this->has('code')) {
            $data['code'] = Str::upper(trim((string) $this->input('code')));
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }
}
