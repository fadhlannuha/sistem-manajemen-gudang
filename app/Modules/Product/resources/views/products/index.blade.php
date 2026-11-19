@extends('layouts.app')

@section('title', 'Barang')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Barang</h1>
                <p class="mt-1 text-sm text-gray-600">Daftar barang beserta satuan dan kategorinya.</p>
            </div>

            @role('admin')
                <a href="{{ route('products.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                    Tambah Barang
                </a>
            @endrole
        </div>

        <form method="GET" action="{{ route('products.index') }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="search" class="mb-1 block text-sm font-semibold text-gray-700">Cari</label>
                    <input type="text" name="search" id="search" value="{{ $filters['search'] ?? '' }}"
                           placeholder="Nama atau SKU"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div>
                    <label for="category_id" class="mb-1 block text-sm font-semibold text-gray-700">Kategori</label>
                    <select name="category_id" id="category_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="unit_id" class="mb-1 block text-sm font-semibold text-gray-700">Satuan</label>
                    <select name="unit_id" id="unit_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        <option value="">Semua satuan</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) ($filters['unit_id'] ?? '') === (string) $unit->id)>
                                {{ $unit->name }} ({{ $unit->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                        Filter
                    </button>
                    <a href="{{ route('products.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
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
                            <th class="px-4 py-3">SKU</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Satuan</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3 text-right">Stok Minimum</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold text-gray-700">{{ $product->sku }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('products.show', $product) }}" class="font-medium text-blue-700 hover:underline">
                                        {{ $product->name }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $product->unit->name }} ({{ $product->unit->code }})</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($product->categories as $category)
                                            <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">{{ $category->name }}</span>
                                        @empty
                                            <span class="text-gray-400">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-gray-700">{{ (float) $product->min_stock }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('products.show', $product) }}" class="text-sm font-semibold text-blue-700 hover:underline">Detail</a>

                                        @role('admin')
                                            <a href="{{ route('products.edit', $product) }}" class="text-sm font-semibold text-amber-700 hover:underline">Edit</a>

                                            <form method="POST" action="{{ route('products.destroy', $product) }}"
                                                  onsubmit="return confirm('Nonaktifkan barang {{ $product->sku }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-semibold text-red-700 hover:underline">Nonaktifkan</button>
                                            </form>
                                        @endrole
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                                    Belum ada barang yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($products->hasPages())
                <div class="border-t border-gray-200 px-4 py-3">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
