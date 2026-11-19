<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Http\Requests;

use App\Modules\Supplier\Models\PaymentTerm;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentTermRequest extends FormRequest
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

                    $paymentTerm = $this->route('payment_term');
                    $ignoreId = $paymentTerm instanceof PaymentTerm ? $paymentTerm->getKey() : $paymentTerm;

                    $exists = PaymentTerm::query()
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                        ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
                        ->exists();

                    if ($exists) {
                        $fail('Nama termin pembayaran sudah digunakan.');
                    }
                },
            ],
            'days' => ['required', 'integer', 'min:0', 'max:365'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama termin wajib diisi.',
            'name.max' => 'Nama termin maksimal 50 karakter.',
            'days.required' => 'Jumlah hari wajib diisi.',
            'days.integer' => 'Jumlah hari harus berupa angka bulat.',
            'days.min' => 'Jumlah hari tidak boleh negatif.',
            'days.max' => 'Jumlah hari maksimal 365.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('name'))) {
            $this->merge([
                'name' => trim($this->input('name')),
            ]);
        }
    }
}
