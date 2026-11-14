<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('funnels')) {
            Schema::create('funnels', function (Blueprint $table) {
                $table->id();
                $table->integer('store_id');
                $table->integer('product_id');
                $table->string('slug');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('video_url')->nullable();
                $table->dateTime('countdown_end')->nullable();
                $table->text('review_gallery')->nullable(); // JSON string
                $table->string('status')->default('active');
                $table->string('theme_color')->default('#4f46e5');
                $table->timestamps();

                $table->unique(['store_id', 'slug']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('funnels');
    }
};
