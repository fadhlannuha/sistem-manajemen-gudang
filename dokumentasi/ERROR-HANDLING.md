# Validasi dan Error Handling

Dokumen ini mengatur semua input form, aturan validasi, custom exception, format pesan error untuk web/API, dan bukti pengujian. Target: **setiap input memakai Form Request**, error mudah dipahami, dan kegagalan transaksi tidak meninggalkan data parsial.

## 1. Prinsip

1. **Semua input** dari user (termasuk filter dan login) divalidasi via Form Request — bukan `$request->validate()` di controller.
2. Validasi format/tipe di Form Request; validasi **aturan bisnis** (stok cukup, produk aktif, referensi valid) di service/domain.
3. Jangan memakai aturan `exists` lintas modul (`exists:products,id`, `exists:suppliers,id`) di Inventory. Gunakan contract.
4. Error web: redirect back + `$errors` + old input + flash message.
5. Error API: JSON dengan `message`, `code`, dan `errors`/`meta`; status HTTP tepat.
6. Jangan pernah menampilkan stack trace, SQL, atau path file ke user.
7. Transaksi stok yang gagal harus **rollback total**; custom exception dilempar di dalam `DB::transaction()`.

## 2. Daftar Form Request

| Modul | Form Request | Dipakai di | Aturan inti |
|---|---|---|---|
| Auth | `LoginRequest` | `POST /login`, `POST /api/v1/auth/login` | email wajib/format; password wajib |
| Auth | `RegisterRequest` | `POST /register` | name min 3; email unik; password min 8 + confirmed |
| Product | `StoreProductRequest` | `POST /products` | SKU unik; name; unit ada; min_stock >= 0; categories array |
| Product | `UpdateProductRequest` | `PUT/PATCH /products/{product}` | sama, unique ignore ID |
| Product | `ProductFilterRequest` | `GET /products` | search; category_id; unit_id; per_page |
| Product | `StoreCategoryRequest` | `POST /categories` | name unik; description opsional |
| Product | `UpdateCategoryRequest` | `PUT/PATCH /categories/{category}` | unique ignore ID |
| Product | `StoreUnitRequest` | `POST /units` | name unik; code unik max 10 |
| Product | `UpdateUnitRequest` | `PUT/PATCH /units/{unit}` | unique ignore ID |
| Supplier | `StoreSupplierRequest` | `POST /suppliers` | code unik; name; payment_term ada; email/phone valid |
| Supplier | `UpdateSupplierRequest` | `PUT/PATCH /suppliers/{supplier}` | unique ignore ID |
| Supplier | `SupplierFilterRequest` | `GET /suppliers` | search; payment_term_id; per_page |
| Supplier | `StorePaymentTermRequest` | `POST /payment-terms` | name unik; days 0–365 |
| Supplier | `UpdatePaymentTermRequest` | `PUT/PATCH /payment-terms/{payment_term}` | unique ignore ID |
| Inventory | `StoreStockReceiptRequest` | `POST /inventory/receipts` | supplier_id; tanggal; items min 1; qty > 0; unit_cost >= 0 |
| Inventory | `StoreStockIssueRequest` | `POST /inventory/issues` | tanggal; items min 1; qty > 0; produk distinct |
| Inventory | `StockDocumentFilterRequest` | `GET /inventory/receipts`, `GET /inventory/issues` | from/to; supplier_id; search |
| Inventory | `StockBalanceFilterRequest` | `GET /inventory/balances` | search; low_stock |
| Inventory | `StockCardFilterRequest` | `GET /inventory/stock-card` | product_id; from/to valid |
| Reporting | `ActivityFilterRequest` | `GET /activity` | event; from/to; actor opsional |

> `StoreProductRequest` boleh memakai `exists:units,id` dan `exists:categories,id` karena units/categories **milik modul Product sendiri**. `StoreSupplierRequest` boleh memakai `exists:payment_terms,id` karena masih modul Supplier. Inventory **tidak boleh** memakai `exists:products`/`exists:suppliers`. Filter GET juga memakai Form Request, bukan validasi manual di controller.

## 3. Aturan Validasi Terperinci

### 3.1 Auth

**LoginRequest**

```php
public function rules(): array
{
    return [
        'email' => ['required', 'string', 'email', 'max:150'],
        'password' => ['required', 'string'],
        'remember' => ['sometimes', 'boolean'],
    ];
}
```

**RegisterRequest**

