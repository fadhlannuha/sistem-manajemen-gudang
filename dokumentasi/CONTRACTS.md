# Contracts dan Domain Events

Dokumen ini mendefinisikan **satu-satunya jalur komunikasi antar modul**: Contract (interface) untuk kebutuhan data sinkron, dan Domain Event untuk reaksi/efek samping. Tidak ada modul yang boleh mengimpor model, query builder, atau tabel modul lain.

## 1. Aturan Utama

1. Contract berada di `app/Modules/<Modul>/Contracts/`, implementasi di `Services/`.
2. Contract **hanya** memakai tipe primitif, DTO milik modul pemilik, array, `null`, dan `bool`. **Dilarang** mengembalikan Eloquent model, `Collection` model, `LengthAwarePaginator`, atau query builder.
3. Contract tidak boleh membocorkan nama tabel/kolom; pemanggil hanya tahu method dan DTO.
4. Contract tidak melempar exception HTTP (`abort`, `HttpException`); gunakan exception domain atau kembalikan `null`.
5. Event membawa ID dan data primitif, **bukan model**. Event dikirim setelah commit.
6. Listener didaftarkan eksplisit di provider modul; tidak mengandalkan auto-discovery.
7. Perubahan signature contract harus diperbarui di dokumen ini dan di semua pemakai.

## 2. Peta Contract

| Contract | Modul pemilik | Pemakai | Tujuan |
|---|---|---|---|
| `ProductCatalogContract` | Product | Inventory, Reporting | Cari produk, snapshot, hitung produk |
| `SupplierDirectoryContract` | Supplier | Inventory, Reporting | Cari pemasok, snapshot, hitung pemasok |
| `UserDirectoryContract` | Auth | Inventory, Reporting | Ringkasan user untuk aktor/log |
| `InventoryQueryContract` | Inventory | Reporting | Saldo, movement, stok rendah |

**Tidak ada contract dari Inventory ke Product/Supplier untuk menulis.** Inventory hanya membaca katalog; penulisan stok sepenuhnya milik Inventory.

## 3. DTO

DTO adalah `final readonly class` dengan properti publik. DTO tidak memiliki method yang mengakses database. Boleh ada static factory `fromModel()` **hanya di dalam modul pemilik** (bukan di contract).

### 3.1 `ProductSnapshot`

```php
<?php

namespace App\Modules\Product\DTO;

final readonly class ProductSnapshot
{
    public function __construct(
        public int $id,
        public string $sku,
        public string $name,
        public int $unitId,
        public string $unitName,
        public string $unitCode,
        public float $minStock,
    ) {}
}
```

### 3.2 `SupplierSnapshot`

```php
<?php

namespace App\Modules\Supplier\DTO;

final readonly class SupplierSnapshot
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public int $paymentTermId,
        public string $paymentTermName,
        public int $paymentTermDays,
    ) {}
}
```

### 3.3 `UserSummary`

```php
<?php

namespace App\Modules\Auth\DTO;

final readonly class UserSummary
{
    /**
     * @param  array<int, string>  $roles
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public array $roles = [],
    ) {}
}
```

### 3.4 `StockBalanceView`

```php
<?php

namespace App\Modules\Inventory\DTO;

final readonly class StockBalanceView
{
    public function __construct(
        public int $productId,
        public float $qty,
        public ?string $updatedAt = null,
    ) {}
}
```

### 3.5 `StockMovementView`

```php
<?php

namespace App\Modules\Inventory\DTO;

final readonly class StockMovementView
{
    public function __construct(
        public int $id,
        public int $productId,
        public string $type,          // receipt | issue | adjustment
        public ?string $referenceType,
        public ?int $referenceId,
        public ?string $referenceNumber,
        public string $movementDate,  // Y-m-d
        public float $qtyIn,
        public float $qtyOut,
        public float $balanceAfter,
        public ?float $unitCost = null,
        public ?string $note = null,
    ) {}
}
```

### 3.6 `LowStockView`

```php
<?php

namespace App\Modules\Inventory\DTO;

final readonly class LowStockView
{
    public function __construct(
        public int $productId,
        public float $qty,
        public float $minStock,
    ) {}
}
```

> Nama produk **tidak** disimpan di DTO Inventory. Reporting/UI mengambil nama lewat `ProductCatalogContract::findMany()` agar tidak ada duplikasi data lintas modul.

### 3.7 `StockReceiptData`

DTO masukan untuk `ReceiveStockService`. Dibentuk di controller dari data Form Request yang sudah tervalidasi.

