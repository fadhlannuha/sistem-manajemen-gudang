<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Database\Seeders;

use App\Modules\Supplier\Models\PaymentTerm;
use App\Modules\Supplier\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $paymentTerms = $this->seedPaymentTerms();

            foreach ($this->suppliers() as $data) {
                Supplier::query()->withTrashed()->updateOrCreate(
                    ['code' => $data['code']],
                    [
                        'name' => $data['name'],
                        'contact_name' => $data['contact_name'],
                        'phone' => $data['phone'],
                        'email' => $data['email'],
                        'address' => $data['address'],
                        'payment_term_id' => $paymentTerms[$data['payment_term']]->id,
                    ],
                );
            }
        });
    }

    /**
     * @return array<string, PaymentTerm>
     */
    private function seedPaymentTerms(): array
    {
        $definitions = [
            'Cash' => 0,
            'Net 7' => 7,
            'Net 14' => 14,
            'Net 30' => 30,
        ];

        $paymentTerms = [];

        foreach ($definitions as $name => $days) {
            $paymentTerms[$name] = PaymentTerm::query()->updateOrCreate(
                ['name' => $name],
                ['days' => $days],
            );
        }

        return $paymentTerms;
    }

    /**
     * @return array<int, array{code: string, name: string, contact_name: string, phone: string, email: string, address: string, payment_term: string}>
     */
    private function suppliers(): array
    {
        return [
            [
                'code' => 'SUP-001',
                'name' => 'PT Sumber Elektronik Nusantara',
                'contact_name' => 'Budi Santoso',
                'phone' => '+62 21 5550101',
                'email' => 'sales@sumberelektronik.test',
                'address' => 'Jl. Industri Raya No. 12, Jakarta Utara',
                'payment_term' => 'Net 30',
            ],
            [
                'code' => 'SUP-002',
                'name' => 'CV Kertas Prima',
                'contact_name' => 'Siti Rahayu',
                'phone' => '+62 22 5550202',
                'email' => 'order@kertasprima.test',
                'address' => 'Jl. Soekarno Hatta No. 88, Bandung',
                'payment_term' => 'Net 14',
            ],
            [
                'code' => 'SUP-003',
                'name' => 'PT Bersih Sejahtera',
                'contact_name' => 'Andi Wijaya',
                'phone' => '+62 31 5550303',
                'email' => 'kontak@bersihsejahtera.test',
                'address' => 'Jl. Rungkut Industri No. 5, Surabaya',
                'payment_term' => 'Net 7',
            ],
            [
                'code' => 'SUP-004',
                'name' => 'UD Sumber Pangan',
                'contact_name' => 'Rina Kartika',
                'phone' => '+62 24 5550404',
                'email' => 'rina@sumberpangan.test',
                'address' => 'Jl. Pandanaran No. 21, Semarang',
                'payment_term' => 'Cash',
            ],
            [
                'code' => 'SUP-005',
                'name' => 'CV Kemas Jaya',
                'contact_name' => 'Dedi Kurniawan',
                'phone' => '+62 21 5550505',
                'email' => 'sales@kemasjaya.test',
                'address' => 'Jl. Pergudangan Bandara Mas Blok C, Tangerang',
                'payment_term' => 'Net 30',
            ],
        ];
    }
}
