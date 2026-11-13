<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('central_pixel_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index();
            $table->string('email', 255)->nullable()->index();
            $table->string('ip', 45)->nullable()->index();
            $table->unsignedBigInteger('store_id')->nullable()->index(); // which store triggered the block
            $table->string('event_type', 50)->default('Purchase'); // Purchase, AddToCart, etc.
            $table->string('reason', 100)->default('fraud'); // fraud, return, chargeback, manual
            $table->string('source', 50)->nullable(); // which fraud check source triggered it
            $table->integer('delivery_rate')->nullable(); // from fraud check
            $table->json('pixel_data')->nullable(); // hashed PII sent to CAPI
            $table->json('context')->nullable(); // order_id, order_total, items, etc.
            $table->timestamp('blocked_at')->index();
            $table->timestamps();

            // Composite index for checking if already blocked
            $table->index(['phone', 'event_type', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('central_pixel_blocks');
    }
};