<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends StoreSupplierRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['code'] = [
            'required',
            'string',
            'max:20',
            Rule::unique('suppliers', 'code')->ignore($this->route('supplier')),
        ];

        return $rules;
    }
}
