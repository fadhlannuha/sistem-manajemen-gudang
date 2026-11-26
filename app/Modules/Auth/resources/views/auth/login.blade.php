@extends('layouts.app', ['title' => 'Login'])

@section('content')
    <div class="mx-auto w-full max-w-md">
        <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-xs">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Login</h1>
                <p class="mt-1.5 text-sm text-slate-600">Masuk untuk mengelola barang, pemasok, dan stok gudang.</p>
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        autofocus
                        class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100"
                    >
                    <x-field-error name="email" />
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100"
                    >
                    <x-field-error name="password" />
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500">
                    Ingat saya
                </label>

                <button type="submit" class="w-full rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition-colors hover:bg-blue-800">
                    Masuk
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-slate-600">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-blue-700 hover:text-blue-800 hover:underline">Daftar sebagai staff gudang</a>
            </p>
        </div>

        <div class="mt-4 rounded-xl border border-blue-200/80 bg-blue-50/70 p-4 text-sm text-blue-900">
            <p class="font-semibold">Akun demo (setelah seeder dijalankan)</p>
            <div class="mt-2 space-y-1 text-xs sm:text-sm">
                <p>Admin: <code class="rounded bg-white/80 px-1.5 py-0.5 font-mono text-xs text-blue-950">admin@warehouse.test</code> / <code class="rounded bg-white/80 px-1.5 py-0.5 font-mono text-xs text-blue-950">password</code></p>
                <p>Staff: <code class="rounded bg-white/80 px-1.5 py-0.5 font-mono text-xs text-blue-950">staff@warehouse.test</code> / <code class="rounded bg-white/80 px-1.5 py-0.5 font-mono text-xs text-blue-950">password</code></p>
            </div>
        </div>
    </div>
@endsection
