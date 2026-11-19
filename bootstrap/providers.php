<?php

use App\Modules\Auth\Providers\AuthServiceProvider;
use App\Modules\Inventory\Providers\InventoryServiceProvider;
use App\Modules\Product\Providers\ProductServiceProvider;
use App\Modules\Reporting\Providers\ReportingServiceProvider;
use App\Modules\Supplier\Providers\SupplierServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    ProductServiceProvider::class,
    SupplierServiceProvider::class,
    InventoryServiceProvider::class,
    ReportingServiceProvider::class,
];
