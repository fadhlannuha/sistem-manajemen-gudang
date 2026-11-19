# Modul — Tanggung Jawab, Route, dan Struktur File

Dokumen ini adalah peta implementasi per modul. Semua route, nama route, controller, dan file di sini harus konsisten dengan kode. Jika ada perubahan, perbarui dokumen ini dan `README.md`.

## 1. Ringkasan Modul

| Modul | Wajib? | Fokus | Contract | Event |
|---|---|---|---|---|
| Auth | Ya (pendukung) | User, role, login/register, token, middleware | `UserDirectoryContract` | `UserRegistered` |
| Product | Ya (inti) | Barang, kategori, satuan, relasi M:N | `ProductCatalogContract` | `ProductCreated/Updated/Deactivated` |
| Supplier | Ya (inti) | Pemasok, termin, relasi O:1 | `SupplierDirectoryContract` | `SupplierCreated/Updated/Deactivated` |
| Inventory | Ya (inti) | Stok masuk/keluar, saldo, kartu stok | `InventoryQueryContract` | `StockReceived/Issued`, `LowStockDetected` |
| Reporting | Ya (pendukung) | Dashboard, activity log | — | — |

**Urutan pengerjaan:** Auth → Product → Supplier → Inventory → Reporting.

## 2. AuthModule

### 2.1 Tanggung jawab

- Model `User` dan `Role`, relasi Many-to-Many `role_user`.
- Registrasi publik (role default `staff_gudang`), login/logout web (session), login/logout API (Sanctum token).
- Middleware `role` dan directive Blade `@role`.
- Contract `UserDirectoryContract` untuk modul lain yang butuh nama/email user.
- Seeder role dan user demo.
- **Tidak** mengelola CRUD user di UI (di luar MVP).

### 2.2 Tabel

`users`, `roles`, `role_user`, `password_reset_tokens`, `personal_access_tokens`.

### 2.3 Route web

| Method | URI | Name | Middleware | Controller@method |
|---|---|---|---|---|
| GET | `/login` | `login` | `guest` | `AuthController@showLogin` |
| POST | `/login` | `login.store` | `guest`, `throttle:6,1` | `AuthController@login` |
| GET | `/register` | `register` | `guest` | `AuthController@showRegister` |
| POST | `/register` | `register.store` | `guest`, `throttle:6,1` | `AuthController@register` |
| POST | `/logout` | `logout` | `auth` | `AuthController@logout` |

### 2.4 Route API

| Method | URI | Name | Middleware | Controller@method |
|---|---|---|---|---|
| POST | `/api/v1/auth/login` | `api.auth.login` | `throttle:6,1` | `ApiAuthController@login` |
| POST | `/api/v1/auth/logout` | `api.auth.logout` | `auth:sanctum` | `ApiAuthController@logout` |
| GET | `/api/v1/me` | `api.me` | `auth:sanctum` | `ApiAuthController@me` |

Respons login API:

```json
{
  "token": "1|xxxxxxxxxxxxxxxx",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Admin Gudang",
    "email": "admin@warehouse.test",
    "roles": ["admin"]
  }
}
```

### 2.5 Struktur file

```text
app/Modules/Auth/
├── Contracts/UserDirectoryContract.php
├── DTO/UserSummary.php
├── Database/
│   ├── Factories/UserFactory.php
│   ├── Migrations/ (lihat DATABASE.md)
│   └── Seeders/
│       ├── RoleSeeder.php
│       └── UserSeeder.php
├── Events/UserRegistered.php
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   └── Api/ApiAuthController.php
│   ├── Middleware/EnsureUserHasRole.php
│   └── Requests/
│       ├── LoginRequest.php
│       └── RegisterRequest.php
├── Models/
│   ├── Role.php
│   └── User.php
├── Policies/ (tidak ada policy model khusus)
├── Providers/AuthServiceProvider.php
├── Services/UserDirectoryService.php
├── resources/views/auth/
│   ├── login.blade.php
│   └── register.blade.php
└── routes/
    ├── web.php
    └── api.php
```

### 2.6 Detail implementasi

- `User` memakai `HasApiTokens`, `HasFactory`, `Notifiable`, `SoftDeletes`? **Tidak** memakai soft delete agar sederhana; user tidak dihapus dari UI.
- `User::roles()` → `belongsToMany(Role::class)` dengan timestamps.
- `User::hasRole(string $role): bool` dan `hasAnyRole(array $roles): bool`.
- `Role::users()` → `belongsToMany(User::class)`.
- `RegisterRequest` memaksa role `staff_gudang`; tidak menerima input `role` dari form.
- `AuthController@login`: `Auth::attempt`, regenerate session, redirect intended dashboard; gagal → error validasi.
- `ApiAuthController@login`: validasi, cek kredensial, buat token dengan abilities = nama role user, kembalikan token; jangan simpan token di session.
- Logout API: `$request->user()->currentAccessToken()->delete()`.
- Middleware `EnsureUserHasRole`:

