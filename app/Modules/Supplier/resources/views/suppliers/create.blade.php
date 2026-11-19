@extends('layouts.app')

@section('title', 'Tambah Pemasok')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tambah Pemasok</h1>
            <p class="mt-1 text-sm text-gray-600">Lengkapi data pemasok dan pilih termin pembayaran.</p>
        </div>

        @include('supplier::suppliers._form', [
            'action' => route('suppliers.store'),
            'method' => 'POST',
            'paymentTerms' => $paymentTerms,
        ])
    </div>
@endsection
