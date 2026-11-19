# Checklist Kepatuhan UTS

Gunakan dokumen ini sebagai kontrol terakhir sebelum submit. Kolom **Status** mengacu pada bukti nyata. M0 (fondasi) dan M1 (Auth & RBAC) sudah selesai; modul M2–M5 belum diimplementasikan. Setelah coding, ubah status menjadi ✅ hanya jika bukti nyata ada.

Legenda: ✅ selesai/tersedia · ⬜ belum · 🔍 perlu verifikasi · N/A tidak berlaku.

## 1. Ketentuan Wajib Soal

### 1.1 Struktur Modular Monolith

| # | Ketentuan | Target | Bukti | Status |
|---|---|---|---|---|
| 1.1.1 | Direktori `app/Modules/` minimal 3 modul | 5 modul: Auth, Product, Supplier, Inventory, Reporting | Screenshot struktur; `find app/Modules -maxdepth 1 -type d` | ✅ (M0) |
| 1.1.2 | Setiap modul punya Service Provider | 5 provider | Screenshot provider; `bootstrap/providers.php` | ✅ (M0) |
| 1.1.3 | Provider memakai `loadMigrationsFrom` | Semua provider | Screenshot kode | ✅ (M0) |
| 1.1.4 | Provider memakai `loadRoutesFrom` | Route web/API sesuai modul | Screenshot kode; `route:list` | ✅ (M0) |
| 1.1.5 | Komunikasi antar modul via Contract/Event | 4 contract + 10 event | `CONTRACTS.md`; screenshot interface/binding | ⬜ |
| 1.1.6 | Tidak ada akses model/tabel lintas modul | 0 pelanggaran | `grep`/architecture test; review | ⬜ |
| 1.1.7 | Tidak ada query `DB::table()` lintas modul | 0 pelanggaran | Review kode | ⬜ |
| 1.1.8 | Modul tidak saling bergantung secara melingkar | Acyclic | Diagram dependensi | ⬜ |

### 1.2 Manajemen Database

| # | Ketentuan | Target | Bukti | Status |
|---|---|---|---|---|
| 1.2.1 | Migration | Semua tabel via migration | `migrate:fresh` sukses; `migrate:status` | 🟡 (M1: tabel Auth) |
| 1.2.2 | Seeder | Role, user, product, supplier, inventory | `--seed` sukses; data demo | 🟡 (M1: role + user) |
| 1.2.3 | Factory | Minimal 8 factory | `database/factories`/modul | 🟡 (M1: 1/8, `UserFactory`) |
| 1.2.4 | Relasi One-to-Many | Unit→Product, PaymentTerm→Supplier, Receipt→Items, Issue→Items | ERD + model + test | ⬜ |
| 1.2.5 | Relasi Many-to-Many dengan pivot | `category_product`, `role_user` | ERD + pivot migration + test | 🟡 (`role_user` selesai; `category_product` di M2) |
| 1.2.6 | ERD akurat | Semua tabel & relasi | `LAPORAN.md` BAB II | ⬜ |
| 1.2.7 | Foreign key & index | FK, unique, index query | `DATABASE.md`; schema | ⬜ |
| 1.2.8 | Data dummy realistis | 20 produk, 5 pemasok, transaksi | Seed + screenshot | ⬜ |

### 1.3 Autentikasi & Otorisasi

| # | Ketentuan | Target | Bukti | Status |
|---|---|---|---|---|
| 1.3.1 | Login/Register memakai Sanctum | Web session + API token | `AUTH` route; token response | ✅ (M1) |
| 1.3.2 | RBAC minimal 2 role | `admin`, `staff_gudang` | Tabel roles + matrix | ✅ (M1) |
| 1.3.3 | Protected routes middleware `auth` | Semua kecuali login/register | `route:list` | ✅ (M1, route yang ada) |
| 1.3.4 | Middleware `role` | Master write admin; stok kedua role | `route:list`; test 403 | 🟡 (middleware + test; route master/stok di M2–M4) |
| 1.3.5 | Policy | Product, Supplier, StockReceipt, StockIssue, ActivityLog | Kode + test | ⬜ |
| 1.3.6 | Token ability/revoke | Login token; logout revoke | Test API | ✅ (M1) |
| 1.3.7 | Register tidak bisa jadi admin | Role dipaksa staff | Test | ✅ (M1) |
| 1.3.8 | Rate limit login | `throttle:6,1` | Test 429 | ✅ (M1) |
| 1.3.9 | Session aman | Regenerate/invalidate | Kode + test | ✅ (M1) |

### 1.4 Validasi & Error Handling

