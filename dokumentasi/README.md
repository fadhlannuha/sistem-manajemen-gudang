# Dokumentasi UTS Backend Development — Sistem Manajemen Gudang

**Mata kuliah:** Backend Development (DT199) · **Studi kasus:** 4. Sistem Manajemen Gudang (Warehouse)
**Anggota:** Fadhlan Nuha M. (solo) · **NIM/Kelas:** 25.01.5292 / `<isi kelas>`

> **Status saat ini:** M0 (fondasi), M1 (Auth & RBAC), M2 (Product), dan M3 (Supplier) selesai. Fitur Inventory dan Reporting belum diimplementasikan.

## Ringkasan Proyek

Aplikasi web modular monolith berbasis **Laravel 13 / PHP 8.4+** untuk mengelola:

- **Barang, kategori, dan satuan** (ProductModule)
- **Pemasok dan termin pembayaran** (SupplierModule)
- **Stok masuk, stok keluar, saldo, dan kartu stok** (InventoryModule)
- **Login/register, role Admin vs Staff Gudang** (AuthModule)
- **Dashboard dan activity log** (ReportingModule)

Fitur wajib UTS yang dipenuhi: CRUD barang & pemasok, transaksi stok dengan validasi stok, kartu stok, RBAC, Sanctum, Form Request, custom exception, database transaction, migration/seeder/factory, relasi One-to-Many dan Many-to-Many pivot.

## Peta Dokumen

Baca dengan urutan ini.

| # | Dokumen | Untuk apa |
|---|---|---|
| 1 | [`PLAN.md`](PLAN.md) | Rencana kerja, milestone, backlog, risiko, Definition of Done |
| 2 | [`ARCHITECTURE.md`](ARCHITECTURE.md) | Bounded context, aturan modular monolith, provider, event, ADR |
| 3 | [`DATABASE.md`](DATABASE.md) | ERD, kamus data, urutan migrasi, transaksi, seeding |
| 4 | [`MODULES.md`](MODULES.md) | Tanggung jawab tiap modul, route, file tree, acceptance |
| 5 | [`CONTRACTS.md`](CONTRACTS.md) | Interface contract, DTO, event, binding, contoh pemakaian |
| 6 | [`RBAC.md`](RBAC.md) | Role, matriks izin, middleware, policy, token Sanctum |
| 7 | [`ERROR-HANDLING.md`](ERROR-HANDLING.md) | Form Request, custom exception, format error web/API |
| 8 | [`TESTING.md`](TESTING.md) | Strategi uji, matriks test, contoh test rollback & RBAC |
| 9 | [`SETUP.md`](SETUP.md) | Instalasi, env, migrate/seed, akun demo, troubleshooting |
| 10 | [`LAPORAN.md`](LAPORAN.md) | Kerangka BAB I–V, daftar screenshot, storyboard video, submit |
| 11 | [`CHECKLIST.md`](CHECKLIST.md) | Matriks ketentuan soal → bukti, rubrik penilaian, DoD |
| 12 | [`AGENTS.md`](AGENTS.md) | Aturan kerja untuk agen/AI selama implementasi |

Bukti screenshot disimpan di [`screenshots/`](screenshots/README.md); daftar file wajib ada di `LAPORAN.md`.

## Keputusan Kunci (Ringkas)

| Topik | Keputusan |
|---|---|
| Arsitektur | Modular monolith, 5 modul: Auth, Product, Supplier, Inventory, Reporting |
| Komunikasi | Contract (interface) untuk query sinkron; domain event untuk notifikasi/reaksi |
| Database | SQLite; FK lintas modul tetap ada, akses kode lewat contract |
| Auth | Sanctum token untuk API, session guard untuk Blade |
| RBAC | Tabel `roles` + pivot `role_user`; role `admin`, `staff_gudang` |
| Stok | `stock_receipts`/`stock_issues` + item, `stock_balances`, `stock_movements` |
| Kartu stok | Query ledger `stock_movements`, bukan tabel rekap terpisah |
| Transaksi | `DB::transaction()` + `lockForUpdate()` + validasi ulang stok |
| Master data | Produk/pemasok soft delete; kategori/satuan/termin dicek referensinya |
| Event | `ShouldDispatchAfterCommit`; listener sinkron, tanpa queue worker |
| UI | Blade + Tailwind (Vite), halaman CRUD, form multi-item, dashboard |

## Struktur Folder yang Dituju

```text
uts-1/
├── app/
│   └── Modules/
│       ├── Auth/
│       ├── Product/
│       ├── Supplier/
│       ├── Inventory/
│       └── Reporting/
├── bootstrap/
│   └── providers.php
├── database/
│   ├── migrations/          # hanya migration global (cache/jobs)
│   ├── seeders/DatabaseSeeder.php
│   └── database.sqlite
├── dokumentasi/             # SEMUA dokumen ini
├── resources/
│   └── views/layouts/       # layout global
├── routes/
│   ├── web.php              # hanya redirect root
│   └── api.php              # bootstrap API
├── tests/
│   ├── Feature/
│   ├── Unit/
│   └── Architecture/         # opsional
└── ...
```

Setiap modul memiliki `Providers/<Module>ServiceProvider.php`, `routes/web.php`, `routes/api.php`, `Database/Migrations`, `Database/Factories`, `Database/Seeders`, `Models`, `Contracts`, `DTO`, `Events`, `Listeners`, `Exceptions`, `Services`, `Http/Controllers`, `Http/Requests`, `Policies`, `resources/views`.

## Cara Cepat Menggunakan Dokumen

- **Mulai coding:** baca `PLAN.md` bagian milestone, lalu `SETUP.md`.
- **Bingung batas modul:** `ARCHITECTURE.md` + `CONTRACTS.md` + `AGENTS.md`.
- **Membuat migration:** `DATABASE.md`.
- **Membuat route/controller:** `MODULES.md`.
- **Mengatur akses:** `RBAC.md`.
- **Menangani error:** `ERROR-HANDLING.md`.
- **Menulis test:** `TESTING.md`.
- **Menyusun laporan/video:** `LAPORAN.md`.
- **Cek sebelum submit:** `CHECKLIST.md`.

## Bukti untuk Laporan (nanti diisi)

| Item | Nilai |
|---|---|
| Repository GitHub | `https://github.com/fadhlannuha/sistem-manajemen-gudang` |
| Video demo (YouTube unlisted) | `menyusul` |
| Laporan PDF | `UTS_BackendDevelopment_<Kelas>_FadhlanNuhaM.pdf` |
| Tanggal submit Waskita | `<isi tanggal>` |

## Catatan Etika Akademik

- Seluruh kode ditulis sendiri; dokumen ini hanya panduan perencanaan.
- Jangan menyalin kode/laporan kelompok lain.
- Riwayat commit harus jujur; untuk solo, commit tetap dibuat bertahap agar proses terlihat.
- Cantumkan sumber referensi (dokumentasi Laravel, artikel, dsb.) di laporan.
