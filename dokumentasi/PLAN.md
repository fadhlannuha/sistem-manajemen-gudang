# Rencana Pengerjaan UTS — Backend Development (DT199)

> Status: **M0 (fondasi), M1 (Auth & RBAC), dan M2 (Product) selesai; modul M3–M5 belum dimulai.**
> Semua panduan teknis ada di folder `dokumentasi/`. Baca `README.md` untuk peta dokumen.

## 1. Identitas dan Lingkup

| Item | Nilai |
|---|---|
| Mata kuliah | Backend Development (DT199), 4 SKS, Semester Ganjil 2026/2027 |
| Dosen | Hastari Utama, M.Cs |
| Studi kasus | **4. Sistem Manajemen Gudang (Warehouse)** |
| Anggota | Fadhlan Nuha M. (solo) |
| NIM / Kelas | 25.01.5292 / `<isi kelas>` |
| Repositori | `https://github.com/fadhlannuha/sistem-manajemen-gudang` |
| Output | Kode sumber (GitHub) + laporan PDF (Waskita) + video demo ≤ 10 menit (YouTube unlisted) |

**Fitur wajib dari soal:** CRUD Barang & Pemasok, transaksi Stok Masuk/Keluar dengan validasi stok, Kartu Stok, RBAC (Admin vs Staff Gudang), Sanctum, Form Request, custom exception, database transaction, modular monolith minimal 3 modul.

**Di luar lingkup (non-goals):** pembelian/pemesanan ke pemasok, penjualan ke pelanggan, multi-gudang, barcode scanner, laporan keuangan, notifikasi email/WA, impor/ekspor Excel, SPA framework. Fitur tersebut hanya boleh ditambahkan setelah semua kriteria wajib lulus.

## 2. Arsitektur Final (dikunci)

- **5 modul** di `app/Modules/`: `Auth`, `Product`, `Supplier`, `Inventory`, `Reporting`.
- Setiap modul punya **Service Provider** sendiri dan memuat migration, routes, views, serta binding contract.
- Komunikasi lintas modul **hanya** lewat **Contract (interface)** atau **Domain Event**. Model/tabel modul lain tidak boleh diakses langsung dari kode modul lain.
- **Database:** SQLite (didukung soal). Semua foreign key lintas modul tetap dibuat di level database untuk integritas; akses kode tetap melalui contract.
- **Auth:** Sanctum untuk API token; session guard untuk halaman Blade. Login/register tersedia di web dan API login tersedia untuk memperoleh token.
- **RBAC:** tabel `roles` + pivot `role_user`; role `admin` dan `staff_gudang`. Route master data yang menulis dilindungi `role:admin`; transaksi stok boleh kedua role.
- **Inventory:** dokumen `stock_receipts`/`stock_issues` + item, saldo `stock_balances`, dan ledger `stock_movements`. "Kartu Stok" dibentuk dari `stock_movements` (append-only) dan dapat difilter per produk/tanggal.
- **Transaksi:** semua perubahan stok dibungkus `DB::transaction()`; baris saldo dikunci `lockForUpdate()` lalu divalidasi ulang di dalam transaksi. Kegagalan satu item membatalkan seluruh dokumen.
- **Soft delete:** produk dan pemasok memakai `SoftDeletes` agar riwayat transaksi tetap utuh; kategori, satuan, dan termin dihapus fisik hanya jika belum dipakai.
- **Event:** event domain dikirim setelah commit (`ShouldDispatchAfterCommit`) dan ditangani listener sinkron (tanpa worker) untuk demo.

Detail lengkap: `ARCHITECTURE.md`, `DATABASE.md`, `MODULES.md`, `CONTRACTS.md`.

## 3. Dokumen yang Wajib Ada

| Dokumen | Isi | Status |
|---|---|---|
| `README.md` | Indeks, ringkasan, cara memakai dokumen | Selesai |
| `PLAN.md` | Rencana, milestone, backlog, risiko, DoD | Selesai |
| `ARCHITECTURE.md` | Bounded context, aturan modul, provider, event, ADR | Selesai |
| `DATABASE.md` | ERD, kamus data, urutan migrasi, transaksi, seed | Selesai |
| `MODULES.md` | Tanggung jawab, route, file tree, acceptance per modul | Selesai |
| `CONTRACTS.md` | Signature contract, DTO, event, binding, contoh pakai | Selesai |
| `RBAC.md` | Role, matriks izin, middleware, policy, token | Selesai |
| `ERROR-HANDLING.md` | Form Request, custom exception, pesan, render | Selesai |
| `TESTING.md` | Strategi, matriks uji, contoh test, rollback | Selesai |
| `SETUP.md` | Instalasi, env, migrate/seed, akun demo, troubleshooting | Selesai |
| `LAPORAN.md` | Kerangka BAB I–V, screenshot, video, checklist submit | Selesai |
| `CHECKLIST.md` | Matriks ketentuan soal → bukti, rubrik, DoD | Selesai |
| `AGENTS.md` | Aturan kerja agen/AI saat implementasi | Selesai |

