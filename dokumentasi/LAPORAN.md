# Panduan Laporan dan Video Demo

Dokumen ini adalah kerangka laporan UTS, daftar bukti screenshot, storyboard video, dan checklist submit. Isi placeholder `<...>` dengan data sebenarnya. Jangan menyalin laporan kelompok lain.

## 1. Identitas Laporan

| Item | Isi |
|---|---|
| Judul | Sistem Manajemen Gudang Berbasis Laravel dengan Arsitektur Modular Monolith |
| Mata kuliah | Backend Development (DT199), 4 SKS |
| Dosen | Hastari Utama, M.Cs |
| Semester | Ganjil 2026/2027 |
| Kelas | `<isi kelas>` |
| Anggota | Fadhlan Nuha M. — 25.01.5292 |
| Studi kasus | 4. Sistem Manajemen Gudang (Warehouse) |
| Repository | `https://github.com/fadhlannuha/sistem-manajemen-gudang` |
| Video | `menyusul` (YouTube unlisted) |
| Tanggal submit | `<tanggal>` |

**Nama file PDF:** `UTS_BackendDevelopment_<Kelas>_FadhlanNuhaM.pdf`
Sesuaikan format dengan instruksi dosen; jangan memakai spasi/karakter aneh.

## 2. Struktur Laporan (Wajib)

### Cover

- Universitas, fakultas, prodi, logo.
- Judul studi kasus.
- Nama, NIM, kelas.
- Mata kuliah, dosen.
- Tahun akademik.
- Link repository dan video (boleh di cover atau lampiran, tetapi harus ada).

### BAB I — Pendahuluan

**1.1 Latar Belakang**

- Pentingnya pengelolaan stok barang di gudang.
- Masalah umum: pencatatan manual, stok tidak akurat, sulit melacak mutasi, kontrol akses lemah.
- Alasan memilih Laravel + modular monolith: struktur rapi, batas modul jelas, mudah dikembangkan.
- Alasan memilih studi kasus Warehouse.

**1.2 Rumusan Masalah**

Contoh:

1. Bagaimana membangun aplikasi manajemen gudang dengan arsitektur modular monolith di Laravel 13?
2. Bagaimana menerapkan komunikasi antar modul melalui contract dan domain event tanpa akses model lintas modul?
3. Bagaimana memastikan transaksi stok konsisten dan tidak menghasilkan stok negatif?
4. Bagaimana menerapkan Sanctum dan RBAC untuk membedakan Admin dan Staff Gudang?
5. Bagaimana menangani validasi dan error secara informatif?

**1.3 Tujuan**

- Membangun aplikasi WMS dengan 5 modul (Auth, Product, Supplier, Inventory, Reporting).
- Menerapkan contract/event sebagai satu-satunya komunikasi antar modul.
- Menerapkan transaksi database dan validasi stok.
- Menerapkan Sanctum + RBAC.
- Menyediakan laporan dan bukti pengujian.

**1.4 Ruang Lingkup**

- Termasuk: CRUD barang/kategori/satuan, CRUD pemasok/termin, stok masuk/keluar, saldo, kartu stok, dashboard, activity log, RBAC.
- Tidak termasuk: pembelian, penjualan, multi-gudang, barcode, laporan keuangan, notifikasi email/WA, mobile app.
- Database SQLite; framework Laravel 13; frontend Blade.

**1.5 Manfaat**

- Bagi penulis: memahami modular monolith, transaksi, RBAC.
- Bagi pengguna: stok lebih akurat, audit lebih mudah.
- Bagi akademik: contoh penerapan konsep backend.

### BAB II — Perancangan

**2.1 Gambaran Umum Sistem**

- Deskripsi singkat alur: admin mengelola master data; staff melakukan transaksi; sistem memperbarui saldo/ledger; reporting menampilkan ringkasan.
- Aktor: Admin, Staff Gudang.

