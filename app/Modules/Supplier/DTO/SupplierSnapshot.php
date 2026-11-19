<?php

declare(strict_types=1);

namespace App\Modules\Supplier\DTO;

final readonly class SupplierSnapshot
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public int $paymentTermId,
        public string $paymentTermName,
        public int $paymentTermDays,
    ) {}
}
