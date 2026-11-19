@extends('layouts.app')

@section('title', 'Detail Pemasok')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('suppliers.index') }}" class="text-sm font-semibold text-blue-700 hover:underline">&larr; Kembali ke daftar pemasok</a>
                <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $supplier->name }}</h1>
                <p class="mt-1 font-mono text-sm text-gray-600">{{ $supplier->code }}</p>
            </div>

            @role('admin')
                <div class="flex items-center gap-2">
                    <a href="{{ route('suppliers.edit', $supplier) }}" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                        Edit
                    </a>

                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                          onsubmit="return confirm('Nonaktifkan pemasok {{ $supplier->code }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">
                            Nonaktifkan
                        </button>
                    </form>
                </div>
            @endrole
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Informasi Pemasok</h2>

                    <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Kode</dt>
                            <dd class="mt-1 font-mono text-sm font-semibold text-gray-900">{{ $supplier->code }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Termin Pembayaran</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $supplier->paymentTerm->name }} ({{ $supplier->paymentTerm->days }} hari)</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nama Kontak</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $supplier->contact_name ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Telepon</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $supplier->phone ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $supplier->email ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Dibuat</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $supplier->created_at?->format('d M Y H:i') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Alamat</h2>
                <p class="mt-4 whitespace-pre-line text-sm text-gray-700">{{ $supplier->address ?: '—' }}</p>
            </div>
        </div>
    </div>
@endsection
