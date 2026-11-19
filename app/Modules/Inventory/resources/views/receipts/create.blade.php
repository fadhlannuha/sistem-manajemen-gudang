@extends('layouts.app')

@section('title', 'Catat Stok Masuk')

@section('content')
    <div class="space-y-6">
        <div>
            <a href="{{ route('inventory.receipts.index') }}" class="text-sm font-semibold text-blue-700 hover:underline">&larr; Kembali ke daftar stok masuk</a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">Catat Stok Masuk</h1>
            <p class="mt-1 text-sm text-gray-600">Nomor dokumen dibuat otomatis saat data disimpan.</p>
        </div>

        @include('inventory::receipts._form', [
            'action' => route('inventory.receipts.store'),
            'suppliers' => $suppliers,
            'products' => $products,
        ])
    </div>
@endsection