```php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    $user = $request->user();

    if ($user === null) {
        abort(401);
    }

    if (! $user->hasAnyRole($roles)) {
        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }

    return $next($request);
}
```

- Alias middleware di `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Modules\Auth\Http\Middleware\EnsureUserHasRole::class,
    ]);
})
```

- Directive Blade di `AuthServiceProvider::boot()`:

```php
Blade::if('role', fn (string ...$roles) => auth()->user()?->hasAnyRole($roles) ?? false);
```

- Event `UserRegistered` dikirim setelah user + role tersimpan. Listener Reporting mencatat activity log.

### 2.7 Acceptance

- [x] `migrate:fresh --seed` membuat role dan 3 user demo.
- [x] Login web sukses/gagal menampilkan pesan; logout menghapus session.
- [x] Register publik menghasilkan user role `staff_gudang`.
- [x] API login mengembalikan token; `GET /api/v1/me` dengan Bearer 200; tanpa token 401.
- [x] Staff membuka route admin → 403; guest membuka route auth → redirect login.
- [ ] `@role('admin')` menyembunyikan menu master data dari staff. *(Directive sudah diuji; menu master data dibuat di M2.)*
- [ ] `UserDirectoryContract` dipakai modul lain; tidak ada model User di Inventory/Reporting. *(Binding dan DTO sudah siap; pemakaian di M4/M5.)*

> **Status M1:** ✅ inti Auth & RBAC selesai pada 2026-11-19. Dua item di atas ditandai belum karena UI/consumer modul lain belum ada.

## 3. ProductModule

### 3.1 Tanggung jawab

- CRUD barang, kategori, satuan.
- Relasi Unit 1:N Product dan Product M:N Category (pivot `category_product`).
- Soft delete produk; kategori/satuan dihapus fisik dengan pengecekan referensi.
- Contract `ProductCatalogContract` untuk Inventory/Reporting.
- Event produk untuk saldo awal (Inventory) dan activity log (Reporting).

### 3.2 Tabel

`categories`, `units`, `products`, `category_product`.

### 3.3 Route web

| Method | URI | Name | Middleware | Controller@method |
|---|---|---|---|---|
| GET | `/products` | `products.index` | `auth` | `ProductController@index` |
| GET | `/products/create` | `products.create` | `auth`, `role:admin` | `ProductController@create` |
| POST | `/products` | `products.store` | `auth`, `role:admin` | `ProductController@store` |
| GET | `/products/{product}` | `products.show` | `auth` | `ProductController@show` |
| GET | `/products/{product}/edit` | `products.edit` | `auth`, `role:admin` | `ProductController@edit` |
| PUT/PATCH | `/products/{product}` | `products.update` | `auth`, `role:admin` | `ProductController@update` |
| DELETE | `/products/{product}` | `products.destroy` | `auth`, `role:admin` | `ProductController@destroy` |
| GET | `/categories` | `categories.index` | `auth` | `CategoryController@index` |
| GET | `/categories/create` | `categories.create` | `auth`, `role:admin` | `CategoryController@create` |
| POST | `/categories` | `categories.store` | `auth`, `role:admin` | `CategoryController@store` |
| GET | `/categories/{category}/edit` | `categories.edit` | `auth`, `role:admin` | `CategoryController@edit` |
| PUT/PATCH | `/categories/{category}` | `categories.update` | `auth`, `role:admin` | `CategoryController@update` |
| DELETE | `/categories/{category}` | `categories.destroy` | `auth`, `role:admin` | `CategoryController@destroy` |
| GET | `/units` | `units.index` | `auth` | `UnitController@index` |
| GET | `/units/create` | `units.create` | `auth`, `role:admin` | `UnitController@create` |
| POST | `/units` | `units.store` | `auth`, `role:admin` | `UnitController@store` |
| GET | `/units/{unit}/edit` | `units.edit` | `auth`, `role:admin` | `UnitController@edit` |
| PUT/PATCH | `/units/{unit}` | `units.update` | `auth`, `role:admin` | `UnitController@update` |
| DELETE | `/units/{unit}` | `units.destroy` | `auth`, `role:admin` | `UnitController@destroy` |

Route API opsional (P1): `GET /api/v1/products`, `GET /api/v1/products/{product}` dengan `auth:sanctum`.

### 3.4 Struktur file