```php
<?php

namespace App\Modules\Inventory\DTO;

final readonly class StockReceiptData
{
    /**
     * @param  array<int, array{product_id: int, qty: float, unit_cost: float}>  $items
     */
    public function __construct(
        public int $supplierId,
        public string $receivedAt,
        public ?string $note,
        public array $items,
    ) {}

    /**
     * @return array<int, int>
     */
    public function productIds(): array
    {
        return array_map(
            static fn (array $item): int => (int) $item['product_id'],
            $this->items,
        );
    }
}
```

### 3.8 `StockIssueData`

DTO masukan untuk `IssueStockService`.

```php
<?php

namespace App\Modules\Inventory\DTO;

final readonly class StockIssueData
{
    /**
     * @param  array<int, array{product_id: int, qty: float}>  $items
     */
    public function __construct(
        public string $issuedAt,
        public ?string $destination,
        public ?string $note,
        public array $items,
    ) {}

    /**
     * @return array<int, int>
     */
    public function productIds(): array
    {
        return array_map(
            static fn (array $item): int => (int) $item['product_id'],
            $this->items,
        );
    }
}
```

> Item tetap berupa array agar form multi-item mudah dipetakan; tipe dan rentangnya sudah dijamin Form Request. Jangan menaruh model `Product` di dalam DTO.

## 4. Contract Product

```php
<?php

namespace App\Modules\Product\Contracts;

use App\Modules\Product\DTO\ProductSnapshot;

interface ProductCatalogContract
{
    public function find(int $productId): ?ProductSnapshot;

    /**
     * Untuk kebutuhan historis (kartu stok, dokumen lama): tetap
     * mengembalikan produk yang sudah soft-deleted.
     */
    public function findIncludingDeactivated(int $productId): ?ProductSnapshot;

    /**
     * @param  array<int, int>  $productIds
     * @return array<int, ProductSnapshot>  keyed by product id
     */
    public function findMany(array $productIds): array;

    /**
     * @param  array<int, int>  $productIds
     * @return array<int, ProductSnapshot>  keyed by product id; termasuk yang soft-deleted
     */
    public function findManyIncludingDeactivated(array $productIds): array;

    public function exists(int $productId): bool;

    /**
     * @return array<int, ProductSnapshot>
     */
    public function search(string $term = '', int $limit = 20): array;

    public function count(): int;
}
```

Implementasi (`ProductCatalogService`) memakai model `Product`, `Unit` (modul sendiri), dan mengembalikan `null` untuk produk soft-deleted. Method `*IncludingDeactivated()` memakai `withTrashed()` khusus untuk menampilkan riwayat. Contoh:

```php
public function find(int $productId): ?ProductSnapshot
{
    $product = Product::query()
        ->with('unit:id,name,code')
        ->find($productId);

    return $product ? $this->toSnapshot($product) : null;
}

public function findIncludingDeactivated(int $productId): ?ProductSnapshot
{
    $product = Product::query()
        ->withTrashed()
        ->with('unit:id,name,code')
        ->find($productId);

    return $product ? $this->toSnapshot($product) : null;
}
```

## 5. Contract Supplier

```php
<?php

namespace App\Modules\Supplier\Contracts;

use App\Modules\Supplier\DTO\SupplierSnapshot;

interface SupplierDirectoryContract
{
    public function find(int $supplierId): ?SupplierSnapshot;

    /**
     * Untuk menampilkan dokumen lama milik pemasok yang sudah soft-deleted.
     */
    public function findIncludingDeactivated(int $supplierId): ?SupplierSnapshot;

    /**
     * @param  array<int, int>  $supplierIds
     * @return array<int, SupplierSnapshot>
     */
    public function findMany(array $supplierIds): array;

    public function exists(int $supplierId): bool;

    /**
     * @return array<int, SupplierSnapshot>
     */
    public function search(string $term = '', int $limit = 20): array;

    public function count(): int;
}
```

`find()` mengembalikan `null` untuk supplier soft-deleted; `findIncludingDeactivated()` dipakai hanya untuk histori.

## 6. Contract User

```php
<?php

namespace App\Modules\Auth\Contracts;

use App\Modules\Auth\DTO\UserSummary;

interface UserDirectoryContract
{
    public function find(int $userId): ?UserSummary;

    /**
     * @param  array<int, int>  $userIds
     * @return array<int, UserSummary>
     */
    public function findMany(array $userIds): array;

    public function count(): int;
}
```

