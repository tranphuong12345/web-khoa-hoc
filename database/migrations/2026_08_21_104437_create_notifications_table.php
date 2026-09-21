<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('title');

            $table->text('content');

            $table->enum('type', [
                'course_approved',
                'course_rejected',
                'order',
                'payment',
                'withdrawal',
                'wallet',
                'system'
            ]);

            $table->unsignedBigInteger('reference_id')
                ->nullable();

            $table->tinyInteger('is_read')
                ->default(0);

            $table->timestamps();

            $table->index('user_id');
            $table->index([
                'user_id',
                'is_read'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};