```text
app/Modules/Product/
├── Contracts/ProductCatalogContract.php
├── DTO/ProductSnapshot.php
├── Database/
│   ├── Factories/CategoryFactory.php, ProductFactory.php, UnitFactory.php
│   ├── Migrations/ (4 file, lihat DATABASE.md)
│   └── Seeders/ProductSeeder.php
├── Events/ProductCreated.php, ProductUpdated.php, ProductDeactivated.php
├── Exceptions/ProductUnavailableException.php
├── Http/
│   ├── Controllers/
│   │   ├── ProductController.php
│   │   ├── CategoryController.php
│   │   ├── UnitController.php
│   │   └── Api/ProductController.php (opsional)
│   └── Requests/
│       ├── ProductFilterRequest.php
│       ├── StoreProductRequest.php, UpdateProductRequest.php
│       ├── StoreCategoryRequest.php, UpdateCategoryRequest.php
│       └── StoreUnitRequest.php, UpdateUnitRequest.php
├── Models/Product.php, Category.php, Unit.php
├── Policies/ProductPolicy.php
├── Providers/ProductServiceProvider.php
├── Services/
│   ├── ProductCatalogService.php
│   └── ProductService.php
├── resources/views/
│   ├── products/{index,create,edit,show,_form}.blade.php
│   ├── categories/{index,create,edit,_form}.blade.php
│   └── units/{index,create,edit,_form}.blade.php
└── routes/web.php, api.php
```

### 3.5 Detail implementasi

- `Product` memakai `SoftDeletes`; `$fillable`: `sku`, `name`, `description`, `unit_id`, `min_stock`; cast `min_stock` decimal:3.
- `Product::unit()` belongsTo `Unit` (modul sendiri); `Product::categories()` belongsToMany `Category` dengan timestamps.
- `ProductService::create/update`: simpan produk + `sync()` kategori dalam `DB::transaction()`; normalisasi SKU uppercase; dispatch event setelah commit.
- `destroy`: soft delete; dispatch `ProductDeactivated`; **tidak** menghapus pivot (produk masih ada secara fisik).
- `CategoryController@destroy`: cek `Product::withTrashed()->whereHas('categories', ...)->exists()`; jika ada → redirect error; jika tidak → delete.
- `UnitController@destroy`: cek `Product::withTrashed()->where('unit_id', $unit->id)->exists()`.
- `ProductPolicy`: `viewAny/view` semua user auth; `create/update/delete` hanya admin; route middleware `role:admin` sebagai lapisan kedua.
- `ProductCatalogService::find` mengembalikan `null` untuk produk soft-deleted (default scope); `findIncludingDeactivated`/`findManyIncludingDeactivated` dipakai untuk menampilkan riwayat.
- Event payload memakai primitif; `ProductCreated` membawa `productId`, `sku`, `name`, `unitId`, `minStock`.
- Listener Inventory `CreateStockBalance` membuat saldo 0 saat `ProductCreated`; jika saldo sudah ada, `firstOrCreate`.

### 3.6 Acceptance

- [x] CRUD barang berfungsi; index/search/pagination rapi.
- [x] Kategori dan satuan CRUD; delete ditolak jika dipakai dengan pesan jelas.
- [x] Validasi SKU unik, unit wajib, min_stock >= 0, kategori array valid.
- [x] Relasi Unit 1:N dan Product M:N terbukti di test dan ERD.
- [x] Soft delete produk: hilang dari index; `find` null; method historis tetap mengembalikan data untuk kartu stok.
- [x] Staff tidak bisa create/edit/delete.
- [ ] `ProductCreated` membuat saldo 0 di Inventory (menunggu listener Inventory di M4).
- [ ] Tidak ada akses model Product dari Inventory/Reporting (diverifikasi saat M4–M5).

## 4. SupplierModule

### 4.1 Tanggung jawab

- CRUD pemasok dan termin pembayaran.
- Relasi PaymentTerm 1:N Supplier.
- Soft delete pemasok.
- Contract `SupplierDirectoryContract` untuk Inventory (transaksi) dan Reporting (dashboard).
- Event pemasok untuk activity log.

### 4.2 Tabel

`payment_terms`, `suppliers`.

### 4.3 Route web

