<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Listeners;

use App\Modules\Auth\Contracts\UserDirectoryContract;
use App\Modules\Auth\Events\UserRegistered;
use App\Modules\Inventory\Events\LowStockDetected;
use App\Modules\Inventory\Events\StockIssued;
use App\Modules\Inventory\Events\StockReceived;
use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeactivated;
use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Reporting\Models\ActivityLog;
use App\Modules\Supplier\Events\SupplierCreated;
use App\Modules\Supplier\Events\SupplierDeactivated;
use App\Modules\Supplier\Events\SupplierUpdated;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RecordActivityLog
{
    public function __construct(private readonly UserDirectoryContract $users) {}

    /**
     * Catat event domain ke activity_logs. Kegagalan logging tidak boleh
     * menggagalkan operasi utama, jadi seluruh proses dibungkus try/catch.
     */
    public function handle(object $event): void
    {
        try {
            $entry = $this->map($event);

            if ($entry === null) {
                return;
            }

            [$actorId, $actorName] = $this->resolveActor(
                $entry['actor_id'] ?? null,
                $entry['actor_name'] ?? null,
            );

            ActivityLog::create([
                'actor_id' => $actorId,
                'actor_name' => $actorName,
                'event' => $entry['event'],
                'subject_type' => $entry['subject_type'],
                'subject_id' => $entry['subject_id'],
                'description' => $entry['description'],
                'payload' => $entry['payload'],
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Gagal mencatat activity log.', [
                'event' => $event::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @return array{
     *     event: string,
     *     description: string,
     *     subject_type: string,
     *     subject_id: int,
     *     payload: array<string, mixed>,
     *     actor_id?: int,
     *     actor_name?: string
     * }|null
     */
    private function map(object $event): ?array
    {
        return match (true) {
            $event instanceof UserRegistered => [
                'event' => 'user.registered',
                'description' => "Pengguna {$event->name} ({$event->email}) mendaftar.",
                'subject_type' => 'user',
                'subject_id' => $event->userId,
                'payload' => [
                    'name' => $event->name,
                    'roles' => $event->roles,
                ],
                'actor_id' => $event->userId,
                'actor_name' => $event->name,
            ],
            $event instanceof ProductCreated => [
                'event' => 'product.created',
                'description' => "Menambahkan barang {$event->name} ({$event->sku}).",
                'subject_type' => 'product',
                'subject_id' => $event->productId,
                'payload' => [
                    'sku' => $event->sku,
                    'name' => $event->name,
                    'min_stock' => $event->minStock,
                ],
            ],
            $event instanceof ProductUpdated => [
                'event' => 'product.updated',
                'description' => "Memperbarui barang {$event->name} ({$event->sku}).",
                'subject_type' => 'product',
                'subject_id' => $event->productId,
                'payload' => [
                    'sku' => $event->sku,
                    'name' => $event->name,
                    'min_stock' => $event->minStock,
                ],
            ],
            $event instanceof ProductDeactivated => [
                'event' => 'product.deactivated',
                'description' => "Menonaktifkan barang {$event->name} ({$event->sku}).",
                'subject_type' => 'product',
                'subject_id' => $event->productId,
                'payload' => [
                    'sku' => $event->sku,
                    'name' => $event->name,
                ],
            ],
            $event instanceof SupplierCreated => [
                'event' => 'supplier.created',
                'description' => "Menambahkan pemasok {$event->name} ({$event->code}).",
                'subject_type' => 'supplier',
                'subject_id' => $event->supplierId,
                'payload' => [
                    'code' => $event->code,
                    'name' => $event->name,
                    'payment_term_id' => $event->paymentTermId,
                ],
            ],
            $event instanceof SupplierUpdated => [
                'event' => 'supplier.updated',
                'description' => "Memperbarui pemasok {$event->name} ({$event->code}).",
                'subject_type' => 'supplier',
                'subject_id' => $event->supplierId,
                'payload' => [
                    'code' => $event->code,
                    'name' => $event->name,
                    'payment_term_id' => $event->paymentTermId,
                ],
            ],
            $event instanceof SupplierDeactivated => [
                'event' => 'supplier.deactivated',
                'description' => "Menonaktifkan pemasok {$event->name} ({$event->code}).",
                'subject_type' => 'supplier',
                'subject_id' => $event->supplierId,
                'payload' => [
                    'code' => $event->code,
                    'name' => $event->name,
                ],
            ],
            $event instanceof StockReceived => [
                'event' => 'stock.received',
                'description' => "Menerima stok {$event->number} sebanyak {$this->itemCount($event->items)} baris barang.",
                'subject_type' => 'stock_receipt',
                'subject_id' => $event->receiptId,
                'payload' => [
                    'number' => $event->number,
                    'supplier_id' => $event->supplierId,
                    'item_count' => $this->itemCount($event->items),
                    'total_qty' => $this->totalQty($event->items),
                    'received_at' => $event->receivedAt,
                ],
                'actor_id' => $event->actorId,
            ],
            $event instanceof StockIssued => [
                'event' => 'stock.issued',
                'description' => "Mengeluarkan stok {$event->number} sebanyak {$this->itemCount($event->items)} baris barang.",
                'subject_type' => 'stock_issue',
                'subject_id' => $event->issueId,
                'payload' => [
                    'number' => $event->number,
                    'item_count' => $this->itemCount($event->items),
                    'total_qty' => $this->totalQty($event->items),
                    'issued_at' => $event->issuedAt,
                ],
                'actor_id' => $event->actorId,
            ],
            $event instanceof LowStockDetected => [
                'event' => 'stock.low',
                'description' => "Stok {$event->productName} menipis: {$this->formatQty($event->currentQty)} dari minimum {$this->formatQty($event->minStock)}.",
                'subject_type' => 'product',
                'subject_id' => $event->productId,
                'payload' => [
                    'product_name' => $event->productName,
                    'current_qty' => $event->currentQty,
                    'min_stock' => $event->minStock,
                    'detected_at' => $event->detectedAt,
                ],
            ],
            default => null,
        };
    }

    /**
     * @return array{0: int|null, 1: string|null}
     */
    private function resolveActor(?int $actorId, ?string $actorName): array
    {
        if ($actorId === null) {
            $currentId = auth()->id();
            $actorId = $currentId === null ? null : (int) $currentId;
            $actorName ??= auth()->user()?->name;
        }

        if ($actorId !== null && $actorName === null) {
            $actorName = $this->users->find($actorId)?->name;
        }

        return [$actorId, $actorName];
    }

    /**
     * @param  array<int, array{product_id: int, qty: float}>  $items
     */
    private function itemCount(array $items): int
    {
        return count($items);
    }

    /**
     * @param  array<int, array{product_id: int, qty: float}>  $items
     */
    private function totalQty(array $items): float
    {
        return array_sum(array_map(
            static fn (array $item): float => (float) $item['qty'],
            $items,
        ));
    }

    private function formatQty(float $qty): string
    {
        return number_format($qty, 3, ',', '.');
    }
}
