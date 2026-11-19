@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tambah Kategori</h1>
            <p class="mt-1 text-sm text-gray-600">Slug dibuat otomatis dari nama kategori.</p>
        </div>

        @include('product::categories._form', [
            'action' => route('categories.store'),
            'method' => 'POST',
        ])
    </div>
@endsection
