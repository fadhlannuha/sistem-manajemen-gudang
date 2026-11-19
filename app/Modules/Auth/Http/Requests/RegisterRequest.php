<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.min' => 'Nama minimal 3 karakter.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email harus berupa teks.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 150 karakter.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('name')) {
            $data['name'] = trim((string) $this->input('name'));
        }

        if ($this->has('email')) {
            $data['email'] = Str::lower(trim((string) $this->input('email')));
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }
}
