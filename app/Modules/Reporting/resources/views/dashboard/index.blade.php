@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard</h1>
            <p class="mt-1 text-sm text-slate-600">
                Selamat datang, <strong class="font-semibold text-slate-800">{{ auth()->user()->name }}</strong>. Ringkasan stok dan aktivitas terakhir.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <a href="{{ route('products.index') }}"
               class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition-all hover:border-blue-200 hover:shadow-sm">
                <p class="text-sm font-medium text-slate-500">Total Barang</p>
                <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 tabular-nums">{{ $productCount }}</p>
            </a>

            <a href="{{ route('suppliers.index') }}"
               class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition-all hover:border-indigo-200 hover:shadow-sm">
                <p class="text-sm font-medium text-slate-500">Total Pemasok</p>
                <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 tabular-nums">{{ $supplierCount }}</p>
            </a>

            <a href="{{ route('inventory.balances.index', ['low_stock' => 1]) }}"
               class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition-all {{ $lowStockCount > 0 ? 'hover:border-red-200' : 'hover:border-emerald-200' }} hover:shadow-sm">
                <p class="text-sm font-medium text-slate-500">Stok Rendah</p>
                <p class="mt-2 text-3xl font-bold tracking-tight tabular-nums {{ $lowStockCount > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $lowStockCount }}</p>
            </a>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Stok Rendah</h2>
                    <a href="{{ route('inventory.balances.index', ['low_stock' => 1]) }}"
                       class="text-sm font-semibold text-blue-700 hover:text-blue-800 hover:underline">
                        Lihat Semua
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Barang</th>
                                <th class="px-4 py-3">Satuan</th>
                                <th class="px-4 py-3 text-right">Saldo</th>
                                <th class="px-5 py-3 text-right">Min.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($lowStock as $item)
                                <tr class="transition-colors hover:bg-slate-50/80">
                                    <td class="px-5 py-3.5">
                                        @if ($item['product'])
                                            <p class="font-medium leading-snug text-slate-900">{{ $item['product']->name }}</p>
                                            <p class="mt-0.5 font-mono text-xs text-slate-500">{{ $item['product']->sku }}</p>
                                        @else
                                            <span class="text-slate-400">Barang tidak aktif</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-slate-600">{{ $item['product']->unitCode ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-right font-semibold text-red-600 tabular-nums">
                                        {{ number_format($item['qty'], 3, ',', '.') }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-right text-slate-600 tabular-nums">
                                        {{ number_format($item['minStock'], 3, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-slate-500">
                                        Semua stok di atas batas minimum.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Transaksi Terakhir</h2>
                    <a href="{{ route('inventory.stock-card.index') }}"
                       class="text-sm font-semibold text-blue-700 hover:text-blue-800 hover:underline">
                        Kartu Stok
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-4 py-3">Barang</th>
                                <th class="px-4 py-3">Tipe</th>
                                <th class="px-4 py-3 text-right">Masuk</th>
                                <th class="px-5 py-3 text-right">Keluar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($recentMovements as $item)
                                @php($movement = $item['movement'])
                                <tr class="transition-colors hover:bg-slate-50/80">
                                    <td class="whitespace-nowrap px-5 py-3.5 text-slate-600 tabular-nums">
                                        {{ \Carbon\Carbon::parse($movement->movementDate)->format('d-m-Y') }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if ($item['product'])
                                            <p class="font-medium leading-snug text-slate-900">{{ $item['product']->name }}</p>
                                            <p class="mt-0.5 font-mono text-xs text-slate-500">{{ $item['product']->sku }}</p>
                                        @else
                                            <span class="text-slate-400">Barang tidak aktif</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                            {{ $movement->type === 'receipt' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200' : 'bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-200' }}">
                                            {{ $movement->type === 'receipt' ? 'Masuk' : 'Keluar' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-right text-slate-700 tabular-nums">
                                        {{ $movement->qtyIn > 0 ? number_format($movement->qtyIn, 3, ',', '.') : '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-right text-slate-700 tabular-nums">
                                        {{ $movement->qtyOut > 0 ? number_format($movement->qtyOut, 3, ',', '.') : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-slate-500">
                                        Belum ada transaksi stok.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="text-base font-semibold text-slate-900">Aktivitas Terakhir</h2>
                @role('admin')
                    <a href="{{ route('activity.index') }}"
                       class="text-sm font-semibold text-blue-700 hover:text-blue-800 hover:underline">
                        Lihat Semua
                    </a>
                @endrole
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($recentActivity as $log)
                    <li class="px-5 py-3.5 transition-colors hover:bg-slate-50/70">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <p class="text-sm font-medium text-slate-900">{{ $log->description }}</p>
                            <span class="whitespace-nowrap text-xs text-slate-500 tabular-nums">
                                {{ $log->created_at?->format('d-m-Y H:i') }}
                            </span>
                        </div>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                            <span>{{ $log->actor_name ?? 'Sistem' }}</span>
                            <span aria-hidden="true">&middot;</span>
                            <span class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] font-medium text-slate-600">{{ $log->event }}</span>
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-10 text-center text-sm text-slate-500">
                        Belum ada aktivitas tercatat.
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