## 4. Milestone dan Estimasi

Estimasi untuk satu orang; total ± 50–60 jam kerja efektif.

| Milestone | Estimasi | Hasil yang bisa dinilai |
|---|---|---|
| **M0 — Fondasi** | 4–5 jam | Repo, Laravel 13, Sanctum, `.env`, SQLite, Pint, struktur `app/Modules/` |
| **M1 — Auth & RBAC** | 8–10 jam | Register/login web + API, token, roles, middleware, policy, halaman login |
| **M2 — Product** | 8–10 jam | CRUD barang/kategori/satuan, relasi O2M + M2M, factory/seeder, contract |
| **M3 — Supplier** | 4–6 jam | CRUD pemasok/termin, relasi O2M, factory/seeder, contract |
| **M4 — Inventory** | 12–15 jam | Stok masuk/keluar, saldo, ledger/kartu stok, validasi stok, transaksi + rollback, event |
| **M5 — Reporting** | 5–7 jam | Dashboard, activity log dari event, contract query |
| **M6 — QA & bukti** | 6–8 jam | Feature/unit test, uji manual, screenshot, git log rapi |
| **M7 — Laporan & video** | 6–8 jam | Laporan PDF, video ≤ 10 menit, submit Waskita |

## 5. Backlog Terperinci

Format ID: `AREA-NN`. Setiap item selesai harus punya commit dan bukti uji.

### M0 — Fondasi

| ID | Tugas | Acceptance |
|---|---|---|
| INF-01 | Buat repo public, clone, tambahkan `.gitignore` Laravel yang benar | `.env`, `database/*.sqlite`, `vendor`, `node_modules`, `storage/*.key` tidak masuk git |
| INF-02 | Pasang Laravel 13 di root repo tanpa menghapus `dokumentasi/` | `php artisan --version` menampilkan Laravel 13.x |
| INF-03 | Pasang Sanctum (`install:api`), pindahkan migration token ke `Auth` | `personal_access_tokens` dimigrasikan dari modul Auth |
| INF-04 | Konfigurasi SQLite, locale `id`, timezone `Asia/Jakarta` | `php artisan migrate` sukses; `APP_LOCALE=id` |
| INF-05 | Buat kerangka 5 modul + provider, daftarkan di `bootstrap/providers.php` | `php artisan about`/`route:list` tidak error; provider termuat |
| INF-06 | Pasang Pint dan jalankan pada scaffold | `vendor/bin/pint --test` bersih |
| INF-07 | Tulis `AGENTS.md` dan konvensi commit | Dokumen ada; commit contoh mengikuti konvensi |

> **Status M0:** ✅ selesai pada 2026-11-17. Laravel 13.43.0, PHP 8.5, SQLite, Sanctum, 5 provider modul, dan autoload `App\Modules\...` sudah berjalan.

### M1 — Auth & RBAC

| ID | Tugas | Acceptance |
|---|---|---|
| AUTH-01 | Migration `users`, `roles`, `role_user`, `password_reset_tokens` | Migrasi jalan; unique email/role; pivot komposit |
| AUTH-02 | Model `User`, `Role`; relasi `belongsToMany`; `hasRole()` | Unit test relasi role lulus |
| AUTH-03 | Seeder role + user admin/staff | `migrate:fresh --seed` menghasilkan 1 admin + 2 staff |
| AUTH-04 | `LoginRequest`, `RegisterRequest`; controller web login/register/logout | Validasi tampil; login sukses redirect dashboard; logout invalidasi session |
| AUTH-05 | API login/logout/me dengan Sanctum token | `POST /api/v1/auth/login` mengembalikan token; `me` dengan Bearer 200; tanpa token 401 |
| AUTH-06 | Middleware `role`, alias di `bootstrap/app.php` | Staff di route admin → 403; guest → redirect login |
| AUTH-07 | `UserDirectoryContract` + binding + DTO `UserSummary` | Inventory/Reporting memakai contract, bukan model User |
| AUTH-08 | Blade layout login/register + `@role` directive | Halaman rapi; menu admin hanya tampil untuk admin |
| AUTH-09 | Rate limit login + regenerasi session + password hashed | Uji 6 login gagal → 429/terkunci sesuai konfigurasi |

