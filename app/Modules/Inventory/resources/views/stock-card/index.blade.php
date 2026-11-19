@extends('layouts.app')

@section('title', 'Kartu Stok')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kartu Stok</h1>
            <p class="mt-1 text-sm text-gray-600">Riwayat mutasi stok per barang, diurutkan kronologis.</p>
        </div>

        <form method="GET" action="{{ route('inventory.stock-card.index') }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <label for="product_id" class="mb-1 block text-sm font-semibold text-gray-700">Barang</label>
                    <select name="product_id" id="product_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        <option value="">— Pilih barang —</option>
                        @foreach ($products as $option)
                            <option value="{{ $option->id }}" @selected((string) ($filters['product_id'] ?? '') === (string) $option->id)>
                                {{ $option->name }} ({{ $option->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="from" class="mb-1 block text-sm font-semibold text-gray-700">Dari Tanggal</label>
                    <input type="date" name="from" id="from" value="{{ $filters['from'] ?? '' }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div>
                    <label for="to" class="mb-1 block text-sm font-semibold text-gray-700">Sampai Tanggal</label>
                    <input type="date" name="to" id="to" value="{{ $filters['to'] ?? '' }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                        Tampilkan
                    </button>
                    <a href="{{ route('inventory.stock-card.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        @if ($product)
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ $product->name }}</h2>
                        <p class="mt-1 font-mono text-sm text-gray-600">{{ $product->sku }}</p>
                        <p class="mt-1 text-sm text-gray-600">Satuan: {{ $product->unitName }} ({{ $product->unitCode }})</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-500">Saldo Saat Ini</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">
                            {{ number_format((float) ($balance?->qty ?? 0), 3, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Tipe</th>
                                <th class="px-4 py-3">Referensi</th>
                                <th class="px-4 py-3 text-right">Masuk</th>
                                <th class="px-4 py-3 text-right">Keluar</th>
                                <th class="px-4 py-3 text-right">Saldo Setelah</th>
                                <th class="px-4 py-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($movements as $movement)
                                <tr class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $movement->movement_date->format('d-m-Y') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        @if ($movement->movement_type === 'receipt')
                                            <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800">Masuk</span>
                                        @elseif ($movement->movement_type === 'issue')
                                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Keluar</span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">Penyesuaian</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-gray-700">{{ $referenceNumbers[$movement->id] ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-green-700">
                                        {{ (float) $movement->qty_in > 0 ? number_format((float) $movement->qty_in, 3, ',', '.') : '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-red-700">
                                        {{ (float) $movement->qty_out > 0 ? number_format((float) $movement->qty_out, 3, ',', '.') : '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-gray-900">
                                        {{ number_format((float) $movement->balance_after, 3, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $movement->note ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                                        Belum ada mutasi stok pada rentang tanggal ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($movements->hasPages())
                    <div class="border-t border-gray-200 px-4 py-3">
                        {{ $movements->links() }}
                    </div>
                @endif
            </div>
        @else
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
                Pilih barang terlebih dahulu untuk menampilkan kartu stok.
            </div>
        @endif
    </div>
@endsection
