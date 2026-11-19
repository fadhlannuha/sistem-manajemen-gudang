@extends('layouts.app')

@section('title', 'Tambah Barang')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tambah Barang</h1>
            <p class="mt-1 text-sm text-gray-600">Lengkapi data barang, satuan, dan kategori.</p>
        </div>

        @include('product::products._form', [
            'action' => route('products.store'),
            'method' => 'POST',
            'categories' => $categories,
            'units' => $units,
        ])
    </div>
@endsection
