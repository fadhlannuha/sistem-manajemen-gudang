<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Controllers;

use App\Modules\Product\Http\Requests\StoreUnitRequest;
use App\Modules\Product\Http\Requests\UpdateUnitRequest;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(): View
    {
        $units = Unit::query()
            ->withCount('products')
            ->orderBy('name')
            ->paginate(15);

        return view('product::units.index', [
            'units' => $units,
        ]);
    }

    public function create(): View
    {
        return view('product::units.create');
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        Unit::query()->create($request->validated());

        return redirect()
            ->route('units.index')
            ->with('status', 'Satuan berhasil ditambahkan.');
    }

    public function edit(Unit $unit): View
    {
        return view('product::units.edit', [
            'unit' => $unit,
        ]);
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return redirect()
            ->route('units.index')
            ->with('status', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $isUsed = Product::query()->withTrashed()->where('unit_id', $unit->id)->exists();

        if ($isUsed) {
            throw ValidationException::withMessages([
                'delete' => 'Satuan masih digunakan oleh produk dan tidak dapat dihapus.',
            ]);
        }

        $unit->delete();

        return redirect()
            ->route('units.index')
            ->with('status', 'Satuan berhasil dihapus.');
    }
}
