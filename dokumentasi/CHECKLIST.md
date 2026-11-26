# Checklist Kepatuhan UTS

Gunakan dokumen ini sebagai kontrol terakhir sebelum submit. Kolom **Status** mengacu pada bukti nyata. M0 (fondasi), M1 (Auth & RBAC), M2 (Product), M3 (Supplier), M4 (Inventory), dan M5 (Reporting) sudah selesai. Setelah coding, ubah status menjadi ✅ hanya jika bukti nyata ada.

Legenda: ✅ selesai/tersedia · ⬜ belum · 🔍 perlu verifikasi · N/A tidak berlaku.

## 1. Ketentuan Wajib Soal

### 1.1 Struktur Modular Monolith

| # | Ketentuan | Target | Bukti | Status |
|---|---|---|---|---|
| 1.1.1 | Direktori `app/Modules/` minimal 3 modul | 5 modul: Auth, Product, Supplier, Inventory, Reporting | Screenshot struktur; `find app/Modules -maxdepth 1 -type d` | ✅ (M0) |
| 1.1.2 | Setiap modul punya Service Provider | 5 provider | Screenshot provider; `bootstrap/providers.php` | ✅ (M0) |
| 1.1.3 | Provider memakai `loadMigrationsFrom` | Semua provider | Screenshot kode | ✅ (M0) |
| 1.1.4 | Provider memakai `loadRoutesFrom` | Route web/API sesuai modul | Screenshot kode; `route:list` | ✅ (M0) |
| 1.1.5 | Komunikasi antar modul via Contract/Event | 4 contract + 10 event | `CONTRACTS.md`; screenshot interface/binding | ✅ (4/4 contract, 10/10 event: Auth + Product + Supplier + Inventory) |
| 1.1.6 | Tidak ada akses model/tabel lintas modul | 0 pelanggaran | `grep`/architecture test; review | ✅ (audit M4: Inventory hanya memakai contract/DTO/exception lintas modul) |
| 1.1.7 | Tidak ada query `DB::table()` lintas modul | 0 pelanggaran | Review kode | ✅ (0 query lintas modul di Inventory) |
| 1.1.8 | Modul tidak saling bergantung secara melingkar | Acyclic | Diagram dependensi | ✅ (Inventory → contract Product/Supplier/Auth; tidak ada balik) |

### 1.2 Manajemen Database

| # | Ketentuan | Target | Bukti | Status |
|---|---|---|---|---|
| 1.2.1 | Migration | Semua tabel via migration | `migrate:fresh` sukses; `migrate:status` | ✅ (Auth, Product, Supplier, Inventory) |
| 1.2.2 | Seeder | Role, user, product, supplier, inventory | `--seed` sukses; data demo | ✅ (role, user, 5 kategori, 5 satuan, 20 produk, 4 termin, 5 pemasok, 20 saldo, 3 penerimaan, 2 pengeluaran) |
| 1.2.3 | Factory | Minimal 8 factory | `database/factories`/modul | ✅ (11 factory — User, Product, Category, Unit, Supplier, PaymentTerm, StockReceipt, StockReceiptItem, StockIssue, StockIssueItem, StockMovement) |
| 1.2.4 | Relasi One-to-Many | Unit→Product, PaymentTerm→Supplier, Receipt→Items, Issue→Items | ERD + model + test | ✅ (semua relasi selesai + test) |
| 1.2.5 | Relasi Many-to-Many dengan pivot | `category_product`, `role_user` | ERD + pivot migration + test | ✅ (`role_user` M1 + `category_product` M2, keduanya diuji) |
| 1.2.6 | ERD akurat | Semua tabel & relasi | `LAPORAN.md` BAB II | ⬜ |
| 1.2.7 | Foreign key & index | FK, unique, index query | `DATABASE.md`; schema | ✅ (Auth + Product + Supplier + Inventory) |
| 1.2.8 | Data dummy realistis | 20 produk, 5 pemasok, transaksi | Seed + screenshot | ✅ (20 produk, 5 pemasok, 3 penerimaan, 2 pengeluaran, 23 movement) |

### 1.3 Autentikasi & Otorisasi