| Method | URI | Name | Middleware | Controller@method |
|---|---|---|---|---|
| GET | `/suppliers` | `suppliers.index` | `auth` | `SupplierController@index` |
| GET | `/suppliers/create` | `suppliers.create` | `auth`, `role:admin` | `SupplierController@create` |
| POST | `/suppliers` | `suppliers.store` | `auth`, `role:admin` | `SupplierController@store` |
| GET | `/suppliers/{supplier}` | `suppliers.show` | `auth` | `SupplierController@show` |
| GET | `/suppliers/{supplier}/edit` | `suppliers.edit` | `auth`, `role:admin` | `SupplierController@edit` |
| PUT/PATCH | `/suppliers/{supplier}` | `suppliers.update` | `auth`, `role:admin` | `SupplierController@update` |
| DELETE | `/suppliers/{supplier}` | `suppliers.destroy` | `auth`, `role:admin` | `SupplierController@destroy` |
| GET | `/payment-terms` | `payment-terms.index` | `auth` | `PaymentTermController@index` |
| GET | `/payment-terms/create` | `payment-terms.create` | `auth`, `role:admin` | `PaymentTermController@create` |
| POST | `/payment-terms` | `payment-terms.store` | `auth`, `role:admin` | `PaymentTermController@store` |
| GET | `/payment-terms/{payment_term}/edit` | `payment-terms.edit` | `auth`, `role:admin` | `PaymentTermController@edit` |
| PUT/PATCH | `/payment-terms/{payment_term}` | `payment-terms.update` | `auth`, `role:admin` | `PaymentTermController@update` |
| DELETE | `/payment-terms/{payment_term}` | `payment-terms.destroy` | `auth`, `role:admin` | `PaymentTermController@destroy` |

Route API opsional (P1): `GET /api/v1/suppliers` dengan `auth:sanctum`.

### 4.4 Struktur file

```text
app/Modules/Supplier/
├── Contracts/SupplierDirectoryContract.php
├── DTO/SupplierSnapshot.php
├── Database/
│   ├── Factories/PaymentTermFactory.php, SupplierFactory.php
│   ├── Migrations/2026_01_01_000200_create_payment_terms_table.php
│   │             2026_01_01_000201_create_suppliers_table.php
│   └── Seeders/SupplierSeeder.php
├── Events/SupplierCreated.php, SupplierUpdated.php, SupplierDeactivated.php
├── Exceptions/SupplierUnavailableException.php
├── Http/
│   ├── Controllers/SupplierController.php, PaymentTermController.php
│   └── Requests/
│       ├── SupplierFilterRequest.php
│       ├── StoreSupplierRequest.php, UpdateSupplierRequest.php
│       └── StorePaymentTermRequest.php, UpdatePaymentTermRequest.php
├── Models/Supplier.php, PaymentTerm.php
├── Policies/SupplierPolicy.php
├── Providers/SupplierServiceProvider.php
├── Services/SupplierCatalogService.php, SupplierService.php
├── resources/views/
│   ├── suppliers/{index,create,edit,show,_form}.blade.php
│   └── payment_terms/{index,create,edit,_form}.blade.php
└── routes/web.php, api.php
```

### 4.5 Detail implementasi

- `Supplier` memakai `SoftDeletes`; `$fillable`: `code`, `name`, `contact_name`, `phone`, `email`, `address`, `payment_term_id`.
- `Supplier::paymentTerm()` belongsTo `PaymentTerm` (modul sendiri).
- `PaymentTerm::suppliers()` hasMany `Supplier` (termasuk soft-deleted? gunakan `withTrashed()` saat cek delete).
- Kode pemasok uppercase; validasi unik.
- `destroy` soft delete + event `SupplierDeactivated`.
- `PaymentTermController@destroy`: cek `Supplier::withTrashed()->where('payment_term_id', ...)->exists()`; jika ada → error.
- `SupplierPolicy`: view all auth; write admin.
- `SupplierCatalogService::find` mengembalikan `null` untuk supplier soft-deleted; `findIncludingDeactivated` dipakai untuk dokumen lama.
- Event payload: `supplierId`, `code`, `name`, `paymentTermId`.

### 4.6 Acceptance

- [x] CRUD pemasok + termin berfungsi.
- [x] Relasi PaymentTerm 1:N Supplier terbukti.
- [x] Delete termin ditolak jika dipakai.
- [x] Soft delete pemasok tidak merusak dokumen lama; nama pemasok lama diambil lewat method historis contract.
- [x] Staff tidak bisa create/edit/delete.
- [ ] Contract dipakai Inventory tanpa model Supplier (diverifikasi saat M4).
- [ ] Event tercatat di Reporting (menunggu M5).

## 5. InventoryModule

### 5.1 Tanggung jawab

- Mencatat stok masuk (dengan pemasok, harga satuan) dan stok keluar (tujuan).
- Menjaga saldo stok dan ledger mutasi.
- Menyediakan halaman saldo dan kartu stok.
- Validasi stok cukup di dalam transaksi; custom exception.
- Contract `InventoryQueryContract` untuk Reporting.
- Event stok untuk activity log dan low stock.

