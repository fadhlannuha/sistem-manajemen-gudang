<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Inventory\Exceptions\InsufficientStockException;
use App\Modules\Product\Exceptions\ProductUnavailableException;
use App\Modules\Supplier\Exceptions\SupplierUnavailableException;
use PHPUnit\Framework\TestCase;

class DomainExceptionTest extends TestCase
{
    public function test_insufficient_stock_message(): void
    {
        $exception = new InsufficientStockException(
            productId: 7,
            productName: 'Kertas HVS',
            requested: 10,
            available: 5,
        );

        $this->assertStringContainsString('Kertas HVS', $exception->getMessage());
        $this->assertStringContainsString('5,000', $exception->getMessage());
        $this->assertStringContainsString('10,000', $exception->getMessage());
        $this->assertSame(7, $exception->productId);
        $this->assertSame(10.0, $exception->requested);
        $this->assertSame(5.0, $exception->available);
    }

    public function test_product_unavailable_message(): void
    {
        $exception = new ProductUnavailableException(99);

        $this->assertStringContainsString('99', $exception->getMessage());
        $this->assertSame(99, $exception->productId);
    }

    public function test_supplier_unavailable_message(): void
    {
        $exception = new SupplierUnavailableException(12);

        $this->assertStringContainsString('12', $exception->getMessage());
        $this->assertSame(12, $exception->supplierId);
    }
}