| # | Ketentuan | Target | Bukti | Status |
|---|---|---|---|---|
| 1.3.1 | Login/Register memakai Sanctum | Web session + API token | `AUTH` route; token response | ✅ (M1) |
| 1.3.2 | RBAC minimal 2 role | `admin`, `staff_gudang` | Tabel roles + matrix | ✅ (M1) |
| 1.3.3 | Protected routes middleware `auth` | Semua kecuali login/register | `route:list` | ✅ (M1, route yang ada) |
| 1.3.4 | Middleware `role` | Master write admin; stok kedua role | `route:list`; test 403 | ✅ (Product + Supplier write admin; stok kedua role, diuji AUTH-12) |
| 1.3.5 | Policy | Product, Supplier, StockReceipt, StockIssue, ActivityLog | Kode + test | ✅ (`ProductPolicy`, `SupplierPolicy`, `StockReceiptPolicy`, `StockIssuePolicy`, `ActivityLogPolicy` selesai + test) |
| 1.3.6 | Token ability/revoke | Login token; logout revoke | Test API | ✅ (M1) |
| 1.3.7 | Register tidak bisa jadi admin | Role dipaksa staff | Test | ✅ (M1) |
| 1.3.8 | Rate limit login | `throttle:6,1` | Test 429 | ✅ (M1) |
| 1.3.9 | Session aman | Regenerate/invalidate | Kode + test | ✅ (M1) |

### 1.4 Validasi & Error Handling

| # | Ketentuan | Target | Bukti | Status |
|---|---|---|---|---|
| 1.4.1 | Semua input via Form Request | 15+ request | Daftar di `ERROR-HANDLING.md` | ✅ (20 request — Auth 2 + Product 7 + Supplier 5 + Inventory 5 + Reporting 1) |
| 1.4.2 | Tidak ada `$request->validate()` di controller | 0 | Review kode | ✅ (0 di kode saat ini) |
| 1.4.3 | Custom exception minimal 1 | 3 exception | Kode + screenshot | ✅ (`ProductUnavailableException`, `SupplierUnavailableException`, `InsufficientStockException` dipakai + diuji) |
| 1.4.4 | Pesan error informatif di view | `$errors`, old input, alert | Screenshot validasi | ✅ (Auth + Product + Supplier + Inventory) |
| 1.4.5 | Error API jelas | status + `code` + `meta` | Screenshot/Postman | ✅ (API 409 `INSUFFICIENT_STOCK` + meta; 422 unavailable; 401 auth) |
| 1.4.6 | Error 401/403/404/419/429/500 | Halaman/JSON ramah | Screenshot | 🟡 (view 403/404/419/429/500 ada; screenshot menyusul) |
| 1.4.7 | Validasi stok di service | Di dalam transaksi | Kode `IssueStockService` | ✅ (`lockForUpdate()` + cek saldo + rollback, diuji) |
| 1.4.8 | Tidak ada SQL/stack trace ke user | 0 | Review + screenshot | ⬜ |

### 1.5 Git & GitHub

| # | Ketentuan | Target | Bukti | Status |
|---|---|---|---|---|
| 1.5.1 | Repo public | URL aktif | Link di laporan/README | ✅ (repo public, HTTP 200) |
| 1.5.2 | Commit berkala & deskriptif | ≥ 30 commit | `git log --oneline --graph` | ⬜ |
| 1.5.3 | Riwayat menunjukkan kontribusi | Solo, bertahap | Git graph + tabel tugas | ⬜ |
| 1.5.4 | Tidak ada file sensitif | `.env`, DB, token | `.gitignore` + scan | ✅ (`.env`, `database/*.sqlite`, `vendor` diabaikan) |
| 1.5.5 | Tag rilis | `v1.0-uts` | `git tag` | ⬜ |

## 2. Fitur Wajib Studi Kasus Warehouse