### 5.2 Tabel

`stock_receipts`, `stock_receipt_items`, `stock_issues`, `stock_issue_items`, `stock_balances`, `stock_movements`.

### 5.3 Route web

| Method | URI | Name | Middleware | Controller@method |
|---|---|---|---|---|
| GET | `/inventory/balances` | `inventory.balances.index` | `auth` | `StockBalanceController@index` |
| GET | `/inventory/stock-card` | `inventory.stock-card.index` | `auth` | `StockCardController@index` |
| GET | `/inventory/receipts` | `inventory.receipts.index` | `auth` | `StockReceiptController@index` |
| GET | `/inventory/receipts/create` | `inventory.receipts.create` | `auth`, `role:admin,staff_gudang` | `StockReceiptController@create` |
| POST | `/inventory/receipts` | `inventory.receipts.store` | `auth`, `role:admin,staff_gudang` | `StockReceiptController@store` |
| GET | `/inventory/receipts/{receipt}` | `inventory.receipts.show` | `auth` | `StockReceiptController@show` |
| GET | `/inventory/issues` | `inventory.issues.index` | `auth` | `StockIssueController@index` |
| GET | `/inventory/issues/create` | `inventory.issues.create` | `auth`, `role:admin,staff_gudang` | `StockIssueController@create` |
| POST | `/inventory/issues` | `inventory.issues.store` | `auth`, `role:admin,staff_gudang` | `StockIssueController@store` |
| GET | `/inventory/issues/{issue}` | `inventory.issues.show` | `auth` | `StockIssueController@show` |

**Tidak ada route edit/delete** untuk dokumen stok. Koreksi dilakukan dengan dokumen baru (di luar MVP).

### 5.4 Route API

| Method | URI | Name | Middleware | Controller@method |
|---|---|---|---|---|
| POST | `/api/v1/inventory/receipts` | `api.inventory.receipts.store` | `auth:sanctum`, `role:admin,staff_gudang` | `Api\InventoryApiController@storeReceipt` |
| POST | `/api/v1/inventory/issues` | `api.inventory.issues.store` | `auth:sanctum`, `role:admin,staff_gudang` | `Api\InventoryApiController@storeIssue` |
| GET | `/api/v1/inventory/balances` | `api.inventory.balances` | `auth:sanctum` | `Api\InventoryApiController@balances` |
| GET | `/api/v1/inventory/products/{productId}/movements` | `api.inventory.movements` | `auth:sanctum` | `Api\InventoryApiController@movements` |

- Endpoint tulis memakai `StoreStockReceiptRequest`/`StoreStockIssueRequest` dan service yang sama dengan web (tidak ada duplikasi logika).
- Respons sukses: `201 Created` dengan `{"data": {"id": ..., "number": ...}}`.
- Validasi gagal: `422` JSON. Stok kurang: `409` JSON dengan `code=INSUFFICIENT_STOCK`.
- `{productId}` pada movements divalidasi lewat contract; jika tidak ada → `404`.

### 5.5 Struktur file

```text
app/Modules/Inventory/
├── Contracts/InventoryQueryContract.php
├── DTO/
│   ├── StockBalanceView.php
│   ├── StockMovementView.php
│   ├── LowStockView.php
│   ├── StockReceiptData.php
│   └── StockIssueData.php
├── Database/
│   ├── Factories/StockReceiptFactory.php, StockReceiptItemFactory.php, StockIssueFactory.php, StockIssueItemFactory.php, StockMovementFactory.php
│   ├── Migrations/ (6 file, lihat DATABASE.md)
│   └── Seeders/InventorySeeder.php
├── Events/StockReceived.php, StockIssued.php, LowStockDetected.php
├── Exceptions/InsufficientStockException.php
├── Http/
│   ├── Controllers/
│   │   ├── StockReceiptController.php
│   │   ├── StockIssueController.php
│   │   ├── StockBalanceController.php
│   │   ├── StockCardController.php
│   │   └── Api/InventoryApiController.php
│   └── Requests/
│       ├── StoreStockReceiptRequest.php
│       ├── StoreStockIssueRequest.php
│       ├── StockDocumentFilterRequest.php
│       ├── StockBalanceFilterRequest.php
│       └── StockCardFilterRequest.php
├── Listeners/CreateStockBalance.php
├── Models/
│   ├── StockReceipt.php, StockReceiptItem.php
│   ├── StockIssue.php, StockIssueItem.php
│   ├── StockBalance.php
│   └── StockMovement.php
├── Policies/StockReceiptPolicy.php, StockIssuePolicy.php
├── Providers/InventoryServiceProvider.php
├── Services/
│   ├── ReceiveStockService.php
│   ├── IssueStockService.php
│   ├── InventoryQueryService.php
│   └── StockNumberGenerator.php
├── resources/views/inventory/
│   ├── balances/index.blade.php
│   ├── stock-card/index.blade.php
│   ├── receipts/{index,create,show,_form,_item-row}.blade.php
│   └── issues/{index,create,show,_form,_item-row}.blade.php
└── routes/web.php, api.php
```

