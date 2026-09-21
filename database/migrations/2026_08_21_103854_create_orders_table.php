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

            $table->foreignId('buyer_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->decimal('total_amount', 15, 2)->default(0);

            $table->string('currency', 10)->default('VND');

            $table->enum('status', [
                'pending',
                'paid',
                'cancelled',
                'refunded',
                'partially_refunded'
            ])->default('pending');

            $table->text('note')->nullable();

            $table->dateTime('paid_at')->nullable();

            $table->timestamps();

            $table->index('buyer_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};