| # | Fitur | Detail | Bukti | Status |
|---|---|---|---|---|
| 2.1 | CRUD Barang | index/create/store/show/edit/update/delete (soft) | Screenshot + test | 🟡 (kode + 32 test lulus; screenshot menyusul) |
| 2.2 | CRUD Pemasok | index/create/store/show/edit/update/delete (soft) | Screenshot + test | 🟡 (kode + test lulus; screenshot menyusul) |
| 2.3 | Kategori & Satuan | CRUD + relasi + delete guard | Screenshot + test | 🟡 (kode + test lulus; screenshot menyusul) |
| 2.4 | Termin | CRUD + relasi O2M | Screenshot + test | 🟡 (kode + test lulus; screenshot menyusul) |
| 2.5 | Stok Masuk | dokumen + item + saldo + movement | Screenshot + test | ⬜ |
| 2.6 | Stok Keluar | dokumen + item + validasi stok | Screenshot + test | ⬜ |
| 2.7 | Validasi stok | stok kurang → exception, rollback | Test + screenshot | ⬜ |
| 2.8 | Kartu Stok | filter produk/tanggal; saldo setelah | Screenshot + test | ⬜ |
| 2.9 | Saldo Stok | daftar saldo & status rendah | Screenshot | ✅ (`/inventory/balances` + filter stok rendah, diuji) |
| 2.10 | RBAC Admin vs Staff | hak berbeda | Screenshot 403 + test | ✅ (Auth + Product + Supplier + Inventory + Activity, diuji 401/403/200) |
| 2.11 | Dashboard | ringkasan & low stock | Screenshot | ✅ (total barang/pemasok, stok rendah, transaksi & aktivitas terakhir) |
| 2.12 | Activity Log | audit aksi | Screenshot + test | ✅ (admin-only, filter event/tanggal/aktor, 11 test Reporting) |

## 3. Struktur dan Kualitas Kode

| # | Item | Target | Status |
|---|---|---|---|
| 3.1 | PSR-4 & namespace modul | `App\Modules\<Modul>\...` | ✅ (Auth + Product + Supplier) |
| 3.2 | Nama kelas/file konsisten | PascalCase, suffix jelas | ✅ (Auth + Product + Supplier) |
| 3.3 | Controller tipis; logika di service | Tidak ada transaksi di controller | 🟡 (Product/Supplier memakai service; registrasi Auth masih di controller) |
| 3.4 | Form Request untuk semua input | 0 validasi manual | ✅ (Auth + Product + Supplier) |
| 3.5 | Mass assignment aman | `$fillable` eksplisit | ✅ (Auth + Product + Supplier) |
| 3.6 | Tidak ada query lintas modul | 0 | ✅ (0 pelanggaran; audit M4 bersih) |
| 3.7 | Contract hanya DTO/primitif | 0 model/paginator | ✅ (`UserDirectoryContract`, `ProductCatalogContract`, `SupplierDirectoryContract`, `InventoryQueryContract`) |
| 3.8 | Event after commit | `ShouldDispatchAfterCommit` | ✅ (Auth + Product + Supplier + Inventory) |
| 3.9 | Transaksi + lock | Semua mutasi stok | ✅ (M4: receipt/issue dalam `DB::transaction()` + `lockForUpdate()`) |
| 3.10 | Decimal untuk uang/qty | Tidak ada float untuk stok | ✅ (kolom decimal(15,3)/decimal(15,2); cast string di model) |
| 3.11 | Format Pint | `pint --test` hijau | ✅ |
| 3.12 | Komentar secukupnya | Tidak ada kode mati | ✅ (Auth + Product + Supplier) |
| 3.13 | Tidak ada debug `dd()`/`dump()` | 0 | ✅ |
| 3.14 | Tidak ada credential hardcoded | 0 | 🟡 (password demo seeder memang didokumentasikan; tidak ada token/secret) |
| 3.15 | Error handling konsisten | Exception + render | ✅ (tiga custom exception dengan render web/JSON) |

## 4. Dokumentasi

| # | Dokumen | Ada | Akurat dengan kode | Status |
|---|---|---|---|---|
| 4.1 | `README.md` | ✅ | ⬜ | ✅ (rencana) |
| 4.2 | `PLAN.md` | ✅ | ⬜ | ✅ (rencana) |
| 4.3 | `ARCHITECTURE.md` | ✅ | ⬜ | ✅ (rencana) |
| 4.4 | `DATABASE.md` | ✅ | ⬜ | ✅ (rencana) |
| 4.5 | `MODULES.md` | ✅ | ⬜ | ✅ (rencana) |
| 4.6 | `CONTRACTS.md` | ✅ | ⬜ | ✅ (rencana) |
| 4.7 | `RBAC.md` | ✅ | ⬜ | ✅ (rencana) |
| 4.8 | `ERROR-HANDLING.md` | ✅ | ⬜ | ✅ (rencana) |
| 4.9 | `TESTING.md` | ✅ | ⬜ | ✅ (rencana) |
| 4.10 | `SETUP.md` | ✅ | ⬜ | ✅ (rencana) |
| 4.11 | `LAPORAN.md` | ✅ | ⬜ | ✅ (rencana) |
| 4.12 | `CHECKLIST.md` | ✅ | ⬜ | ✅ (rencana) |
| 4.13 | Diagram sesuai schema aktual | ⬜ | ⬜ | ⬜ |
| 4.14 | Screenshot lengkap | ⬜ | ⬜ | ⬜ |