**2.2 Bounded Context Diagram**

Salin diagram dari `ARCHITECTURE.md` bagian 2. Jelaskan:

- 5 modul dan tanggung jawabnya.
- Arah dependensi.
- Contract yang menghubungkan modul.
- Event yang mengalir.

**2.3 Entity Relationship Diagram (ERD)**

Salin ERD dari `DATABASE.md` bagian 2. Jelaskan:

- Relasi One-to-Many (Unit → Product, PaymentTerm → Supplier, Receipt → Items, Issue → Items).
- Relasi Many-to-Many dengan pivot (`category_product`, `role_user`).
- Relasi lintas modul (FK) dan alasannya.
- Tabel utama: products, suppliers, stock_receipts, stock_issues, stock_balances, stock_movements, activity_logs.

**2.4 Penjelasan Modul**

Untuk setiap modul (Auth, Product, Supplier, Inventory, Reporting), tulis:

- Tanggung jawab.
- Tabel yang dimiliki.
- Contract yang diekspos.
- Event yang dikirim/diterima.
- Route utama dan middleware.
- File penting.

Gunakan tabel ringkas dari `MODULES.md`.

**2.5 Alur Komunikasi Antar Modul**

- Contract: `ProductCatalogContract`, `SupplierDirectoryContract`, `UserDirectoryContract`, `InventoryQueryContract`.
- Event: `ProductCreated`, `StockReceived`, `StockIssued`, `LowStockDetected`, dll.
- Contoh alur stok keluar (sequence diagram dari `ARCHITECTURE.md` bagian 6.3).
- Jelaskan mengapa model/tabel tidak diakses langsung.

**2.6 Perancangan RBAC**

- Tabel role dan matriks izin dari `RBAC.md`.
- Middleware `auth` + `role`.
- Policy dan directive Blade.
- Alur login web vs API.

**2.7 Perancangan Validasi dan Error**

- Form Request di semua input.
- Custom exception: `InsufficientStockException`, `ProductUnavailableException`, `SupplierUnavailableException`.
- Format error web/API.
- Status code.

**2.8 Perancangan Transaksi**

- `DB::transaction()`.
- `lockForUpdate()` pada `stock_balances`.
- Validasi ulang stok di dalam transaksi.
- Rollback seluruh dokumen jika satu item gagal.
- Urutan lock berdasarkan `product_id` untuk menghindari deadlock.
- Catatan keterbatasan SQLite.

**2.9 Use Case / Alur Pengguna**

Minimal:

1. Login/register.
2. Admin menambah barang + kategori + satuan.
3. Admin menambah pemasok + termin.
4. Staff mencatat stok masuk.
5. Staff mencatat stok keluar (stok cukup).
6. Staff mencoba stok keluar melebihi saldo → error.
7. Melihat saldo dan kartu stok.
8. Admin melihat activity log.
9. Staff mencoba akses halaman admin → 403.

### BAB III — Implementasi

**3.1 Lingkungan Pengembangan**

| Item | Nilai |
|---|---|
| OS | `<...>` |
| PHP | `<php -v>` |
| Laravel | 13.x |
| Database | SQLite |
| Node/npm | `<...>` |
| Editor | `<...>` |
| Browser | `<...>` |

**3.2 Struktur Direktori Modul**

- Screenshot `app/Modules/` (5 folder).
- Jelaskan pola folder (Contracts, Models, Services, Http, Database, routes, resources/views, Providers).

**3.3 Implementasi Service Provider**

- Screenshot `ProductServiceProvider` (atau salah satu).
- Tunjukkan `loadMigrationsFrom`, `loadRoutesFrom`, `loadViewsFrom`, binding contract, policy.
- Screenshot `bootstrap/providers.php`.

**3.4 Implementasi Model dan Relasi**

