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
        Schema::create('items', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->string('name', 255)->index();
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->string('meta_title', 100)->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('status')->default(true)->index();
            $table->string('item_type', 50)->index();
            $table->uuid('itemable_id')->nullable();
            $table->string('itemable_type')->nullable();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
