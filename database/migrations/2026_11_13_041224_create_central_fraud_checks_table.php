<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('central_fraud_checks', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index();
            $table->integer('total_parcels')->default(0);
            $table->integer('delivered')->default(0);
            $table->integer('returned')->default(0);
            $table->integer('delivery_rate')->default(0); // percentage
            $table->string('risk_level', 20)->default('unknown'); // safe / medium / high / unknown
            $table->string('source', 50)->nullable(); // which source provided the best data
            $table->unsignedBigInteger('store_id')->nullable()->index(); // which store triggered the check (null = manual super admin check)
            $table->json('raw')->nullable(); // full raw response from all sources for audit
            $table->timestamp('checked_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('central_fraud_checks');
    }
};
