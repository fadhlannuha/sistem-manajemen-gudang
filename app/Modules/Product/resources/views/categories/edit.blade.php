@extends('layouts.app')

@section('title', 'Edit Kategori')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Kategori</h1>
            <p class="mt-1 text-sm text-gray-600">Perbarui data kategori {{ $category->name }}.</p>
        </div>

        @include('product::categories._form', [
            'action' => route('categories.update', $category),
            'method' => 'PUT',
            'category' => $category,
        ])
    </div>
@endsection
