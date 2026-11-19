# Setup dan Instalasi

Panduan ini mengasumsikan repo sudah berisi folder `dokumentasi/` dan file soal UTS. Tujuan: aplikasi Laravel 13 berjalan lokal dengan SQLite, modul terpasang, dan data demo siap.

> **Status M0:** scaffold Laravel 13 sudah ada di repo ini. Jika kamu baru clone repo, lewati bagian 2 dan langsung ke konfigurasi `.env`/migrasi. Bagian 2 dipertahankan sebagai panduan bila scaffold perlu dibuat ulang dari nol.

## 1. Prasyarat

| Tool | Versi | Cek |
|---|---|---|
| PHP | 8.4+ (disarankan 8.4/8.5) | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | 20+ | `node -v` |
| npm | 10+ | `npm -v` |
| SQLite | 3.x + ekstensi PHP `pdo_sqlite` | `php -m | grep -i sqlite` |
| Git | 2.x | `git --version` |

Ekstensi PHP yang biasanya dibutuhkan: `mbstring`, `openssl`, `pdo`, `pdo_sqlite`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath` (untuk `bccomp`), `fileinfo`.

Jika `bcmath` tidak tersedia, gunakan pembulatan/`round` dengan hati-hati dan dokumentasikan; lebih baik aktifkan `bcmath`.

## 2. Membuat Proyek Laravel 13 di Repo yang Sudah Ada

Repo sudah memiliki `dokumentasi/`, jadi jangan menjalankan `composer create-project` langsung di root (Composer menolak direktori tidak kosong). Buat proyek di folder sementara, lalu salin isinya.

```bash
# dari parent folder repo
cd /home/fadhlannuha/Documents/campus/backend-development

# buat proyek Laravel 13 di folder sementara
composer create-project laravel/laravel:^13.0 uts-app-temp

# salin scaffold ke repo, pertahankan dokumentasi/
rsync -a --exclude='.git' uts-app-temp/ uts-1/

# hapus folder sementara
rm -rf uts-app-temp

