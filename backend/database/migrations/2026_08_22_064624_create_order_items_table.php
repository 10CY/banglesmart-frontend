<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table->foreignId('product_variant_id')
                ->nullable()
                ->constrained('product_variants')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Product snapshots
            |--------------------------------------------------------------------------
            */

            $table->string('product_name');

            $table->string('variant_sku');

            $table->string('size_name')
                ->nullable();

            $table->string('color_name')
                ->nullable();

            $table->string('image')
                ->nullable();

            $table->decimal('mrp', 12, 2);

            $table->decimal('price', 12, 2);

            $table->unsignedInteger('quantity');

            $table->decimal('line_total', 12, 2);

            $table->timestamps();

            $table->index('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};