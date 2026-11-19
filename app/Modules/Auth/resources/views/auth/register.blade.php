@extends('layouts.app', ['title' => 'Register'])

@section('content')
    <div class="mx-auto max-w-md">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold text-gray-900">Daftar Akun</h1>
            <p class="mt-1 text-sm text-gray-600">
                Akun baru otomatis mendapat role <strong>Staff Gudang</strong>. Role admin hanya dibuat lewat seeder.
            </p>

            <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-800">Nama</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        autocomplete="name"
                        required
                        autofocus
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                    <x-field-error name="name" />
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-800">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
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
                        autocomplete="new-password"
                        required
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                    <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter.</p>
                    <x-field-error name="password" />
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-800">Konfirmasi Password</label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                </div>

                <button type="submit" class="w-full rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
                    Daftar
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-gray-600">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-semibold text-blue-700 hover:underline">Masuk</a>
            </p>
        </div>
    </div>
@endsection
