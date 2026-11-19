@extends('layouts.app')

@section('title', 'Edit Termin')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Termin</h1>
            <p class="mt-1 text-sm text-gray-600">Perbarui data termin {{ $paymentTerm->name }}.</p>
        </div>

        @include('supplier::payment_terms._form', [
            'action' => route('payment-terms.update', $paymentTerm),
            'method' => 'PUT',
            'paymentTerm' => $paymentTerm,
        ])
    </div>
@endsection
