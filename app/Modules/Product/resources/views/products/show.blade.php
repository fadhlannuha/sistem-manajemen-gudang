@extends('layouts.app')

@section('title', 'Detail Barang')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('products.index') }}" class="text-sm font-semibold text-blue-700 hover:underline">&larr; Kembali ke daftar barang</a>
                <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
                <p class="mt-1 font-mono text-sm text-gray-600">{{ $product->sku }}</p>
            </div>

            @role('admin')
                <div class="flex items-center gap-2">
                    <a href="{{ route('products.edit', $product) }}" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                        Edit
                    </a>

                    <form method="POST" action="{{ route('products.destroy', $product) }}"
                          onsubmit="return confirm('Nonaktifkan barang {{ $product->sku }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">
                            Nonaktifkan
                        </button>
                    </form>
                </div>
            @endrole
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Informasi Barang</h2>

                    <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">SKU</dt>
                            <dd class="mt-1 font-mono text-sm font-semibold text-gray-900">{{ $product->sku }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Satuan</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $product->unit->name }} ({{ $product->unit->code }})</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Stok Minimum</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ (float) $product->min_stock }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Dibuat</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $product->created_at?->format('d M Y H:i') ?? '—' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6">
                        <dt class="text-sm font-medium text-gray-500">Deskripsi</dt>
                        <dd class="mt-1 whitespace-pre-line text-sm text-gray-900">{{ $product->description ?: '—' }}</dd>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Kategori</h2>

                <div class="mt-4 flex flex-wrap gap-2">
                    @forelse ($product->categories as $category)
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700">{{ $category->name }}</span>
                    @empty
                        <p class="text-sm text-gray-500">Barang ini belum memiliki kategori.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
