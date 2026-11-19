<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Controllers;

use App\Modules\Product\Http\Requests\ProductFilterRequest;
use App\Modules\Product\Http\Requests\StoreProductRequest;
use App\Modules\Product\Http\Requests\UpdateProductRequest;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Unit;
use App\Modules\Product\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(ProductFilterRequest $request): View
    {
        $this->authorize('viewAny', Product::class);

        $filters = $request->validated();

        $products = Product::query()
            ->with(['unit:id,name,code', 'categories:id,name'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($filters['category_id'] ?? null, function ($query, string|int $categoryId): void {
                $query->whereHas('categories', fn ($query) => $query->whereKey($categoryId));
            })
            ->when($filters['unit_id'] ?? null, function ($query, string|int $unitId): void {
                $query->where('unit_id', $unitId);
            })
            ->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();

        return view('product::products.index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'units' => Unit::query()->orderBy('name')->get(['id', 'name', 'code']),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('product::products.create', [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'units' => Unit::query()->orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->products->create($request->validated());

        return redirect()
            ->route('products.show', $product)
            ->with('status', 'Barang berhasil ditambahkan.');
    }

    public function show(Product $product): View
    {
        $this->authorize('view', $product);

        $product->load('unit', 'categories');

        return view('product::products.show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $product->load('categories');

        return view('product::products.edit', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'units' => Unit::query()->orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $this->products->update($product, $request->validated());

        return redirect()
            ->route('products.show', $product)
            ->with('status', 'Barang berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->products->deactivate($product);

        return redirect()
            ->route('products.index')
            ->with('status', 'Barang berhasil dinonaktifkan.');
    }
}
