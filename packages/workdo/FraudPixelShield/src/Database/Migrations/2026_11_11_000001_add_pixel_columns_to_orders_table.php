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
        Schema::table('orders', function (Blueprint $builder) {
            if (!Schema::hasColumn('orders', 'pixel_status')) {
                $builder->string('pixel_status')->default('hold')->after('product_order_status');
            }
            if (!Schema::hasColumn('orders', 'capi_status')) {
                $builder->string('capi_status')->default('pending')->after('pixel_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $builder) {
            $builder->dropColumn(['pixel_status', 'capi_status']);
        });
    }
};