## 7. Contract Inventory

```php
<?php

namespace App\Modules\Inventory\Contracts;

use App\Modules\Inventory\DTO\LowStockView;
use App\Modules\Inventory\DTO\StockBalanceView;
use App\Modules\Inventory\DTO\StockMovementView;

interface InventoryQueryContract
{
    public function balanceFor(int $productId): ?StockBalanceView;

    /**
     * @param  array<int, int>  $productIds  kosong = semua
     * @return array<int, StockBalanceView>  keyed by product id
     */
    public function balances(array $productIds = []): array;

    /**
     * @return array<int, StockMovementView>
     */
    public function movementsFor(
        int $productId,
        ?string $from = null,
        ?string $to = null,
    ): array;

    /**
     * @return array<int, StockMovementView>
     */
    public function recentMovements(int $limit = 5): array;

    /**
     * @return array<int, LowStockView>
     */
    public function lowStock(int $limit = 20): array;

    public function countLowStock(): int;

    public function hasMovements(int $productId): bool;
}
```

**Catatan implementasi:** `movementsFor()` mengambil `referenceNumber` dengan lookup internal ke `StockReceipt`/`StockIssue` (masih dalam modul Inventory). Jangan mengekspos model; cukup string nomor. `lowStock()` dihitung di `InventoryQueryService` dengan membandingkan `stock_balances.qty` terhadap `minStock` dari `ProductCatalogContract` (Inventory boleh bergantung pada Product). Reporting hanya memakai hasilnya.

## 8. Binding Contract

Setiap provider modul mendaftarkan binding di `register()`:

```php
// ProductServiceProvider
$this->app->bind(ProductCatalogContract::class, ProductCatalogService::class);

// SupplierServiceProvider
$this->app->bind(SupplierDirectoryContract::class, SupplierCatalogService::class);

// AuthServiceProvider
$this->app->bind(UserDirectoryContract::class, UserDirectoryService::class);

// InventoryServiceProvider
$this->app->bind(InventoryQueryContract::class, InventoryQueryService::class);
```

Gunakan `bind` (bukan `singleton`) karena service stateless; jika ada cache internal, gunakan `singleton` dan dokumentasikan.

## 9. Contoh Pemakaian

### 9.1 Inventory memakai contract Product + Supplier + User

```php
<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Auth\Contracts\UserDirectoryContract;
use App\Modules\Inventory\DTO\StockReceiptData;
use App\Modules\Inventory\Models\StockReceipt;
use App\Modules\Product\Contracts\ProductCatalogContract;
use App\Modules\Product\Exceptions\ProductUnavailableException;
use App\Modules\Supplier\Contracts\SupplierDirectoryContract;
use App\Modules\Supplier\Exceptions\SupplierUnavailableException;
use Illuminate\Support\Facades\DB;

final class ReceiveStockService
{
    public function __construct(
        private readonly ProductCatalogContract $products,
        private readonly SupplierDirectoryContract $suppliers,
        private readonly UserDirectoryContract $users,
    ) {}

    public function handle(StockReceiptData $data, int $actorId): StockReceipt
    {
        $supplier = $this->suppliers->find($data->supplierId);
        if ($supplier === null) {
            throw new SupplierUnavailableException($data->supplierId);
        }

        $actor = $this->users->find($actorId);
        if ($actor === null) {
            throw new \RuntimeException('Aktor tidak ditemukan.');
        }

        $snapshots = $this->products->findMany($data->productIds());

        foreach ($data->items as $item) {
            if (! isset($snapshots[(int) $item['product_id']])) {
                throw new ProductUnavailableException((int) $item['product_id']);
            }
        }

        return DB::transaction(function () use ($data, $actorId): StockReceipt {
            // ... buat receipt, item, saldo, movement, nomor dokumen
        });
    }
}
```

### 9.2 Reporting memakai beberapa contract

```php
final class DashboardService
{
    public function __construct(
        private readonly ProductCatalogContract $products,
        private readonly SupplierDirectoryContract $suppliers,
        private readonly InventoryQueryContract $inventory,
    ) {}

    public function summary(): array
    {
        $lowStock = $this->inventory->lowStock();
        $names = $this->products->findMany(array_map(fn ($row) => $row->productId, $lowStock));

        return [
            'product_count' => $this->products->count(),
            'supplier_count' => $this->suppliers->count(),
            'low_stock_count' => count($lowStock),
            'low_stock' => array_map(fn ($row) => [
                'product' => $names[$row->productId]->name ?? 'Produk tidak ditemukan',
                'qty' => $row->qty,
                'min_stock' => $row->minStock,
            ], $lowStock),
        ];
    }
}
```

