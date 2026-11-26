<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 30)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('min_stock', 15, 3)->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index('deleted_at');
            $table->index('name');
            $table->index('unit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
