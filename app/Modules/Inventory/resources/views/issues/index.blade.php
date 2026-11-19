@extends('layouts.app')

@section('title', 'Stok Keluar')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Stok Keluar</h1>
                <p class="mt-1 text-sm text-gray-600">Dokumen pengeluaran barang beserta tujuannya.</p>
            </div>

            @role('admin', 'staff_gudang')
                <a href="{{ route('inventory.issues.create') }}" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                    Catat Stok Keluar
                </a>
            @endrole
        </div>

        <form method="GET" action="{{ route('inventory.issues.index') }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <label for="search" class="mb-1 block text-sm font-semibold text-gray-700">Cari</label>
                    <input type="text" name="search" id="search" value="{{ $filters['search'] ?? '' }}"
                           placeholder="Nomor dokumen atau tujuan"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div>
                    <label for="from" class="mb-1 block text-sm font-semibold text-gray-700">Dari</label>
                    <input type="date" name="from" id="from" value="{{ $filters['from'] ?? '' }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div>
                    <label for="to" class="mb-1 block text-sm font-semibold text-gray-700">Sampai</label>
                    <input type="date" name="to" id="to" value="{{ $filters['to'] ?? '' }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                        Filter
                    </button>
                    <a href="{{ route('inventory.issues.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
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
                            <th class="px-4 py-3">Nomor</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Tujuan</th>
                            <th class="px-4 py-3 text-right">Item</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($issues as $issue)
                            <tr class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold text-gray-700">{{ $issue->number ?: '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $issue->issued_at->format('d-m-Y') }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $issue->destination ?: '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-gray-700">{{ $issue->items_count }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <a href="{{ route('inventory.issues.show', $issue) }}" class="text-sm font-semibold text-blue-700 hover:underline">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                                    Belum ada dokumen stok keluar yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($issues->hasPages())
                <div class="border-t border-gray-200 px-4 py-3">
                    {{ $issues->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