### 5.6 Detail implementasi

#### Model dan relasi (semua dalam modul Inventory)

- Factory Inventory tidak boleh mengimpor model Product/Supplier/User. Semua ID lintas modul (`supplier_id`, `product_id`, `created_by`) diberikan oleh test; jangan membuat relasi lintas modul di factory.

- `StockReceipt`: `items()` hasMany `StockReceiptItem`; `$fillable` number, supplier_id, received_at, total_amount, created_by, note; cast `received_at` date, `total_amount` decimal:2.
- `StockReceiptItem`: `receipt()` belongsTo `StockReceipt`; `$fillable` product_id, qty, unit_cost, subtotal; cast decimal.
- `StockIssue`: `items()` hasMany `StockIssueItem`.
- `StockIssueItem`: `issue()` belongsTo `StockIssue`.
- `StockBalance`: `$fillable` product_id, qty; cast decimal:3.
- `StockMovement`: `$fillable` semua kolom; cast qty decimal:3, balance_after decimal:3, unit_cost decimal:2, movement_date date. Tidak ada relasi ke Product/User; `reference` dapat di-resolve internal untuk nomor dokumen.
- **Jangan** menambahkan `belongsTo(Product::class)`/`belongsTo(User::class)` lintas modul.

#### Service penerimaan

`ReceiveStockService::handle(StockReceiptData $data, int $actorId): StockReceipt`

1. Ambil snapshot supplier + produk lewat contract **sebelum** transaksi; jika null → `SupplierUnavailableException`/`ProductUnavailableException`.
2. `DB::transaction`:
   - buat receipt (number null),
   - loop item urut `product_id`:
     - buat item + subtotal,
     - `StockBalance::where('product_id')->lockForUpdate()->firstOrFail()`,
     - `qty_after = qty + item.qty`,
     - update saldo,
     - insert movement type `receipt`,
   - hitung total, set number via `StockNumberGenerator::forReceipt($receipt->id, $receipt->received_at)`, update.
3. Setelah commit: dispatch `StockReceived`; jika ada item yang saldo setelahnya <= min_stock, dispatch `LowStockDetected` (satu event per produk).
4. Kembalikan receipt.

#### Service pengeluaran

`IssueStockService::handle(StockIssueData $data, int $actorId): StockIssue`

1. Snapshot produk lewat contract; jika null → exception.
2. `DB::transaction`:
   - buat issue (number null),
   - loop item urut `product_id`:
     - lock saldo,
     - jika `qty < requested` → `throw new InsufficientStockException(...)`,
     - buat item,
     - update saldo (`qty - requested`),
     - insert movement type `issue`,
   - set number `SI-...`.
3. Setelah commit: dispatch `StockIssued`; dispatch `LowStockDetected` untuk produk yang melewati ambang.
4. Kembalikan issue.

#### Nomor dokumen

`StockNumberGenerator`:

```php
final class StockNumberGenerator
{
    public function forReceipt(int $id, CarbonInterface $date): string
    {
        return sprintf('SR-%s-%06d', $date->format('Ym'), $id);
    }

    public function forIssue(int $id, CarbonInterface $date): string
    {
        return sprintf('SI-%s-%06d', $date->format('Ym'), $id);
    }
}
```

#### Validasi stok

- `StoreStockIssueRequest`: `items` wajib array min 1; setiap `product_id` divalidasi `integer` + `distinct` (bukan `exists`). Service memvalidasi keberadaan/keaktifan produk lewat `ProductCatalogContract`; controller mengisi pilihan form dari contract.
- `StoreStockReceiptRequest`: `supplier_id` divalidasi `integer`; service memvalidasi via `SupplierDirectoryContract`. `items.*.product_id` integer + distinct; `qty` > 0; `unit_cost` >= 0.
- Jangan memakai `exists:products,id` atau `exists:suppliers,id` di Form Request karena itu query lintas modul. Validasi referensi dilakukan di service.

#### Halaman

