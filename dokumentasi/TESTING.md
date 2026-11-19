# Testing — Strategi, Matriks, dan Contoh

Dokumen ini menjadi acuan pengujian otomatis dan manual. Bukti test dipakai di BAB IV laporan. Target: semua fitur wajib punya test, kasus gagal penting terbukti, dan rollback transaksi dapat didemonstrasikan.

## 1. Prinsip

1. **Test perilaku, bukan implementasi.** Fokus pada input → output, status code, database, dan event.
2. **Setiap bug yang ditemukan** harus mendapat regression test.
3. **Gunakan factory** untuk data; jangan bergantung pada data seeder kecuali test seeder.
4. **Test database terpisah**: SQLite `:memory:`.
5. **Jangan memakai `Event::fake()`** pada test yang ingin memverifikasi listener/activity log; fake hanya untuk memastikan event dikirim.
6. **Test rollback** wajib ada untuk transaksi stok.
7. **Test RBAC** wajib untuk setiap area (master data, inventory, activity).
8. **Test validasi** minimal satu kasus gagal per form penting.
9. Test harus deterministik: jangan bergantung pada waktu sekarang tanpa `Carbon::setTestNow()`.
10. Jalankan `vendor/bin/pint --test` sebelum commit.

## 2. Konfigurasi Test

`.env.testing`:

```dotenv
APP_ENV=testing
APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=
APP_DEBUG=true
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_STORE=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
MAIL_MAILER=array
```

`phpunit.xml` (ringkas):

```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="CACHE_STORE" value="array"/>
    <env name="SESSION_DRIVER" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
</php>
```

Base test case:

```php
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication; // Laravel 11+ biasanya tidak perlu trait ini
}
```

Gunakan `RefreshDatabase` di feature test yang menyentuh database:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;
}
```

## 3. Struktur Test

```text
tests/
├── Feature/
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   ├── RegisterTest.php
│   │   └── RoleAccessTest.php
│   ├── Product/
│   │   ├── ProductCrudTest.php
│   │   ├── CategoryCrudTest.php
│   │   └── UnitCrudTest.php
│   ├── Supplier/
│   │   └── SupplierCrudTest.php
│   ├── Inventory/
│   │   ├── ReceiveStockTest.php
│   │   ├── IssueStockTest.php
│   │   ├── InsufficientStockTest.php
│   │   ├── RollbackTest.php
│   │   ├── StockCardTest.php
│   │   └── StockBalanceTest.php
│   └── Reporting/
│       ├── DashboardTest.php
│       └── ActivityLogTest.php
├── Unit/
│   ├── StockNumberGeneratorTest.php
│   └── DomainExceptionTest.php
└── Architecture/
    └── ModuleBoundaryTest.php (opsional)
