<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone')->unique()->nullable();

            $table->string('avatar')->nullable();
            $table->text('address')->nullable();

            $table->enum('gender', [
                'male',
                'female',
                'other'
            ])->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('password');

            $table->enum('role', [
                'admin',
                'seller',
                'student'
            ])->default('student');

            $table->tinyInteger('status')->default(1);

            // Thông tin ngân hàng của Seller
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_holder_name')->nullable();

            $table->rememberToken();

            $table->timestamps();

            $table->index('role');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};