<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Inventory\Services\StockNumberGenerator;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class StockNumberGeneratorTest extends TestCase
{
    public function test_receipt_number(): void
    {
        $generator = new StockNumberGenerator;

        $this->assertSame(
            'SR-202601-000001',
            $generator->forReceipt(1, Carbon::parse('2026-01-05')),
        );
    }

    public function test_issue_number(): void
    {
        $generator = new StockNumberGenerator;

        $this->assertSame(
            'SI-202601-000001',
            $generator->forIssue(1, Carbon::parse('2026-01-06')),
        );
    }

    public function test_numbers_use_document_month_and_padded_id(): void
    {
        $generator = new StockNumberGenerator;

        $this->assertSame('SR-202612-000042', $generator->forReceipt(42, Carbon::parse('2026-12-31')));
        $this->assertSame('SI-202607-000007', $generator->forIssue(7, Carbon::parse('2026-07-01')));
    }
}
