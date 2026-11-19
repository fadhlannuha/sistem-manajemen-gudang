<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('title')@yield('title') — @endif{{ config('app.name') }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 0; color: #1f2937; background: #f3f4f6; }
            .container { max-width: 960px; margin: 0 auto; padding: 0 1.5rem; }
            .card { background: #fff; border: 1px solid #e5e7eb; border-radius: .75rem; padding: 1.5rem; box-shadow: 0 1px 2px rgb(0 0 0 / .05); }
            .btn { display: inline-block; border: 0; border-radius: .5rem; padding: .6rem 1rem; font-weight: 600; cursor: pointer; text-decoration: none; }
            .btn-primary { background: #1d4ed8; color: #fff; }
            .btn-danger { background: #b91c1c; color: #fff; }
            .input { width: 100%; box-sizing: border-box; border: 1px solid #d1d5db; border-radius: .5rem; padding: .6rem .75rem; }
            .label { display: block; font-weight: 600; margin-bottom: .35rem; }
            .error { color: #b91c1c; font-size: .875rem; margin-top: .35rem; }
            .alert { border-radius: .5rem; padding: .75rem 1rem; margin-bottom: 1rem; }
            .alert-success { background: #dcfce7; color: #166534; }
            .alert-error { background: #fee2e2; color: #991b1b; }
        </style>
    @endif
</head>
<body class="min-h-screen bg-gray-100 text-gray-900 antialiased">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-6 py-4">
            <a href="{{ url('/') }}" class="text-lg font-semibold text-gray-900">
                {{ config('app.name') }}
            </a>

            <nav class="flex items-center gap-4 text-sm">
                @auth
                    <a href="{{ route('dashboard.index') }}" class="font-medium text-gray-700 hover:text-blue-700">Dashboard</a>
                    <a href="{{ route('products.index') }}" class="font-medium text-gray-700 hover:text-blue-700">Barang</a>
                    <a href="{{ route('categories.index') }}" class="font-medium text-gray-700 hover:text-blue-700">Kategori</a>
                    <a href="{{ route('units.index') }}" class="font-medium text-gray-700 hover:text-blue-700">Satuan</a>
                    <span class="hidden text-gray-500 sm:inline">{{ auth()->user()->name }}</span>

                    @foreach (auth()->user()->roles as $role)
                        <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800">{{ $role->label }}</span>
                    @endforeach

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-red-700 px-3 py-1.5 font-semibold text-white hover:bg-red-800">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="font-medium text-gray-700 hover:text-blue-700">Login</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-blue-700 px-3 py-1.5 font-semibold text-white hover:bg-blue-800">
                        Register
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-5xl px-6 py-8">
        @if (session('status'))
            <x-alert type="success" :message="session('status')" />
        @endif

        @if (session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-sm text-red-800" role="alert">
                <p class="font-semibold">Periksa kembali input berikut:</p>
                <ul class="mt-1 list-disc space-y-0.5 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
