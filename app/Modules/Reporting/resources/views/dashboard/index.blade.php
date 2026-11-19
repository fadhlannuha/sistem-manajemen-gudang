@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600">
                Selamat datang, <strong>{{ auth()->user()->name }}</strong>. Ringkasan stok dan aktivitas terakhir.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold text-gray-500">Total Barang</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $productCount }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold text-gray-500">Total Pemasok</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $supplierCount }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold text-gray-500">Stok Rendah</p>
                <p class="mt-2 text-3xl font-bold {{ $lowStockCount > 0 ? 'text-red-700' : 'text-gray-900' }}">{{ $lowStockCount }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Stok Rendah</h2>
                    <a href="{{ route('inventory.balances.index', ['low_stock' => 1]) }}"
                       class="text-sm font-semibold text-blue-700 hover:underline">
                        Lihat Semua
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Barang</th>
                                <th class="px-4 py-3">Satuan</th>
                                <th class="px-4 py-3 text-right">Saldo</th>
                                <th class="px-4 py-3 text-right">Min.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($lowStock as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        @if ($item['product'])
                                            <p class="font-medium text-gray-900">{{ $item['product']->name }}</p>
                                            <p class="font-mono text-xs text-gray-500">{{ $item['product']->sku }}</p>
                                        @else
                                            <span class="text-gray-400">Barang tidak aktif</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $item['product']->unitCode ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-red-700">
                                        {{ number_format($item['qty'], 3, ',', '.') }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-gray-700">
                                        {{ number_format($item['minStock'], 3, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-gray-500">
                                        Semua stok di atas batas minimum.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Transaksi Terakhir</h2>
                    <a href="{{ route('inventory.stock-card.index') }}"
                       class="text-sm font-semibold text-blue-700 hover:underline">
                        Kartu Stok
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Barang</th>
                                <th class="px-4 py-3">Tipe</th>
                                <th class="px-4 py-3 text-right">Masuk</th>
                                <th class="px-4 py-3 text-right">Keluar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($recentMovements as $item)
                                @php($movement = $item['movement'])
                                <tr class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                        {{ \Carbon\Carbon::parse($movement->movementDate)->format('d-m-Y') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($item['product'])
                                            <p class="font-medium text-gray-900">{{ $item['product']->name }}</p>
                                            <p class="font-mono text-xs text-gray-500">{{ $item['product']->sku }}</p>
                                        @else
                                            <span class="text-gray-400">Barang tidak aktif</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold
                                            {{ $movement->type === 'receipt' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $movement->type === 'receipt' ? 'Masuk' : 'Keluar' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-gray-700">
                                        {{ $movement->qtyIn > 0 ? number_format($movement->qtyIn, 3, ',', '.') : '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-gray-700">
                                        {{ $movement->qtyOut > 0 ? number_format($movement->qtyOut, 3, ',', '.') : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                                        Belum ada transaksi stok.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Aktivitas Terakhir</h2>
                @role('admin')
                    <a href="{{ route('activity.index') }}"
                       class="text-sm font-semibold text-blue-700 hover:underline">
                        Lihat Semua
                    </a>
                @endrole
            </div>
            <ul class="divide-y divide-gray-100">
                @forelse ($recentActivity as $log)
                    <li class="px-6 py-3">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <p class="text-sm text-gray-900">{{ $log->description }}</p>
                            <span class="whitespace-nowrap text-xs text-gray-500">
                                {{ $log->created_at?->format('d-m-Y H:i') }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ $log->actor_name ?? 'Sistem' }} · {{ $log->event }}
                        </p>
                    </li>
                @empty
                    <li class="px-6 py-10 text-center text-sm text-gray-500">
                        Belum ada aktivitas tercatat.
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection