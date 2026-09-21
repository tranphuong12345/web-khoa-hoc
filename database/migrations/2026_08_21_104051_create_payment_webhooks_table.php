<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_webhooks', function (Blueprint $table) {
            $table->id();

            $table->string('provider', 50);

            $table->string('event_type', 100)->nullable();

            $table->string('transaction_id', 150)->nullable();

            $table->string('reference_code', 150)->nullable();

            $table->decimal('amount', 15, 2)->nullable();

            // Laravel hỗ trợ JSON
            $table->json('payload');

            $table->string('signature', 500)->nullable();

            $table->enum('status', [
                'received',
                'processed',
                'failed',
                'ignored'
            ])->default('received');

            $table->dateTime('processed_at')->nullable();

            $table->timestamps();

            $table->index('provider');
            $table->index('transaction_id');
            $table->index('reference_code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhooks');
    }
};