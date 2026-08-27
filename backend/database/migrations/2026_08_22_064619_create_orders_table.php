<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('order_number')
                ->unique();

            $table->string('status')
                ->default('pending');

            $table->string('payment_method')
                ->default('cod');

            $table->string('payment_status')
                ->default('pending');

            $table->decimal('subtotal', 12, 2)
                ->default(0);

            $table->decimal('shipping_amount', 12, 2)
                ->default(0);

            $table->decimal('discount_amount', 12, 2)
                ->default(0);

            $table->decimal('total_amount', 12, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Address snapshots
            |--------------------------------------------------------------------------
            |
            | We intentionally store copies of addresses here.
            | If a customer changes their address later, old orders remain correct.
            |
            */

            $table->json('shipping_address');

            $table->json('billing_address')
                ->nullable();

            $table->text('customer_note')
                ->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'created_at',
            ]);

            $table->index([
                'status',
                'payment_status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};