- Screenshot `Product` (SoftDeletes, fillable, casts, relasi unit/categories).
- Screenshot `Unit`/`Category` (hasMany/belongsToMany).
- Screenshot `StockReceipt`/`StockReceiptItem`.
- Tunjukkan relasi O2M dan M2M.

**3.5 Implementasi Contract**

- Screenshot interface `ProductCatalogContract`.
- Screenshot implementasi `ProductCatalogService`.
- Screenshot binding di provider.
- Jelaskan DTO `ProductSnapshot` dan alasan tidak mengembalikan model.

**3.6 Implementasi Form Request**

- Screenshot `StoreProductRequest` dan `StoreStockIssueRequest`.
- Tunjukkan rules, messages, authorize.
- Jelaskan validasi lintas modul di service (bukan `exists`).

**3.7 Implementasi Custom Exception**

- Screenshot `InsufficientStockException`.
- Tunjukkan pesan, data (available/requested), render web/API.

**3.8 Implementasi Controller/Service**

- Screenshot controller stok keluar.
- Screenshot service dengan `DB::transaction`, `lockForUpdate`, pengecekan stok, movement.
- Jelaskan alur.

**3.9 Implementasi Database Transaction**

- Screenshot kode `DB::transaction()` + `lockForUpdate()`.
- Screenshot test rollback.
- Jelaskan mengapa rollback penting.

**3.10 Implementasi RBAC**

- Screenshot middleware `role`.
- Screenshot policy.
- Screenshot route dengan middleware.
- Screenshot menu `@role`.
- Bukti 403.

**3.11 Riwayat Commit Git**

- Screenshot `git log --oneline --graph --decorate -n 40`.
- Tabel ringkas commit penting.
- Jelaskan pembagian tugas.

**3.12 Pembagian Tugas**

Karena solo:

| Nama | NIM | Tugas | Persentase |
|---|---|---|---|
| Fadhlan Nuha M. | 25.01.5292 | Seluruh analisis, desain, implementasi, pengujian, laporan, video | 100% |

Jika kelak berkelompok, buat tabel per anggota dan sesuaikan dengan riwayat commit.

### BAB IV — Pengujian

**4.1 Lingkungan dan Metode Pengujian**

- Unit test, feature test, manual test.
- Database test SQLite in-memory.
- Tools: PHPUnit/Artisan test, browser, Postman/Thunder Client.

**4.2 Hasil Pengujian Fitur Utama**

Tabel (isi status setelah pengujian nyata; jangan menyalin "Lulus" tanpa bukti):

| No | Fitur | Skenario | Hasil | Status |
|---|---|---|---|---|
| 1 | Login | kredensial valid | dashboard | ⬜ |
| 2 | Login | password salah | pesan error | ⬜ |
| 3 | CRUD Barang | tambah/edit/hapus | data berubah | ⬜ |
| 4 | CRUD Pemasok | tambah/edit/hapus | data berubah | ⬜ |
| 5 | Stok Masuk | 2 item | saldo bertambah | ⬜ |
| 6 | Stok Keluar | stok cukup | saldo berkurang | ⬜ |
| 7 | Stok Keluar | stok kurang | error, rollback | ⬜ |
| 8 | Rollback | item kedua gagal | tidak ada data parsial | ⬜ |
| 9 | Kartu Stok | filter tanggal | data kronologis | ⬜ |
| 10 | RBAC | staff akses admin | 403 | ⬜ |
| 11 | API | token valid/invalid | 200/401 | ⬜ |
| 12 | Activity Log | aksi tercatat | log muncul | ⬜ |

**4.3 Screenshot Validasi Gagal dan Berhasil**

- Form barang kosong/duplikat → pesan per field.
- Form stok qty 0 → pesan.
- Form stok melebihi saldo → pesan stok tersedia/diminta.
- Form berhasil → flash sukses.

**4.4 Screenshot Error Handling**

