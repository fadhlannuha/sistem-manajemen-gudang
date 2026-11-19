@php
    $oldItems = old('items');

    if (! is_array($oldItems) || $oldItems === []) {
        $oldItems = [['product_id' => '', 'qty' => '']];
    }

    $oldItems = array_values($oldItems);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    @csrf

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="issued_at" class="mb-1 block text-sm font-semibold text-gray-700">Tanggal Pengeluaran</label>
            <input type="date" name="issued_at" id="issued_at"
                   value="{{ old('issued_at', now()->toDateString()) }}" max="{{ now()->toDateString() }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            <p class="mt-1 text-xs text-gray-500">Tidak boleh melewati hari ini.</p>
            <x-field-error name="issued_at" />
        </div>

        <div>
            <label for="destination" class="mb-1 block text-sm font-semibold text-gray-700">Tujuan <span class="font-normal text-gray-500">(opsional)</span></label>
            <input type="text" name="destination" id="destination" value="{{ old('destination') }}"
                   maxlength="100" placeholder="Contoh: Divisi Umum"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            <x-field-error name="destination" />
        </div>
    </div>

    <div>
        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
            <span class="text-sm font-semibold text-gray-700">Barang Dikeluarkan</span>
            <button type="button" id="add-issue-item"
                    class="rounded-lg border border-amber-200 px-3 py-1.5 text-sm font-semibold text-amber-700 hover:bg-amber-50">
                + Tambah Baris
            </button>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-3 py-2">Barang</th>
                        <th class="px-3 py-2">Kuantitas</th>
                        <th class="px-3 py-2 text-right">Saldo Tersedia</th>
                        <th class="px-3 py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="issue-items" class="divide-y divide-gray-100 bg-white" data-next-index="{{ count($oldItems) }}">
                    @foreach ($oldItems as $index => $item)
                        @include('inventory::issues._item-row', [
                            'rowIndex' => $index,
                            'item' => $item,
                            'products' => $products,
                            'balances' => $balances,
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
        <a href="{{ route('inventory.issues.index') }}"
           class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Batal
        </a>
        <button type="submit" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
            Simpan Pengeluaran
        </button>
    </div>
</form>

<template id="issue-item-template">
    @include('inventory::issues._item-row', [
        'rowIndex' => '__INDEX__',
        'item' => [],
        'products' => $products,
        'balances' => $balances,
    ])
</template>

<script>
    (function () {
        const body = document.getElementById('issue-items');
        const template = document.getElementById('issue-item-template');
        const addButton = document.getElementById('add-issue-item');

        if (!body || !template || !addButton) {
            return;
        }

        let nextIndex = Number(body.dataset.nextIndex || 0);

        function formatBalance(value) {
            return Number(value).toLocaleString('id-ID', {
                minimumFractionDigits: 3,
                maximumFractionDigits: 3,
            });
        }

        function refreshBalance(row) {
            const select = row.querySelector('[data-issue-product]');
            const target = row.querySelector('[data-issue-balance]');

            if (!select || !target) {
                return;
            }

            const option = select.options[select.selectedIndex];
            const balance = option ? option.dataset.balance : null;

            target.textContent = balance === undefined || balance === '' ? '—' : formatBalance(balance);
        }

        addButton.addEventListener('click', function () {
            body.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(nextIndex)));
            nextIndex += 1;
        });

        body.addEventListener('change', function (event) {
            if (!event.target.matches('[data-issue-product]')) {
                return;
            }

            refreshBalance(event.target.closest('[data-item-row]'));
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

        body.querySelectorAll('[data-item-row]').forEach(refreshBalance);
    })();
</script>
