<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $pageTitle = trim($__env->yieldContent('title')) ?: ($title ?? null);
    @endphp
    <title>{{ $pageTitle ? $pageTitle.' — ' : '' }}{{ config('app.name') }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 0; color: #1f2937; background: #f8fafc; }
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
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-900 antialiased">
    @auth
        @php
            $navLinkClass = fn (string $pattern): string => request()->routeIs($pattern)
                ? 'group flex items-center gap-3 rounded-lg bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700'
                : 'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900';

            $navIconClass = fn (string $pattern): string => request()->routeIs($pattern)
                ? 'h-4 w-4 shrink-0 text-blue-700'
                : 'h-4 w-4 shrink-0 text-slate-400 transition-colors group-hover:text-slate-600';

            $userInitial = mb_strtoupper(mb_substr(auth()->user()->name, 0, 1));
        @endphp

        <header class="sticky top-0 z-30 h-16 w-full border-b border-slate-200 bg-white/95 backdrop-blur-xs lg:hidden">
            <div class="flex h-full w-full items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        id="sidebar-open-btn"
                        aria-label="Buka menu navigasi"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900 lg:hidden"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <a href="{{ route('dashboard.index') }}" class="flex items-center gap-2.5 whitespace-nowrap">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-700 text-white shadow-xs">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                        </span>
                        <span class="text-base font-bold tracking-tight text-slate-900">{{ config('app.name') }}</span>
                    </a>
                </div>

                <div class="flex shrink-0 items-center gap-3">
                    <div class="hidden items-center gap-2.5 sm:flex">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200">
                            {{ $userInitial }}
                        </span>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-slate-700">{{ auth()->user()->name }}</span>
                            @foreach (auth()->user()->roles as $role)
                                <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200/80">
                                    {{ $role->label }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <span class="hidden h-5 w-px bg-slate-200 sm:block" aria-hidden="true"></span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-sm font-semibold text-red-700 transition-colors hover:bg-red-600 hover:border-red-600 hover:text-white"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                            </svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <div class="relative flex flex-1">
            <div
                id="sidebar-backdrop"
                class="fixed inset-0 z-30 hidden bg-slate-900/40 backdrop-blur-[1px] lg:hidden"
                aria-hidden="true"
            ></div>

            <aside
                id="app-sidebar"
                class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 ease-out lg:sticky lg:top-0 lg:z-20 lg:h-screen lg:translate-x-0"
            >
                <div class="flex h-16 items-center justify-between border-b border-slate-200 px-4">
                    <a href="{{ route('dashboard.index') }}" class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-700 text-white shadow-xs">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                        </span>
                        <div class="leading-tight">
                            <span class="block text-sm font-bold tracking-tight text-slate-900">Sistem Manajemen</span>
                            <span class="block text-[10px] font-semibold uppercase tracking-widest text-blue-700">Gudang</span>
                        </div>
                    </a>
                    <button
                        type="button"
                        id="sidebar-close-btn"
                        aria-label="Tutup menu navigasi"
                        class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800 lg:hidden"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5" aria-label="Navigasi utama">
                    <div class="space-y-1">
                        <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Utama</p>
                        <a href="{{ route('dashboard.index') }}" class="{{ $navLinkClass('dashboard.*') }}">
                            <svg class="{{ $navIconClass('dashboard.*') }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </div>

                    <div class="space-y-1">
                        <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Master Data</p>
                        <a href="{{ route('products.index') }}" class="{{ $navLinkClass('products.*') }}">
                            <svg class="{{ $navIconClass('products.*') }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                            </svg>
                            <span>Barang</span>
                        </a>
                        <a href="{{ route('categories.index') }}" class="{{ $navLinkClass('categories.*') }}">
                            <svg class="{{ $navIconClass('categories.*') }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                            </svg>
                            <span>Kategori</span>
                        </a>
                        <a href="{{ route('units.index') }}" class="{{ $navLinkClass('units.*') }}">
                            <svg class="{{ $navIconClass('units.*') }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" />
                            </svg>
                            <span>Satuan</span>
                        </a>
                        <a href="{{ route('suppliers.index') }}" class="{{ $navLinkClass('suppliers.*') }}">
                            <svg class="{{ $navIconClass('suppliers.*') }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                            </svg>
                            <span>Pemasok</span>
                        </a>
                        <a href="{{ route('payment-terms.index') }}" class="{{ $navLinkClass('payment-terms.*') }}">
                            <svg class="{{ $navIconClass('payment-terms.*') }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span>Termin</span>
                        </a>
                    </div>

                    <div class="space-y-1">
                        <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Inventaris</p>
                        <a href="{{ route('inventory.balances.index') }}" class="{{ $navLinkClass('inventory.balances.*') }}">
                            <svg class="{{ $navIconClass('inventory.balances.*') }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3" />
                            </svg>
                            <span>Saldo</span>
                        </a>
                        <a href="{{ route('inventory.receipts.index') }}" class="{{ $navLinkClass('inventory.receipts.*') }}">
                            <svg class="{{ $navIconClass('inventory.receipts.*') }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            <span>Stok Masuk</span>
                        </a>
                        <a href="{{ route('inventory.issues.index') }}" class="{{ $navLinkClass('inventory.issues.*') }}">
                            <svg class="{{ $navIconClass('inventory.issues.*') }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                            <span>Stok Keluar</span>
                        </a>
                        <a href="{{ route('inventory.stock-card.index') }}" class="{{ $navLinkClass('inventory.stock-card.*') }}">
                            <svg class="{{ $navIconClass('inventory.stock-card.*') }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                            </svg>
                            <span>Kartu Stok</span>
                        </a>
                    </div>

                    @role('admin')
                        <div class="space-y-1">
                            <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Sistem</p>
                            <a href="{{ route('activity.index') }}" class="{{ $navLinkClass('activity.*') }}">
                                <svg class="{{ $navIconClass('activity.*') }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z" />
                                </svg>
                                <span>Activity Log</span>
                            </a>
                        </div>
                    @endrole
                </nav>

                <div class="border-t border-slate-200 p-3">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200">
                            {{ $userInitial }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <div class="mt-2.5 flex items-center justify-between gap-2">
                        <div class="flex flex-wrap gap-1">
                            @foreach (auth()->user()->roles as $role)
                                <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700">{{ $role->label }}</span>
                            @endforeach
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-red-600 transition-colors hover:bg-red-50"
                            >
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <main class="min-w-0 flex-1">
                <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    @if (session('status'))
                        <x-alert type="success" :message="session('status')" class="mb-6" />
                    @endif

                    @if (session('error'))
                        <x-alert type="error" :message="session('error')" class="mb-6" />
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-xs" role="alert">
                            <p class="font-semibold">Periksa kembali input berikut:</p>
                            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>

        <script>
            (() => {
                const sidebar = document.getElementById('app-sidebar');
                const backdrop = document.getElementById('sidebar-backdrop');
                const openBtn = document.getElementById('sidebar-open-btn');
                const closeBtn = document.getElementById('sidebar-close-btn');

                if (!sidebar || !backdrop || !openBtn) return;

                const toggle = (open) => {
                    sidebar.classList.toggle('-translate-x-full', !open);
                    backdrop.classList.toggle('hidden', !open);
                };

                openBtn.addEventListener('click', () => toggle(true));
                closeBtn?.addEventListener('click', () => toggle(false));
                backdrop.addEventListener('click', () => toggle(false));
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') toggle(false);
                });
            })();
        </script>
    @else
        <main class="flex flex-1 flex-col justify-center px-4 py-10 sm:px-6">
            <div class="mx-auto mb-6 flex items-center justify-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-700 text-white shadow-xs">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                    </svg>
                </span>
                <div class="leading-tight">
                    <span class="block text-base font-bold tracking-tight text-slate-900">Sistem Manajemen</span>
                    <span class="block text-xs font-semibold uppercase tracking-widest text-blue-700">Gudang</span>
                </div>
            </div>

            <div class="mx-auto w-full max-w-md">
                @if (session('status'))
                    <x-alert type="success" :message="session('status')" class="mb-5" />
                @endif

                @if (session('error'))
                    <x-alert type="error" :message="session('error')" class="mb-5" />
                @endif

                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-xs" role="alert">
                        <p class="font-semibold">Periksa kembali input berikut:</p>
                        <ul class="mt-1 list-disc space-y-0.5 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            @yield('content')

            <p class="mt-8 text-center text-xs text-slate-400">
                Sistem Manajemen Gudang · Laravel {{ app()->version() }} · PHP {{ PHP_VERSION }}
            </p>
        </main>
    @endauth
</body>
</html>