- **Saldo**: tabel produk (nama dari contract), qty, min stok, status (Aman/Rendah), link kartu stok. Filter `search` via contract atau query saldo + snapshot.
- **Kartu stok**: pilih produk (select dari contract), filter tanggal, tabel tanggal, tipe, referensi, qty masuk, qty keluar, saldo setelah, catatan. Pagination. Nama produk untuk riwayat memakai `ProductCatalogContract::findIncludingDeactivated()` agar produk soft-deleted tetap terbaca.
- **Stok masuk**: form pilih pemasok (contract), tanggal, catatan, item dinamis (produk, qty, harga). Total dihitung di server.
- **Stok keluar**: form tanggal, tujuan, catatan, item dinamis (produk, qty). Tampilkan saldo saat ini di select/opsi (dari `InventoryQueryContract` internal service).
- **Show**: header dokumen + item + total; tombol kembali. Nama pemasok/produk pada dokumen lama diambil lewat `SupplierDirectoryContract::findIncludingDeactivated()` dan `ProductCatalogContract::findManyIncludingDeactivated()`.

#### API

- `Api\InventoryApiController` menerima `ReceiveStockService`, `IssueStockService`, dan `InventoryQueryContract`.
- `storeReceipt(StoreStockReceiptRequest $request, ReceiveStockService $service)`: bentuk `StockReceiptData` dari data tervalidasi, panggil service dengan `$request->user()->id`, kembalikan `201` + `{"data": {"id": ..., "number": ...}}`.
- `storeIssue(StoreStockIssueRequest $request, IssueStockService $service)`: sama, memakai `StockIssueData`; `InsufficientStockException` otomatis menjadi `409` JSON.
- `balances()`: kembalikan array `StockBalanceView` dari contract (JSON).
- `movements(int $productId)`: validasi produk lewat `ProductCatalogContract::findIncludingDeactivated()`; jika null → `404`; lalu kembalikan `StockMovementView[]`.
- Jangan menduplikasi logika transaksi/validasi stok di controller API; semua lewat service yang sama.

#### Policy

- `StockReceiptPolicy`/`StockIssuePolicy`: `viewAny/view` semua auth; `create` semua auth; `update/delete` selalu false.
- Route create/store eksplisit `role:admin,staff_gudang`.
- Tidak ada route update/delete, jadi policy update/delete hanya pertahanan jika kelak ditambah.

#### Event

- `StockReceived` payload: `receiptId`, `number`, `supplierId`, `items` (array productId, qty, unitCost), `receivedAt`, `actorId`.
- `StockIssued` payload: `issueId`, `number`, `items` (productId, qty), `issuedAt`, `actorId`.
- `LowStockDetected` payload: `productId`, `productName`, `currentQty`, `minStock`, `detectedAt`.
- Semua `ShouldDispatchAfterCommit`.

### 5.7 Acceptance

- [x] Stok masuk menambah saldo dan movement; total benar.
- [x] Stok keluar mengurangi saldo; stok kurang → exception, tidak ada data tersimpan.
- [x] Dua item, item kedua gagal → seluruh dokumen rollback.
- [x] Kartu stok menampilkan urutan kronologis dan saldo setelah sesuai.
- [x] Saldo tidak pernah negatif.
- [x] Nomor dokumen unik dan berformat benar.
- [x] Staff dan admin bisa transaksi; tidak ada edit/delete dokumen.
- [x] API transaksi memakai service yang sama; 201 sukses, 422 validasi, 409 stok kurang.
- [ ] Contract dipakai Reporting; tidak ada model Inventory di Reporting.
- [ ] Event tercatat; low stock muncul di dashboard.
- [x] `lockForUpdate()` dan `DB::transaction()` terlihat di kode (bukti laporan).

> **Status M4 (2026-11-19):** seluruh acceptance Inventory yang tidak bergantung pada Reporting sudah terpenuhi dan diuji. Dua item terakhir menunggu M5 (dashboard/activity log).

## 6. ReportingModule

### 6.1 Tanggung jawab

- Activity log dari event domain.
- Dashboard ringkasan: jumlah produk/pemasok, stok rendah, transaksi terakhir, aktivitas terakhir.
- Halaman activity (admin) dengan filter.
- **Tidak** memiliki tabel transaksi; hanya `activity_logs`.

### 6.2 Tabel

`activity_logs`.

### 6.3 Route web

| Method | URI | Name | Middleware | Controller@method |
|---|---|---|---|---|
| GET | `/dashboard` | `dashboard.index` | `auth` | `DashboardController@index` |
| GET | `/activity` | `activity.index` | `auth`, `role:admin` | `ActivityController@index` |

Root `/` di `routes/web.php` mengarah ke `dashboard.index` (dengan middleware `auth`), atau redirect login untuk guest.

### 6.4 Struktur file

