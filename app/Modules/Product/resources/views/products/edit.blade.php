@extends('layouts.app')

@section('title', 'Edit Barang')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Barang</h1>
            <p class="mt-1 text-sm text-gray-600">Perbarui data {{ $product->sku }} — {{ $product->name }}.</p>
        </div>

        @include('product::products._form', [
            'action' => route('products.update', $product),
            'method' => 'PUT',
            'product' => $product,
            'categories' => $categories,
            'units' => $units,
        ])
    </div>
@endsection
