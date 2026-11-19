@extends('layouts.app')

@section('title', 'Edit Satuan')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Satuan</h1>
            <p class="mt-1 text-sm text-gray-600">Perbarui data satuan {{ $unit->name }}.</p>
        </div>

        @include('product::units._form', [
            'action' => route('units.update', $unit),
            'method' => 'PUT',
            'unit' => $unit,
        ])
    </div>
@endsection
