<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class SupplierUnavailableException extends RuntimeException
{
    public function __construct(public readonly int $supplierId)
    {
        parent::__construct("Pemasok dengan ID {$supplierId} tidak ditemukan atau sudah tidak aktif.");
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'code' => 'SUPPLIER_UNAVAILABLE',
            ], 422);
        }

        return back()
            ->withInput()
            ->withErrors(['supplier_id' => $this->getMessage()])
            ->with('error', $this->getMessage());
    }
}