### 9.3 Controller memakai contract untuk pilihan form

```php
public function create(ProductCatalogContract $products, SupplierDirectoryContract $suppliers): View
{
    return view('inventory::receipts.create', [
        'products' => $products->search(limit: 100),
        'suppliers' => $suppliers->search(limit: 100),
    ]);
}
```

> Jangan mengirim model ke view lintas modul; kirim DTO/array. View hanya membaca properti DTO.

## 10. Event Katalog

Semua event:

- Berada di `app/Modules/<Modul>/Events/`.
- `final readonly class` dengan properti publik.
- Implementasi `Illuminate\Contracts\Events\ShouldDispatchAfterCommit`.
- Tidak membawa model.
- Memiliki nama logis untuk activity log (`product.created`, dst.).

### 10.1 Daftar event

| Event | Modul | Payload minimal | Listener |
|---|---|---|---|
| `UserRegistered` | Auth | `userId`, `name`, `email`, `roles` | Reporting |
| `ProductCreated` | Product | `productId`, `sku`, `name`, `unitId`, `minStock` | Inventory (buat saldo), Reporting |
| `ProductUpdated` | Product | `productId`, `sku`, `name`, `unitId`, `minStock` | Reporting |
| `ProductDeactivated` | Product | `productId`, `sku`, `name` | Reporting |
| `SupplierCreated` | Supplier | `supplierId`, `code`, `name`, `paymentTermId` | Reporting |
| `SupplierUpdated` | Supplier | `supplierId`, `code`, `name`, `paymentTermId` | Reporting |
| `SupplierDeactivated` | Supplier | `supplierId`, `code`, `name` | Reporting |
| `StockReceived` | Inventory | `receiptId`, `number`, `supplierId`, `items`, `receivedAt`, `actorId` | Reporting |
| `StockIssued` | Inventory | `issueId`, `number`, `items`, `issuedAt`, `actorId` | Reporting |
| `LowStockDetected` | Inventory | `productId`, `productName`, `currentQty`, `minStock`, `detectedAt` | Reporting |

### 10.2 Contoh event

```php
<?php

namespace App\Modules\Product\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final readonly class ProductCreated implements ShouldDispatchAfterCommit
{
    public function __construct(
        public int $productId,
        public string $sku,
        public string $name,
        public int $unitId,
        public float $minStock,
    ) {}
}
```

```php
<?php

namespace App\Modules\Inventory\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final readonly class StockIssued implements ShouldDispatchAfterCommit
{
    /**
     * @param  array<int, array{product_id: int, qty: float}>  $items
     */
    public function __construct(
        public int $issueId,
        public string $number,
        public array $items,
        public string $issuedAt,
        public int $actorId,
    ) {}
}
```

### 10.3 Registrasi listener

Di provider modul:

```php
use Illuminate\Support\Facades\Event;

public function boot(): void
{
    // ...
    Event::listen(ProductCreated::class, CreateStockBalance::class);
}
```

```php
// ReportingServiceProvider
Event::listen(UserRegistered::class, RecordActivityLog::class);
Event::listen(ProductCreated::class, RecordActivityLog::class);
// ... daftar lengkap
```

Atau buat `EventServiceProvider` per modul dan daftarkan di `bootstrap/providers.php`; pilih satu pola dan konsisten.

### 10.4 Dispatch

```php
DB::transaction(function () use ($product) {
    // ... simpan
});

ProductCreated::dispatch(
    productId: $product->id,
    sku: $product->sku,
    name: $product->name,
    unitId: $product->unit_id,
    minStock: (float) $product->min_stock,
);
```

Karena implementasi `ShouldDispatchAfterCommit`, event baru dikirim setelah transaksi commit. Jangan memanggil `event()` di dalam transaksi tanpa memahami mekanisme ini.

## 11. Listener Reporting

`RecordActivityLog` memetakan event ke baris `activity_logs`. Contoh:

```php
final class RecordActivityLog
{
    public function handle(object $event): void
    {
        [$name, $description, $subjectType, $subjectId, $payload] = match (true) {
            $event instanceof ProductCreated => [
                'product.created',
                "Menambahkan barang {$event->name} ({$event->sku}).",
                'product', // string logis, bukan FQCN model lintas modul
                $event->productId,
                ['sku' => $event->sku, 'min_stock' => $event->minStock],
            ],
            // ... event lain
            default => [null, null, null, null, []],
        };

        if ($name === null) {
            return;
        }

        ActivityLog::create([
            'actor_id' => auth()->id(),
            'actor_name' => auth()->user()?->name,
            'event' => $name,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
            'payload' => $payload,
        ]);
    }
}
```

