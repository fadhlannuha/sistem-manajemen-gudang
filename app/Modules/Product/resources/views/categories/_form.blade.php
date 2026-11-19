@php
    $category = $category ?? null;
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="name" class="mb-1 block text-sm font-semibold text-gray-700">Nama Kategori</label>
        <input type="text" name="name" id="name" value="{{ old('name', $category?->name) }}"
               maxlength="100" placeholder="Contoh: Elektronik"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
        <p class="mt-1 text-xs text-gray-500">Nama kategori harus unik (tidak membedakan huruf besar/kecil).</p>
        <x-field-error name="name" />
    </div>

    <div>
        <label for="description" class="mb-1 block text-sm font-semibold text-gray-700">Deskripsi <span class="font-normal text-gray-500">(opsional)</span></label>
        <textarea name="description" id="description" rows="3" maxlength="2000"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">{{ old('description', $category?->description) }}</textarea>
        <x-field-error name="description" />
    </div>

    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <a href="{{ route('categories.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Batal
        </a>
        <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
            Simpan
        </button>
    </div>
</form>
