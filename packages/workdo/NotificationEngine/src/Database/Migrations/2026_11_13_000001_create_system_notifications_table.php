<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('sender_type', ['super_admin', 'store_admin', 'system']);
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->enum('recipient_type', ['all_stores', 'store_admin', 'customer']);
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['order', 'billing', 'announcement', 'alert']);
            $table->string('target_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['recipient_type', 'recipient_id', 'is_read']);
            $table->index(['sender_type', 'sender_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
    }
};