> **Status M1:** ✅ selesai pada 2026-11-19. Migration Auth, model `User`/`Role`, factory dan seeder, login/register/logout web, token Sanctum, middleware `role`, `@role`, `UserDirectoryContract`, halaman error, dan test Auth sudah berjalan. Bukti: `php artisan test` (33 test, 134 assertions) dan `vendor/bin/pint --test` bersih.

### M2 — Product

| ID | Tugas | Acceptance |
|---|---|---|
| PRD-01 | Migration `categories`, `units`, `products`, `category_product` | FK, unique SKU/slug/kode; pivot dengan timestamps |
| PRD-02 | Model + relasi: Unit 1:N Product, Product M:N Category, soft delete Product | Relasi diuji; produk terhapus tidak muncul default |
| PRD-03 | Factory + seeder: 5 kategori, 5 satuan, 20 barang | Data dummy realistis; SKU unik |
| PRD-04 | CRUD barang (index/create/store/show/edit/update/destroy) | Semua route berfungsi; destroy = soft delete |
| PRD-05 | CRUD kategori + satuan | Delete ditolak jika masih dipakai (pesan jelas) |
| PRD-06 | Form Request store/update untuk barang, kategori, satuan | Semua form memakai Form Request; pesan Indonesia |
| PRD-07 | Policy `ProductPolicy`, route `role:admin` untuk tulis | Staff 403; admin 200 |
| PRD-08 | `ProductCatalogContract`, `ProductSnapshot`, binding | Dipakai Inventory tanpa akses model Product |
| PRD-09 | Event `ProductCreated`, `ProductUpdated`, `ProductDeactivated` | Event terkirim setelah commit; listener Inventory membuat saldo 0 |
| PRD-10 | Blade views + komponen error/old input | Validasi gagal menampilkan error per field |

> **Status M2:** ✅ selesai pada 2026-11-19. Migration, model, relasi 1:N/M:N, factory, seeder 5 kategori/5 satuan/20 produk, CRUD lengkap, Form Request, `ProductPolicy`, `ProductCatalogContract`, dan tiga event produk sudah berjalan. Bukti: `tests/Feature/Product/` (32 test) lulus, total suite 65 test / 287 assertions, `vendor/bin/pint --test` bersih. Listener saldo awal tetap menunggu M4.

### M3 — Supplier

| ID | Tugas | Acceptance |
|---|---|---|
| SUP-01 | Migration `payment_terms`, `suppliers` | FK termin; unique kode; soft delete supplier |
| SUP-02 | Model + relasi PaymentTerm 1:N Supplier | Relasi diuji |
| SUP-03 | Factory + seeder: 4 termin, 5 pemasok | Data dummy valid |
| SUP-04 | CRUD pemasok + termin | Semua route berfungsi; delete termin ditolak jika dipakai |
| SUP-05 | Form Request store/update pemasok/termin | Validasi lengkap; pesan Indonesia |
| SUP-06 | Policy + route admin untuk tulis | Staff 403 |
| SUP-07 | `SupplierDirectoryContract`, `SupplierSnapshot`, binding | Dipakai Inventory dan Reporting |
| SUP-08 | Event `SupplierCreated/Updated/Deactivated` | Reporting mencatat activity log |

### M4 — Inventory

| ID | Tugas | Acceptance |
|---|---|---|
| INV-01 | Migration `stock_receipts`, `stock_receipt_items`, `stock_issues`, `stock_issue_items`, `stock_balances`, `stock_movements` | FK, index, unique number, unique product_id di saldo |
| INV-02 | Model + relasi dalam modul Inventory saja | Tidak ada `use` model Product/Supplier/User |
| INV-03 | `ReceiveStockService` + nomor dokumen | Stok masuk menambah saldo, menulis movement, total benar |
| INV-04 | `IssueStockService` + validasi stok dalam transaksi | Stok kurang → `InsufficientStockException`; tidak ada data tersimpan |
| INV-05 | Lock saldo + revalidasi + rollback seluruh dokumen | Uji dua item, item kedua gagal → item pertama ikut rollback |
| INV-06 | Halaman stok masuk/keluar (index/create/show) + form multi-item | Dokumen tampil dengan item; stok saldo terlihat |
| INV-07 | Halaman saldo + kartu stok dengan filter produk/tanggal | Kartu stok kronologis; saldo setelah setiap mutasi benar |
| INV-08 | Event `StockReceived`, `StockIssued`, `LowStockDetected` | Reporting mencatat log; low stock muncul di dashboard |
| INV-09 | `InventoryQueryContract` + DTO `StockBalanceView`, `StockMovementView` | Reporting memakai contract |
| INV-10 | Policy dokumen: view semua, create semua, update/delete tidak diizinkan | Tidak ada route edit/delete dokumen |
| INV-11 | Number generator unit-testable | Format `SR-YYYYMM-000001` / `SI-YYYYMM-000001` |
| INV-12 | API inventory: transaksi stok + saldo/movement | `auth:sanctum`; 201/422/409 sesuai skenario |