```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'min:3', 'max:100'],
        'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
        'password' => ['required', 'string', 'confirmed', Password::min(8)],
    ];
}
```

- Jangan menerima `role` dari request.
- `authorize()` mengembalikan `true` (guest).
- Pesan: "Email sudah terdaftar.", "Password minimal 8 karakter.", "Konfirmasi password tidak cocok."

### 3.2 Product

**StoreProductRequest**

```php
public function rules(): array
{
    return [
        'sku' => ['required', 'string', 'max:30', 'unique:products,sku'],
        'name' => ['required', 'string', 'max:150'],
        'description' => ['nullable', 'string', 'max:2000'],
        'unit_id' => ['required', 'integer', 'exists:units,id'],
        'min_stock' => ['required', 'numeric', 'min:0', 'max:999999999'],
        'categories' => ['nullable', 'array'],
        'categories.*' => ['integer', 'distinct', 'exists:categories,id'],
    ];
}
```

**UpdateProductRequest**

```php
'sku' => ['required', 'string', 'max:30', Rule::unique('products', 'sku')->ignore($this->route('product'))],
```

- Normalisasi SKU uppercase di `prepareForValidation()`.
- `authorize()`: `return $this->user()?->hasRole('admin') ?? false;` (defense in depth; route sudah `role:admin`).
- `min_stock` boleh 0 (barang tanpa ambang).
- Kategori boleh kosong (produk tanpa kategori) — tetapi untuk demo, seeder mengisi.

**Category/Unit**

- Category `name` unik secara case-insensitive (dicek dengan closure `LOWER(name)` karena `Rule::unique` biasa bersifat case-sensitive di SQLite); slug dibuat otomatis dari name, bukan input.
- Unit `code` dinormalisasi uppercase di `prepareForValidation()`, unik; `name` unik secara case-insensitive dengan pola yang sama.
- `authorize()` admin.

**ProductFilterRequest** (index)

```php
'search' => ['nullable', 'string', 'max:100'],
'category_id' => ['nullable', 'integer', 'exists:categories,id'],
'unit_id' => ['nullable', 'integer', 'exists:units,id'],
'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
```

### 3.3 Supplier

**StoreSupplierRequest**

```php
public function rules(): array
{
    return [
        'code' => ['required', 'string', 'max:20', 'unique:suppliers,code'],
        'name' => ['required', 'string', 'max:150'],
        'contact_name' => ['nullable', 'string', 'max:100'],
        'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
        'email' => ['nullable', 'string', 'email', 'max:150'],
        'address' => ['nullable', 'string', 'max:1000'],
        'payment_term_id' => ['required', 'integer', 'exists:payment_terms,id'],
    ];
}
```

**SupplierFilterRequest** (index)

```php
'search' => ['nullable', 'string', 'max:100'],
'payment_term_id' => ['nullable', 'integer', 'exists:payment_terms,id'],
'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
```

**PaymentTerm**

```php
'name' => ['required', 'string', 'max:50', 'unique:payment_terms,name'],
'days' => ['required', 'integer', 'min:0', 'max:365'],
```

### 3.4 Inventory

**StoreStockReceiptRequest**

```php
public function rules(): array
{
    return [
        'supplier_id' => ['required', 'integer'],
        'received_at' => ['required', 'date', 'before_or_equal:today'],
        'note' => ['nullable', 'string', 'max:1000'],
        'items' => ['required', 'array', 'min:1', 'max:50'],
        'items.*.product_id' => ['required', 'integer', 'distinct'],
        'items.*.qty' => ['required', 'numeric', 'gt:0', 'max:999999999'],
        'items.*.unit_cost' => ['required', 'numeric', 'min:0', 'max:999999999'],
    ];
}

public function messages(): array
{
    return [
        'supplier_id.required' => 'Pemasok wajib dipilih.',
        'received_at.before_or_equal' => 'Tanggal penerimaan tidak boleh di masa depan.',
        'items.required' => 'Tambahkan minimal satu barang.',
        'items.*.product_id.distinct' => 'Barang tidak boleh dipilih dua kali.',
        'items.*.qty.gt' => 'Kuantitas harus lebih dari 0.',
        'items.*.unit_cost.min' => 'Harga satuan tidak boleh negatif.',
    ];
}
```

**StoreStockIssueRequest**

