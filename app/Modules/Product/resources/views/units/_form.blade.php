@php
    $unit = $unit ?? null;
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="name" class="mb-1 block text-sm font-semibold text-gray-700">Nama Satuan</label>
            <input type="text" name="name" id="name" value="{{ old('name', $unit?->name) }}"
                   maxlength="50" placeholder="Contoh: Piece"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            <p class="mt-1 text-xs text-gray-500">Nama satuan harus unik (tidak membedakan huruf besar/kecil).</p>
            <x-field-error name="name" />
        </div>

        <div>
            <label for="code" class="mb-1 block text-sm font-semibold text-gray-700">Kode</label>
            <input type="text" name="code" id="code" value="{{ old('code', $unit?->code) }}"
                   maxlength="10" placeholder="PCS"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            <p class="mt-1 text-xs text-gray-500">Maksimal 10 karakter, harus unik.</p>
            <x-field-error name="code" />
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <a href="{{ route('units.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Batal
        </a>
        <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
            Simpan
        </button>
    </div>
</form>