cd uts-1
php artisan --version   # harus Laravel 13.x
```

Jika `laravel/laravel:^13.0` belum tersedia di Packagist, gunakan `composer create-project laravel/laravel uts-app-temp` (versi terbaru) dan pastikan `composer.json` memakai `laravel/framework: ^13.0`. Jangan menurunkan versi tanpa mencatat alasan.

Alternatif jika repo masih kosong: `composer create-project laravel/laravel:^13.0 .`.

## 3. Inisialisasi Git

```bash
git init
git add .
git commit -m "chore: scaffold laravel 13 with uts documentation"
```

- Pastikan `.gitignore` Laravel sudah mencakup `.env`, `vendor/`, `node_modules/`, `database/*.sqlite`, `storage/*.key`.
- Jangan commit `.env`, token, atau database.
- Buat repo GitHub public, lalu:

```bash
git remote add origin https://github.com/fadhlannuha/sistem-manajemen-gudang.git
git branch -M main
git push -u origin main
```

## 4. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```dotenv
APP_NAME="WMS UTS"
APP_ENV=local
APP_KEY=base64:...            # hasil key:generate
APP_DEBUG=true                # false saat demo publik/produksi
APP_URL=http://localhost:8000

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=sqlite
# Gunakan path absolut agar tidak bergantung cwd
DB_DATABASE=/home/fadhlannuha/Documents/campus/backend-development/uts-1/database/database.sqlite

SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_STORE=file
QUEUE_CONNECTION=sync

FILESYSTEM_DISK=local
MAIL_MAILER=log
```

Buat file database:

```bash
touch database/database.sqlite
php artisan migrate
```

Jika muncul error "Database file does not exist", pastikan path `DB_DATABASE` benar dan file dibuat.

### 4.1 Sanctum

```bash
php artisan install:api
```

Perintah ini memasang Sanctum, mempublikasikan migration token, dan menambahkan routing API. Setelah itu:

1. Pindahkan migration `*_create_personal_access_tokens_table.php` dari `database/migrations/` ke `app/Modules/Auth/Database/Migrations/` dan ganti nama menjadi `2026_01_01_000005_create_personal_access_tokens_table.php`.
2. Pastikan `bootstrap/app.php` memuat route API root (jika ada) dan module provider memuat route API modul.
3. Cek `config/sanctum.php` dan `composer.json` memuat `laravel/sanctum`.
4. Jangan biarkan migration token duplikat di root dan modul.

Jika `install:api` tidak tersedia, tambahkan manual:

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

lalu sesuaikan migration dan route API.

### 4.2 Struktur migration Auth

Migration bawaan Laravel biasanya menggabungkan `users`, `password_reset_tokens`, dan `sessions` dalam satu file. Sesuaikan:

- Pindahkan `users` ke `app/Modules/Auth/Database/Migrations/2026_01_01_000002_create_users_table.php`.
- Pindahkan `password_reset_tokens` ke `2026_01_01_000001_create_password_reset_tokens_table.php`.
- Hapus `sessions` karena `SESSION_DRIVER=file`.
- Buat `2026_01_01_000003_create_roles_table.php` dan `2026_01_01_000004_create_role_user_table.php`.
- Pastikan migration root hanya cache/jobs.
- Urutan lengkap ada di `DATABASE.md` bagian 5.

## 5. Membuat Kerangka Modul

Buat folder:

```bash
mkdir -p app/Modules/{Auth,Product,Supplier,Inventory,Reporting}
```

Untuk setiap modul, buat subfolder:

```text
Contracts/
DTO/
Database/Factories/
Database/Migrations/
Database/Seeders/
Events/
Exceptions/
Http/Controllers/
Http/Requests/
Listeners/
Models/
Policies/
Providers/
Services/
resources/views/
routes/
```

Buat provider (contoh `ProductServiceProvider`) sesuai `ARCHITECTURE.md`, lalu daftarkan semua provider di `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Modules\Auth\Providers\AuthServiceProvider::class,
    App\Modules\Product\Providers\ProductServiceProvider::class,
    App\Modules\Supplier\Providers\SupplierServiceProvider::class,
    App\Modules\Inventory\Providers\InventoryServiceProvider::class,
    App\Modules\Reporting\Providers\ReportingServiceProvider::class,
];
```

Setelah menambah class baru:

```bash
composer dump-autoload
```

## 6. Migrasi dan Seed

Urutan yang benar:

```bash
php artisan migrate:fresh --seed
php artisan migrate:status
```

Jika seeder belum dibuat, jalankan migrasi dulu, lalu implementasi seeder. Jangan menjalankan `migrate:fresh` setelah ada data yang ingin dipertahankan.

Akun demo (dari `UserSeeder`):

| Email | Password | Role |
|---|---|---|
| `admin@warehouse.test` | `password` | admin |
| `staff@warehouse.test` | `password` | staff_gudang |
| `staff2@warehouse.test` | `password` | staff_gudang |

> Ganti password jika aplikasi diakses publik. Akun demo hanya untuk lokal/demo UTS.

## 7. Frontend

Laravel 13 memakai Vite. Jika skeleton sudah menyertakan Tailwind, cukup:

```bash
npm install
npm run dev
```

Untuk build produksi/demo:

```bash
npm run build
```

Jika Tailwind belum ada, instal:

```bash
npm install -D tailwindcss @tailwindcss/vite
```

lalu tambahkan plugin di `vite.config.js` dan `@import "tailwindcss";` di `resources/css/app.css`. Sebagai fallback cepat, gunakan Bootstrap via CDN di layout — tetapi konsisten dengan pilihan desain.

Pastikan layout memuat:

```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

Jika muncul error "Vite manifest not found", jalankan `npm run dev` (saat development) atau `npm run build` (setelah build).

## 8. Menjalankan Aplikasi

Terminal 1:

```bash
php artisan serve
```

Terminal 2 (development asset):

```bash
npm run dev
```

Buka `http://localhost:8000` → redirect login. Login admin untuk melihat menu lengkap.

Perintah harian:

```bash
php artisan route:list
php artisan route:list --path=inventory
php artisan test
vendor/bin/pint
php artisan migrate:fresh --seed
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## 9. Uji Cepat Setelah Setup

```bash
# 1. Aplikasi hidup
php artisan about

# 2. Route terdaftar
php artisan route:list --except-vendor

# 3. Database + seed
php artisan migrate:fresh --seed

# 4. Test
php artisan test

# 5. Format
vendor/bin/pint --test
```

Checklist manual:

- [ ] Login admin berhasil.
- [ ] Login staff berhasil.
- [ ] Dashboard tampil.
- [ ] Menu Barang/Pemasok/Stok tampil.
- [ ] Staff tidak melihat tombol tambah/edit/hapus master data.
- [ ] Staff membuka `/products/create` → 403.
- [ ] API login mengembalikan token.
- [ ] `GET /api/v1/me` dengan token → 200.

## 10. Troubleshooting

| Gejala | Penyebab | Solusi |
|---|---|---|
| `could not find driver` | `pdo_sqlite` tidak aktif | Install `php-sqlite3`, restart PHP |
| `Database file does not exist` | File SQLite belum ada/path salah | `touch database/database.sqlite`; cek `DB_DATABASE` absolut |
| `Permission denied` di `storage/` | Permission folder | `chmod -R ug+rwx storage bootstrap/cache` |
| `Vite manifest not found` | Asset belum dibuild | `npm install && npm run build` atau `npm run dev` |
| 419 Page Expired | CSRF token hilang/session | Pastikan `@csrf`, session driver benar, refresh |
| 403 pada admin | Role belum terpasang | Jalankan `RoleSeeder`/`UserSeeder`; cek `role_user` |
| Route 404 | Provider tidak memuat route / cache | Cek provider, `php artisan route:clear`, `route:list` |
| Class not found | Autoload belum diperbarui | `composer dump-autoload` |
| Migration duplikat | File sama di root dan modul | Hapus salah satu; `migrate:fresh` |
| `No application encryption key` | `.env` belum di-key | `php artisan key:generate` |
| `SQLSTATE[HY000]: General error: 1 no such table` | Belum migrate | `php artisan migrate` |
| Login selalu gagal | Seeder belum jalan/password salah | `php artisan migrate:fresh --seed`; cek hash |
| 429 saat login | Rate limit | Tunggu 1 menit; untuk test gunakan `withoutMiddleware(ThrottleRequests::class)` |
| Port 8000 dipakai | Proses lain | `php artisan serve --port=8001` |
| Composer memory limit | Instalasi besar | `COMPOSER_MEMORY_LIMIT=-1 composer install` |
| `npm` error versi Node | Node terlalu lama | Gunakan Node 20+ |

## 11. Reset dan Backup

```bash
# Reset total (hapus data)
php artisan migrate:fresh --seed

# Backup SQLite (aplikasi berhenti)
cp database/database.sqlite database/backup-$(date +%Y%m%d-%H%M).sqlite

# Restore
cp database/backup-YYYYMMDD-HHMM.sqlite database/database.sqlite
```

Jangan commit file backup.

## 12. Konfigurasi Demo/Produksi Ringkas

Jika demo direkam/diunggah:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<domain-atau-lokal>
SESSION_SECURE_COOKIE=true   # hanya jika HTTPS
```

- Jalankan `php artisan config:cache`, `route:cache`, `view:cache` setelah stabil.
- Pastikan `storage/` dan `bootstrap/cache/` writable.
- Jangan tampilkan `.env` atau log ke publik.
- Untuk video, gunakan data seed yang bersih dan akun demo.

## 13. Checklist Setup

- [ ] PHP 8.4+, Composer, Node, SQLite siap.
- [ ] Laravel 13 terpasang tanpa menghapus `dokumentasi/`.
- [ ] `.env` berisi APP_KEY, locale `id`, timezone `Asia/Jakarta`, SQLite.
- [ ] Sanctum terpasang; migration token berada di modul Auth.
- [ ] 5 provider terdaftar.
- [ ] `php artisan migrate:fresh --seed` sukses.
- [ ] Akun demo bisa login.
- [ ] `npm run build`/`dev` sukses.
- [ ] `php artisan test` dan `vendor/bin/pint --test` hijau.
- [ ] Route list menunjukkan middleware yang benar.
- [ ] Tidak ada file sensitif di git.
