# RBAC — Role, Izin, Middleware, Policy, dan Token

Dokumen ini mengatur autentikasi dan otorisasi. Target: **Admin** dan **Staff Gudang** memiliki akses yang jelas berbeda, semua route terlindungi, dan tidak ada jalur eskalasi hak akses.

## 1. Role

| Role (`roles.name`) | Label | Deskripsi |
|---|---|---|
| `admin` | Administrator | Akses penuh: master data, transaksi stok, activity log |
| `staff_gudang` | Staff Gudang | Melihat master data, melakukan transaksi stok, melihat dashboard/saldo/kartu stok |

Role disimpan di tabel `roles`; user dapat memiliki lebih dari satu role melalui pivot `role_user` (Many-to-Many). Di MVP, setiap user diberi satu role, tetapi struktur mendukung banyak role.

**Register publik selalu menghasilkan `staff_gudang`.** Admin hanya dibuat lewat seeder (atau perubahan manual di luar UI). Input `role` dari form register diabaikan/ditolak.

## 2. Matriks Izin

Legenda: ✅ boleh, ❌ tidak boleh, 👁 hanya lihat.

| Resource / Aksi | Admin | Staff Gudang | Catatan |
|---|---|---|---|
| Dashboard | ✅ | ✅ | Ringkasan stok & aktivitas terbatas |
| Produk — lihat daftar/detail | ✅ | ✅ | Semua user login |
| Produk — tambah/edit/hapus | ✅ | ❌ | Soft delete; route `role:admin` |
| Kategori — lihat | ✅ | ✅ | — |
| Kategori — tambah/edit/hapus | ✅ | ❌ | Hapus ditolak jika dipakai |
| Satuan — lihat | ✅ | ✅ | — |
| Satuan — tambah/edit/hapus | ✅ | ❌ | Hapus ditolak jika dipakai |
| Pemasok — lihat daftar/detail | ✅ | ✅ | — |
| Pemasok — tambah/edit/hapus | ✅ | ❌ | Soft delete |
| Termin — lihat | ✅ | ✅ | — |
| Termin — tambah/edit/hapus | ✅ | ❌ | Hapus ditolak jika dipakai |
| Stok masuk — lihat | ✅ | ✅ | — |
| Stok masuk — buat | ✅ | ✅ | Form multi-item; transaksi (web & API) |
| Stok keluar — lihat | ✅ | ✅ | — |
| Stok keluar — buat | ✅ | ✅ | Validasi stok (web & API) |
| Saldo stok | ✅ | ✅ | — |
| Kartu stok | ✅ | ✅ | Filter produk/tanggal |
| Activity log | ✅ | ❌ | Route `role:admin` |
| Edit/hapus dokumen stok | ❌ | ❌ | Dokumen immutable, tidak ada route |
| CRUD user/role | ❌ | ❌ | Tidak ada UI; hanya seeder |
| API login/me/logout | ✅ | ✅ | Token Sanctum |
| API inventory — baca saldo/movement | ✅ | ✅ | `auth:sanctum` |
| API inventory — transaksi stok | ✅ | ✅ | `auth:sanctum`, `role:admin,staff_gudang` |
| API read produk/pemasok (P1) | ✅ | ✅ | `auth:sanctum` |

**Prinsip:** hak tulis master data hanya admin; hak transaksi stok untuk kedua role; activity log hanya admin; tidak ada edit/hapus dokumen stok untuk siapa pun.

## 3. Peta Perlindungan Route

| Route | Middleware | Policy (defense in depth) |
|---|---|---|
| `login`, `login.store`, `register`, `register.store` | `guest`, `throttle:6,1` | — |
| `logout` | `auth` | — |
| `dashboard.index` | `auth` | — |
| `products.index`, `products.show` | `auth` | `ProductPolicy@viewAny/view` |
| `products.create/store/edit/update/destroy` | `auth`, `role:admin` | `ProductPolicy@create/update/delete` |
| `categories.*`, `units.*` | `auth` (baca), `auth`, `role:admin` (tulis) | route middleware |
| `suppliers.index/show` | `auth` | `SupplierPolicy@viewAny/view` |
| `suppliers.create/store/edit/update/destroy` | `auth`, `role:admin` | `SupplierPolicy@create/update/delete` |
| `payment-terms.*` | `auth` (baca), `auth`, `role:admin` (tulis) | route middleware |
| `inventory.balances.index`, `inventory.stock-card.index` | `auth` | — |
| `inventory.receipts.index/show`, `inventory.issues.index/show` | `auth` | `StockReceiptPolicy@viewAny/view`, `StockIssuePolicy@viewAny/view` |
| `inventory.receipts.create/store`, `inventory.issues.create/store` | `auth`, `role:admin,staff_gudang` | `StockReceiptPolicy@create`, `StockIssuePolicy@create` |
| `api.inventory.receipts.store`, `api.inventory.issues.store` | `auth:sanctum`, `role:admin,staff_gudang` | — (service memvalidasi) |
| `api.inventory.balances`, `api.inventory.movements` | `auth:sanctum` | — |
| `activity.index` | `auth`, `role:admin` | `ActivityLogPolicy@viewAny` |
| `api.auth.login` | `throttle:6,1` | — |
| `api.auth.logout`, `api.me` | `auth:sanctum` | — |
| API read (P1) | `auth:sanctum` | — |

