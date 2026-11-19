@php
    $rowIndex = $rowIndex ?? 0;
    $item = $item ?? [];
    $selectedProduct = (string) ($item['product_id'] ?? '');
    $qty = $item['qty'] ?? '';
    $unitCost = $item['unit_cost'] ?? '';
@endphp

<tr data-item-row class="align-top">
    <td class="px-3 py-2">
        <select name="items[{{ $rowIndex }}][product_id]"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            <option value="">— Pilih barang —</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}" @selected($selectedProduct === (string) $product->id)>
                    {{ $product->name }} ({{ $product->sku }})
                </option>
            @endforeach
        </select>
    </td>
    <td class="px-3 py-2">
        <input type="number" name="items[{{ $rowIndex }}][qty]" value="{{ $qty }}"
               min="0.001" step="0.001" placeholder="0"
               class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-right text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
    </td>
    <td class="px-3 py-2">
        <input type="number" name="items[{{ $rowIndex }}][unit_cost]" value="{{ $unitCost }}"
               min="0" step="0.01" placeholder="0"
               class="w-36 rounded-lg border border-gray-300 px-3 py-2 text-right text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
    </td>
    <td class="px-3 py-2 text-right">
        <button type="button" data-remove-item
                class="rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">
            Hapus
        </button>
    </td>
</tr>