- 403 staff.
- 404 data.
- 409/422 API stok kurang.
- 401 API tanpa token.
- 419 CSRF (opsional).
- 500 halaman ramah (opsional; jangan sengaja merusak demo).

**4.5 Bukti Database Transaction/Rollback**

- Screenshot saldo sebelum.
- Screenshot percobaan issue gagal.
- Screenshot saldo sesudah (tidak berubah).
- Screenshot test `RollbackTest` hijau.
- Screenshot kode `DB::transaction`.

**4.6 Bukti Test Otomatis**

- Screenshot `php artisan test` (semua hijau).
- Screenshot `vendor/bin/pint --test`.
- Jumlah test dan assertions.

**4.7 Link Video Demo**

`menyusul` — isi setelah video direkam; pastikan unlisted dan dapat dibuka.

### BAB V — Kesimpulan dan Saran

**5.1 Kesimpulan**

- Modular monolith berhasil diterapkan dengan 5 modul dan provider.
- Contract/event menjaga batas modul.
- Transaksi + lock + validasi ulang menjaga stok.
- Sanctum + RBAC melindungi route.
- Form Request + custom exception memberi error informatif.
- Semua fitur wajib terpenuhi.

**5.2 Saran**

- Tambah multi-gudang, purchase order, laporan periodik.
- Tambah queue untuk notifikasi stok rendah.
- Tambah restore produk soft-deleted.
- Tambah uji konkurensi nyata dengan MySQL/PostgreSQL.
- Tambah API CRUD lengkap dan dokumentasi OpenAPI.
- Tambah audit trail yang lebih kaya.

### Lampiran

1. Link repository GitHub (public).
2. Link video YouTube (unlisted).
3. Form peer assessment (solo).
4. Daftar akun demo (tanpa password produksi).
5. Perintah menjalankan aplikasi.
6. Bukti tambahan (opsional).

## 3. Daftar Screenshot Wajib

Simpan di `dokumentasi/screenshots/` dengan nama konsisten.

| No | Nama file | Yang diambil |
|---|---|---|
| 1 | `01-struktur-modul.png` | `app/Modules/` dengan 5 folder |
| 2 | `02-provider.png` | `ProductServiceProvider` + `bootstrap/providers.php` |
| 3 | `03-model-product.png` | Model Product + relasi |
| 4 | `04-relasi-pivot.png` | Relasi category_product/role_user (migration/model) |
| 5 | `05-contract.png` | Interface + implementasi + binding |
| 6 | `06-form-request.png` | StoreProductRequest/StoreStockIssueRequest |
| 7 | `07-custom-exception.png` | InsufficientStockException |
| 8 | `08-controller.png` | Controller stok keluar |
| 9 | `09-transaksi.png` | `DB::transaction` + `lockForUpdate` |
| 10 | `10-git-log.png` | `git log --oneline --graph` |
| 11 | `11-login.png` | Halaman login |
| 12 | `12-login-gagal.png` | Error login |
| 13 | `13-dashboard.png` | Dashboard admin |
| 14 | `14-produk-index.png` | Daftar barang |
| 15 | `15-produk-form.png` | Form tambah barang |
| 16 | `16-produk-validasi.png` | Error validasi barang |
| 17 | `17-pemasok.png` | Daftar pemasok |
| 18 | `18-stok-masuk.png` | Form/daftar stok masuk |
| 19 | `19-stok-masuk-sukses.png` | Setelah simpan + saldo |
| 20 | `20-stok-keluar.png` | Form stok keluar |
| 21 | `21-stok-kurang.png` | Error stok kurang |
| 22 | `22-kartu-stok.png` | Kartu stok |
| 23 | `23-saldo.png` | Saldo stok |
| 24 | `24-staff-403.png` | Staff akses admin |
| 25 | `25-activity.png` | Activity log admin |
| 26 | `26-api-login.png` | Postman login + token |
| 27 | `27-api-me.png` | Postman me dengan Bearer |
| 28 | `28-api-401.png` | API tanpa token |
| 29 | `29-test-hijau.png` | `php artisan test` |
| 30 | `30-pint.png` | Pint bersih |
| 31 | `31-api-inventory.png` | (Opsional) API transaksi stok sukses/409 |

