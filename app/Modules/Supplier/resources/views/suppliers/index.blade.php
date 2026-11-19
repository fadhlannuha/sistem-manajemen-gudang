@extends('layouts.app')

@section('title', 'Pemasok')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pemasok</h1>
                <p class="mt-1 text-sm text-gray-600">Daftar pemasok beserta termin pembayarannya.</p>
            </div>

            @role('admin')
                <a href="{{ route('suppliers.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                    Tambah Pemasok
                </a>
            @endrole
        </div>

        <form method="GET" action="{{ route('suppliers.index') }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="search" class="mb-1 block text-sm font-semibold text-gray-700">Cari</label>
                    <input type="text" name="search" id="search" value="{{ $filters['search'] ?? '' }}"
                           placeholder="Nama atau kode"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div>
                    <label for="payment_term_id" class="mb-1 block text-sm font-semibold text-gray-700">Termin</label>
                    <select name="payment_term_id" id="payment_term_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        <option value="">Semua termin</option>
                        @foreach ($paymentTerms as $paymentTerm)
                            <option value="{{ $paymentTerm->id }}" @selected((string) ($filters['payment_term_id'] ?? '') === (string) $paymentTerm->id)>
                                {{ $paymentTerm->name }} ({{ $paymentTerm->days }} hari)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2 sm:col-span-2">
                    <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                        Filter
                    </button>
                    <a href="{{ route('suppliers.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Kontak</th>
                            <th class="px-4 py-3">Termin</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($suppliers as $supplier)
                            <tr class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold text-gray-700">{{ $supplier->code }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="font-medium text-blue-700 hover:underline">
                                        {{ $supplier->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    <p>{{ $supplier->contact_name ?: '—' }}</p>
                                    @if ($supplier->phone)
                                        <p class="text-xs text-gray-500">{{ $supplier->phone }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $supplier->paymentTerm->name }} ({{ $supplier->paymentTerm->days }} hari)</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="text-sm font-semibold text-blue-700 hover:underline">Detail</a>

                                        @role('admin')
                                            <a href="{{ route('suppliers.edit', $supplier) }}" class="text-sm font-semibold text-amber-700 hover:underline">Edit</a>

                                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                                                  onsubmit="return confirm('Nonaktifkan pemasok {{ $supplier->code }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-semibold text-red-700 hover:underline">Nonaktifkan</button>
                                            </form>
                                        @endrole
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                                    Belum ada pemasok yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($suppliers->hasPages())
                <div class="border-t border-gray-200 px-4 py-3">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
