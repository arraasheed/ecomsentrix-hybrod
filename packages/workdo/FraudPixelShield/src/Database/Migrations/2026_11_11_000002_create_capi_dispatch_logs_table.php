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
        if (!Schema::hasTable('capi_dispatch_logs')) {
            Schema::create('capi_dispatch_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('store_id');
                $table->string('event_name')->default('Purchase');
                $table->string('status')->default('dispatched'); // dispatched, failed
                $table->text('response_payload')->nullable();
                $table->timestamps();

                $table->unique(['order_id', 'event_name']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capi_dispatch_logs');
    }
};
