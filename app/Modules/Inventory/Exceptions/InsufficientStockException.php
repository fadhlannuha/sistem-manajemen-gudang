<?php

declare(strict_types=1);

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