**Tips screenshot:**

- Gunakan resolusi 1366×768 atau 1920×1080; hindari blur.
- Crop ke area relevan; jangan memperlihatkan token/password.
- Ambil screenshot **saat fitur selesai**, jangan menumpuk di akhir.
- Untuk kode, gunakan font monospace dan zoom yang terbaca.
- Beri caption "Gambar 3.x — ..." di laporan.

## 4. Storyboard Video Demo (≤ 10 menit)

| Waktu | Adegan | Yang ditunjukkan |
|---|---|---|
| 0:00–0:30 | Intro | Judul, nama, NIM, studi kasus, stack |
| 0:30–1:15 | Arsitektur | Buka `app/Modules`, jelaskan 5 modul + provider + contract/event (slide/diagram) |
| 1:15–2:00 | Login/RBAC | Login admin, tunjukkan menu; login staff, tunjukkan menu terbatas |
| 2:00–3:00 | Master data | Tambah kategori/satuan, tambah barang, validasi gagal, edit, hapus |
| 3:00–3:40 | Pemasok | Tambah termin, tambah pemasok, validasi |
| 3:40–5:00 | Stok masuk | Buat penerimaan 2 item, tunjukkan saldo bertambah, kartu stok |
| 5:00–6:00 | Stok keluar | Buat pengeluaran, tunjukkan saldo berkurang |
| 6:00–7:00 | Error stok | Coba keluar melebihi saldo, tunjukkan pesan, buktikan saldo tidak berubah |
| 7:00–7:40 | Kartu stok | Filter tanggal, tunjukkan urutan dan saldo setelah |
| 7:40–8:20 | RBAC | Staff buka `/products/create` → 403; activity admin |
| 8:20–9:00 | API | Postman login token, `GET /me`, 401 tanpa token; opsional POST transaksi stok (201/409) |
| 9:00–9:30 | Test & Git | `php artisan test` hijau, `git log --oneline --graph` |
| 9:30–10:00 | Penutup | Kesimpulan singkat, link repo |

**Tips video:**

- Rekam 1080p, audio jelas; gunakan mikrofon/headset.
- Tutup notifikasi dan tab tidak relevan.
- Siapkan data demo; jangan mengetik terlalu lambat.
- Jangan memperlihatkan `.env`, token, atau password asli.
- Upload YouTube **unlisted**, bukan private; tempel link di laporan.
- Uji link di browser incognito.

## 5. Checklist Submit

### Dokumen

- [ ] Cover lengkap (judul, nama, NIM, kelas, mata kuliah, dosen).
- [ ] BAB I–V lengkap.
- [ ] Bounded context diagram ada dan terbaca.
- [ ] ERD ada dan cocok dengan schema.
- [ ] Penjelasan modul + contract/event.
- [ ] Screenshot struktur, provider, model, relasi, contract, Form Request, custom exception, controller, transaksi.
- [ ] Screenshot git log.
- [ ] Tabel pembagian tugas.
- [ ] Screenshot pengujian fitur, validasi, error.
- [ ] Link video demo.
- [ ] Lampiran repo + video + peer assessment.
- [ ] Daftar pustaka (jika ada).
- [ ] Format PDF rapi; nomor halaman; daftar isi.

### Teknis

- [ ] Repo public; URL benar.
- [ ] `.env`/database/token tidak tercommit.
- [ ] `README.md` repo berisi cara menjalankan.
- [ ] `php artisan migrate:fresh --seed` sukses di mesin bersih.
- [ ] `php artisan test` hijau.
- [ ] `vendor/bin/pint --test` hijau.
- [ ] Akun demo berfungsi.
- [ ] Video unlisted dapat diakses.
- [ ] Tidak ada data sensitif di screenshot/video.

