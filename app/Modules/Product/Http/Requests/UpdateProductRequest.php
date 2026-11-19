<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateProductRequest extends StoreProductRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['sku'] = [
            'required',
            'string',
            'max:30',
            Rule::unique('products', 'sku')->ignore($this->route('product')),
        ];

        return $rules;
    }
}
