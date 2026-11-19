@extends('layouts.app')

@section('title', 'Edit Pemasok')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Pemasok</h1>
            <p class="mt-1 text-sm text-gray-600">Perbarui data {{ $supplier->code }} — {{ $supplier->name }}.</p>
        </div>

        @include('supplier::suppliers._form', [
            'action' => route('suppliers.update', $supplier),
            'method' => 'PUT',
            'supplier' => $supplier,
            'paymentTerms' => $paymentTerms,
        ])
    </div>
@endsection