```

## 4. Matriks Test

### 4.1 Auth dan RBAC

> **Status 2026-11-19:** AUTH-01 sampai AUTH-11 sudah diimplementasikan di `tests/Feature/Auth/` dan lulus. AUTH-12/AUTH-13 menunggu modul Inventory (M4).

| ID | Test | Skenario | Ekspektasi |
|---|---|---|---|
| AUTH-01 | `LoginTest::test_user_can_login_with_valid_credentials` | email/password benar | redirect dashboard, authenticated |
| AUTH-02 | `LoginTest::test_user_cannot_login_with_invalid_password` | password salah | error `email`, guest |
| AUTH-03 | `LoginTest::test_login_is_rate_limited` | 6+ percobaan | 429/terkunci |
| AUTH-04 | `RegisterTest::test_public_registration_creates_staff_only` | register | user role `staff_gudang`, bukan admin |
| AUTH-05 | `RegisterTest::test_email_must_be_unique` | email duplikat | error `email` |
| AUTH-06 | `RoleAccessTest::test_guest_is_redirected_to_login` | GET `/products` | redirect login |
| AUTH-07 | `RoleAccessTest::test_staff_cannot_access_admin_routes` | GET create/POST/DELETE | 403 |
| AUTH-08 | `RoleAccessTest::test_admin_can_access_admin_routes` | GET create | 200 |
| AUTH-09 | `LoginTest::test_api_login_returns_token` | POST API login | 200, token ada |
| AUTH-10 | `LoginTest::test_api_requires_token` | GET `/api/v1/me` | 401 |
| AUTH-11 | `LoginTest::test_api_logout_revokes_token` | logout + me | 401 setelah logout |
| AUTH-12 | `RoleAccessTest::test_staff_can_create_stock_receipt` | POST receipt | sukses, saldo naik |
| AUTH-13 | `RoleAccessTest::test_staff_cannot_view_activity_log` | GET `/activity` | 403 |

### 4.2 Product

| ID | Test | Skenario | Ekspektasi |
|---|---|---|---|
| PRD-01 | `ProductCrudTest::test_admin_can_create_product` | data valid + kategori | 302, DB ada, pivot ada |
| PRD-02 | `ProductCrudTest::test_sku_must_be_unique` | SKU duplikat | error `sku` |
| PRD-03 | `ProductCrudTest::test_unit_is_required_and_exists` | unit kosong/tidak ada | error `unit_id` |
| PRD-04 | `ProductCrudTest::test_min_stock_cannot_be_negative` | -1 | error `min_stock` |
| PRD-05 | `ProductCrudTest::test_admin_can_update_product` | ubah nama/kategori | DB berubah, pivot sync |
| PRD-06 | `ProductCrudTest::test_admin_can_soft_delete_product` | delete | `deleted_at` terisi, index tidak memuat |
| PRD-07 | `ProductCrudTest::test_soft_deleted_product_is_not_found_by_contract` | delete + contract find | null |
| PRD-08 | `CategoryCrudTest::test_category_cannot_be_deleted_when_used` | kategori dipakai | error `delete`, DB tetap |
| PRD-09 | `UnitCrudTest::test_unit_cannot_be_deleted_when_used` | unit dipakai | error `delete` |
| PRD-10 | `ProductCrudTest::test_staff_cannot_create_product` | staff POST | 403 |
| PRD-11 | `ProductCrudTest::test_contract_can_return_deactivated_product_for_history` | delete + `findIncludingDeactivated` | snapshot tidak null |

> **Status 2026-11-19:** PRD-01 sampai PRD-11 sudah diimplementasikan di `tests/Feature/Product/`. Tambahan test mencakup event produk, filter/pencarian contract, slug kategori, normalisasi kode satuan, delete guard untuk produk soft-deleted, seeder, dan RBAC staff. Total 32 test Product lulus.

### 4.3 Supplier

| ID | Test | Skenario | Ekspektasi |
|---|---|---|---|
| SUP-01 | `SupplierCrudTest::test_admin_can_create_supplier` | data valid | 302, DB ada |
| SUP-02 | `SupplierCrudTest::test_supplier_code_must_be_unique` | kode duplikat | error `code` |
| SUP-03 | `SupplierCrudTest::test_payment_term_must_exist` | ID tidak ada | error `payment_term_id` |
| SUP-04 | `SupplierCrudTest::test_admin_can_soft_delete_supplier` | delete | `deleted_at` terisi |
| SUP-05 | `SupplierCrudTest::test_payment_term_cannot_be_deleted_when_used` | termin dipakai | error `delete` |
| SUP-06 | `SupplierCrudTest::test_staff_cannot_update_supplier` | staff PUT | 403 |

### 4.4 Inventory

| ID | Test | Skenario | Ekspektasi |
|---|---|---|---|
| INV-01 | `ReceiveStockTest::test_receipt_increases_balance_and_creates_movement` | 2 item | saldo naik, movement 2, total benar |
| INV-02 | `ReceiveStockTest::test_receipt_number_format` | tanggal tetap | `SR-YYYYMM-000001` |
| INV-03 | `ReceiveStockTest::test_receipt_requires_at_least_one_item` | items kosong | error `items` |
| INV-04 | `ReceiveStockTest::test_receipt_rejects_supplier_unavailable` | supplier soft-deleted | error, tidak ada data |
| INV-05 | `IssueStockTest::test_issue_decreases_balance_and_creates_movement` | stok cukup | saldo turun, movement ada |
| INV-06 | `IssueStockTest::test_issue_number_format` | tanggal tetap | `SI-YYYYMM-000001` |
| INV-07 | `InsufficientStockTest::test_issue_throws_when_stock_is_not_enough` | qty > saldo | exception, pesan memuat tersedia/diminta |
| INV-08 | `InsufficientStockTest::test_no_data_is_saved_when_stock_is_insufficient` | issue gagal | tidak ada issue/item/movement; saldo tetap |
| INV-09 | `RollbackTest::test_second_item_failure_rolls_back_first_item` | item 1 sukses, item 2 gagal | tidak ada issue/item/movement; saldo kedua tetap |
| INV-10 | `StockBalanceTest::test_balance_never_negative` | issue melebihi stok | saldo >= 0 |
| INV-11 | `StockCardTest::test_stock_card_lists_movements_chronologically` | 2 mutasi | urutan tanggal/id, saldo setelah benar |
| INV-12 | `StockCardTest::test_stock_card_can_filter_by_date` | from/to | hanya rentang yang tampil |
| INV-13 | `StockCardTest::test_staff_can_view_stock_card` | staff GET | 200 |
| INV-14 | `InsufficientStockTest::test_api_returns_409_with_code` | POST `/api/v1/inventory/issues` | 409, `code=INSUFFICIENT_STOCK` |
| INV-15 | `ReceiveStockTest::test_low_stock_event_dispatched` | saldo <= min_stock | event `LowStockDetected` |
| INV-16 | `IssueStockTest::test_stock_issue_is_immutable` | route edit/delete | 404/405 |
| INV-17 | `ReceiveStockTest::test_api_receipt_returns_201` | POST API dengan token | 201, nomor dokumen ada |
| INV-18 | `IssueStockTest::test_api_requires_token` | POST API tanpa token | 401 |

### 4.5 Reporting

| ID | Test | Skenario | Ekspektasi |
|---|---|---|---|
| RPT-01 | `ActivityLogTest::test_product_created_is_logged` | buat produk | log `product.created` |
| RPT-02 | `ActivityLogTest::test_stock_issued_is_logged` | issue | log `stock.issued` dengan nomor |
| RPT-03 | `ActivityLogTest::test_activity_page_is_admin_only` | staff GET | 403 |
| RPT-04 | `DashboardTest::test_dashboard_shows_counts` | 2 produk, 1 supplier | angka sesuai |
| RPT-05 | `DashboardTest::test_dashboard_shows_low_stock` | saldo < min | produk muncul |
| RPT-06 | `DashboardTest::test_dashboard_does_not_expose_other_modules_models` | code review/architecture test | tidak ada import model |

### 4.6 Unit

| ID | Test | Ekspektasi |
|---|---|---|
| UNIT-01 | `StockNumberGeneratorTest::test_receipt_number` | `SR-202601-000001` |
| UNIT-02 | `StockNumberGeneratorTest::test_issue_number` | `SI-202601-000001` |
| UNIT-03 | `DomainExceptionTest::test_insufficient_stock_message` | pesan memuat nama, tersedia, diminta |
| UNIT-04 | `DomainExceptionTest::test_product_unavailable_message` | memuat ID |

## 5. Contoh Test

### 5.1 Persiapan role dan user

```php
protected function setUp(): void
{
    parent::setUp();

    $this->seed(RoleSeeder::class);
}
```

```php
$admin = User::factory()->create();
$admin->roles()->attach(Role::where('name', 'admin')->first());

