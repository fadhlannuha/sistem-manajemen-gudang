# Sistem Manajemen Gudang

Proyek UTS **Backend Development (DT199)** — studi kasus **4. Sistem Manajemen Gudang (Warehouse)**.

| Item | Isi |
|---|---|
| Nama | Fadhlan Nuha M. |
| NIM | 25.01.5292 |
| Kelas | `<isi kelas>` |
| Mata kuliah | Backend Development (DT199), 4 SKS |
| Semester | Ganjil 2026/2027 |
| Dosen | Hastari Utama, M.Cs |
| Repository | https://github.com/fadhlannuha/sistem-manajemen-gudang |
| Video demo | menyusul (YouTube unlisted) |

## Tentang Aplikasi

Aplikasi web modular monolith untuk mengelola barang, pemasok, stok masuk/keluar, saldo stok, kartu stok, dan activity log. Hak akses dibagi menjadi **Admin** dan **Staff Gudang**.

- **Stack:** Laravel 13, PHP 8.4+, SQLite, Blade, Vite/Tailwind, Laravel Sanctum.
- **Arsitektur:** modular monolith dengan 5 modul: `Auth`, `Product`, `Supplier`, `Inventory`, `Reporting`.
- **Komunikasi antar modul:** Contract untuk query sinkron dan domain event untuk efek samping. Tidak ada modul yang mengakses model/tabel modul lain secara langsung.
- **Database:** SQLite; `stock_movements` menjadi ledger kartu stok, `stock_balances` menyimpan saldo terkini.
- **Transaksi stok:** `DB::transaction()` + `lockForUpdate()` + validasi ulang stok di dalam transaksi.

## Status

**M0 — Fondasi selesai. M1 — Auth & RBAC selesai (2026-11-19).** Laravel 13.43.0, SQLite, Sanctum, session web, token API, role `admin`/`staff_gudang`, middleware `role`, dan `UserDirectoryContract` sudah berjalan. Modul Product–Reporting belum diimplementasikan.

### Akun Demo

Setelah `php artisan migrate:fresh --seed`:

| Email | Password | Role |
|---|---|---|
| `admin@warehouse.test` | `password` | `admin` |
| `staff@warehouse.test` | `password` | `staff_gudang` |
| `staff2@warehouse.test` | `password` | `staff_gudang` |

Endpoint API: `POST /api/v1/auth/login`, `POST /api/v1/auth/logout`, `GET /api/v1/me`.

## Dokumentasi

Semua perencanaan, arsitektur, schema, kontrak, aturan RBAC, error handling, testing, setup, laporan, dan checklist ada di folder [`dokumentasi/`](dokumentasi/README.md).

Mulai dari:

1. [`dokumentasi/PLAN.md`](dokumentasi/PLAN.md) — milestone dan backlog.
2. [`dokumentasi/ARCHITECTURE.md`](dokumentasi/ARCHITECTURE.md) — batas modul dan provider.
3. [`dokumentasi/DATABASE.md`](dokumentasi/DATABASE.md) — ERD dan schema.
4. [`dokumentasi/CONTRACTS.md`](dokumentasi/CONTRACTS.md) — contract, DTO, event.

## Instalasi Lokal

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run build
php artisan serve
```

Konfigurasi default memakai SQLite, locale `id`, dan timezone `Asia/Jakarta`.

## Perintah Penting

```bash
php artisan test
vendor/bin/pint --test
php artisan route:list --except-vendor
php artisan migrate:fresh --seed
```

## Aturan Kontribusi

Proyek ini solo, tetapi aturan kerja untuk agen/AI dan kontributor ada di [`dokumentasi/AGENTS.md`](dokumentasi/AGENTS.md). Patuhi batas modul, gunakan Form Request, contract, event, dan transaksi database sesuai dokumen tersebut.

## Lisensi

Kode aplikasi menggunakan lisensi MIT. Dokumen dan laporan UTS merupakan karya Fadhlan Nuha M.
