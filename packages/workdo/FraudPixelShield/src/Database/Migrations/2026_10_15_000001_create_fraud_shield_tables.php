<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Fraud Shield & Lead Tracking.
     */
    public function up(): void
    {
        // 1. Logs of all courier verification checks (Live checkout + manual admin)
        if (!Schema::hasTable('fraud_check_logs')) {
            Schema::create('fraud_check_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->nullable()->index();
                $table->string('phone', 20)->index();
                $table->text('courier_details')->nullable();
                $table->integer('total_orders')->default(0);
                $table->integer('total_delivery')->default(0);
                $table->integer('total_cancel')->default(0);
                $table->integer('success_rate')->default(0); // 0 - 100 percentage
                $table->string('risk_status', 30)->default('safe'); // safe, moderate, high_risk
                $table->boolean('is_blocked')->default(false);
                $table->string('check_type', 30)->default('live_checkout'); // live_checkout, manual_admin
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
            });
        }

        // 2. Leads captured from blocked checkout attempts (so merchant never loses contact)
        if (!Schema::hasTable('fraud_lead_logs')) {
            Schema::create('fraud_lead_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->nullable()->index();
                $table->string('phone', 20)->index();
                $table->string('name')->nullable();
                $table->text('address')->nullable();
                $table->decimal('cart_total', 10, 2)->default(0.00);
                $table->integer('success_rate')->default(0);
                $table->text('courier_details')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('status', 40)->default('blocked_whatsapp_offered');
                $table->timestamps();
            });
        }

        // 3. Multi-account API keys pool for rotation (BD Courier, etc.)
        if (!Schema::hasTable('fraud_api_keys')) {
            Schema::create('fraud_api_keys', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('store_id')->nullable()->index();
                $table->string('courier_name', 50)->default('BD Courier');
                $table->string('key_value', 255);
                $table->integer('daily_limit')->default(50);
                $table->integer('usage_count')->default(0);
                $table->string('status', 20)->default('active');
                $table->timestamp('last_reset_time')->nullable();
                $table->timestamps();
            });
        }

        // 4. API Usage Logs (Aggregated per day per key/store)
        if (!Schema::hasTable('fraud_api_usage_logs')) {
            Schema::create('fraud_api_usage_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->nullable()->index();
                $table->unsignedBigInteger('api_key_id')->nullable()->index();
                $table->string('courier_name', 50)->default('BD Courier');
                $table->date('date')->index();
                $table->integer('total_calls')->default(0);
                $table->integer('successful_calls')->default(0);
                $table->integer('failed_calls')->default(0);
                $table->integer('avg_response_time_ms')->default(0);
                $table->timestamp('last_called_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fraud_api_usage_logs');
        Schema::dropIfExists('fraud_lead_logs');
        Schema::dropIfExists('fraud_check_logs');
        Schema::dropIfExists('fraud_api_keys');
    }
};