## 5. Pengujian

| # | Jenis | Target | Status |
|---|---|---|---|
| 5.1 | Feature Auth/RBAC | 13+ skenario | ✅ (AUTH-01..13) |
| 5.2 | Feature Product | 10+ skenario | ✅ (32 test Product) |
| 5.3 | Feature Supplier | 6+ skenario | ✅ (22 test Supplier) |
| 5.4 | Feature Inventory | 16+ skenario | ✅ (24 test Inventory) |
| 5.5 | Feature Reporting | 6+ skenario | ✅ (11 test Reporting) |
| 5.6 | Unit | 4+ skenario | ✅ (6 test Unit Inventory) |
| 5.7 | Rollback | 1+ skenario kritis | ✅ (`RollbackTest` dua item) |
| 5.8 | Validasi | minimal 1 gagal per form | ✅ (Auth + Product + Supplier + Inventory + Reporting) |
| 5.9 | API | 6+ skenario | ✅ (API Auth + receipt 201 + issue 409 + 401) |
| 5.10 | Semua test hijau | `php artisan test` | ✅ (137 test, 750 assertions per 2026-11-19) |
| 5.11 | Pint bersih | `vendor/bin/pint --test` | ✅ |
| 5.12 | Uji manual semua halaman | checklist `TESTING.md` | ✅ (Auth + Product + Supplier + Inventory + Reporting, HTTP 200/403 sesuai role) |
| 5.13 | Screenshot test/hasil | 30 bukti | ⬜ |

## 6. Laporan dan Video

| # | Item | Target | Status |
|---|---|---|---|
| 6.1 | Cover lengkap | Identitas + link | ⬜ |
| 6.2 | BAB I | Latar belakang, rumusan, tujuan, ruang lingkup | ⬜ |
| 6.3 | BAB II | Bounded context, ERD, modul, contract/event, RBAC, transaksi | ⬜ |
| 6.4 | BAB III | Screenshot struktur, provider, model, relasi, contract, Form Request, exception, controller, transaksi, git, tugas | ⬜ |
| 6.5 | BAB IV | Pengujian fitur, validasi, error, rollback, link video | ⬜ |
| 6.6 | BAB V | Kesimpulan + saran | ⬜ |
| 6.7 | Lampiran | Repo, video, peer assessment, akun demo | ⬜ |
| 6.8 | Video ≤ 10 menit | Semua fitur wajib | ⬜ |
| 6.9 | YouTube unlisted | Link dapat diakses | ⬜ |
| 6.10 | PDF nama benar | `UTS_BackendDevelopment_<Kelas>_<Nama>.pdf` | ⬜ |
| 6.11 | Upload Waskita | Bukti submit | ⬜ |
| 6.12 | Repo public | URL benar | ⬜ |
| 6.13 | Peer assessment | Solo, jujur | ⬜ |

## 7. Pemetaan Rubrik Penilaian

| Bobot | Kriteria | Target nilai | Bukti utama | Status |
|---|---|---|---|---|
| 15% | Struktur Modular Monolith | Sangat Baik | 5 modul, provider, 0 pelanggaran | ⬜ |
| 15% | Komunikasi Antar Modul | Sangat Baik | 4 contract + 10 event konsisten | ⬜ |
| 15% | Relasi Antar Tabel | Sangat Baik | ERD akurat, O2M + 2 M2M | ⬜ |
| 10% | Auth & Otorisasi | Sangat Baik | Sanctum + RBAC + 401/403 | ⬜ |
| 10% | Validasi & Error | Sangat Baik | Form Request semua + 3 exception + pesan | ⬜ |
| 10% | Database Transaction | Sangat Baik | `DB::transaction` + lock + rollback test | ⬜ |
| 10% | Kualitas Kode & Git | Sangat Baik | Pint, commit rapi, kontribusi | ⬜ |
| 10% | Laporan & Dokumentasi | Sangat Baik | Lengkap, diagram, screenshot, video | ⬜ |
| 5% | Video Demo | Sangat Baik | Semua fitur, audio/visual baik | ⬜ |

## 8. Verifikasi Perintah

Jalankan sebelum menyatakan selesai:

