<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class SupplierUpdated implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public int $supplierId,
        public string $code,
        public string $name,
        public int $paymentTermId,
    ) {}
}
