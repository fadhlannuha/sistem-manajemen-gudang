@extends('layouts.app')

@section('title', 'Tambah Satuan')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tambah Satuan</h1>
            <p class="mt-1 text-sm text-gray-600">Kode satuan otomatis diubah menjadi huruf kapital.</p>
        </div>

        @include('product::units._form', [
            'action' => route('units.store'),
            'method' => 'POST',
        ])
    </div>
@endsection