### M5 — Reporting

| ID | Tugas | Acceptance |
|---|---|---|
| RPT-01 | Migration + model `activity_logs` | Log berisi actor, event, subject, payload, timestamp |
| RPT-02 | Listener `RecordActivityLog` untuk seluruh event domain | Setiap aksi penting menghasilkan satu log |
| RPT-03 | Dashboard: total produk, stok rendah, transaksi terakhir, aktivitas | Angka sesuai data seed/test |
| RPT-04 | Halaman activity (admin) dengan filter event/tanggal | Staff 403; admin dapat filter |
| RPT-05 | `ActivityLogPolicy` + route `role:admin` | RBAC konsisten |
| RPT-06 | Contract `InventoryQueryContract` dipakai dashboard | Tidak ada akses model Inventory dari Reporting |

### M6 — QA & Bukti

| ID | Tugas | Acceptance |
|---|---|---|
| QA-01 | Feature test Auth/RBAC | 401/403/200 sesuai matriks |
| QA-02 | Feature test CRUD Product/Supplier + validasi | Happy path + gagal validasi + delete terlarang |
| QA-03 | Feature test Inventory: receive, issue, insufficient, rollback | Semua lulus; saldo/movement konsisten |
| QA-04 | Feature test kartu stok + dashboard | Filter dan agregasi benar |
| QA-05 | Unit test number generator + exception | Format & pesan benar |
| QA-06 | Uji manual semua halaman; simpan screenshot | Screenshot sesuai `LAPORAN.md` |
| QA-07 | `vendor/bin/pint --test`, `php artisan test` | Keduanya hijau |
| QA-08 | `git log --oneline --graph` rapi + screenshot | ≥ 30 commit bermakna |

### M7 — Laporan & Video

| ID | Tugas | Acceptance |
|---|---|---|
| DOC-01 | Isi BAB I–V dari template `LAPORAN.md` | Semua subbab ada; diagram dan screenshot tertanam |
| DOC-02 | Susun tabel pembagian tugas (solo) + peer assessment | Jujur dan konsisten |
| DOC-03 | Rekam video ≤ 10 menit sesuai storyboard | Semua fitur wajib terlihat; audio jelas |
| DOC-04 | Upload YouTube unlisted, tempel link di laporan | Link dapat diakses |
| DOC-05 | Export PDF dengan nama `UTS_BackendDevelopment_<Kelas>_<NamaKelompok>.pdf` | Nama file benar; ukuran wajar |
| DOC-06 | Submit ke Waskita + cek repo public | Bukti submit tersimpan |

## 6. Urutan Eksekusi yang Disarankan

1. **M0** sampai `migrate` dan provider bersih.
2. **M1** karena semua modul bergantung pada user/role dan middleware.
3. **M2** lalu **M3**; keduanya menghasilkan contract yang dibutuhkan Inventory.
4. **M4** inti penilaian (transaksi, exception, rollback, kartu stok).
5. **M5** untuk dashboard dan bukti event.
6. **M6** mengunci kualitas; jangan menulis laporan sebelum test hijau.
7. **M7** laporan dan video; ambil screenshot dari aplikasi yang sudah stabil.

**Aturan penting:** jangan mengerjakan semua modul sekaligus. Selesaikan satu modul + test + commit sebelum pindah. Ini menjaga riwayat commit dan memudahkan rollback.

## 7. Strategi Git

- Branch utama `main`; untuk solo, commit langsung ke `main` boleh, tetapi gunakan branch fitur bila ingin riwayat lebih rapi:
  `feat/auth`, `feat/product`, `feat/supplier`, `feat/inventory`, `feat/reporting`, `docs/final`.
- Commit kecil dan deskriptif, format Conventional Commits:
  - `feat(auth): add role middleware and policies`
  - `feat(product): add category-product pivot and relations`
  - `fix(inventory): rollback issue when stock insufficient`
  - `test(inventory): cover insufficient stock and rollback`
  - `docs: add architecture and database guides`
