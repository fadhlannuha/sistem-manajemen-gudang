<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Http\Requests;

class UpdatePaymentTermRequest extends StorePaymentTermRequest
{
    // Aturan unique memakai parameter route `payment_term` untuk mengabaikan data saat ini.
}
