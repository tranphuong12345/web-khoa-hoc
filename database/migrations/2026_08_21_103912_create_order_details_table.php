<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->restrictOnDelete();

            $table->foreignId('seller_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Snapshot tại thời điểm mua
            $table->string('course_name');

            $table->decimal('original_price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->default(0);

            $table->decimal('commission_rate', 5, 2)->default(10);

            $table->decimal('commission_amount', 15, 2)->default(0);

            $table->decimal('seller_amount', 15, 2)->default(0);

            $table->enum('seller_amount_status', [
                'pending',
                'available',
                'withdrawn',
                'refunded'
            ])->default('pending');

            $table->timestamps();

            $table->index('order_id');
            $table->index('course_id');
            $table->index('seller_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};