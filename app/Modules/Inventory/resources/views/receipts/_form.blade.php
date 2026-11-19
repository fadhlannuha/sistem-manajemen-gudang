@php
    $oldItems = old('items');

    if (! is_array($oldItems) || $oldItems === []) {
        $oldItems = [['product_id' => '', 'qty' => '', 'unit_cost' => '']];
    }

    $oldItems = array_values($oldItems);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    @csrf

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="supplier_id" class="mb-1 block text-sm font-semibold text-gray-700">Pemasok</label>
            <select name="supplier_id" id="supplier_id"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                <option value="">— Pilih pemasok —</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>
                        {{ $supplier->name }} ({{ $supplier->code }})
                    </option>
                @endforeach
            </select>
            @if ($suppliers === [])
                <p class="mt-1 text-xs text-amber-700">Belum ada pemasok aktif. Tambahkan pemasok terlebih dahulu.</p>
            @endif
            <x-field-error name="supplier_id" />
        </div>

        <div>
            <label for="received_at" class="mb-1 block text-sm font-semibold text-gray-700">Tanggal Penerimaan</label>
            <input type="date" name="received_at" id="received_at"
                   value="{{ old('received_at', now()->toDateString()) }}" max="{{ now()->toDateString() }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            <p class="mt-1 text-xs text-gray-500">Tidak boleh melewati hari ini.</p>
            <x-field-error name="received_at" />
        </div>
    </div>

    <div>
        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
            <span class="text-sm font-semibold text-gray-700">Barang Diterima</span>
            <button type="button" id="add-receipt-item"
                    class="rounded-lg border border-blue-200 px-3 py-1.5 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                + Tambah Baris
            </button>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-3 py-2">Barang</th>
                        <th class="px-3 py-2">Kuantitas</th>
                        <th class="px-3 py-2">Harga Satuan (Rp)</th>
                        <th class="px-3 py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="receipt-items" class="divide-y divide-gray-100 bg-white" data-next-index="{{ count($oldItems) }}">
                    @foreach ($oldItems as $index => $item)
                        @include('inventory::receipts._item-row', [
                            'rowIndex' => $index,
                            'item' => $item,
                            'products' => $products,
                        ])
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-field-error name="items" />
        <x-field-error name="items.*" />
    </div>

    <div>
        <label for="note" class="mb-1 block text-sm font-semibold text-gray-700">Catatan <span class="font-normal text-gray-500">(opsional)</span></label>
        <textarea name="note" id="note" rows="3" maxlength="1000"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">{{ old('note') }}</textarea>
        <x-field-error name="note" />
    </div>

    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <a href="{{ route('inventory.receipts.index') }}"
           class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Batal
        </a>
        <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
            Simpan Penerimaan
        </button>
    </div>
</form>

<template id="receipt-item-template">
    @include('inventory::receipts._item-row', [
        'rowIndex' => '__INDEX__',
        'item' => [],
        'products' => $products,
    ])
</template>

<script>
    (function () {
        const body = document.getElementById('receipt-items');
        const template = document.getElementById('receipt-item-template');
        const addButton = document.getElementById('add-receipt-item');

        if (!body || !template || !addButton) {
            return;
        }

        let nextIndex = Number(body.dataset.nextIndex || 0);

        addButton.addEventListener('click', function () {
            body.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(nextIndex)));
            nextIndex += 1;
        });

        body.addEventListener('click', function (event) {
            const button = event.target.closest('[data-remove-item]');

            if (!button) {
                return;
            }

            if (body.querySelectorAll('[data-item-row]').length === 1) {
                return;
            }

            button.closest('[data-item-row]').remove();
        });
    })();
</script>
