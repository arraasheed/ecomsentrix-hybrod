<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fraud_check_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('phone', 20);
            $table->json('courier_details')->nullable();
            $table->unsignedInteger('total_orders')->default(0);
            $table->unsignedInteger('total_delivery')->default(0);
            $table->unsignedInteger('total_cancel')->default(0);
            $table->unsignedInteger('success_rate')->default(0);
            $table->enum('risk_status', ['safe', 'medium', 'high'])->default('safe');
            $table->boolean('is_blocked')->default(false);
            $table->string('check_type', 50)->nullable(); // bd_courier, elitemart, fraudchecker
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['store_id', 'phone']);
            $table->index(['store_id', 'created_at']);
        });

        Schema::create('fraud_lead_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('phone', 20);
            $table->string('name')->nullable();
            $table->text('address')->nullable();
            $table->decimal('cart_total', 12, 2)->default(0);
            $table->unsignedInteger('success_rate')->default(0);
            $table->json('courier_details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->enum('status', ['pending', 'contacted', 'converted', 'rejected'])->default('pending');
            $table->timestamps();

            $table->index(['store_id', 'phone']);
            $table->index(['store_id', 'status', 'created_at']);
        });

        Schema::create('fraud_api_keys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Super admin who added the key
            $table->unsignedBigInteger('store_id')->nullable(); // null = global pool, specific = store-specific
            $table->string('courier_name', 50); // 'BD Courier', 'EliteMart', 'FraudChecker'
            $table->text('key_value'); // API key / credentials
            $table->unsignedInteger('daily_limit')->default(50);
            $table->unsignedInteger('usage_count')->default(0);
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->timestamp('last_reset_time')->nullable();
            $table->timestamps();

            $table->index(['courier_name', 'status']);
            $table->index(['store_id', 'courier_name']);
        });

        Schema::create('fraud_api_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('courier_name', 50);
            $table->unsignedBigInteger('api_key_id')->nullable();
            $table->date('date');
            $table->unsignedInteger('total_calls')->default(0);
            $table->unsignedInteger('successful_calls')->default(0);
            $table->unsignedInteger('failed_calls')->default(0);
            $table->unsignedInteger('avg_response_time_ms')->default(0);
            $table->timestamp('last_called_at')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'courier_name', 'api_key_id', 'date']);
            $table->index(['store_id', 'courier_name', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fraud_api_usage_logs');
        Schema::dropIfExists('fraud_api_keys');
        Schema::dropIfExists('fraud_lead_logs');
        Schema::dropIfExists('fraud_check_logs');
    }
};