<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_wallets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seller_id')
                ->unique()
                ->constrained('users')
                ->restrictOnDelete();

            $table->decimal('available_balance', 15, 2)->default(0);

            $table->decimal('pending_balance', 15, 2)->default(0);

            $table->decimal('total_earned', 15, 2)->default(0);

            $table->decimal('total_withdrawn', 15, 2)->default(0);

            $table->string('currency', 10)->default('VND');

            $table->enum('status', [
                'active',
                'locked'
            ])->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_wallets');
    }
};