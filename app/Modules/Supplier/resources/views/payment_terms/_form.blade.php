@php
    $paymentTerm = $paymentTerm ?? null;
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="name" class="mb-1 block text-sm font-semibold text-gray-700">Nama Termin</label>
            <input type="text" name="name" id="name" value="{{ old('name', $paymentTerm?->name) }}"
                   maxlength="50" placeholder="Contoh: Net 30"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            <p class="mt-1 text-xs text-gray-500">Nama termin harus unik (tidak membedakan huruf besar/kecil).</p>
            <x-field-error name="name" />
        </div>

        <div>
            <label for="days" class="mb-1 block text-sm font-semibold text-gray-700">Jumlah Hari</label>
            <input type="number" name="days" id="days" value="{{ old('days', $paymentTerm?->days ?? 0) }}"
                   min="0" max="365" step="1"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            <p class="mt-1 text-xs text-gray-500">Isi 0 untuk pembayaran tunai. Maksimal 365 hari.</p>
            <x-field-error name="days" />
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <a href="{{ route('payment-terms.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Batal
        </a>
        <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
            Simpan
        </button>
    </div>
</form>
