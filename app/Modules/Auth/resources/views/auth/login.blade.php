@extends('layouts.app', ['title' => 'Login'])

@section('content')
    <div class="mx-auto max-w-md">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold text-gray-900">Login</h1>
            <p class="mt-1 text-sm text-gray-600">Masuk untuk mengelola barang, pemasok, dan stok gudang.</p>

            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-800">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        autofocus
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                    <x-field-error name="email" />
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-800">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                    <x-field-error name="password" />
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="rounded border-gray-300">
                    Ingat saya
                </label>

                <button type="submit" class="w-full rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
                    Masuk
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-gray-600">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-blue-700 hover:underline">Daftar sebagai staff gudang</a>
            </p>
        </div>

        <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
            <p class="font-semibold">Akun demo (setelah seeder dijalankan)</p>
            <p class="mt-1">Admin: <code>admin@warehouse.test</code> / <code>password</code></p>
            <p>Staff: <code>staff@warehouse.test</code> / <code>password</code></p>
        </div>
    </div>
@endsection
