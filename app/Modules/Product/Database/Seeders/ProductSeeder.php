<?php

declare(strict_types=1);

namespace App\Modules\Product\Database\Seeders;

use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $categories = $this->seedCategories();
            $units = $this->seedUnits();

            foreach ($this->products() as $data) {
                $categoryIds = collect($data['categories'])
                    ->map(fn (string $name): int => $categories[$name]->id)
                    ->all();

                $product = Product::query()->withTrashed()->updateOrCreate(
                    ['sku' => $data['sku']],
                    [
                        'name' => $data['name'],
                        'description' => $data['description'] ?? null,
                        'unit_id' => $units[$data['unit']]->id,
                        'min_stock' => $data['min_stock'],
                    ],
                );

                if ($product->trashed()) {
                    $product->restore();
                }

                $product->categories()->sync($categoryIds);
            }
        });
    }

    /**
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $definitions = [
            'Elektronik' => 'Perangkat dan aksesori elektronik.',
            'ATK' => 'Alat tulis dan kebutuhan administrasi kantor.',
            'Kebersihan' => 'Perlengkapan kebersihan dan sanitasi.',
            'Konsumsi' => 'Makanan dan minuman untuk kebutuhan kantor.',
            'Aksesori' => 'Aksesori pendukung operasional gudang.',
        ];

        $categories = [];

        foreach ($definitions as $name => $description) {
            $categories[$name] = Category::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description],
            );
        }

        return $categories;
    }

    /**
     * @return array<string, Unit>
     */
    private function seedUnits(): array
    {
        $definitions = [
            'PCS' => 'Piece',
            'BOX' => 'Box',
            'KG' => 'Kilogram',
            'L' => 'Liter',
            'ROLL' => 'Roll',
        ];

        $units = [];

        foreach ($definitions as $code => $name) {
            $units[$code] = Unit::query()->updateOrCreate(
                ['code' => $code],
                ['name' => $name],
            );
        }

        return $units;
    }

    /**
     * @return array<int, array{sku: string, name: string, unit: string, min_stock: float, categories: array<int, string>, description?: string}>
     */
    private function products(): array
    {
        return [
            ['sku' => 'PRD-0001', 'name' => 'Kertas HVS A4 80gsm', 'unit' => 'PCS', 'min_stock' => 20, 'categories' => ['ATK'], 'description' => 'Kertas putih untuk kebutuhan cetak dan fotokopi.'],
            ['sku' => 'PRD-0002', 'name' => 'Pulpen Gel Hitam', 'unit' => 'PCS', 'min_stock' => 30, 'categories' => ['ATK']],
            ['sku' => 'PRD-0003', 'name' => 'Spidol Whiteboard', 'unit' => 'PCS', 'min_stock' => 12, 'categories' => ['ATK']],
            ['sku' => 'PRD-0004', 'name' => 'Map Plastik F4', 'unit' => 'PCS', 'min_stock' => 15, 'categories' => ['ATK']],
            ['sku' => 'PRD-0005', 'name' => 'Tinta Printer Hitam', 'unit' => 'PCS', 'min_stock' => 5, 'categories' => ['ATK', 'Elektronik']],
            ['sku' => 'PRD-0006', 'name' => 'Kabel HDMI 2 Meter', 'unit' => 'PCS', 'min_stock' => 4, 'categories' => ['Elektronik']],
            ['sku' => 'PRD-0007', 'name' => 'Mouse Wireless', 'unit' => 'PCS', 'min_stock' => 5, 'categories' => ['Elektronik']],
            ['sku' => 'PRD-0008', 'name' => 'Keyboard USB', 'unit' => 'PCS', 'min_stock' => 5, 'categories' => ['Elektronik']],
            ['sku' => 'PRD-0009', 'name' => 'Flashdisk 64GB', 'unit' => 'PCS', 'min_stock' => 6, 'categories' => ['Elektronik', 'Aksesori']],
            ['sku' => 'PRD-0010', 'name' => 'Power Bank 10000mAh', 'unit' => 'PCS', 'min_stock' => 3, 'categories' => ['Elektronik', 'Aksesori']],
            ['sku' => 'PRD-0011', 'name' => 'Sabun Cuci Piring 1 Liter', 'unit' => 'L', 'min_stock' => 10, 'categories' => ['Kebersihan']],
            ['sku' => 'PRD-0012', 'name' => 'Pembersih Lantai 1 Liter', 'unit' => 'L', 'min_stock' => 10, 'categories' => ['Kebersihan']],
            ['sku' => 'PRD-0013', 'name' => 'Tisu Gulung', 'unit' => 'ROLL', 'min_stock' => 12, 'categories' => ['Kebersihan']],
            ['sku' => 'PRD-0014', 'name' => 'Kantong Sampah Hitam', 'unit' => 'ROLL', 'min_stock' => 8, 'categories' => ['Kebersihan']],
            ['sku' => 'PRD-0015', 'name' => 'Kopi Bubuk Premium', 'unit' => 'BOX', 'min_stock' => 6, 'categories' => ['Konsumsi']],
            ['sku' => 'PRD-0016', 'name' => 'Teh Celup 25 Kantung', 'unit' => 'BOX', 'min_stock' => 6, 'categories' => ['Konsumsi']],
            ['sku' => 'PRD-0017', 'name' => 'Gula Pasir', 'unit' => 'KG', 'min_stock' => 5, 'categories' => ['Konsumsi']],
            ['sku' => 'PRD-0018', 'name' => 'Air Mineral 600ml', 'unit' => 'BOX', 'min_stock' => 10, 'categories' => ['Konsumsi']],
            ['sku' => 'PRD-0019', 'name' => 'Karton Box 40x30x30 cm', 'unit' => 'PCS', 'min_stock' => 25, 'categories' => ['Aksesori']],
            ['sku' => 'PRD-0020', 'name' => 'Lakban Bening', 'unit' => 'ROLL', 'min_stock' => 10, 'categories' => ['Aksesori']],
        ];
    }
}
