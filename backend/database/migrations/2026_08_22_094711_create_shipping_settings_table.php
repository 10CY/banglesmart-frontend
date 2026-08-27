<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_settings', function (Blueprint $table) {
            $table->id();

            $table->decimal(
                'flat_shipping_amount',
                12,
                2
            )->default(0);

            $table->decimal(
                'free_shipping_minimum',
                12,
                2
            )->nullable();

            $table->boolean(
                'shipping_enabled'
            )->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'shipping_settings'
        );
    }
};