```bash
# Struktur
find app/Modules -maxdepth 2 -type d | sort

# Provider
cat bootstrap/providers.php

# Route + middleware
php artisan route:list --except-vendor

# Migrasi + seed dari nol
php artisan migrate:fresh --seed

# Test
php artisan test
vendor/bin/pint --test

# Cek akses model lintas modul (contoh)
rg -F 'App\Modules\Product\Models' app/Modules/Inventory app/Modules/Reporting || true
rg -F 'App\Modules\Auth\Models' app/Modules/Product app/Modules/Supplier app/Modules/Inventory app/Modules/Reporting || true
rg -F "DB::table('products')" app/Modules || true
rg -F "DB::table('suppliers')" app/Modules || true
rg -F "DB::table('users')" app/Modules || true

# Cek debug
grep -R "dd(\|dump(" app/Modules || true

# Git
git log --oneline --graph --decorate -n 40
git status
```

## 9. Kriteria "Selesai" (Definition of Done)

- [ ] Semua fitur wajib berfungsi.
- [ ] Semua route terlindungi sesuai RBAC.
- [ ] Semua form memakai Form Request.
- [ ] Stok tidak pernah negatif; rollback terbukti.
- [ ] Tidak ada akses model/tabel lintas modul.
- [ ] Semua contract/event berfungsi dan teruji.
- [ ] Test hijau; Pint bersih.
- [ ] Tidak ada data sensitif di repo.
- [ ] Laporan lengkap dan akurat.
- [ ] Video demo selesai dan link valid.
- [ ] Submit Waskita + bukti.

## 10. Risiko yang Harus Dipantau

| Risiko | Tanda | Tindakan |
|---|---|---|
| Batas modul bocor | `use` model lain / query tabel lain | Perbaiki sebelum commit; jalankan grep |
| Contract mengembalikan model | signature `Product`/`Collection` | Ganti DTO |
| Stok minus | saldo < 0 setelah issue | Cek lock + validasi ulang; test rollback |
| Event sebelum commit | listener baca data lama | `ShouldDispatchAfterCommit` |
| Laporan kurang bukti | screenshot kosong | Ambil screenshot tiap fitur |
| Commit menumpuk | git log tidak rapi | Commit per task |
| Waktu habis | fitur wajib belum selesai | Prioritas wajib, tunda fitur tambahan |
| Nama file PDF salah | ditolak dashboard | Cek format sebelum upload |
| Video private | link tidak bisa dibuka | Set unlisted; uji incognito |

## 11. Catatan Status

- Dokumen perencanaan (13 file) **sudah dibuat** dan konsisten.
- M0 (fondasi proyek), M1 (Auth & RBAC), M2 (Product), M3 (Supplier), M4 (Inventory), dan M5 (Reporting) **selesai**.
- Bukti M1: `migrate:fresh --seed` sukses, 33 feature test lulus (134 assertions), Pint bersih, dan uji manual login web + API token.
- Bukti M2: `migrate:fresh --seed` menghasilkan 5 kategori, 5 satuan, dan 20 produk; 32 test Product lulus; halaman `/products`, `/categories`, `/units` sudah diuji manual dan memuat CSS ter-build.
- Bukti M3: `migrate:fresh --seed` menghasilkan 4 termin dan 5 pemasok; 22 test Supplier lulus; halaman `/suppliers` dan `/payment-terms` sudah diuji manual; total suite 87 test / 402 assertions; Pint bersih.
- Bukti M4: `migrate:fresh --seed` menghasilkan 20 saldo, 3 penerimaan (16 item), 2 pengeluaran (7 item), dan 23 movement; 24 feature test Inventory + 6 unit test lulus; total suite 118 test / 553 assertions; halaman saldo, stok masuk, stok keluar, dan kartu stok sudah diuji manual (HTTP 200 + CSS ter-build); Pint bersih. PRD-0005 dan empat produk Konsumsi menjadi contoh stok rendah.
- Bukti M5: `migrate:fresh --seed` mengisi 6 activity log stok (3 `stock.received`, 2 `stock.issued`, 1 `stock.low`); 11 feature test Reporting + AUTH-13 lulus; total suite 137 test / 750 assertions; dashboard admin/staff HTTP 200, `/activity` staff 403 dan admin 200; Pint bersih. Reporting hanya memakai contract/DTO/event dan model `ActivityLog` sendiri.
- Setelah coding, perbarui dokumen ini dan `README.md` dengan status sebenarnya.
- Jangan menandai ✅ tanpa bukti (screenshot/test/commit).
