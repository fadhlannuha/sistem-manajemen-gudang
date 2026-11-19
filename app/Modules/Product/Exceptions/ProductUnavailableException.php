<?php

declare(strict_types=1);

namespace App\Modules\Product\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

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

        return back()
            ->withInput()
            ->withErrors(['items' => $this->getMessage()])
            ->with('error', $this->getMessage());
    }
}
