<?php

declare(strict_types=1);

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
