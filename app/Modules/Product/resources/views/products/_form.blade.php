@php
    $product = $product ?? null;
    $selectedCategories = array_map('intval', (array) old('categories', $product?->categories->pluck('id')->all() ?? []));
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="sku" class="mb-1 block text-sm font-semibold text-gray-700">SKU</label>
            <input type="text" name="sku" id="sku" value="{{ old('sku', $product?->sku) }}"
                   maxlength="30" placeholder="PRD-0001"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            <p class="mt-1 text-xs text-gray-500">Huruf otomatis diubah menjadi kapital. SKU harus unik.</p>
            <x-field-error name="sku" />
        </div>

        <div>
            <label for="name" class="mb-1 block text-sm font-semibold text-gray-700">Nama Barang</label>
            <input type="text" name="name" id="name" value="{{ old('name', $product?->name) }}"
                   maxlength="150" placeholder="Contoh: Kertas HVS A4"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            <x-field-error name="name" />
        </div>

        <div>
            <label for="unit_id" class="mb-1 block text-sm font-semibold text-gray-700">Satuan</label>
            <select name="unit_id" id="unit_id"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                <option value="">— Pilih satuan —</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" @selected((string) old('unit_id', $product?->unit_id) === (string) $unit->id)>
                        {{ $unit->name }} ({{ $unit->code }})
                    </option>
                @endforeach
            </select>
            <x-field-error name="unit_id" />
        </div>

        <div>
            <label for="min_stock" class="mb-1 block text-sm font-semibold text-gray-700">Stok Minimum</label>
            <input type="number" name="min_stock" id="min_stock" value="{{ old('min_stock', $product?->min_stock ?? 0) }}"
                   min="0" step="0.001"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            <p class="mt-1 text-xs text-gray-500">Ambang peringatan stok rendah. Boleh 0.</p>
            <x-field-error name="min_stock" />
        </div>
    </div>

    <div>
        <label for="description" class="mb-1 block text-sm font-semibold text-gray-700">Deskripsi <span class="font-normal text-gray-500">(opsional)</span></label>
        <textarea name="description" id="description" rows="3" maxlength="2000"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">{{ old('description', $product?->description) }}</textarea>
        <x-field-error name="description" />
    </div>

    <div>
        <span class="mb-2 block text-sm font-semibold text-gray-700">Kategori <span class="font-normal text-gray-500">(boleh lebih dari satu)</span></span>

        <div class="grid gap-2 sm:grid-cols-2">
            @forelse ($categories as $category)
                <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                           @checked(in_array($category->id, $selectedCategories, true))
                           class="h-4 w-4 rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                    {{ $category->name }}
                </label>
            @empty
                <p class="text-sm text-gray-500">Belum ada kategori. Tambahkan kategori terlebih dahulu.</p>
            @endforelse
        </div>

        <x-field-error name="categories" />
        <x-field-error name="categories.*" />
    </div>

    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <a href="{{ $product ? route('products.show', $product) : route('products.index') }}"
           class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Batal
        </a>
        <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
            Simpan
        </button>
    </div>
</form>
