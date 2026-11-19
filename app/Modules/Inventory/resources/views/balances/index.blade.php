@extends('layouts.app')

@section('title', 'Saldo Stok')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Saldo Stok</h1>
                <p class="mt-1 text-sm text-gray-600">Saldo terkini setiap barang beserta status stok minimumnya.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('inventory.receipts.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                    Stok Masuk
                </a>
                <a href="{{ route('inventory.issues.create') }}" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                    Stok Keluar
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('inventory.balances.index') }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <label for="search" class="mb-1 block text-sm font-semibold text-gray-700">Cari Barang</label>
                    <input type="text" name="search" id="search" value="{{ $filters['search'] ?? '' }}"
                           placeholder="Nama atau SKU"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div class="flex items-end">
                    <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                        <input type="checkbox" name="low_stock" value="1" @checked(request()->boolean('low_stock'))
                               class="h-4 w-4 rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                        Hanya stok rendah
                    </label>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                        Filter
                    </button>
                    <a href="{{ route('inventory.balances.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Barang</th>
                            <th class="px-4 py-3">Satuan</th>
                            <th class="px-4 py-3 text-right">Saldo</th>
                            <th class="px-4 py-3 text-right">Stok Minimum</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($balances as $balance)
                            @php
                                $product = $productsById[$balance->product_id] ?? null;
                                $isLow = $product !== null && (float) $balance->qty <= $product->minStock;
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    @if ($product)
                                        <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                        <p class="font-mono text-xs text-gray-500">{{ $product->sku }}</p>
                                    @else
                                        <span class="text-gray-400">Barang tidak aktif</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                    {{ $product ? $product->unitName.' ('.$product->unitCode.')' : '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-gray-900">
                                    {{ number_format((float) $balance->qty, 3, ',', '.') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-gray-700">
                                    {{ $product ? number_format($product->minStock, 3, ',', '.') : '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    @if ($isLow)
                                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-800">Rendah</span>
                                    @else
                                        <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800">Aman</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    @if ($product)
                                        <a href="{{ route('inventory.stock-card.index', ['product_id' => $product->id]) }}"
                                           class="text-sm font-semibold text-blue-700 hover:underline">
                                            Kartu Stok
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                                    Belum ada saldo stok yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($balances->hasPages())
                <div class="border-t border-gray-200 px-4 py-3">
                    {{ $balances->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
