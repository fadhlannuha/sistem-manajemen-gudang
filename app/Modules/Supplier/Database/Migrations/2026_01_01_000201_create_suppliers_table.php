<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 150);
            $table->string('contact_name', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->foreignId('payment_term_id')->constrained('payment_terms')->restrictOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('deleted_at');
            $table->index('name');
            $table->index('payment_term_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