```php
public function rules(): array
{
    return [
        'issued_at' => ['required', 'date', 'before_or_equal:today'],
        'destination' => ['nullable', 'string', 'max:100'],
        'note' => ['nullable', 'string', 'max:1000'],
        'items' => ['required', 'array', 'min:1', 'max:50'],
        'items.*.product_id' => ['required', 'integer', 'distinct'],
        'items.*.qty' => ['required', 'numeric', 'gt:0', 'max:999999999'],
    ];
}
```

- **Tidak** memakai `exists:products,id`; service memvalidasi via `ProductCatalogContract`.
- **Tidak** memakai `exists:suppliers,id`; service memvalidasi via `SupplierDirectoryContract`.
- Produk soft-deleted dianggap tidak tersedia (contract `find` null).
- Stok cukup dicek di service, di dalam transaksi, bukan di Form Request.
- `authorize()`: `return $this->user() !== null;` (kedua role boleh transaksi).

**StockCardFilterRequest**

```php
public function rules(): array
{
    return [
        'product_id' => ['nullable', 'integer'],
        'from' => ['nullable', 'date'],
        'to' => ['nullable', 'date', 'after_or_equal:from'],
    ];
}
```

Filter bukan aksi tulis; `authorize()` true untuk user login. Jika `product_id` diisi, controller memvalidasi lewat contract dan menampilkan 404/empty state jika tidak ada.

**StockDocumentFilterRequest** (index penerimaan/pengeluaran)

```php
public function rules(): array
{
    return [
        'search' => ['nullable', 'string', 'max:50'],
        'supplier_id' => ['nullable', 'integer'],
        'from' => ['nullable', 'date'],
        'to' => ['nullable', 'date', 'after_or_equal:from'],
    ];
}
```

**StockBalanceFilterRequest**

```php
public function rules(): array
{
    return [
        'search' => ['nullable', 'string', 'max:100'],
        'low_stock' => ['nullable', 'boolean'],
    ];
}
```

**StockCardFilterRequest** (kartu stok)

```php
public function rules(): array
{
    return [
        'product_id' => ['nullable', 'integer', 'min:1'],
        'from' => ['nullable', 'date'],
        'to' => ['nullable', 'date', 'after_or_equal:from'],
    ];
}
```

- `product_id` **tidak** memakai `exists:products,id`; controller memvalidasi lewat `ProductCatalogContract::findIncludingDeactivated()` dan menampilkan error jika tidak ada.
- `from`/`to` hanya untuk filter tampilan; tidak mengubah data.

### 3.5 Reporting

**ActivityFilterRequest**

```php
'event' => ['nullable', 'string', 'max:80'],
'from' => ['nullable', 'date'],
'to' => ['nullable', 'date', 'after_or_equal:from'],
'actor' => ['nullable', 'string', 'max:100'],
```

## 4. Custom Exception

Minimal satu custom exception; implementasi memakai **tiga** untuk skenario domain yang berbeda.

| Exception | Modul | Kapan dilempar | HTTP (API) | Kode |
|---|---|---|---|---|
| `InsufficientStockException` | Inventory | Stok saldo < qty diminta saat issue | 409 Conflict | `INSUFFICIENT_STOCK` |
| `ProductUnavailableException` | Product | Produk tidak ditemukan/soft-deleted saat transaksi | 422 Unprocessable | `PRODUCT_UNAVAILABLE` |
| `SupplierUnavailableException` | Supplier | Pemasok tidak ditemukan/soft-deleted saat transaksi | 422 Unprocessable | `SUPPLIER_UNAVAILABLE` |

### 4.1 `InsufficientStockException`

```php
<?php

namespace App\Modules\Inventory\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly int $productId,
        public readonly string $productName,
        public readonly float $requested,
        public readonly float $available,
    ) {
        parent::__construct(sprintf(
            'Stok %s tidak cukup. Tersedia %s, diminta %s.',
            $productName,
            number_format($available, 3, ',', '.'),
            number_format($requested, 3, ',', '.'),
        ));
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'code' => 'INSUFFICIENT_STOCK',
                'meta' => [
                    'product_id' => $this->productId,
                    'available' => $this->available,
                    'requested' => $this->requested,
                ],
            ], 409);
        }

        return back()
            ->withInput()
            ->withErrors(['items' => $this->getMessage()])
            ->with('error', $this->getMessage());
    }
}
```

- Dilempar **di dalam** `DB::transaction()` agar rollback.
- Jangan menangkap exception ini di service; biarkan handler/render menampilkan.
- Log sebagai warning (bukan error) dengan konteks produk/qty; jangan log seluruh request.

