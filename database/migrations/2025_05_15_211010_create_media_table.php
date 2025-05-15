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
        Schema::create('media', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->uuid('mediable_id');
            $table->string('mediable_type');
            $table->string('media_type', 50)->default('image')->comment('image, video, etc.');
            $table->string('path')->comment('Path to the media file');
            $table->string('alt_text', 255)->nullable();
            $table->boolean('is_primary')->default(false)->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index(['mediable_id', 'mediable_type']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