$staff = User::factory()->create();
$staff->roles()->attach(Role::where('name', 'staff_gudang')->first());
```

Atau factory state:

```php
// UserFactory
public function admin(): static
{
    return $this->afterCreating(fn (User $user) => $user->roles()->sync(
        Role::where('name', 'admin')->pluck('id')
    ));
}

public function staff(): static
{
    return $this->afterCreating(fn (User $user) => $user->roles()->sync(
        Role::where('name', 'staff_gudang')->pluck('id')
    ));
}
```

### 5.2 Product CRUD

```php
public function test_admin_can_create_product(): void
{
    $admin = User::factory()->admin()->create();
    $unit = Unit::factory()->create();
    $categories = Category::factory()->count(2)->create();

    $response = $this->actingAs($admin)->post(route('products.store'), [
        'sku' => 'prd-0001',
        'name' => 'Semen 50kg',
        'description' => 'Semen Portland',
        'unit_id' => $unit->id,
        'min_stock' => 10,
        'categories' => $categories->pluck('id')->all(),
    ]);

    $response->assertRedirect(route('products.index'));
    $this->assertDatabaseHas('products', ['sku' => 'PRD-0001']);

    $product = Product::where('sku', 'PRD-0001')->first();
    $this->assertCount(2, $product->categories);
}
```

### 5.3 Stok masuk

```php
public function test_receipt_increases_balance_and_creates_movement(): void
{
    $staff = User::factory()->staff()->create();
    $supplier = Supplier::factory()->create();
    $unit = Unit::factory()->create();

    $productA = Product::factory()->create(['unit_id' => $unit->id, 'min_stock' => 0]);
    $productB = Product::factory()->create(['unit_id' => $unit->id, 'min_stock' => 0]);

    StockBalance::create(['product_id' => $productA->id, 'qty' => 0]);
    StockBalance::create(['product_id' => $productB->id, 'qty' => 0]);

    $response = $this->actingAs($staff)->post(route('inventory.receipts.store'), [
        'supplier_id' => $supplier->id,
        'received_at' => '2026-01-05',
        'note' => 'Penerimaan awal',
        'items' => [
            ['product_id' => $productA->id, 'qty' => 10, 'unit_cost' => 5000],
            ['product_id' => $productB->id, 'qty' => 5, 'unit_cost' => 2000],
        ],
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('stock_balances', ['product_id' => $productA->id, 'qty' => 10]);
    $this->assertDatabaseHas('stock_balances', ['product_id' => $productB->id, 'qty' => 5]);
    $this->assertDatabaseCount('stock_movements', 2);

    $receipt = StockReceipt::first();
    $this->assertSame('60000.00', $receipt->total_amount);
    $this->assertMatchesRegularExpression('/^SR-\d{6}-\d{6}$/', $receipt->number);
}
```

> Jika seeder/event `ProductCreated` sudah membuat saldo, jangan `create` manual; gunakan `StockBalance::firstOrCreate`. Sesuaikan test dengan mekanisme yang dipilih.

### 5.4 Stok kurang

```php
public function test_issue_throws_when_stock_is_not_enough(): void
{
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create(['min_stock' => 0]);
    StockBalance::create(['product_id' => $product->id, 'qty' => 5]);

    $response = $this->actingAs($staff)->post(route('inventory.issues.store'), [
        'issued_at' => '2026-01-06',
        'items' => [
            ['product_id' => $product->id, 'qty' => 10],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('items');

    $this->assertDatabaseCount('stock_issues', 0);
    $this->assertDatabaseCount('stock_issue_items', 0);
    $this->assertDatabaseCount('stock_movements', 0);
    $this->assertDatabaseHas('stock_balances', ['product_id' => $product->id, 'qty' => 5]);
}
```

### 5.5 Rollback dua item

```php
public function test_second_item_failure_rolls_back_first_item(): void
{
    $staff = User::factory()->staff()->create();

    $productA = Product::factory()->create(['min_stock' => 0]);
    $productB = Product::factory()->create(['min_stock' => 0]);

    StockBalance::create(['product_id' => $productA->id, 'qty' => 10]);
    StockBalance::create(['product_id' => $productB->id, 'qty' => 1]);

    $response = $this->actingAs($staff)->post(route('inventory.issues.store'), [
        'issued_at' => '2026-01-06',
        'items' => [
            ['product_id' => $productA->id, 'qty' => 2], // cukup
            ['product_id' => $productB->id, 'qty' => 5], // gagal
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('items');

    $this->assertDatabaseCount('stock_issues', 0);
    $this->assertDatabaseCount('stock_issue_items', 0);
    $this->assertDatabaseCount('stock_movements', 0);
    $this->assertDatabaseHas('stock_balances', ['product_id' => $productA->id, 'qty' => 10]);
    $this->assertDatabaseHas('stock_balances', ['product_id' => $productB->id, 'qty' => 1]);
}
```

**Penting:** service mengurutkan item berdasarkan `product_id` menaik. Pastikan ID produk A < B, atau buat urutan eksplisit agar item pertama benar-benar diproses sebelum item kedua gagal.

### 5.6 Kartu stok

```php
public function test_stock_card_lists_movements_chronologically(): void
{
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create(['min_stock' => 0]);

    StockMovement::factory()->create([
        'product_id' => $product->id,
        'movement_type' => 'receipt',
        'movement_date' => '2026-01-01',
        'qty_in' => 10,
        'qty_out' => 0,
        'balance_after' => 10,
        'created_by' => $staff->id,
    ]);
    StockMovement::factory()->create([
        'product_id' => $product->id,
        'movement_type' => 'issue',
        'movement_date' => '2026-01-02',
        'qty_in' => 0,
        'qty_out' => 3,
        'balance_after' => 7,
        'created_by' => $staff->id,
    ]);

    $response = $this->actingAs($staff)->get(route('inventory.stock-card.index', [
        'product_id' => $product->id,
    ]));

    $response->assertOk();
    // View menampilkan tanggal dengan format d-m-Y.
    $response->assertSeeInOrder(['01-01-2026', '02-01-2026']);
    $response->assertSee('10,000');
    $response->assertSee('7,000');
}
```

### 5.7 API token

```php
public function test_api_login_returns_token_and_me_requires_it(): void
{
    $user = User::factory()->staff()->create([
        'email' => 'staff@example.test',
        'password' => Hash::make('password123'),
    ]);

    $login = $this->postJson('/api/v1/auth/login', [
        'email' => 'staff@example.test',
        'password' => 'password123',
    ]);

    $login->assertOk()->assertJsonStructure(['token', 'token_type', 'user']);

    $token = $login->json('token');

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('email', 'staff@example.test');
}
```

### 5.8 Event

```php
use Illuminate\Support\Facades\Event;

public function test_stock_issued_event_is_dispatched(): void
{
    Event::fake([StockIssued::class]);

    // ... siapkan produk, saldo, user
    $this->actingAs($staff)->post(route('inventory.issues.store'), [...]);
}
```

Jika `Event::fake()`, listener Reporting tidak berjalan. Untuk test activity log, jangan fake; assert DB `activity_logs`.

> **API + Sanctum:** untuk route `auth:sanctum`, gunakan `Sanctum::actingAs($user)` (atau `Sanctum::actingAs($user, ['*'])`) atau uji token nyata dari login. `$this->actingAs($user)` hanya mengeset guard web; jangan mengandalkannya sebagai bukti token. Route API inventory juga memakai `role:admin,staff_gudang`, jadi pastikan user punya role; ability token bukan pengganti role.

### 5.9 Contract

```php
public function test_product_contract_excludes_soft_deleted(): void
{
    $product = Product::factory()->create();
    $product->delete();

    $this->assertNull(app(ProductCatalogContract::class)->find($product->id));
}
```

### 5.10 Architecture boundary (opsional)

```php
public function test_modules_do_not_import_other_module_models(): void
{
    $modules = ['Auth', 'Product', 'Supplier', 'Inventory', 'Reporting'];

    foreach ($modules as $module) {
        $other = array_diff($modules, [$module]);
        $files = File::allFiles(app_path("Modules/{$module}"));

        foreach ($files as $file) {
            $contents = File::get($file);

            foreach ($other as $target) {
                $this->assertStringNotContainsString(
                    "App\\Modules\\{$target}\\Models",
                    $contents,
                    "{$file} mengimpor model modul {$target}."
                );
            }
        }
    }
}
```

Sesuaikan pengecualian untuk file test; test ini opsional tetapi bagus untuk bukti batas modul.

## 6. Uji Manual dan Bukti Laporan

Ambil screenshot saat aplikasi stabil, bukan di akhir. Daftar minimal:

| No | Bukti | Lokasi saran |
|---|---|---|
| 1 | Struktur `app/Modules/` | `dokumentasi/screenshots/01-struktur-modul.png` |
| 2 | Service Provider (satu contoh) | `02-provider.png` |
| 3 | Model + relasi (Product) | `03-model-relasi.png` |
| 4 | Contract + binding | `04-contract.png` |
| 5 | Form Request | `05-form-request.png` |
| 6 | Custom Exception | `06-custom-exception.png` |
| 7 | Controller / Service transaksi | `07-transaksi.png` |
| 8 | `git log --oneline --graph` | `08-git-log.png` |
| 9 | Login sukses | `09-login.png` |
| 10 | Login gagal (validasi) | `10-login-gagal.png` |
| 11 | Dashboard admin | `11-dashboard-admin.png` |
| 12 | CRUD barang | `12-produk.png` |
| 13 | Validasi barang gagal | `13-validasi-produk.png` |
| 14 | CRUD pemasok | `14-pemasok.png` |
| 15 | Stok masuk + saldo | `15-stok-masuk.png` |
| 16 | Stok keluar + saldo | `16-stok-keluar.png` |
| 17 | Stok kurang (error) | `17-stok-kurang.png` |
| 18 | Kartu stok | `18-kartu-stok.png` |
| 19 | Staff 403 | `19-staff-403.png` |
| 20 | Activity log | `20-activity-log.png` |
| 21 | API login token (Postman) | `21-api-login.png` |
| 22 | API 401/403/409 | `22-api-error.png` |
| 23 | Bukti test hijau | `23-test-hijau.png` |
| 24 | Git graph | `24-git-graph.png` |

Checklist manual:

- [ ] Login/logout web dan API.
- [ ] Setiap role melihat menu yang benar.
- [ ] CRUD semua master data.
- [ ] Validasi setiap form (kosong, duplikat, negatif, referensi tidak ada).
- [ ] Stok masuk/keluar satu dan banyak item.
- [ ] Stok kurang, produk soft-deleted, supplier soft-deleted.
- [ ] Rollback dua item (lihat saldo sebelum/sesudah).
- [ ] Kartu stok filter tanggal dan saldo setelah.
- [ ] Dashboard stok rendah.
- [ ] Activity log admin.
- [ ] 403 staff, 401 API, 404 data, 419 CSRF.
- [ ] Responsif dasar (mobile/desktop).

## 7. Perintah

```bash
# Semua test
php artisan test

# Satu file
php artisan test tests/Feature/Inventory/RollbackTest.php

# Filter nama test
php artisan test --filter=rollback

# Stop pada kegagalan pertama
php artisan test --stop-on-failure

# Coverage (jika xdebug/pcov tersedia)
php artisan test --coverage

# Format
vendor/bin/pint --test
vendor/bin/pint

# Migrasi + seed ulang
php artisan migrate:fresh --seed
```

## 8. CI Sederhana (Opsional)

`.github/workflows/tests.yml`:

```yaml
name: Tests

on:
  push:
    branches: [main]
  pull_request:

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, pdo_sqlite, sqlite3

      - name: Install Composer
        run: composer install --no-interaction --prefer-dist

      - name: Prepare env
        run: |
          cp .env.example .env
          php artisan key:generate

      - name: Run tests
        run: php artisan test

      - name: Pint
        run: vendor/bin/pint --test
```

Jika CI tidak dipakai, tetap jalankan perintah yang sama secara lokal sebelum submit.

## 9. Definition of Done Test

- [ ] Semua test di matriks wajib ada minimal satu per fitur.
- [ ] Test rollback dan stok kurang lulus.
- [ ] Test RBAC 401/403 lulus.
- [ ] Test validasi per form lulus.
- [ ] Test contract/event lulus.
- [ ] `php artisan test` hijau.
- [ ] `vendor/bin/pint --test` hijau.
- [ ] Screenshot test hijau diambil.
- [ ] Kegagalan yang ditemukan didokumentasikan di laporan (bab pengujian).
- [ ] Tidak ada test yang bergantung pada database produksi.
