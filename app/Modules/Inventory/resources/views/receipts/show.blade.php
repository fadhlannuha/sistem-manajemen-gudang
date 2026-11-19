@extends('layouts.app')

@section('title', 'Detail Stok Masuk')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('inventory.receipts.index') }}" class="text-sm font-semibold text-blue-700 hover:underline">&larr; Kembali ke daftar stok masuk</a>
                <h1 class="mt-2 font-mono text-2xl font-bold text-gray-900">{{ $receipt->number }}</h1>
                <p class="mt-1 text-sm text-gray-600">Penerimaan tanggal {{ $receipt->received_at->format('d-m-Y') }}</p>
            </div>

            <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">Stok Masuk</span>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="text-lg font-semibold text-gray-900">Informasi Dokumen</h2>

                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nomor Dokumen</dt>
                        <dd class="mt-1 font-mono text-sm font-semibold text-gray-900">{{ $receipt->number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tanggal Penerimaan</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $receipt->received_at->format('d-m-Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Pemasok</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $supplier?->name ?? 'Pemasok tidak aktif' }}
                            @if ($supplier)
                                <span class="font-mono text-xs text-gray-500">({{ $supplier->code }})</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Dicatat Oleh</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $actor?->name ?? 'Pengguna tidak tersedia' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Catatan</dt>
                        <dd class="mt-1 whitespace-pre-line text-sm text-gray-900">{{ $receipt->note ?: '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Total Penerimaan</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">Rp {{ number_format((float) $receipt->total_amount, 2, ',', '.') }}</p>
                <p class="mt-1 text-sm text-gray-500">{{ $receipt->items->count() }} baris barang</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Rincian Barang</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Barang</th>
                            <th class="px-4 py-3">Satuan</th>
                            <th class="px-4 py-3 text-right">Kuantitas</th>
                            <th class="px-4 py-3 text-right">Harga Satuan</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($receipt->items as $item)
                            @php($product = $productsById[$item->product_id] ?? null)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    @if ($product)
                                        <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                        <p class="font-mono text-xs text-gray-500">{{ $product->sku }}</p>
                                    @else
                                        <span class="text-gray-400">Barang tidak aktif</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $product?->unitCode ?? '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-gray-900">{{ number_format((float) $item->qty, 3, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-gray-700">Rp {{ number_format((float) $item->unit_cost, 2, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-gray-900">Rp {{ number_format((float) $item->subtotal, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Total</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-base font-bold text-gray-900">
                                Rp {{ number_format((float) $receipt->total_amount, 2, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <p class="text-xs text-gray-500">
            Dokumen stok bersifat permanen dan tidak dapat diubah atau dihapus.
        </p>
    </div>
@endsection