**Urutan middleware:** `auth` selalu sebelum `role`. Middleware `role` tidak menangani guest; guest akan diarahkan ke login oleh `auth`.

## 4. Implementasi

### 4.1 Migration dan model

```php
// roles
Schema::create('roles', function (Blueprint $table) {
    $table->id();
    $table->string('name', 30)->unique();
    $table->string('label', 50);
    $table->timestamps();
});

// role_user
Schema::create('role_user', function (Blueprint $table) {
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('role_id')->constrained()->cascadeOnDelete();
    $table->timestamps();
    $table->primary(['user_id', 'role_id']);
});
```

```php
// User
public function roles(): BelongsToMany
{
    return $this->belongsToMany(Role::class)->withTimestamps();
}

public function hasRole(string $role): bool
{
    return $this->roles()->where('name', $role)->exists();
}

/**
 * @param  array<int, string>  $roles
 */
public function hasAnyRole(array $roles): bool
{
    return $this->roles()->whereIn('name', $roles)->exists();
}
```

```php
// Role
public function users(): BelongsToMany
{
    return $this->belongsToMany(User::class)->withTimestamps();
}
```

> Query `hasRole` di atas menyentuh tabel `roles`/`role_user` milik modul Auth; ini sah karena masih dalam modul Auth. Modul lain tidak boleh memanggilnya lewat model; gunakan `UserDirectoryContract` bila butuh data user.

### 4.2 Seeder role dan user

```php
// RoleSeeder
Role::create(['name' => 'admin', 'label' => 'Administrator']);
Role::create(['name' => 'staff_gudang', 'label' => 'Staff Gudang']);
```

```php
// UserSeeder
$admin = User::factory()->create([
    'name' => 'Admin Gudang',
    'email' => 'admin@warehouse.test',
    'password' => Hash::make('password'),
]);
$admin->roles()->attach(Role::where('name', 'admin')->first());

$staff = User::factory()->create([
    'name' => 'Staff Gudang',
    'email' => 'staff@warehouse.test',
    'password' => Hash::make('password'),
]);
$staff->roles()->attach(Role::where('name', 'staff_gudang')->first());
```

**Jangan** memakai password `password` di produksi; ini hanya akun demo lokal. Seeder sebaiknya idempotent (`firstOrCreate`/`updateOrCreate`) agar `db:seed` berulang tidak gagal karena unique constraint.

### 4.3 Middleware role

```php
<?php

namespace App\Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
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
}
```

Daftarkan alias di `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Modules\Auth\Http\Middleware\EnsureUserHasRole::class,
    ]);
})
```

Pemakaian route:

```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('products', ProductController::class)->except(['index', 'show']);
});

Route::middleware(['auth', 'role:admin,staff_gudang'])->group(function () {
    Route::get('inventory/receipts/create', [StockReceiptController::class, 'create'])
        ->name('inventory.receipts.create');
    Route::post('inventory/receipts', [StockReceiptController::class, 'store'])
        ->name('inventory.receipts.store');
});
```

### 4.4 Directive Blade

Daftarkan di `AuthServiceProvider::boot()`:

```php
use Illuminate\Support\Facades\Blade;

Blade::if('role', function (string ...$roles) {
    return auth()->user()?->hasAnyRole($roles) ?? false;
});
```

Pemakaian:

```blade
@role('admin')
    <a href="{{ route('products.create') }}" class="btn btn-primary">Tambah Barang</a>
@endrole

@role('admin')
    <a href="{{ route('activity.index') }}">Activity Log</a>
@endrole
```

**Direktif Blade hanya menyembunyikan UI, bukan pengaman.** Route dan policy tetap wajib.

### 4.5 Policy

Daftarkan policy secara eksplisit di provider modul karena namespace model bukan `App\Models`:

```php
// ProductServiceProvider::boot()
Gate::policy(Product::class, ProductPolicy::class);

// SupplierServiceProvider::boot()
Gate::policy(Supplier::class, SupplierPolicy::class);

// InventoryServiceProvider::boot()
Gate::policy(StockReceipt::class, StockReceiptPolicy::class);
Gate::policy(StockIssue::class, StockIssuePolicy::class);

// ReportingServiceProvider::boot()
Gate::policy(ActivityLog::class, ActivityLogPolicy::class);
```