### Submit

- [ ] Nama file PDF sesuai format.
- [ ] Upload ke Waskita/Dashboard.
- [ ] Simpan bukti submit (screenshot/email).
- [ ] Submit sebelum deadline; ingat pengurangan 10%/hari.
- [ ] Simpan arsip repo (tag `v1.0-uts`) dan PDF.

## 6. Tips Menulis Cepat dan Berkualitas

1. **Tulis sambil coding.** Setiap fitur selesai, tulis 1 paragraf + screenshot.
2. **Gunakan tabel** untuk route, aturan validasi, matriks RBAC, dan hasil test.
3. **Salin diagram** dari `ARCHITECTURE.md`/`DATABASE.md`, lalu sesuaikan dengan schema aktual.
4. **Jangan mengarang hasil.** Jika ada bug, tulis jujur dan jelaskan perbaikannya.
5. **Konsisten istilah:** "barang/produk", "pemasok/supplier", "stok masuk/receipt", "stok keluar/issue".
6. **Sebutkan batasan** (SQLite locking, listener sinkron) di kesimpulan/saran.
7. **Cek plagiarisme:** tulis dengan bahasa sendiri; cantumkan referensi.
8. **Minta review** teman/dosen sebelum submit jika memungkinkan.

## 7. Export PDF

Opsi:

- **Word/Google Docs:** tempel isi, atur style Heading 1/2, daftar isi otomatis, export PDF.
- **Markdown → PDF:** gunakan Typora, VS Code Markdown PDF, atau Pandoc:

```bash
pandoc laporan.md -o UTS_BackendDevelopment_Kelas_FadhlanNuhaM.pdf \
  --pdf-engine=xelatex \
  -V geometry:margin=2.5cm \
  -V fontsize=11pt
```

- Pastikan gambar/diagram tidak terpotong.
- Cek ukuran file (hindari > 20 MB jika dashboard membatasi).
- Beri nama file sesuai format.

## 8. Form Peer Assessment (Solo)

Isi sesuai instruksi dosen. Contoh:

| Nama | NIM | Peran | Kontribusi | Nilai diri |
|---|---|---|---|---|
| Fadhlan Nuha M. | 25.01.5292 | Semua peran | Analisis, desain, implementasi, test, laporan, video | 100% |

Lampirkan form resmi jika disediakan dosen. Jangan memalsukan kontribusi.

## 9. Pemetaan Bukti ke Rubrik

| Kriteria (bobot) | Bukti di laporan |
|---|---|
| Struktur Modular Monolith (15%) | Screenshot `app/Modules`, provider, penjelasan batas |
| Komunikasi Antar Modul (15%) | Diagram contract/event, screenshot interface + binding, kode pemakaian |
| Relasi Antar Tabel (15%) | ERD, screenshot model/relasi, screenshot pivot, penjelasan |
| Auth & Otorisasi (10%) | Screenshot login, middleware, policy, 403, token API |
| Validasi & Error (10%) | Form Request, custom exception, screenshot error |
| Database Transaction (10%) | Screenshot `DB::transaction`, test rollback, saldo sebelum/sesudah |
| Kualitas Kode & Git (10%) | `git log --graph`, Pint, struktur rapi |
| Laporan & Dokumentasi (10%) | Kelengkapan bab, diagram, screenshot, video |
| Video Demo (5%) | Link + storyboard + demo lancar |

## 10. Catatan Akhir

- Laporan adalah bukti proses, bukan sekadar kumpulan screenshot.
- Jika ada fitur yang tidak selesai, jelaskan kendala dan rencana perbaikan; jangan menulis fitur yang tidak ada.
- Simpan semua bukti mentah (screenshot, log, video) sampai nilai keluar.
- Pastikan repo tetap public sampai penilaian selesai.
