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
        Schema::create('combos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->decimal('regular_price', 12, 2)->nullable();
            $table->decimal('offered_price', 12, 2);
            $table->text('image_path')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->enum('stock_status', ['available', 'out_of_stock', 'low_stock'])->default('available');
            $table->timestamps();
            $table->softDeletes();
            // $table->uuid('tenant_id')->nullable();
            // $table->string('name', 255)->index();
            // $table->string('slug', 255)->unique();
            // $table->uuid('combo_category_id')->nullable();
            // $table->foreignId('combo_category_id')->references('id')->on('categories')->onDelete('cascade');
            // $table->decimal('regular_price', 12, 2)->nullable();
            // $table->decimal('offered_price', 12, 2);
            // $table->text('image_path')->nullable();
            // $table->text('description')->nullable();
            // $table->unsignedSmallInteger('sort_order')->default(0)->index();
            // $table->string('meta_title', 100)->nullable();
            // $table->text('meta_description')->nullable();
            // $table->text('meta_keywords')->nullable();
            // $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            // $table->dateTime('start_date')->nullable();
            // $table->dateTime('end_date')->nullable();
            // $table->enum('stock_status', ['available', 'out_of_stock', 'low_stock'])->default('available');
            // $table->boolean('status')->default(true)->index();
            // $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            // $table->timestamps();
            // $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combos');
    }
};
