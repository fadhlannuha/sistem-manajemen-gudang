# Sistem Manajemen Gudang

Aplikasi manajemen gudang (warehouse) untuk UTS mata kuliah Backend Development (DT199). Dibangun dengan Laravel 13 menggunakan arsitektur modular monolith: satu aplikasi dan satu deployment, dengan batas antar modul yang dijaga melalui contract dan domain event.

- **Kelas:** 25 D3TI 02 — Program Studi D3 Teknik Informatika, Universitas AMIKOM Yogyakarta
- **Anggota:**
  - Fadhlan Nuha Mukhlishin (`25.01.5292`)
  - Najib Arafi (`25.01.5296`)
- **Video demo (YouTube, unlisted):** *(menyusul)*

## Modul

Seluruh kode domain berada di `app/Modules/`, dipisah ke dalam 5 modul yang masing-masing memiliki `ServiceProvider`, migrasi, route, model, dan view sendiri.

| Modul | Tanggung Jawab |
|---|---|
| `Auth` | Login, register, logout (session web & token Sanctum), role `admin` dan `staff_gudang`, middleware `role`, direktif `@role` |
| `Product` | CRUD barang (soft delete), kategori, satuan, relasi 1:N (`units` → `products`) dan M:N (`category_product`) |
| `Supplier` | CRUD pemasok (soft delete) dan termin pembayaran, relasi 1:N (`payment_terms` → `suppliers`) |
| `Inventory` | Stok masuk, stok keluar, saldo stok, kartu stok (ledger mutasi), validasi stok dan rollback transaksi |
| `Reporting` | Dashboard ringkasan, peringatan stok rendah, dan activity log khusus admin |

Modul tidak mengakses model atau tabel milik modul lain secara langsung. Query lintas modul memakai empat contract (`UserDirectoryContract`, `ProductCatalogContract`, `SupplierDirectoryContract`, `InventoryQueryContract`) yang mengembalikan DTO, sedangkan efek samping setelah transaksi dikirim lewat 10 domain event (`ShouldDispatchAfterCommit`).

## Fitur Utama

- Autentikasi session web dan API token **Laravel Sanctum** (`/api/v1/auth/login`, `/api/v1/auth/logout`, `/api/v1/me`) dengan rate limiting login.
- **RBAC** untuk dua peran: `admin` (akses penuh master data, stok, dan activity log) dan `staff_gudang` (baca master data dan transaksi stok; akses route admin ditolak dengan HTTP 403).
- CRUD barang, kategori, satuan, pemasok, dan termin pembayaran, lengkap dengan soft delete pada barang/pemasok serta delete guard pada kategori, satuan, dan termin yang masih dipakai.
- Transaksi stok masuk (`SR-YYYYMM-000001`) dan stok keluar (`SI-YYYYMM-000001`) multi-item di dalam `DB::transaction()` dengan `lockForUpdate()`. Jika stok salah satu item tidak cukup, `InsufficientStockException` dilempar dan seluruh transaksi di-rollback.
- Saldo stok dengan penanda stok rendah dan kartu stok dengan filter barang serta rentang tanggal.
- Validasi seluruh input memakai 20 kelas **Form Request** dan 3 custom exception (`InsufficientStockException`, `ProductUnavailableException`, `SupplierUnavailableException`).
- Dashboard ringkasan operasional dan halaman Activity Log admin-only yang mencatat seluruh domain event.

## Cara Menjalankan

Membutuhkan PHP 8.4+, Composer, Node.js, dan SQLite.

```bash
git clone https://github.com/fadhlannuha/sistem-manajemen-gudang.git
cd sistem-manajemen-gudang
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan serve
```

Buka `http://127.0.0.1:8000`.

## Akun Demo

| Role | Email | Password |
|---|---|---|
| Admin | `admin@warehouse.test` | `password` |
| Staff Gudang | `staff@warehouse.test` | `password` |

Seeder mengisi 5 kategori, 5 satuan, 20 barang, 4 termin pembayaran, 5 pemasok, saldo stok, contoh dokumen stok masuk/keluar, dan activity log awal.

## Pengujian

Jalankan seluruh feature dan unit test:

```bash
php artisan test
```

Suite pengujian mencakup 137 test (750 assertions) untuk autentikasi, API Sanctum, RBAC, CRUD master data, relasi, validasi, transaksi stok, rollback, dan reporting.

Cek format kode dengan Laravel Pint:

```bash
vendor/bin/pint --test
```

## Dokumen Pengumpulan

- **Video demo (YouTube, unlisted):** *(menyusul)*
- **Laporan UTS:** diunggah terpisah dalam format PDF melalui Waskita
