@php
    $supplier = $supplier ?? null;
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="code" class="mb-1 block text-sm font-semibold text-gray-700">Kode Pemasok</label>
            <input type="text" name="code" id="code" value="{{ old('code', $supplier?->code) }}"
                   maxlength="20" placeholder="SUP-001"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            <p class="mt-1 text-xs text-gray-500">Huruf otomatis menjadi kapital. Kode harus unik.</p>
            <x-field-error name="code" />
        </div>

        <div>
            <label for="name" class="mb-1 block text-sm font-semibold text-gray-700">Nama Pemasok</label>
            <input type="text" name="name" id="name" value="{{ old('name', $supplier?->name) }}"
                   maxlength="150" placeholder="Contoh: PT Sumber Elektronik"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            <x-field-error name="name" />
        </div>

        <div>
            <label for="contact_name" class="mb-1 block text-sm font-semibold text-gray-700">Nama Kontak <span class="font-normal text-gray-500">(opsional)</span></label>
            <input type="text" name="contact_name" id="contact_name" value="{{ old('contact_name', $supplier?->contact_name) }}"
                   maxlength="100" placeholder="Nama PIC"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            <x-field-error name="contact_name" />
        </div>

        <div>
            <label for="payment_term_id" class="mb-1 block text-sm font-semibold text-gray-700">Termin Pembayaran</label>
            <select name="payment_term_id" id="payment_term_id"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                <option value="">— Pilih termin —</option>
                @foreach ($paymentTerms as $paymentTerm)
                    <option value="{{ $paymentTerm->id }}" @selected((string) old('payment_term_id', $supplier?->payment_term_id) === (string) $paymentTerm->id)>
                        {{ $paymentTerm->name }} ({{ $paymentTerm->days }} hari)
                    </option>
                @endforeach
            </select>
            <x-field-error name="payment_term_id" />
        </div>

        <div>
            <label for="phone" class="mb-1 block text-sm font-semibold text-gray-700">Telepon <span class="font-normal text-gray-500">(opsional)</span></label>
            <input type="text" name="phone" id="phone" value="{{ old('phone', $supplier?->phone) }}"
                   maxlength="30" placeholder="+62 21 5550101"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            <x-field-error name="phone" />
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-semibold text-gray-700">Email <span class="font-normal text-gray-500">(opsional)</span></label>
            <input type="email" name="email" id="email" value="{{ old('email', $supplier?->email) }}"
                   maxlength="150" placeholder="kontak@pemasok.test"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            <x-field-error name="email" />
        </div>
    </div>

    <div>
        <label for="address" class="mb-1 block text-sm font-semibold text-gray-700">Alamat <span class="font-normal text-gray-500">(opsional)</span></label>
        <textarea name="address" id="address" rows="3" maxlength="1000"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">{{ old('address', $supplier?->address) }}</textarea>
        <x-field-error name="address" />
    </div>

    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <a href="{{ $supplier ? route('suppliers.show', $supplier) : route('suppliers.index') }}"
           class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Batal
        </a>
        <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
            Simpan
        </button>
    </div>
</form>