Contoh `ProductPolicy`:

```php
<?php

namespace App\Modules\Product\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use App\Modules\Product\Models\Product;

class ProductPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return true;
    }

    public function view(Authenticatable $user, Product $product): bool
    {
        return true;
    }

    public function create(Authenticatable $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(Authenticatable $user, Product $product): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(Authenticatable $user, Product $product): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(Authenticatable $user, Product $product): bool
    {
        return false; // restore tidak ada di MVP
    }

    public function forceDelete(Authenticatable $user, Product $product): bool
    {
        return false;
    }
}
```

> **Catatan batas modul:** policy tidak mengimpor model `User` milik Auth. Policy menerima principal framework (`Authenticatable`) dan memanggil `hasRole()`/`hasAnyRole()` yang dimiliki user. Ini bukan akses tabel lintas modul; route middleware tetap menjadi pengaman utama.

`SupplierPolicy` identik untuk supplier. `StockReceiptPolicy`/`StockIssuePolicy`:

```php
public function viewAny(Authenticatable $user): bool { return true; }
public function view(Authenticatable $user, StockReceipt $receipt): bool { return true; }
public function create(Authenticatable $user): bool { return true; } // kedua role
public function update(Authenticatable $user, StockReceipt $receipt): bool { return false; }
public function delete(Authenticatable $user, StockReceipt $receipt): bool { return false; }
```

`ActivityLogPolicy`:

```php
public function viewAny(Authenticatable $user): bool
{
    return $user->hasRole('admin');
}

public function view(Authenticatable $user, ActivityLog $log): bool
{
    return $user->hasRole('admin');
}
```

Pemakaian di controller:

```php
public function create(): View
{
    $this->authorize('create', Product::class);

    return view('product::products.create');
}
```

Dengan route `role:admin`, policy menjadi lapisan kedua. Jika policy dan middleware berbeda pendapat, gunakan aturan paling ketat dan perbaiki.

### 4.6 Sanctum: login API dan ability token

```php
public function login(LoginRequest $request): JsonResponse
{
    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => 'Email atau password salah.',
        ]);
    }

    $token = $user->createToken(
        name: 'api-'.$user->id,
        abilities: $user->roles()->pluck('name')->all(),
    );

    return response()->json([
        'token' => $token->plainTextToken,
        'token_type' => 'Bearer',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles()->pluck('name'),
        ],
    ]);
}
```

- Ability token diisi nama role (`admin`, `staff_gudang`) sebagai metadata/pertahanan tambahan.
- Otorisasi API tetap memakai `role` middleware yang membaca role di database; token ability saja tidak cukup jika role user berubah.
- Logout API: `$request->user()->currentAccessToken()->delete();`
- Jangan pernah mengembalikan token di response selain login; jangan log token.

### 4.7 Login web

```php
public function login(LoginRequest $request): RedirectResponse
{
    $credentials = $request->only('email', 'password');

    if (! Auth::attempt($credentials, $request->boolean('remember'))) {
        throw ValidationException::withMessages([
            'email' => 'Email atau password salah.',
        ]);
    }

    $request->session()->regenerate();

    return redirect()->intended(route('dashboard.index'));
}
```

Logout web:

```php
Auth::logout();
$request->session()->invalidate();
$request->session()->regenerateToken();

return redirect()->route('login');
```

## 5. Perilaku Error Autentikasi/Otorisasi

| Kondisi | Web | API (`Accept: application/json`) |
|---|---|---|
| Belum login | redirect `/login` (302) | 401 `{"message":"Unauthenticated."}` |
| Role tidak cocok | 403 halaman error | 403 `{"message":"Forbidden."}` |
| Token invalid/expired | — | 401 |
| Validasi gagal | redirect back + `$errors` | 422 dengan `errors` |
| CSRF token salah | 419 halaman | — |
| Rate limit login | 429 | 429 |

Sediakan view `resources/views/errors/403.blade.php`, `404.blade.php`, `419.blade.php`, `500.blade.php` dengan pesan ramah dan tombol kembali.

## 6. Akun Demo

| Email | Password | Role | Kegunaan |
|---|---|---|---|
| `admin@warehouse.test` | `password` | `admin` | Demo CRUD, activity log |
| `staff@warehouse.test` | `password` | `staff_gudang` | Demo transaksi stok & 403 |
| `staff2@warehouse.test` | `password` | `staff_gudang` | Uji multi-user/aktivitas |

Jangan gunakan akun ini di lingkungan publik. Untuk demo, pastikan `APP_ENV=local` dan `APP_DEBUG=false` saat merekam video jika memungkinkan.