### 4.2 `ProductUnavailableException`

```php
final class ProductUnavailableException extends RuntimeException
{
    public function __construct(public readonly int $productId)
    {
        parent::__construct("Barang dengan ID {$productId} tidak ditemukan atau sudah tidak aktif.");
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'code' => 'PRODUCT_UNAVAILABLE',
            ], 422);
        }

        return back()->withInput()->withErrors(['items' => $this->getMessage()])->with('error', $this->getMessage());
    }
}
```

### 4.3 `SupplierUnavailableException`

Sama polanya, kode `SUPPLIER_UNAVAILABLE`, status 422, field error `supplier_id`.

### 4.4 Exception untuk hapus master data yang dipakai

Kategori/satuan/termin yang masih dipakai **tidak** memakai custom exception baru; controller melempar `ValidationException::withMessages()`:

```php
if ($category->products()->withTrashed()->exists()) {
    throw ValidationException::withMessages([
        'delete' => 'Kategori masih digunakan oleh produk dan tidak dapat dihapus.',
    ]);
}
```

Hasil web: redirect back + error; API: 422 dengan `errors.delete`.

### 4.5 Registrasi exception di `bootstrap/app.php`

Untuk exception yang tidak punya method `render`, daftarkan global:

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->dontReport([
        \App\Modules\Inventory\Exceptions\InsufficientStockException::class,
        \App\Modules\Product\Exceptions\ProductUnavailableException::class,
        \App\Modules\Supplier\Exceptions\SupplierUnavailableException::class,
    ]);

    $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        return response()->view('errors.404', [], 404);
    });
})
```

- `dontReport` mencegah log noise untuk error bisnis yang wajar.
- Tetap catat `Log::warning` secara eksplisit di service dengan konteks aman jika diperlukan audit.
- Jangan `dontReport` untuk `QueryException`/error tak terduga.

## 5. Format Error

### 5.1 Web (Blade)

- **Validasi gagal:** redirect back, `$errors` berisi pesan per field, old input tetap.
- **Domain exception:** redirect back, `withErrors(['items' => ...])` + `session('error')`.
- **403/404/419/429/500:** halaman error khusus.

Komponen view:

```blade
{{-- resources/views/components/alert.blade.php --}}
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

```blade
{{-- resources/views/components/field-error.blade.php --}}
@props(['name'])
@error($name)
    <p class="text-danger small mt-1">{{ $message }}</p>
@enderror
```

Form:

```blade
<form method="POST" action="{{ route('products.store') }}">
    @csrf
    <label for="name">Nama Barang</label>
    <input id="name" name="name" value="{{ old('name', $product->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror">
    <x-field-error name="name" />
    <button type="submit">Simpan</button>
</form>
```

- Gunakan `old()` untuk semua field.
- Untuk select, gunakan `old('unit_id', $product->unit_id ?? '')` untuk memilih ulang.
- Jangan menampilkan pesan "SQLSTATE..." ke user.

### 5.2 API (JSON)

