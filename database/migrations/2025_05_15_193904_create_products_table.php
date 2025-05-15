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
        Schema::create('products', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('subcategory_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('sub_subcategory_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->string('sku', 100)->unique();
            $table->enum('shipping_charge', ['free', 'paid'])->default('paid');
            $table->timestamps();
            $table->softDeletes();
            // $table->uuid('tenant_id')->nullable();
            // $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            // $table->foreignId('subcategory_id')->nullable()->constrained('categories')->onDelete('set null');
            // $table->foreignId('sub_subcategory_id')->nullable()->constrained('categories')->onDelete('set null');
            // $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            // $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            // $table->string('product_name', 255)->index();
            // $table->string('slug', 255)->unique();
            // $table->string('sku', 100)->unique();
            // $table->text('description')->nullable();
            // $table->unsignedSmallInteger('sort_order')->default(0)->index();
            // $table->string('meta_title', 100)->nullable();
            // $table->text('meta_description')->nullable();
            // $table->text('meta_keywords')->nullable();
            // $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            // $table->enum('shipping_charge', ['free', 'paid'])->default('paid');
            // $table->boolean('status')->default(true)->index();
            // $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            // $table->timestamps();
            // $table->softDeletes();
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
