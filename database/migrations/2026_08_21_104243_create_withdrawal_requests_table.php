<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wallet_id')
                ->constrained('seller_wallets')
                ->restrictOnDelete();

            $table->foreignId('seller_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->decimal('amount', 15, 2);

            /*
             * Snapshot thông tin ngân hàng
             * tại thời điểm Seller yêu cầu rút.
             */
            $table->string('bank_name');
            $table->string('bank_account_number');
            $table->string('account_holder_name');

            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'rejected',
                'cancelled',
                'failed'
            ])->default('pending');

            $table->string('transfer_reference', 150)->nullable();

            $table->text('admin_note')->nullable();

            $table->dateTime('requested_at')->useCurrent();
            $table->dateTime('processed_at')->nullable();
            $table->dateTime('completed_at')->nullable();

            $table->index('wallet_id');
            $table->index('seller_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};