@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kategori</h1>
                <p class="mt-1 text-sm text-gray-600">Kelompokkan barang berdasarkan kategori. Kategori yang masih dipakai tidak dapat dihapus.</p>
            </div>

            @role('admin')
                <a href="{{ route('categories.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                    Tambah Kategori
                </a>
            @endrole
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Slug</th>
                            <th class="px-4 py-3">Deskripsi</th>
                            <th class="px-4 py-3 text-right">Produk (aktif/total)</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($categories as $category)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $category->name }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $category->slug }}</td>
                                <td class="max-w-xs px-4 py-3 text-gray-600">{{ $category->description ?: '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-gray-700">{{ $category->products_count }} / {{ $category->all_products_count }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    @role('admin')
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('categories.edit', $category) }}" class="text-sm font-semibold text-amber-700 hover:underline">Edit</a>

                                            <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                                  onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-semibold text-red-700 hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endrole
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-500">Belum ada kategori.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($categories->hasPages())
                <div class="border-t border-gray-200 px-4 py-3">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
