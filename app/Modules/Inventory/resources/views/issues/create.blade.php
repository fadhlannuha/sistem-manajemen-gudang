@extends('layouts.app')

@section('title', 'Catat Stok Keluar')

@section('content')
    <div class="space-y-6">
        <div>
            <a href="{{ route('inventory.issues.index') }}" class="text-sm font-semibold text-blue-700 hover:underline">&larr; Kembali ke daftar stok keluar</a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">Catat Stok Keluar</h1>
            <p class="mt-1 text-sm text-gray-600">Stok dicek ulang di dalam transaksi sebelum dokumen disimpan.</p>
        </div>

        @include('inventory::issues._form', [
            'action' => route('inventory.issues.store'),
            'products' => $products,
            'balances' => $balances,
        ])
    </div>
@endsection
