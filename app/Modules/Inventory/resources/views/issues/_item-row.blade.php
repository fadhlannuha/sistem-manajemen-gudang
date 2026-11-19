@php
    $rowIndex = $rowIndex ?? 0;
    $item = $item ?? [];
    $selectedProduct = (string) ($item['product_id'] ?? '');
    $qty = $item['qty'] ?? '';
    $selectedBalance = $selectedProduct !== '' && isset($balances[(int) $selectedProduct])
        ? (float) $balances[(int) $selectedProduct]->qty
        : null;
@endphp

<tr data-item-row class="align-top">
    <td class="px-3 py-2">
        <select name="items[{{ $rowIndex }}][product_id]" data-issue-product
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            <option value="">— Pilih barang —</option>
            @foreach ($products as $product)
                @php($available = (float) ($balances[$product->id]->qty ?? 0))
                <option value="{{ $product->id }}" data-balance="{{ $available }}" @selected($selectedProduct === (string) $product->id)>
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
    <td class="whitespace-nowrap px-3 py-2 text-right text-sm text-gray-700" data-issue-balance>
        {{ $selectedBalance === null ? '—' : number_format($selectedBalance, 3, ',', '.') }}
    </td>
    <td class="px-3 py-2 text-right">
        <button type="button" data-remove-item
                class="rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">
            Hapus
        </button>
    </td>
</tr>
