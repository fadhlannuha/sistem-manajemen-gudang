@extends('layouts.app')

@section('title', 'Detail Stok Keluar')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('inventory.issues.index') }}" class="text-sm font-semibold text-blue-700 hover:underline">&larr; Kembali ke daftar stok keluar</a>
                <h1 class="mt-2 font-mono text-2xl font-bold text-gray-900">{{ $issue->number }}</h1>
                <p class="mt-1 text-sm text-gray-600">Pengeluaran tanggal {{ $issue->issued_at->format('d-m-Y') }}</p>
            </div>

            <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">Stok Keluar</span>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Informasi Dokumen</h2>

            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nomor Dokumen</dt>
                    <dd class="mt-1 font-mono text-sm font-semibold text-gray-900">{{ $issue->number }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Tanggal Pengeluaran</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $issue->issued_at->format('d-m-Y') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Tujuan</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $issue->destination ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Dicatat Oleh</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $actor?->name ?? 'Pengguna tidak tersedia' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Catatan</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm text-gray-900">{{ $issue->note ?: '—' }}</dd>
                </div>
            </dl>
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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($issue->items as $item)
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
                                <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-gray-900">
                                    {{ number_format((float) $item->qty, 3, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-xs text-gray-500">
            Dokumen stok bersifat permanen dan tidak dapat diubah atau dihapus.
        </p>
    </div>
@endsection
