<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->restrictOnDelete();

            $table->string('transaction_code', 150)->unique();

            $table->string('provider', 50)->default('bank_transfer');

            $table->enum('payment_method', [
                'bank_transfer',
                'vnpay',
                'momo',
                'zalopay'
            ])->default('bank_transfer');

            $table->decimal('amount', 15, 2);

            $table->string('currency', 10)->default('VND');

            $table->enum('status', [
                'pending',
                'processing',
                'success',
                'failed',
                'cancelled',
                'refunded'
            ])->default('pending');

            $table->string('provider_transaction_id', 150)->nullable();
            $table->string('provider_reference', 150)->nullable();

            $table->string('payment_content')->nullable();

            $table->dateTime('paid_at')->nullable();
            $table->dateTime('expired_at')->nullable();

            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
            $table->index('provider_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};