@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600">
                Selamat datang, <strong>{{ auth()->user()->name }}</strong>.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold text-gray-500">Akun</p>
                <p class="mt-2 text-lg font-bold text-gray-900">{{ auth()->user()->email }}</p>
                <p class="mt-1 text-sm text-gray-600">
                    Role:
                    @foreach (auth()->user()->roles as $role)
                        <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-800">{{ $role->label }}</span>
                    @endforeach
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold text-gray-500">Status modul</p>
                <p class="mt-2 text-lg font-bold text-gray-900">M1 — Auth &amp; RBAC</p>
                <p class="mt-1 text-sm text-gray-600">
                    Master data, transaksi stok, dan laporan akan ditambahkan pada milestone berikutnya.
                </p>
            </div>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            Halaman dashboard ini masih placeholder sampai modul Reporting (M5) selesai.
        </div>
    </div>
@endsection