## 7. Keamanan Tambahan

| Kontrol | Implementasi |
|---|---|
| Password hashing | `Hash::make`, `Hash::check`; algorithm default Laravel |
| Password policy | Minimal 8 karakter, `confirmed`; gunakan `Password::min(8)` |
| Rate limit login | `throttle:6,1` pada route login web/API; pesan 429 |
| Session fixation | `session()->regenerate()` setelah login |
| Session invalidation | `invalidate()` + `regenerateToken()` saat logout |
| Remember me | Opsional; token remember disimpan aman oleh Laravel |
| CSRF | `@csrf` di form; API token tidak memakai CSRF |
| Mass assignment | `$fillable` eksplisit; role tidak mass-assignable dari register |
| Escalation | Register memaksa `staff_gudang`; tidak ada route assign role |
| Policy | Cek per model untuk aksi sensitif |
| Token | Disimpan hashed oleh Sanctum; revoke saat logout |
| Log | Jangan catat password/token; actor name saja |
| Debug | `APP_DEBUG=false` di demo publik/produksi |
| HTTPS | Wajib di produksi; cookie `secure` + `http_only` + `same_site=lax` |
| IDOR | Route model binding + policy; jangan menerima `user_id` dari form untuk aksi sensitif |

## 8. Testing RBAC

Matriks minimum:

| ID | Skenario | Ekspektasi |
|---|---|---|
| RBAC-01 | Guest GET `/products` | redirect `/login` |
| RBAC-02 | Guest GET `/api/v1/me` | 401 JSON |
| RBAC-03 | Staff GET `/products` | 200 |
| RBAC-04 | Staff GET `/products/create` | 403 |
| RBAC-05 | Staff POST `/products` | 403; data tidak tersimpan |
| RBAC-06 | Staff DELETE `/products/{id}` | 403 |
| RBAC-07 | Admin POST `/products` | 302 sukses; data tersimpan |
| RBAC-08 | Staff POST `/inventory/receipts` | 302 sukses; saldo bertambah |
| RBAC-09 | Staff POST `/inventory/issues` | 302 sukses (jika stok cukup) |
| RBAC-10 | Staff GET `/activity` | 403 |
| RBAC-11 | Admin GET `/activity` | 200 |
| RBAC-12 | API login valid | 200 + token |
| RBAC-13 | API login salah | 422 |
| RBAC-14 | API `me` dengan token | 200 |
| RBAC-15 | API `me` tanpa token | 401 |
| RBAC-16 | Token staff ke route admin (jika ada) | 403 |
| RBAC-17 | Register publik | role `staff_gudang`, bukan admin |
| RBAC-18 | Logout API | token dicabut; request berikutnya 401 |

Contoh test:

```php
public function test_staff_cannot_create_product(): void
{
    $staff = User::factory()->staff()->create();

    $response = $this->actingAs($staff)->post(route('products.store'), [
        'sku' => 'PRD-9999',
        'name' => 'Produk Uji',
        'unit_id' => Unit::factory()->create()->id,
        'min_stock' => 1,
        'categories' => [],
    ]);

    $response->assertForbidden();
    $this->assertDatabaseMissing('products', ['sku' => 'PRD-9999']);
}
```

```php
public function test_api_requires_token(): void
{
    $this->getJson('/api/v1/me')->assertUnauthorized();
}
```

## 9. Checklist RBAC

- [x] Role `admin` dan `staff_gudang` ada di seeder.
- [x] Setiap user punya minimal satu role. *(Dijamin untuk register publik dan seeder; tidak ada UI pembuatan user lain.)*
- [x] Register publik tidak bisa menjadi admin.
- [x] Semua route aplikasi memakai `auth`, kecuali login/register. *(Route modul M2+ wajib mengikuti aturan ini.)*
- [ ] Route tulis master data memakai `role:admin`.
- [ ] Route transaksi stok memakai `role:admin,staff_gudang`.
- [ ] Activity log hanya admin.
- [ ] Policy terdaftar dan dipanggil di controller untuk aksi sensitif.
- [ ] Menu/tombol admin disembunyikan dengan `@role`.
- [x] 401/403 diuji untuk web dan API.
- [x] Token Sanctum dibuat saat login API, dicabut saat logout.
- [x] Tidak ada token/password di log atau response selain login.

> **Status M1 (2026-11-19):** checklist yang bertanda `[x]` sudah dibuktikan lewat `tests/Feature/Auth/`; sisanya menunggu modul Product/Inventory/Reporting.
- [ ] Session diregenerasi saat login dan diinvalidasi saat logout.
- [ ] Rate limit login aktif.
- [ ] Halaman error 403/404/419/500 ada.