**Catatan:** Untuk event yang terjadi di CLI/seeder, `auth()->id()` null; gunakan `actorId` dari event jika tersedia. Jangan pernah menyimpan password/token di payload. `subject_type` memakai **string logis** (`product`, `supplier`, `stock_receipt`, `stock_issue`, `user`) agar tidak menyimpan FQCN model lintas modul; `subject_id` menyimpan ID-nya.

## 12. Test Contract

### 12.1 Binding test

```php
public function test_product_contract_is_bound(): void
{
    $this->assertInstanceOf(
        ProductCatalogService::class,
        app(ProductCatalogContract::class)
    );
}
```

### 12.2 Contract behavior test

```php
public function test_product_contract_excludes_soft_deleted_products(): void
{
    $product = Product::factory()->create();
    $product->delete();

    $this->assertNull(app(ProductCatalogContract::class)->find($product->id));
}
```

### 12.3 Event test

```php
Event::fake([StockIssued::class]);

$this->actingAs($staff)->post(route('inventory.issues.store'), $payload);

Event::assertDispatched(StockIssued::class, fn ($event) => $event->number !== '');
```

Jika memakai `Event::fake`, listener tidak berjalan; pisahkan test untuk listener Reporting.

### 12.4 Fake contract di test modul lain

```php
$this->mock(ProductCatalogContract::class, function ($mock) use ($snapshot) {
    $mock->shouldReceive('find')->with(1)->andReturn($snapshot);
    $mock->shouldReceive('findMany')->andReturn([1 => $snapshot]);
});
```

Gunakan fake contract untuk menguji Inventory tanpa membuat data Product; ini juga membuktikan batas modul.

## 13. Aturan Validasi Lintas Modul

**Jangan** memakai aturan `exists:products,id` atau `exists:suppliers,id` di Form Request karena itu query langsung ke tabel modul lain. Validasi keberadaan/keaktifan dilakukan di service lewat contract:

```php
$product = $this->products->find($productId);
if ($product === null) {
    throw new ProductUnavailableException($productId);
}
```

Form Request cukup memvalidasi tipe, rentang, `distinct`, dan format. Ini menjaga batas modul sekaligus menghasilkan pesan error yang jelas.

## 14. Anti-Pattern

| Anti-pattern | Mengapa salah | Perbaikan |
|---|---|---|
| `use App\Modules\Product\Models\Product` di Inventory | akses model lintas modul | pakai `ProductCatalogContract` |
| Contract mengembalikan `Product` | membocorkan Eloquent/relasi/tabel | kembalikan `ProductSnapshot` |
| Event membawa `Product $product` | serialisasi model, data bisa berubah | bawa `productId` + primitif |
| Listener memanggil service yang menulis ke modul lain | efek samping tersembunyi, siklus | listener hanya log/notifikasi; tulis di modul pemilik |
| `DB::table('products')` di Reporting | query langsung lintas modul | pakai contract |
| Dispatch event di dalam transaksi tanpa after-commit | listener membaca data belum commit | implementasi `ShouldDispatchAfterCommit` |
| Contract mengembalikan paginator | UI/HTTP bocor ke domain | kembalikan array DTO; pagination di controller modul pemilik |
| Exception domain melempar HTTP response | domain tidak portabel | lempar exception; render di handler/exception |

## 15. Checklist Contract/Event

- [ ] Interface berada di `Contracts/`, implementasi di `Services/`.
- [ ] Binding terdaftar di provider modul.
- [ ] Signature hanya primitif/DTO/array/`null`/`bool`.
- [ ] Tersedia method historis (`findIncludingDeactivated`) untuk entitas soft-deleted.
- [ ] Tidak ada model, paginator, atau query builder yang bocor.
- [ ] Nama method jelas dan konsisten.
- [ ] Event membawa ID + primitif, bukan model.
- [ ] Event implementasi `ShouldDispatchAfterCommit`.
- [ ] Listener terdaftar eksplisit.
- [ ] Listener Reporting tidak menggagalkan operasi utama.
- [ ] Test binding + behavior + event ada.
- [ ] Dokumentasi ini diperbarui jika signature berubah.