- Contoh urutan commit minimal:
  1. `chore: scaffold laravel 13 with sanctum`
  2. `feat(auth): add users roles and role_user migrations`
  3. `feat(auth): implement login register and logout`
  4. `feat(auth): add role middleware and policy base`
  5. `feat(product): add product category unit migrations`
  6. `feat(product): add models factories and seeders`
  7. `feat(product): implement product crud`
  8. `feat(product): add product catalog contract`
  9. `feat(supplier): implement supplier crud and contract`
  10. `feat(inventory): add stock ledger migrations`
  11. `feat(inventory): implement receive stock transaction`
  12. `feat(inventory): implement issue stock with stock validation`
  13. `fix(inventory): lock balance rows and rollback on failure`
  14. `feat(inventory): add stock card and balance pages`
  15. `feat(reporting): add activity log and dashboard`
  16. `test: cover auth rbac product supplier inventory`
  17. `docs: add readme architecture database contracts`
  18. `chore: format code with pint`
- Tag akhir: `v1.0-uts`.
- Jangan commit `.env`, database SQLite, token, atau kredensial.

## 8. Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Akses model lintas modul tanpa sengaja | Kehilangan nilai struktur + komunikasi (30%) | Review `use` di setiap controller/service; aturan di `AGENTS.md`; architecture test opsional |
| Contract mengembalikan Eloquent model | Modul lain dapat memanggil relasi/tabel secara tidak langsung | Contract hanya mengembalikan DTO/array primitif; review signature |
| SQLite tidak mendukung `lockForUpdate` seperti MySQL | Race condition tidak teruji nyata | Dokumentasikan; tetap gunakan transaksi + lock; uji logika; sebutkan batasan di laporan |
| Stok minus karena dua transaksi bersamaan | Data tidak konsisten saat demo | Validasi ulang di dalam transaksi; unique saldo; uji rollback; demo satu operator |
| Event dikirim sebelum commit | Listener membaca data lama | Gunakan `ShouldDispatchAfterCommit`; jangan dispatch manual di dalam transaksi |
| Soft delete membuat produk "hilang" dari riwayat | Kartu stok tidak bisa dibuka | Akses kartu stok lewat riwayat/movement; contract menyediakan data historis; dokumentasikan |
| Laporan dikerjakan di akhir | Screenshot tidak lengkap | Ambil screenshot setiap fitur selesai; checklist di `LAPORAN.md` |
| Laravel 13 masih baru | API berubah | Ikuti dokumentasi resmi; fallback Laravel 12 hanya jika benar-benar gagal; jangan gunakan fitur eksperimental |
| Waktu habis | Fitur wajib tidak selesai | Prioritas: auth → product/supplier → inventory → reporting → polish |

## 9. Definition of Done

Sebuah task dianggap selesai jika:

- [ ] Kode berjalan tanpa error dan mengikuti struktur modul.
- [ ] Tidak ada akses model/tabel lintas modul; contract/event dipakai.
- [ ] Form input memakai Form Request; error tampil informatif.
- [ ] Perubahan stok memakai `DB::transaction()` dan diuji rollback.
- [ ] Feature/unit test relevan lulus.
- [ ] `vendor/bin/pint --test` bersih.
- [ ] Commit deskriptif dibuat.
- [ ] Screenshot/bukti untuk laporan diambil (jika task terlihat di UI).
- [ ] Dokumentasi terkait diperbarui bila keputusan berubah.

## 10. Keputusan Terbuka

| Hal | Status | Tindakan |
|---|---|---|
| NIM dan kelas | NIM `25.01.5292` sudah diisi; kelas belum | Isi kelas di cover laporan dan `README.md` |
| URL repo GitHub | `https://github.com/fadhlannuha/sistem-manajemen-gudang` | Sudah dibuat; pastikan tetap public |
| Konfirmasi studi kasus ke dosen/asisten | Belum | Konfirmasi secepatnya (first come, first served) |
| Tanggal deadline | Ikuti dashboard | Catat di kalender; target submit H-1 |
| Nama kelompok di file PDF | Solo | Gunakan nama sendiri; ikuti format dosen |

## 11. Cara Memakai Dokumen Ini

1. Baca `README.md` → pahami peta dokumen.
2. Baca `ARCHITECTURE.md` + `CONTRACTS.md` → pahami batas modul.
3. Baca `DATABASE.md` → buat migration sesuai urutan.
4. Implementasi per modul mengikuti `MODULES.md`.
5. Pasang auth/RBAC mengikuti `RBAC.md`.
6. Terapkan validasi/error mengikuti `ERROR-HANDLING.md`.
7. Uji mengikuti `TESTING.md`.
8. Ambil bukti dan susun laporan mengikuti `LAPORAN.md`.
9. Sebelum submit, centang `CHECKLIST.md`.
10. Saat memakai agen/AI, berikan `AGENTS.md` sebagai instruksi kerja.
