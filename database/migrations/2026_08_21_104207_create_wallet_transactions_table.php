<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wallet_id')
                ->constrained('seller_wallets')
                ->restrictOnDelete();

            $table->foreignId('seller_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('transaction_type', [
                'course_sale',
                'withdrawal',
                'refund',
                'adjustment'
            ]);

            $table->string('reference_type', 50)->nullable();

            $table->unsignedBigInteger('reference_id')->nullable();

            /*
             * Dương = tiền vào ví
             * Âm = tiền ra ví
             */
            $table->decimal('amount', 15, 2);

            $table->decimal('balance_before', 15, 2);

            $table->decimal('balance_after', 15, 2);

            $table->string('description')->nullable();

            $table->enum('status', [
                'pending',
                'completed',
                'failed',
                'reversed'
            ])->default('completed');

            $table->timestamps();

            $table->index('wallet_id');
            $table->index('seller_id');
            $table->index('transaction_type');
            $table->index([
                'reference_type',
                'reference_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};