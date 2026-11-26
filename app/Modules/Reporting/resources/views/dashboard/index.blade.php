@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Selamat datang, <strong class="font-semibold text-slate-800">{{ auth()->user()->name }}</strong>. Ringkasan stok dan aktivitas terakhir.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('inventory.receipts.create') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-blue-700 px-3.5 py-2 text-sm font-semibold text-white shadow-xs transition-colors hover:bg-blue-800">
                    <span>+ Stok Masuk</span>
                </a>
                <a href="{{ route('inventory.issues.create') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-xs transition-colors hover:bg-slate-50">
                    <span>+ Stok Keluar</span>
                </a>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <a href="{{ route('products.index') }}"
               class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition-all hover:border-blue-200 hover:shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Barang</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 tabular-nums">{{ $productCount }}</p>
                    </div>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700 transition-colors group-hover:bg-blue-100">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                        </svg>
                    </span>
                </div>
            </a>

            <a href="{{ route('suppliers.index') }}"
               class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition-all hover:border-indigo-200 hover:shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Pemasok</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 tabular-nums">{{ $supplierCount }}</p>
                    </div>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 transition-colors group-hover:bg-indigo-100">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                        </svg>
                    </span>
                </div>
            </a>

            <a href="{{ route('inventory.balances.index', ['low_stock' => 1]) }}"
               class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition-all {{ $lowStockCount > 0 ? 'hover:border-red-200' : 'hover:border-emerald-200' }} hover:shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Stok Rendah</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight tabular-nums {{ $lowStockCount > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $lowStockCount }}</p>
                    </div>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-colors {{ $lowStockCount > 0 ? 'bg-red-50 text-red-600 group-hover:bg-red-100' : 'bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </span>
                </div>
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
