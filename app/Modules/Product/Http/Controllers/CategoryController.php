<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Controllers;

use App\Modules\Product\Http\Requests\StoreCategoryRequest;
use App\Modules\Product\Http\Requests\UpdateCategoryRequest;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('products')
            ->withCount(['products as all_products_count' => fn ($query) => $query->withTrashed()])
            ->orderBy('name')
            ->paginate(15);

        return view('product::categories.index', [
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        return view('product::categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['name']);

        Category::query()->create($data);

        return redirect()
            ->route('categories.index')
            ->with('status', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('product::categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['name'], $category->id);

        $category->update($data);

        return redirect()
            ->route('categories.index')
            ->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $isUsed = Product::query()
            ->withTrashed()
            ->whereHas('categories', fn ($query) => $query->whereKey($category->id))
            ->exists();

        if ($isUsed) {
            throw ValidationException::withMessages([
                'delete' => 'Kategori masih digunakan oleh produk dan tidak dapat dihapus.',
            ]);
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('status', 'Kategori berhasil dihapus.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $base = $base === '' ? 'kategori' : $base;
        $slug = $base;
        $counter = 2;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId): bool
    {
        return Category::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
}
