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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('delivered_status');
            }
            if (!Schema::hasColumn('orders', 'purchase_price')) {
                $table->decimal('purchase_price', 15, 2)->default(0)->after('final_price');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'purchase_price')) {
                $table->decimal('purchase_price', 15, 2)->default(0)->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_read', 'purchase_price']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['purchase_price']);
        });
    }
};