Validasi (422):

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "items.0.qty": ["Kuantitas harus lebih dari 0."]
  }
}
```

Stok kurang (409), berlaku untuk `POST /api/v1/inventory/issues`:

```json
{
  "message": "Stok Semen 50kg tidak cukup. Tersedia 5,000, diminta 10,000.",
  "code": "INSUFFICIENT_STOCK",
  "meta": {
    "product_id": 3,
    "available": 5,
    "requested": 10
  }
}
```

Tidak terautentikasi (401):

```json
{"message": "Unauthenticated."}
```

Terlarang (403):

```json
{"message": "Anda tidak memiliki akses ke halaman ini."}
```

Tidak ditemukan (404):

```json
{"message": "Data tidak ditemukan."}
```

Rate limit (429):

```json
{"message": "Terlalu banyak percobaan. Coba lagi dalam beberapa saat."}
```

## 6. Logging dan Monitoring

| Kejadian | Level | Data yang dicatat |
|---|---|---|
| Stok kurang | warning | product_id, available, requested, issue draft (tanpa data sensitif) |
| Produk/pemasok tidak tersedia | warning | ID yang diminta, aktor |
| Validasi gagal | info/debug (opsional) | route, jumlah error; **jangan** log password |
| QueryException/error tak terduga | error | exception + stack trace di log internal; user hanya pesan umum |
| Login gagal/rate limit | warning | email (opsional), IP; jangan password |
| Aksi sukses | info | activity log (bukan file log) |

Aturan:

- Jangan log token, password, atau isi session.
- Gunakan `Log::withContext(['user_id' => auth()->id()])` bila perlu.
- Activity log adalah audit bisnis; file log adalah debugging.

## 7. Transaksi dan Error

1. `DB::transaction()` otomatis rollback jika exception dilempar.
2. Jangan menangkap exception di dalam closure lalu melanjutkan.
3. Jika butuh fallback, tangkap **di luar** transaksi dan tampilkan error.
4. Jangan mengirim event di dalam transaksi; gunakan `ShouldDispatchAfterCommit`.
5. Jika listener Reporting gagal, jangan gagalkan operasi stok: bungkus `try/catch` + `Log::warning` di listener.
6. Unique constraint (nomor dokumen, SKU) harus dicegah Form Request; jika tetap terjadi, tangkap `QueryException` dan tampilkan pesan umum 500/409.

Contoh service yang benar:

```php
return DB::transaction(function () use ($data, $actorId, $snapshots) {
    // ... validasi stok dan tulis
    // throw InsufficientStockException jika gagal
    return $issue;
});
```

## 8. Halaman Error

Buat di `resources/views/errors/`:

| File | Status | Pesan |
|---|---|---|
| `403.blade.php` | 403 | "Anda tidak memiliki akses ke halaman ini." + tombol dashboard |
| `404.blade.php` | 404 | "Data yang Anda cari tidak ditemukan." + tombol kembali |
| `419.blade.php` | 419 | "Sesi Anda telah berakhir. Silakan muat ulang halaman." |
| `429.blade.php` | 429 | "Terlalu banyak percobaan. Coba lagi nanti." |
| `500.blade.php` | 500 | "Terjadi kesalahan pada server. Silakan coba lagi." |
| `503.blade.php` | 503 | "Layanan sedang tidak tersedia." (opsional) |

Semua halaman memakai layout sederhana, tanpa stack trace, dan menyediakan jalan kembali.

## 9. Matriks Pengujian Error

| ID | Skenario | Ekspektasi |
|---|---|---|
| ERR-01 | Login tanpa email | 302 back; error `email` |
| ERR-02 | Register email duplikat | 302 back; error `email` |
| ERR-03 | Product SKU duplikat | 302 back; error `sku`; data tidak tersimpan |
| ERR-04 | Product `unit_id` tidak ada | 302 back; error `unit_id` |
| ERR-05 | Product `min_stock` negatif | 302 back; error `min_stock` |
| ERR-06 | Supplier email tidak valid | 302 back; error `email` |
| ERR-07 | Stock receipt tanpa item | 302 back; error `items` |
| ERR-08 | Stock receipt qty 0 | 302 back; error `items.0.qty` |
| ERR-09 | Stock issue produk duplikat | 302 back; error `items.*.product_id` |
| ERR-10 | Stock issue stok kurang | redirect back; error `items`; tidak ada issue/movement; saldo tidak berubah |
| ERR-11 | Stock issue produk soft-deleted | redirect back; error `items`; tidak ada data |
| ERR-12 | API login salah | 422 JSON |
| ERR-13 | API `me` tanpa token | 401 JSON |
| ERR-14 | API stok kurang | 409 JSON dengan `code` |
| ERR-15 | Route admin oleh staff | 403 (web) / 403 JSON |
| ERR-16 | Delete kategori yang dipakai | redirect back; error `delete` |
| ERR-17 | Rate limit login | 429 |
| ERR-18 | CSRF token hilang | 419 |
| ERR-19 | Akses data tidak ada | 404 halaman |
| ERR-20 | Exception tak terduga | 500 halaman, log error, tanpa stack trace |

## 10. Checklist Validasi/Error

- [ ] Semua form memakai Form Request (tidak ada `$request->validate()` di controller).
- [ ] Semua field punya pesan Indonesia yang jelas.
- [ ] Validasi lintas modul memakai contract, bukan `exists`.
- [ ] Custom exception dilempar di dalam transaksi dan menghasilkan rollback.
- [ ] Web menampilkan `$errors`, old input, dan flash message.
- [ ] API mengembalikan status + `code` yang konsisten.
- [ ] 401/403/404/419/429/500 punya halaman/JSON yang ramah.
- [ ] Tidak ada stack trace/SQL/password/token di response atau log.
- [ ] Error stok menampilkan nama produk, tersedia, dan diminta.
- [ ] Test error dijalankan dan screenshot diambil untuk laporan.
