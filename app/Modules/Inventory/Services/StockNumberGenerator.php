<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use Carbon\CarbonInterface;

final class StockNumberGenerator
{
    public function forReceipt(int $id, CarbonInterface $date): string
    {
        return sprintf('SR-%s-%06d', $date->format('Ym'), $id);
    }

    public function forIssue(int $id, CarbonInterface $date): string
    {
        return sprintf('SI-%s-%06d', $date->format('Ym'), $id);
    }
}