```text
app/Modules/Reporting/
├── Database/
│   ├── Factories/ActivityLogFactory.php
│   ├── Migrations/2026_01_01_000400_create_activity_logs_table.php
│   └── Seeders/ (opsional)
├── Http/Controllers/
│   ├── DashboardController.php
│   └── ActivityController.php
├── Http/Requests/
│   └── ActivityFilterRequest.php
├── Listeners/RecordActivityLog.php
├── Models/ActivityLog.php
├── Policies/ActivityLogPolicy.php
├── Providers/ReportingServiceProvider.php
├── Services/DashboardService.php
├── resources/views/
│   ├── dashboard/index.blade.php
│   └── activity/index.blade.php
└── routes/web.php
```

### 6.5 Detail implementasi

- `RecordActivityLog` mendengarkan semua event domain (daftar di `CONTRACTS.md`). Mapping event → `event` string + `description`.
- Listener harus **tidak** menggagalkan operasi utama jika logging gagal; bungkus dengan `try/catch` + `Log::warning`, kecuali untuk test yang mengharapkan log.
- `ActivityLog` `$fillable`: actor_id, actor_name, event, subject_type, subject_id, description, payload; cast `payload` array; `$timestamps = false` dengan `created_at` diisi manual atau gunakan `CREATED_AT` saja.
- `DashboardService`:
  - `ProductCatalogContract::count()`
  - `SupplierDirectoryContract::count()`
  - `InventoryQueryContract::lowStock()` (butuh nama produk; gunakan `ProductCatalogContract::findMany`)
  - `InventoryQueryContract::recentMovements(5)` (nama produk via contract)
  - query `ActivityLog::latest('created_at')->limit(5)`
- `ActivityController@index`: filter `event`, `from`, `to`, `actor` (opsional); pagination 25; hanya admin.
- `ActivityLogPolicy::viewAny` hanya admin; route juga `role:admin`.
- Tidak ada contract Reporting; modul lain tidak perlu membaca activity log.

### 6.6 Acceptance

- [ ] Setiap aksi penting (login/register, CRUD master, stok masuk/keluar) menghasilkan activity log.
- [ ] Dashboard menampilkan angka yang benar dan daftar stok rendah.
- [ ] Activity page hanya admin; staff 403.
- [ ] Reporting tidak mengimpor model Product/Inventory/User.
- [ ] Log tidak menyimpan data sensitif.

## 7. Route Global dan Middleware

### 7.1 Root

`routes/web.php`:

```php
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard.index')
        : redirect()->route('login');
})->name('home');
```

### 7.2 Middleware stack

| Kebutuhan | Middleware |
|---|---|
| Halaman publik | `guest` |
| Halaman login/register | `guest`, `throttle:6,1` |
| Semua halaman aplikasi | `auth` |
| Tulis master data | `auth`, `role:admin` |
| Transaksi stok | `auth`, `role:admin,staff_gudang` |
| Activity | `auth`, `role:admin` |
| API terproteksi | `auth:sanctum` |
| API admin (jika ada) | `auth:sanctum`, `role:admin` |

### 7.3 Nama route yang dipakai di view

Gunakan `route('products.index')`, `route('inventory.receipts.create')`, dst. Jangan hardcode URL.

## 8. Navigasi UI

| Menu | Route | Terlihat untuk |
|---|---|---|
| Dashboard | `dashboard.index` | Semua |
| Barang | `products.index` | Semua |
| Kategori | `categories.index` | Semua |
| Satuan | `units.index` | Semua |
| Pemasok | `suppliers.index` | Semua |
| Termin | `payment-terms.index` | Semua |
| Stok Masuk | `inventory.receipts.index` | Semua |
| Stok Keluar | `inventory.issues.index` | Semua |
| Saldo Stok | `inventory.balances.index` | Semua |
| Kartu Stok | `inventory.stock-card.index` | Semua |
| Activity Log | `activity.index` | Admin (`@role('admin')`) |
| Logout | `logout` (POST) | Semua |

Tombol "Tambah/Edit/Hapus" master data dibungkus `@role('admin')`; route tetap dilindungi middleware.

## 9. Definition of Done per Modul

Sebuah modul selesai jika:

- [ ] Migration, model, relasi, factory, seeder berjalan dari nol.
- [ ] Provider memuat migration/route/view dan binding contract.
- [ ] Route sesuai tabel di dokumen ini; middleware benar.
- [ ] Form Request dipakai di semua input; pesan Indonesia.
- [ ] Policy/middleware diuji (401/403/200).
- [ ] Tidak ada akses model/tabel lintas modul.
- [ ] Event dikirim setelah commit dan listener terdaftar.
- [ ] Feature test modul lulus; Pint bersih.
- [ ] Screenshot UI modul diambil untuk laporan.
- [ ] Commit deskriptif dibuat.
