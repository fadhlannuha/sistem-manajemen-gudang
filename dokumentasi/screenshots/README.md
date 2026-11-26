# Screenshot Bukti UTS

Folder ini berisi **24 tangkapan layar asli** aplikasi (bukan mockup) sebagai bukti fitur, validasi, dan RBAC. Bukti dikumpulkan oleh tim kelompok 2 anggota:

- **Fadhlan Nuha Mukhlishin** — 25.01.5292 (`@fadhlannuha`)
- **Najib Arafi** — 25.01.5296 (`@najibarafi`)

## Konteks pengambilan

- Sumber: `http://127.0.0.1:8000` setelah `php artisan migrate:fresh --seed`.
- Resolusi: lebar **1400 px** (layout desktop, sidebar terlihat); format PNG.
- Akun demo: `admin@warehouse.test` / `password` dan `staff@warehouse.test` / `password`.
- Tidak ada token, password produksi, atau isi `.env` di dalam gambar.
- Daftar wajib yang belum terpenuhi (kode sumber, Postman API, hasil `php artisan test`, PDF/video) ada di [`../LAPORAN.md`](../LAPORAN.md) bagian **Daftar Screenshot Wajib**.

## Daftar file tersedia

| File | Halaman | Role | Yang ditunjukkan |
|---|---|---|---|
| `01-login.png` | `/login` | Tamu | Form login, tautan daftar |
| `02-dashboard.png` | `/dashboard` | Admin | Kartu ringkasan, stok rendah, transaksi & aktivitas terakhir |
| `03-produk-index.png` | `/products` | Admin | Tabel barang, filter, pagination |
| `04-produk-form.png` | `/products/create` | Admin | Form tambah barang (SKU, nama, satuan, kategori) |
| `05-pemasok.png` | `/suppliers` | Admin | Daftar pemasok beserta termin |
| `06-stok-masuk.png` | `/inventory/receipts` | Admin | Daftar dokumen stok masuk |
| `07-stok-keluar.png` | `/inventory/issues` | Admin | Daftar dokumen stok keluar |
| `08-saldo-stok.png` | `/inventory/balances` | Admin | Saldo stok per barang + penanda stok rendah |
| `09-kartu-stok.png` | `/inventory/stock-card?product_id=5` | Admin | Kartu stok kronologis + saldo setelah mutasi |
| `10-activity-log.png` | `/activity` | Admin | Audit log (khusus admin) |
| `11-stok-masuk-form.png` | `/inventory/receipts/create` | Admin | Form penerimaan multi-item |
| `12-stok-keluar-form.png` | `/inventory/issues/create` | Admin | Form pengeluaran multi-item |
| `13-kategori.png` | `/categories` | Admin | CRUD kategori + info delete guard |
| `14-satuan.png` | `/units` | Admin | CRUD satuan + info delete guard |
| `15-termin.png` | `/payment-terms` | Admin | CRUD termin + info delete guard |
| `16-detail-barang.png` | `/products/1` | Admin | Detail barang + kategori |
| `17-detail-stok-masuk.png` | `/inventory/receipts/3` | Admin | Detail penerimaan + item |
| `18-detail-stok-keluar.png` | `/inventory/issues/2` | Admin | Detail pengeluaran + item |
| `19-detail-pemasok.png` | `/suppliers/1` | Admin | Detail pemasok + termin |
| `20-validasi-produk.png` | `/products/create` (submit kosong) | Admin | Pesan validasi per field dalam bahasa Indonesia |
| `21-dashboard-staff.png` | `/dashboard` | Staff | Dashboard tanpa menu Activity Log |
| `22-rbac-403.png` | `/products/create` | Staff | Halaman 403 saat staff membuka form admin |
| `23-rbac-403-activity.png` | `/activity` | Staff | Halaman 403 saat staff membuka activity log |
| `24-register.png` | `/register` | Tamu | Form pendaftaran (role otomatis Staff Gudang) |

## Cara mengambil ulang

```bash
php artisan migrate:fresh --seed
php artisan serve --host=127.0.0.1 --port=8000
```

1. Buka `http://127.0.0.1:8000/login` dengan jendela minimal 1280 px.
2. Login sebagai admin untuk gambar 02–20, staff untuk gambar 21–23.
3. Untuk gambar 20, buka form tambah barang lalu klik **Simpan** tanpa mengisi apa pun.
4. Simpan dengan nama file sesuai tabel di atas agar tautan di `README.md` dan `LAPORAN.md` tetap valid.