| # | Ketentuan | Target | Bukti | Status |
|---|---|---|---|---|
| 1.4.1 | Semua input via Form Request | 15+ request | Daftar di `ERROR-HANDLING.md` | 🟡 (M1: 2 request) |
| 1.4.2 | Tidak ada `$request->validate()` di controller | 0 | Review kode | ⬜ |
| 1.4.3 | Custom exception minimal 1 | 3 exception | Kode + screenshot | ⬜ |
| 1.4.4 | Pesan error informatif di view | `$errors`, old input, alert | Screenshot validasi | 🟡 (login/register; form modul menyusul) |
| 1.4.5 | Error API jelas | status + `code` + `meta` | Screenshot/Postman | 🟡 (validasi/401 API; custom exception di M4) |
| 1.4.6 | Error 401/403/404/419/429/500 | Halaman/JSON ramah | Screenshot | 🟡 (view 403/404/419/429/500 ada; screenshot menyusul) |
| 1.4.7 | Validasi stok di service | Di dalam transaksi | Kode `IssueStockService` | ⬜ |
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
| 2.1 | CRUD Barang | index/create/store/show/edit/update/delete (soft) | Screenshot + test | ⬜ |
| 2.2 | CRUD Pemasok | index/create/store/show/edit/update/delete (soft) | Screenshot + test | ⬜ |
| 2.3 | Kategori & Satuan | CRUD + relasi + delete guard | Screenshot + test | ⬜ |
| 2.4 | Termin | CRUD + relasi O2M | Screenshot + test | ⬜ |
| 2.5 | Stok Masuk | dokumen + item + saldo + movement | Screenshot + test | ⬜ |
| 2.6 | Stok Keluar | dokumen + item + validasi stok | Screenshot + test | ⬜ |
| 2.7 | Validasi stok | stok kurang → exception, rollback | Test + screenshot | ⬜ |
| 2.8 | Kartu Stok | filter produk/tanggal; saldo setelah | Screenshot + test | ⬜ |
| 2.9 | Saldo Stok | daftar saldo & status rendah | Screenshot | ⬜ |
| 2.10 | RBAC Admin vs Staff | hak berbeda | Screenshot 403 + test | ⬜ |
| 2.11 | Dashboard | ringkasan & low stock | Screenshot | ⬜ |
| 2.12 | Activity Log | audit aksi | Screenshot + test | ⬜ |

## 3. Struktur dan Kualitas Kode

| # | Item | Target | Status |
|---|---|---|---|
| 3.1 | PSR-4 & namespace modul | `App\Modules\<Modul>\...` | ⬜ |
| 3.2 | Nama kelas/file konsisten | PascalCase, suffix jelas | ⬜ |
| 3.3 | Controller tipis; logika di service | Tidak ada transaksi di controller | ⬜ |
| 3.4 | Form Request untuk semua input | 0 validasi manual | ⬜ |
| 3.5 | Mass assignment aman | `$fillable` eksplisit | ⬜ |
| 3.6 | Tidak ada query lintas modul | 0 | ⬜ |
| 3.7 | Contract hanya DTO/primitif | 0 model/paginator | ⬜ |
| 3.8 | Event after commit | `ShouldDispatchAfterCommit` | ⬜ |
| 3.9 | Transaksi + lock | Semua mutasi stok | ⬜ |
| 3.10 | Decimal untuk uang/qty | Tidak ada float untuk stok | ⬜ |
| 3.11 | Format Pint | `pint --test` hijau | ⬜ |
| 3.12 | Komentar secukupnya | Tidak ada kode mati | ⬜ |
| 3.13 | Tidak ada debug `dd()`/`dump()` | 0 | ⬜ |
| 3.14 | Tidak ada credential hardcoded | 0 | ⬜ |
| 3.15 | Error handling konsisten | Exception + render | ⬜ |

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
| 4.13 | `AGENTS.md` | ✅ | ⬜ | ✅ (rencana) |
| 4.14 | Diagram sesuai schema aktual | ⬜ | ⬜ | ⬜ |
| 4.15 | Screenshot lengkap | ⬜ | ⬜ | ⬜ |

## 5. Pengujian

| # | Jenis | Target | Status |
|---|---|---|---|
| 5.1 | Feature Auth/RBAC | 13+ skenario | 🟡 (AUTH-01..11; AUTH-12/13 butuh M4) |
| 5.2 | Feature Product | 10+ skenario | ⬜ |
| 5.3 | Feature Supplier | 6+ skenario | ⬜ |
| 5.4 | Feature Inventory | 16+ skenario | ⬜ |
| 5.5 | Feature Reporting | 6+ skenario | ⬜ |
| 5.6 | Unit | 4+ skenario | ⬜ |
| 5.7 | Rollback | 1+ skenario kritis | ⬜ |
| 5.8 | Validasi | minimal 1 gagal per form | ⬜ |
| 5.9 | API | 6+ skenario | ⬜ |
| 5.10 | Semua test hijau | `php artisan test` | ⬜ |
| 5.11 | Pint bersih | `vendor/bin/pint --test` | ⬜ |
| 5.12 | Uji manual semua halaman | checklist `TESTING.md` | ⬜ |
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
- M0 (fondasi proyek) dan M1 (Auth & RBAC) **selesai**; modul M2–M5 belum dimulai, sehingga checklist fitur terkait masih ⬜.
- Bukti M1: `migrate:fresh --seed` sukses, 33 feature test lulus (134 assertions), Pint bersih, dan uji manual login web + API token.
- Setelah coding, perbarui dokumen ini dan `README.md` dengan status sebenarnya.
- Jangan menandai ✅ tanpa bukti (screenshot/test/commit).
