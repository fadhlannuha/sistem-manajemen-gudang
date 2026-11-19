@extends('layouts.app')

@section('title', 'Tambah Termin')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tambah Termin</h1>
            <p class="mt-1 text-sm text-gray-600">Tentukan nama termin dan jumlah hari jatuh tempo.</p>
        </div>

        @include('supplier::payment_terms._form', [
            'action' => route('payment-terms.store'),
            'method' => 'POST',
        ])
    </div>
@endsection
